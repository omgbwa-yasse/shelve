<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicRecord;
use App\Models\Record;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class PublicRecordApiController extends Controller
{
    /**
     * Get paginated records for frontend
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'language' => 'nullable|string|max:50',
            'publisher_id' => 'nullable|integer|exists:users,id',
            'per_page' => 'nullable|integer|min:1|max:50',
            'type' => 'nullable|string|max:100',
            'limit' => 'nullable|integer|min:1|max:50',
            'exclude' => 'nullable|integer'
        ]);

        $filters = $this->validateSearchFilters($request->only(['search', 'date_from', 'date_to', 'language', 'publisher_id', 'type']));
        $perPage = min($request->get('per_page', $request->get('limit', 10)), 50);
        $exclude = $request->get('exclude');

        $records = $this->getPaginatedRecords($filters, $perPage, $exclude);

        $transformedRecords = $records->getCollection()->map(function ($record) {
            return $this->transformRecordForApi($record);
        });

        return response()->json([
            'success' => true,
            'data' => $transformedRecords,
            'pagination' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
                'from' => $records->firstItem(),
                'to' => $records->lastItem(),
            ],
            'filters' => $this->getAvailableFilters()
        ]);
    }

    /**
     * Get single record details
     */
    public function show(PublicRecord $record): JsonResponse
    {
        if (!$this->isRecordAvailable($record)) {
            return response()->json([
                'success' => false,
                'message' => 'Record expired or not available'
            ], 404);
        }

        $record->load(['record', 'publisher']);

        if (!$record->record) {
            return response()->json([
                'success' => false,
                'message' => 'Associated record not found'
            ], 404);
        }

        $transformedRecord = $this->transformRecordForApi($record, true);

        return response()->json([
            'success' => true,
            'data' => $transformedRecord
        ]);
    }

    /**
     * Search records with advanced filtering
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|max:255',
            'filters' => 'array',
            'filters.date_from' => 'nullable|date',
            'filters.date_to' => 'nullable|date',
            'filters.language' => 'nullable|string',
            'filters.publisher_id' => 'nullable|integer|exists:users,id',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $searchTerm = $validated['query'];
        $filters = $validated['filters'] ?? [];
        $perPage = min($validated['per_page'] ?? 20, 50);

        $records = $this->searchRecords($searchTerm, $filters, $perPage);

        $transformedRecords = $records->getCollection()->map(function ($record) {
            return $this->transformRecordForApi($record);
        });

        return response()->json([
            'success' => true,
            'data' => $transformedRecords,
            'pagination' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
                'from' => $records->firstItem(),
                'to' => $records->lastItem(),
            ],
            'query' => $searchTerm,
            'filters' => $this->getAvailableFilters()
        ]);
    }

    /**
     * Get autocomplete suggestions for search
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $query = $request->get('q');
        $limit = min($request->get('limit', 10), 20);

        $suggestions = $this->getSearchSuggestions($query, $limit);

        return response()->json([
            'success' => true,
            'data' => $suggestions
        ]);
    }

    /**
     * Get statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->getStatistics();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get available filters
     */
    public function filters(): JsonResponse
    {
        $filters = $this->getAvailableFilters();

        return response()->json([
            'success' => true,
            'data' => $filters
        ]);
    }

    /**
     * Export records (placeholder)
     */
    public function export(Request $request): JsonResponse
    {
        // TODO: Implement export functionality
        return response()->json([
            'success' => false,
            'message' => 'Export functionality not yet implemented'
        ], 501);
    }

    // ==========================================
    // PRIVATE METHODS (previously in service)
    // ==========================================

    /**
     * Get paginated records with filters
     */
    private function getPaginatedRecords(array $filters = [], int $perPage = 10, ?int $exclude = null): LengthAwarePaginator
    {
        $query = PublicRecord::with(['record', 'publisher'])
            ->available();

        // Exclude specific record
        if ($exclude) {
            $query->where('id', '!=', $exclude);
        }

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->searchContent($filters['search']);
        }

        // Apply type filter
        if (!empty($filters['type'])) {
            $query->whereHas('record', function ($q) use ($filters) {
                $q->where('type', $filters['type']);
            });
        }

        // Apply date filters
        if (!empty($filters['date_from'])) {
            $query->where('published_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('published_at', '<=', $filters['date_to']);
        }

        // Apply language filter
        if (!empty($filters['language'])) {
            $query->whereHas('record', function ($q) use ($filters) {
                $q->where('language_material', 'like', "%{$filters['language']}%");
            });
        }

        // Apply publisher filter
        if (!empty($filters['publisher_id'])) {
            $query->where('published_by', $filters['publisher_id']);
        }

        return $query->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Search records with advanced filtering
     */
    private function searchRecords(string $searchTerm, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = PublicRecord::with(['record', 'publisher'])
            ->available()
            ->searchContent($searchTerm);

        // Apply additional filters
        foreach ($filters as $key => $value) {
            if (empty($value)) {
                continue;
            }

            switch ($key) {
                case 'date_from':
                    $query->where('published_at', '>=', $value);
                    break;
                case 'date_to':
                    $query->where('published_at', '<=', $value);
                    break;
                case 'language':
                    $query->whereHas('record', function ($q) use ($value) {
                        $q->where('language_material', 'like', "%{$value}%");
                    });
                    break;
                case 'publisher_id':
                    $query->where('published_by', $value);
                    break;
                default:
                    // Ignore unknown filter keys
                    break;
            }
        }

        return $query->orderBy('published_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get search suggestions
     */
    private function getSearchSuggestions(string $query, int $limit = 10): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        return PublicRecord::with('record')
            ->available()
            ->searchContent($query)
            ->limit($limit)
            ->get()
            ->map(function ($publicRecord) {
                return [
                    'id' => $publicRecord->id,
                    'title' => $publicRecord->title,
                    'reference' => $publicRecord->code,
                    'suggestion' => $publicRecord->title . ($publicRecord->code ? ' (' . $publicRecord->code . ')' : ''),
                    'type' => 'record'
                ];
            })
            ->toArray();
    }

    /**
     * Transform record for API response
     */
    private function transformRecordForApi(PublicRecord $record, bool $includeDetails = false): array
    {
        $data = [
            'id' => $record->id,
            'title' => $record->title,
            'code' => $record->code,
            'content' => $record->content,
            'description' => $record->content, // Alias pour le frontend
            'subtitle' => $record->publication_notes,
            'reference' => $record->code,
            'type' => $record->record->type ?? 'document',
            'date' => $record->date_exact ?? $record->date_start,
            'date_range' => $record->formatted_date_range,
            'formatted_date_range' => $record->formatted_date_range,
            'published_at' => $record->published_at,
            'expires_at' => $record->expires_at,
            'publication_notes' => $record->publication_notes,
            'is_available' => $record->is_available,
            'is_expired' => $record->is_expired,
            'digital_copy_available' => $record->is_available && !$record->is_expired,
            'created_at' => $record->created_at,
            'updated_at' => $record->updated_at,
            'thumbnail_url' => null, // TODO: Implement if needed
            'images' => [], // TODO: Implement if needed
            'attachments' => [], // TODO: Implement if needed
            'tags' => [], // TODO: Implement if needed
            'publisher' => $record->publisher ? [
                'id' => $record->publisher->id,
                'name' => $record->publisher->name,
            ] : null,
        ];

        if ($includeDetails && $record->record) {
            $data = array_merge($data, [
                'access_conditions' => $record->access_conditions,
                'classification' => $record->record->classification ?? null,
                'series' => $record->record->series ?? null,
                'location' => $record->record->location_original ?? null,
                'format' => $record->record->physical_format ?? null,
                'dimensions' => $record->record->dimensions ?? null,
                'language' => $record->language_material,
                'biographical_history' => $record->biographical_history,
                'archival_history' => $record->record->archival_history ?? '',
                'reproduction_conditions' => $record->record->reproduction_conditions ?? '',
                'characteristic' => $record->record->characteristic ?? '',
                'finding_aids' => $record->record->finding_aids ?? '',
                'related_unit' => $record->record->related_unit ?? '',
                'publication_note' => $record->record->publication_note ?? '',
                'note' => $record->record->note ?? '',
            ]);
        }

        return $data;
    }

    /**
     * Get statistics
     */
    private function getStatistics(): array
    {
        $total = PublicRecord::count();
        $available = PublicRecord::available()->count();
        $expired = PublicRecord::where('expires_at', '<=', now())->count();
        $publishedThisMonth = PublicRecord::where('published_at', '>=', now()->startOfMonth())->count();

        return [
            'total_records' => $total,
            'available_records' => $available,
            'expired_records' => $expired,
            'published_this_month' => $publishedThisMonth,
            'availability_rate' => $total > 0 ? round(($available / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get available filters
     */
    private function getAvailableFilters(): array
    {
        $languages = Record::whereNotNull('language_material')
            ->distinct('language_material')
            ->pluck('language_material')
            ->filter()
            ->sort()
            ->values();

        $publishers = User::whereIn('id',
            PublicRecord::distinct('published_by')->pluck('published_by')
        )->select('id', 'name')->get();

        $dateRange = PublicRecord::selectRaw('MIN(published_at) as min_date, MAX(published_at) as max_date')
            ->first();

        return [
            'languages' => $languages,
            'publishers' => $publishers,
            'date_range' => [
                'min' => $dateRange->min_date,
                'max' => $dateRange->max_date,
            ]
        ];
    }

    /**
     * Validate record availability
     */
    private function isRecordAvailable(PublicRecord $record): bool
    {
        return $record->is_available && !$record->is_expired;
    }

    /**
     * Validate search parameters
     */
    private function validateSearchFilters(array $filters): array
    {
        $validFilters = [];

        if (isset($filters['search']) && !empty($filters['search'])) {
            $validFilters['search'] = trim($filters['search']);
        }

        if (isset($filters['type']) && !empty($filters['type'])) {
            $validFilters['type'] = trim($filters['type']);
        }

        if (isset($filters['date_from']) && !empty($filters['date_from'])) {
            $validFilters['date_from'] = $filters['date_from'];
        }

        if (isset($filters['date_to']) && !empty($filters['date_to'])) {
            $validFilters['date_to'] = $filters['date_to'];
        }

        if (isset($filters['language']) && !empty($filters['language'])) {
            $validFilters['language'] = trim($filters['language']);
        }

        if (isset($filters['publisher_id']) && is_numeric($filters['publisher_id'])) {
            $validFilters['publisher_id'] = (int) $filters['publisher_id'];
        }

        return $validFilters;
    }
}
