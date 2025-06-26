<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicRecord;
use App\Services\PublicRecordService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PublicRecordApiController extends Controller
{
    protected PublicRecordService $publicRecordService;

    public function __construct(PublicRecordService $publicRecordService)
    {
        $this->publicRecordService = $publicRecordService;
    }

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
        ]);

        $filters = $request->only(['search', 'date_from', 'date_to', 'language', 'publisher_id']);
        $filters = $this->publicRecordService->validateSearchFilters($filters);
        $perPage = min($request->get('per_page', 10), 50);

        $records = $this->publicRecordService->getPaginatedRecords($filters, $perPage);

        $transformedRecords = $records->getCollection()->map(function ($record) {
            return $this->publicRecordService->transformRecordForApi($record);
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
            'filters' => $this->publicRecordService->getAvailableFilters()
        ]);
    }

    /**
     * Get single record details
     */
    public function show(PublicRecord $record): JsonResponse
    {
        if (!$this->publicRecordService->isRecordAvailable($record)) {
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

        $transformedRecord = $this->publicRecordService->transformRecordForApi($record, true);

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
        $filters = $this->publicRecordService->validateSearchFilters($filters);
        $perPage = $validated['per_page'] ?? 20;

        // Track the search term for analytics
        $this->publicRecordService->trackSearchTerm($searchTerm);

        $records = $this->publicRecordService->searchRecords($searchTerm, $filters, $perPage);

        $transformedRecords = $records->getCollection()->map(function ($record) {
            $data = $this->publicRecordService->transformRecordForApi($record);
            $data['relevance_excerpt'] = $this->publicRecordService->generateExcerpt(
                $record->content,
                $record->title
            );
            return $data;
        });

        return response()->json([
            'success' => true,
            'data' => $transformedRecords,
            'pagination' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
            ],
            'query' => $searchTerm,
            'filters' => $filters
        ]);
    }

    /**
     * Get search suggestions
     */
    public function suggestions(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'limit' => 'nullable|integer|min:1|max:20',
        ]);

        $query = $request->get('q');
        $limit = $request->get('limit', 10);

        $suggestions = $this->publicRecordService->getSearchSuggestions($query, $limit);

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    }

    /**
     * Get popular searches
     */
    public function popularSearches(): JsonResponse
    {
        $popularSearches = $this->publicRecordService->getPopularSearches();

        return response()->json([
            'success' => true,
            'popular_searches' => $popularSearches
        ]);
    }

    /**
     * Get statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->publicRecordService->getStatistics();

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    /**
     * Get available filters
     */
    public function filters(): JsonResponse
    {
        $filters = $this->publicRecordService->getAvailableFilters();

        return response()->json([
            'success' => true,
            'filters' => $filters
        ]);
    }

    /**
     * Export records (future implementation)
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'format' => 'nullable|string|in:csv,xlsx,pdf',
            'filters' => 'array',
        ]);

        $format = $request->get('format', 'csv');
        $filters = $request->get('filters', []);

        // For now, return a mock response indicating the export is queued
        // In production, this would queue a job to generate the export file
        return response()->json([
            'success' => true,
            'message' => 'Export request queued successfully. You will receive an email when ready.',
            'export_id' => uniqid('export_'),
            'format' => $format,
            'filters_applied' => count($filters),
            'estimated_time' => '5-10 minutes'
        ]);
    }

    /**
     * Export search results (future implementation)
     */
    public function exportSearch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'required|string|max:255',
            'format' => 'nullable|string|in:csv,xlsx,pdf',
            'filters' => 'array',
        ]);

        $query = $validated['query'];
        $format = $request->get('format', 'csv');
        $filters = $request->get('filters', []);

        // For now, return a mock response indicating the search export is queued
        // In production, this would queue a job to generate the search results export
        return response()->json([
            'success' => true,
            'message' => 'Search export request queued successfully. You will receive an email when ready.',
            'export_id' => uniqid('search_export_'),
            'query' => $query,
            'format' => $format,
            'filters_applied' => count($filters),
            'estimated_time' => '5-10 minutes'
        ]);
    }

    /**
     * Get autocomplete suggestions for records (for admin forms)
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:3|max:255',
            'limit' => 'nullable|integer|min:1|max:5',
        ]);

        $query = $request->get('q');
        $limit = $request->get('limit', 5);

        // Recherche dans les records par nom et code
        $records = \App\Models\Record::where(function($q) use ($query) {
            $q->where('name', 'LIKE', '%' . $query . '%')
              ->orWhere('code', 'LIKE', '%' . $query . '%');
        })
        ->select('id', 'name', 'code')
        ->limit($limit)
        ->get();

        $suggestions = $records->map(function ($record) {
            return [
                'id' => $record->id,
                'label' => $record->name . ' (' . $record->code . ')',
                'name' => $record->name,
                'code' => $record->code
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $suggestions
        ]);
    }
}
