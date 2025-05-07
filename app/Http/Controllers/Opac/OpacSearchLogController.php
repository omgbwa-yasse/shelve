<?php

namespace App\Http\Controllers\Opac;

use App\Http\Controllers\Controller;
use App\Models\PublicSearchLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OpacSearchLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $searchLogs = PublicSearchLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'message' => 'Search logs retrieved successfully',
            'data' => $searchLogs
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(PublicSearchLog $searchLog)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Search log details retrieved successfully',
            'data' => $searchLog->load('user')
        ], 200);
    }

    /**
     * Display search statistics.
     */
    public function statistics()
    {
        $stats = [
            'total_searches' => PublicSearchLog::count(),
            'searches_today' => PublicSearchLog::whereDate('created_at', today())->count(),
            'searches_this_week' => PublicSearchLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'searches_this_month' => PublicSearchLog::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->count(),
            'most_common_terms' => PublicSearchLog::select('search_term')
                ->selectRaw('count(*) as count')
                ->groupBy('search_term')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'search_results' => PublicSearchLog::select('results_count')
                ->selectRaw('count(*) as count')
                ->groupBy('results_count')
                ->orderBy('results_count')
                ->get(),
        ];

        return view('opac.search-logs.statistics', compact('stats'));
    }

    /**
     * Export search logs to CSV.
     */
    public function export()
    {
        $searchLogs = PublicSearchLog::with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="search-logs.csv"',
        ];

        $callback = function() use ($searchLogs) {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, ['ID', 'User', 'Search Term', 'Results Count', 'Filters', 'Created At']);

            // Add data
            foreach ($searchLogs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user->name,
                    $log->search_term,
                    $log->results_count,
                    json_encode($log->filters),
                    $log->created_at
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Clear old search logs.
     */
    public function clearOldLogs()
    {
        $date = now()->subMonths(3);
        PublicSearchLog::where('created_at', '<', $date)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Old search logs cleared successfully'
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|max:255',
            'filters' => 'nullable|array',
            'results_count' => 'required|integer|min:0',
            'user_id' => 'required|exists:users,id',
        ]);

        $searchLog = PublicSearchLog::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Search log created successfully',
            'data' => $searchLog
        ], 201);
    }

    public function destroy(PublicSearchLog $searchLog)
    {
        $searchLog->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Search log deleted successfully'
        ], 200);
    }
}
