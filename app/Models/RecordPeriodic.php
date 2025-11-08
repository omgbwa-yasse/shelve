<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle pour les publications périodiques (revues, magazines, journaux)
 * Phase 8 - SpecKit
 */
class RecordPeriodic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'title',
        'subtitle',
        'description',
        'issn',
        'eissn',
        'type',
        'subject_area',
        'keywords',
        'publisher',
        'publisher_location',
        'language',
        'frequency',
        'first_year',
        'last_year',
        'is_active',
        'website',
        'contact_email',
        'metadata',
        'access_level',
        'status',
        'creator_id',
        'organisation_id',
    ];

    protected $casts = [
        'keywords' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
        'first_year' => 'integer',
        'last_year' => 'integer',
    ];

    /**
     * Relations
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(RecordPeriodicIssue::class, 'periodic_id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(RecordPeriodicArticle::class, 'periodic_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(RecordPeriodicSubscription::class, 'periodic_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCeased($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySubjectArea($query, string $area)
    {
        return $query->where('subject_area', $area);
    }

    /**
     * Accessors
     */
    public function getFullTitleAttribute(): string
    {
        return $this->subtitle
            ? "{$this->title} : {$this->subtitle}"
            : $this->title;
    }

    public function getPublicationYearsAttribute(): string
    {
        if ($this->first_year && $this->last_year) {
            return "{$this->first_year}-{$this->last_year}";
        }

        if ($this->first_year) {
            return $this->is_active
                ? "{$this->first_year}-"
                : (string) $this->first_year;
        }

        return '';
    }

    /**
     * Méthodes métier
     */
    public function hasActiveSubscription(): bool
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('end_date', '>=', now())
            ->exists();
    }

    public function getLatestIssue(): ?RecordPeriodicIssue
    {
        return $this->issues()
            ->orderBy('publication_date', 'desc')
            ->first();
    }

    public function getIssueCount(): int
    {
        return $this->issues()->count();
    }

    public function getArticleCount(): int
    {
        return $this->articles()->count();
    }
}
