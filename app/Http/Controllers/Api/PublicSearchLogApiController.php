<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicSearchLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * API Controller for Public Search Logs
 * Handles search analytics and logging for the public portal
 */
class PublicSearchLogApiController extends Controller
{
    // Message constants
    private const LOG_CREATED = 'Search logged successfully';
    private const LOG_NOT_FOUND = 'Search log not found';

    // Validation rule constants
    private const REQUIRED_STRING = 'required|string';
    private const NULLABLE_ARRAY = 'nullable|array';

    private const STORE_RULES = [
        'search_term' => 'required|string|max:255',
        'filters' => self::NULLABLE_ARRAY,
        'results_count' => 'required|integer|min:0',
    ];

    /**
     * Get search logs (admin only)
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = PublicSearchLog::with(['user']);

        // Filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('search_term', 'like', "%{$search}%");
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->get('date_to') . ' 23:59:59');
        }

        $query->orderBy('created_at', 'desc');

        $perPage = min($request->get('per_page', 50), 100);
        $logs = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => collect($logs->items())->map(function ($log) {
                return $this->transformSearchLog($log);
            })->toArray(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ]
        ]);
    }

    /**
     * Store new search log
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate(self::STORE_RULES);

        // Associate with authenticated user if available
        if ($request->user()) {
            $validated['user_id'] = $request->user()->id;
        }

        $log = PublicSearchLog::create($validated);

        return $this->successResponse(
            self::LOG_CREATED,
            $this->transformSearchLog($log),
            201
        );
    }

    /**
     * Get search statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $request->validate([
            'period' => 'nullable|in:day,week,month,year',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $period = $request->get('period', 'month');
        $limit = $request->get('limit', 20);

        // Get date range based on period
        $dateRange = $this->getDateRange($period);

        // Most popular search terms
        $popularTerms = PublicSearchLog::select('search_term', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', $dateRange)
            ->groupBy('search_term')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();

        // Search volume over time
        $searchVolume = PublicSearchLog::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as searches'),
                DB::raw('AVG(results_count) as avg_results')
            )
            ->whereBetween('created_at', $dateRange)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // No results searches (potential improvement areas)
        $noResultsSearches = PublicSearchLog::select('search_term', DB::raw('COUNT(*) as count'))
            ->where('results_count', 0)
            ->whereBetween('created_at', $dateRange)
            ->groupBy('search_term')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'date_range' => [
                    'from' => $dateRange[0]->format('Y-m-d'),
                    'to' => $dateRange[1]->format('Y-m-d'),
                ],
                'popular_terms' => $popularTerms->map(function ($item) {
                    return [
                        'term' => $item->search_term,
                        'count' => $item->count,
                    ];
                }),
                'search_volume' => $searchVolume->map(function ($item) {
                    return [
                        'date' => $item->date,
                        'searches' => $item->searches,
                        'avg_results' => round($item->avg_results, 2),
                    ];
                }),
                'no_results_searches' => $noResultsSearches->map(function ($item) {
                    return [
                        'term' => $item->search_term,
                        'count' => $item->count,
                    ];
                }),
                'total_searches' => PublicSearchLog::whereBetween('created_at', $dateRange)->count(),
                'unique_terms' => PublicSearchLog::whereBetween('created_at', $dateRange)
                    ->distinct('search_term')->count(),
            ]
        ]);
    }

    /**
     * Get user's search history
     */
    public function userHistory(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Authentication required', 401);
        }

        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $perPage = min($request->get('per_page', 20), 50);

        $logs = PublicSearchLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => collect($logs->items())->map(function ($log) {
                return $this->transformSearchLog($log, true); // User view
            })->toArray(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ]
        ]);
    }

    /**
     * Get date range for statistics
     */
    private function getDateRange(string $period): array
    {
        $end = now();

        $start = match ($period) {
            'day' => $end->copy()->subDay(),
            'week' => $end->copy()->subWeek(),
            'month' => $end->copy()->subMonth(),
            'year' => $end->copy()->subYear(),
            default => $end->copy()->subMonth(),
        };

        return [$start, $end];
    }

    /**
     * Transform search log data for API response
     */
    private function transformSearchLog($log, bool $userView = false): array
    {
        $data = [
            'id' => $log->id,
            'search_term' => $log->search_term,
            'filters' => $log->filters ?? [],
            'results_count' => $log->results_count,
            'created_at' => $log->created_at?->toISOString(),
            'formatted_created_at' => $log->created_at ?
                $log->created_at->format('d/m/Y H:i') : null,
        ];

        // Add user info for admin view
        if (!$userView && $log->user) {
            $data['user'] = [
                'id' => $log->user->id,
                'name' => $log->user->name,
                'email' => $log->user->email,
            ];
        }

        return $data;
    }

    /**
     * Success response helper
     */
    private function successResponse(string $message, $data = null, int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $status);
    }

    /**
     * Error response helper
     */
    private function errorResponse(string $message, int $status = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message
        ], $status);
    }
}
