<?php

namespace App\Services;

use App\Contracts\PublicRecordServiceInterface;
use App\Models\PublicRecord;
use App\Models\Record;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PublicRecordService implements PublicRecordServiceInterface
{
    /**
     * Get paginated records with filters
     */
    public function getPaginatedRecords(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = PublicRecord::with(['record', 'publisher'])
            ->available();

        // Apply search filter
        if (!empty($filters['search'])) {
            $query->searchContent($filters['search']);
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
    public function searchRecords(string $searchTerm, array $filters = [], int $perPage = 20): LengthAwarePaginator
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
    public function getSearchSuggestions(string $query, int $limit = 10): array
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
    public function transformRecordForApi(PublicRecord $record, bool $includeDetails = false): array
    {
        $data = [
            'id' => $record->id,
            'title' => $record->title,
            'code' => $record->code,
            'content' => $record->content,
            'formatted_date_range' => $record->formatted_date_range,
            'published_at' => $record->published_at,
            'expires_at' => $record->expires_at,
            'publication_notes' => $record->publication_notes,
            'is_available' => $record->is_available,
            'is_expired' => $record->is_expired,
            'publisher' => $record->publisher ? [
                'id' => $record->publisher->id,
                'name' => $record->publisher->name,
            ] : null,
        ];

        if ($includeDetails) {
            $data['record_details'] = [
                'date_start' => $record->date_start,
                'date_end' => $record->date_end,
                'date_exact' => $record->date_exact,
                'biographical_history' => $record->biographical_history,
                'archival_history' => $record->record->archival_history ?? '',
                'access_conditions' => $record->access_conditions,
                'reproduction_conditions' => $record->record->reproduction_conditions ?? '',
                'language_material' => $record->language_material,
                'characteristic' => $record->record->characteristic ?? '',
                'finding_aids' => $record->record->finding_aids ?? '',
                'location_original' => $record->record->location_original ?? '',
                'related_unit' => $record->record->related_unit ?? '',
                'publication_note' => $record->record->publication_note ?? '',
                'note' => $record->record->note ?? '',
            ];
        }

        return $data;
    }

    /**
     * Generate smart excerpt from content
     */
    public function generateExcerpt(string $content, string $title = '', int $maxLength = 200): string
    {
        if (empty($content)) {
            return $title ? "Document: {$title}" : 'Contenu non disponible';
        }

        if (strlen($content) <= $maxLength) {
            return $content;
        }

        // Cut at the nearest word
        $excerpt = substr($content, 0, $maxLength);
        $lastSpace = strrpos($excerpt, ' ');

        if ($lastSpace !== false && $lastSpace > $maxLength * 0.8) {
            $excerpt = substr($excerpt, 0, $lastSpace);
        }

        return $excerpt . '...';
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
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
    public function getAvailableFilters(): array
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
    public function isRecordAvailable(PublicRecord $record): bool
    {
        return $record->is_available && !$record->is_expired;
    }

    /**
     * Get popular search terms
     * TODO: Replace with real tracking system using database or cache
     */
    public function getPopularSearches(): array
    {
        // For now, return mock data
        // In production, this could track searches in database/cache
        return [
            'Archives historiques',
            'Documents administratifs',
            'Correspondance officielle',
            'Rapports annuels',
            'Procès-verbaux'
        ];
    }

    /**
     * Track a search term (placeholder for future implementation)
     */
    public function trackSearchTerm(string $searchTerm): void
    {
        // TODO: Implement search tracking
        // Could store in database table or increment cache counters
    }

    /**
     * Validate search parameters
     */
    public function validateSearchFilters(array $filters): array
    {
        $validFilters = [];

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
