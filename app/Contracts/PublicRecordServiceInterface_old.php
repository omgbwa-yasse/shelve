<?php

namespace App\Contracts;

use App\Models\PublicRecord;
use Illuminate\Pagination\LengthAwarePaginator;

interface PublicRecordServiceInterface
{
    /**
     * Get paginated records with filters
     */
    public function getPaginatedRecords(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    /**
     * Search records with advanced filtering
     */
    public function searchRecords(string $searchTerm, array $filters = [], int $perPage = 20): LengthAwarePaginator;

    /**
     * Get search suggestions
     */
    public function getSearchSuggestions(string $query, int $limit = 10): array;

    /**
     * Transform record for API response
     */
    public function transformRecordForApi(PublicRecord $record, bool $includeDetails = false): array;

    /**
     * Generate smart excerpt from content
     */
    public function generateExcerpt(string $content, string $title = '', int $maxLength = 200): string;

    /**
     * Get statistics
     */
    public function getStatistics(): array;

    /**
     * Get available filters
     */
    public function getAvailableFilters(): array;

    /**
     * Validate record availability
     */
    public function isRecordAvailable(PublicRecord $record): bool;

    /**
     * Get popular search terms
     */
    public function getPopularSearches(): array;

    /**
     * Track a search term
     */
    public function trackSearchTerm(string $searchTerm): void;

    /**
     * Validate search parameters
     */
    public function validateSearchFilters(array $filters): array;
}
