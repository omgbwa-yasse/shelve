<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PublicRecord;
use App\Models\Record;
use App\Models\User;
use App\Models\ThesaurusConcept;
use App\Models\RecordLevel;
use App\Models\RecordStatus;
use App\Models\RecordSupport;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
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
            'exclude' => 'nullable|integer',
            'level_id' => 'nullable|integer|exists:record_levels,id',
            'status_id' => 'nullable|integer|exists:record_statuses,id',
            'support_id' => 'nullable|integer|exists:record_supports,id',
            'activity_id' => 'nullable|integer|exists:activities,id',
            'thesaurus_concept_id' => 'nullable|integer|exists:thesaurus_concepts,id',
            'date_format' => 'nullable|string|in:exact,approximate,range',
            'has_digital_copy' => 'nullable|boolean'
        ]);

        $filters = $this->validateSearchFilters($request->only([
            'search', 'date_from', 'date_to', 'language', 'publisher_id', 'type',
            'level_id', 'status_id', 'support_id', 'activity_id', 'thesaurus_concept_id',
            'date_format', 'has_digital_copy'
        ]));
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

        // Vérifier que le record est dans une room publique ou héritant d'un building public
        if (!$this->isRecordInPublicRoom($record->record)) {
            return response()->json([
                'success' => false,
                'message' => 'Record not available in public areas'
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

        // Filtrer par la visibilité des rooms
        $query = $this->applyRoomVisibilityFilter($query);

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

        // Apply ISAD(G) filters
        if (!empty($filters['level_id'])) {
            $query->whereHas('record', function ($q) use ($filters) {
                $q->where('level_id', $filters['level_id']);
            });
        }

        if (!empty($filters['status_id'])) {
            $query->whereHas('record', function ($q) use ($filters) {
                $q->where('status_id', $filters['status_id']);
            });
        }

        if (!empty($filters['support_id'])) {
            $query->whereHas('record', function ($q) use ($filters) {
                $q->where('support_id', $filters['support_id']);
            });
        }

        if (!empty($filters['activity_id'])) {
            $query->whereHas('record', function ($q) use ($filters) {
                $q->where('activity_id', $filters['activity_id']);
            });
        }

        if (!empty($filters['thesaurus_concept_id'])) {
            $query->whereHas('record.thesaurusConcepts', function ($q) use ($filters) {
                $q->where('thesaurus_concepts.id', $filters['thesaurus_concept_id']);
            });
        }

        if (!empty($filters['date_format'])) {
            $query->whereHas('record', function ($q) use ($filters) {
                $q->where('date_format', $filters['date_format']);
            });
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

        // Filtrer par la visibilité des rooms
        $query = $this->applyRoomVisibilityFilter($query);

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

        $queryBuilder = PublicRecord::with('record')
            ->available();

        // Filtrer par la visibilité des rooms
        $queryBuilder = $this->applyRoomVisibilityFilter($queryBuilder);

        return $queryBuilder->searchContent($query)
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

            // 7 champs ISAD(G) essentiels
            'isad_fields' => [
                'reference_code' => $record->code,                           // 3.1.1 Reference code(s)
                'title' => $record->title,                                   // 3.1.2 Title
                'dates' => [                                                 // 3.1.3 Date(s)
                    'start' => $record->record->date_start ?? null,
                    'end' => $record->record->date_end ?? null,
                    'exact' => $record->record->date_exact ?? null,
                    'format' => $record->record->date_format ?? null
                ],
                'level_of_description' => $record->record->level->name ?? null,  // 3.1.4 Level of description
                'extent' => $record->record->width_description ?? null,      // 3.1.5 Extent and medium
                'creator' => $record->record->activity->name ?? null,       // 3.2.1 Name of creator(s)
                'scope_content' => $record->content                         // 3.3.1 Scope and content
            ],

            // Classifications et thésaurus
            'classifications' => [],
            'thesaurus_concepts' => $record->record && $record->record->thesaurusConcepts ?
                $record->record->thesaurusConcepts->map(function($concept) {
                    return [
                        'id' => $concept->id,
                        'term' => $concept->term,
                        'weight' => $concept->pivot->weight ?? null,
                        'context' => $concept->pivot->context ?? null
                    ];
                })->toArray() : []
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
        $total = $this->applyRoomVisibilityFilter(PublicRecord::query())->count();
        $available = $this->applyRoomVisibilityFilter(PublicRecord::available())->count();
        $expired = $this->applyRoomVisibilityFilter(PublicRecord::where('expires_at', '<=', now()))->count();
        $publishedThisMonth = $this->applyRoomVisibilityFilter(PublicRecord::where('published_at', '>=', now()->startOfMonth()))->count();

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

        // Filtres ISAD(G) additionnels
        $levels = RecordLevel::select('id', 'name')->orderBy('name')->get();
        $statuses = RecordStatus::select('id', 'name')->orderBy('name')->get();
        $supports = RecordSupport::select('id', 'name')->orderBy('name')->get();
        $activities = Activity::select('id', 'name')->orderBy('name')->get();

        // Concepts thésaurus les plus utilisés
        $topThesaurusConcepts = ThesaurusConcept::whereHas('records')
            ->withCount('records')
            ->with(['labels' => function($query) {
                $query->where('type', 'prefLabel')->where('language', 'fr-fr');
            }])
            ->orderBy('records_count', 'desc')
            ->limit(50)
            ->get()
            ->map(function($concept) {
                return [
                    'id' => $concept->id,
                    'term' => $concept->getPreferredLabel() ? $concept->getPreferredLabel()->literal_form : $concept->uri
                ];
            });

        return [
            'languages' => $languages,
            'publishers' => $publishers,
            'date_range' => [
                'min' => $dateRange->min_date,
                'max' => $dateRange->max_date,
            ],
            // Nouveaux filtres ISAD(G)
            'levels' => $levels,
            'statuses' => $statuses,
            'supports' => $supports,
            'activities' => $activities,
            'thesaurus_concepts' => $topThesaurusConcepts,
            'date_formats' => [
                ['value' => 'exact', 'label' => 'Date exacte'],
                ['value' => 'approximate', 'label' => 'Date approximative'],
                ['value' => 'range', 'label' => 'Période']
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

    /**
     * Vérifier si un record est dans une room publique
     */
    private function isRecordInPublicRoom(Record $record): bool
    {
        return $record->containers()
            ->whereHas('shelf.room', function ($roomQuery) {
                $roomQuery->where(function ($q) {
                    // Room est publique
                    $q->where('visibility', 'public')
                      // OU Room hérite et le building est public
                      ->orWhere(function ($inheritQuery) {
                          $inheritQuery->where('visibility', 'inherit')
                                       ->whereHas('floor.building', function ($buildingQuery) {
                                           $buildingQuery->where('visibility', 'public');
                                       });
                      });
                });
            })
            ->exists();
    }

    /**
     * Appliquer le filtre de visibilité des rooms sur une query PublicRecord
     */
    private function applyRoomVisibilityFilter($query)
    {
        return $query->whereHas('record.containers.shelf.room', function ($roomQuery) {
            $roomQuery->where(function ($q) {
                // Room est publique
                $q->where('visibility', 'public')
                  // OU Room hérite et le building est public
                  ->orWhere(function ($inheritQuery) {
                      $inheritQuery->where('visibility', 'inherit')
                                   ->whereHas('floor.building', function ($buildingQuery) {
                                       $buildingQuery->where('visibility', 'public');
                                   });
                  });
            });
        });
    }
}
