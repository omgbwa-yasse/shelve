<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Modèle pour les articles de périodiques
 * Phase 8 - SpecKit
 */
class RecordPeriodicArticle extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'issue_id',
        'periodic_id',
        'title',
        'abstract',
        'authors',
        'page_start',
        'page_end',
        'section',
        'doi',
        'url',
        'keywords',
        'language',
        'article_type',
        'metadata',
        'is_peer_reviewed',
    ];

    protected $casts = [
        'authors' => 'array',
        'keywords' => 'array',
        'metadata' => 'array',
        'is_peer_reviewed' => 'boolean',
    ];

    /**
     * Relations
     */
    public function issue(): BelongsTo
    {
        return $this->belongsTo(RecordPeriodicIssue::class, 'issue_id');
    }

    public function periodic(): BelongsTo
    {
        return $this->belongsTo(RecordPeriodic::class, 'periodic_id');
    }

    /**
     * Scopes
     */
    public function scopePeerReviewed($query)
    {
        return $query->where('is_peer_reviewed', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('article_type', $type);
    }

    public function scopeByLanguage($query, string $language)
    {
        return $query->where('language', $language);
    }

    /**
     * Accessors
     */
    public function getPageRangeAttribute(): ?string
    {
        if ($this->page_start && $this->page_end) {
            return "{$this->page_start}-{$this->page_end}";
        }

        if ($this->page_start) {
            return "p. {$this->page_start}";
        }

        return null;
    }

    public function getAuthorNamesAttribute(): array
    {
        if (!is_array($this->authors)) {
            return [];
        }

        return array_map(function ($author) {
            return is_array($author) ? ($author['name'] ?? '') : $author;
        }, $this->authors);
    }

    public function getFormattedAuthorsAttribute(): string
    {
        $names = $this->getAuthorNamesAttribute();

        if (empty($names)) {
            return '';
        }

        if (count($names) === 1) {
            return $names[0];
        }

        if (count($names) === 2) {
            return implode(' et ', $names);
        }

        $lastAuthor = array_pop($names);
        return implode(', ', $names) . ' et ' . $lastAuthor;
    }

    public function getCitationAttribute(): string
    {
        $citation = $this->formatted_authors;

        if ($citation) {
            $citation .= '. ';
        }

        $citation .= '"' . $this->title . '". ';
        $citation .= $this->periodic->title;

        if ($this->issue->volume) {
            $citation .= ', Vol. ' . $this->issue->volume;
        }

        $citation .= ', No. ' . $this->issue->issue_number;

        if ($this->issue->year) {
            $citation .= ' (' . $this->issue->year . ')';
        }

        if ($this->page_range) {
            $citation .= ', ' . $this->page_range;
        }

        $citation .= '.';

        return $citation;
    }

    /**
     * Méthodes métier
     */
    public function hasFullText(): bool
    {
        return !empty($this->url);
    }

    public function hasDoi(): bool
    {
        return !empty($this->doi);
    }
}
