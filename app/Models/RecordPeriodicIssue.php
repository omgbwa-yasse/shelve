<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle pour les numéros/issues de périodiques
 * Phase 8 - SpecKit
 */
class RecordPeriodicIssue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'periodic_id',
        'issue_number',
        'volume',
        'year',
        'publication_date',
        'season',
        'title',
        'summary',
        'page_count',
        'doi',
        'cover_image_path',
        'status',
        'received_date',
        'location',
        'call_number',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'publication_date' => 'date',
        'received_date' => 'date',
        'page_count' => 'integer',
    ];

    /**
     * Relations
     */
    public function periodic(): BelongsTo
    {
        return $this->belongsTo(RecordPeriodic::class, 'periodic_id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(RecordPeriodicArticle::class, 'issue_id');
    }

    /**
     * Scopes
     */
    public function scopeExpected($query)
    {
        return $query->where('status', 'expected');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeCatalogued($query)
    {
        return $query->where('status', 'catalogued');
    }

    public function scopeMissing($query)
    {
        return $query->where('status', 'missing');
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Accessors
     */
    public function getFullReferenceAttribute(): string
    {
        $ref = "{$this->periodic->title}";

        if ($this->volume) {
            $ref .= ", Vol. {$this->volume}";
        }

        $ref .= ", No. {$this->issue_number}";

        if ($this->year) {
            $ref .= " ({$this->year})";
        }

        return $ref;
    }

    public function getDisplayTitleAttribute(): string
    {
        return $this->title ?: "Numéro {$this->issue_number}";
    }

    /**
     * Méthodes métier
     */
    public function markAsReceived(\DateTime $date = null): void
    {
        $this->update([
            'status' => 'received',
            'received_date' => $date ?? now(),
        ]);
    }

    public function markAsCatalogued(): void
    {
        $this->update(['status' => 'catalogued']);
    }

    public function markAsMissing(): void
    {
        $this->update(['status' => 'missing']);
    }

    public function isAvailable(): bool
    {
        return in_array($this->status, ['received', 'catalogued', 'archived']);
    }
}
