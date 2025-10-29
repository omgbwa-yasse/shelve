<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PublicRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'public_records';

    protected $fillable = [
        'record_id',
        'published_at',
        'expires_at',
        'published_by',
        'publication_notes',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function record()
    {
        return $this->belongsTo(Record::class, 'record_id');
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function documentRequests()
    {
        return $this->hasMany(PublicDocumentRequest::class, 'record_id');
    }

    public function attachments()
    {
        return $this->hasMany(PublicRecordAttachment::class, 'public_record_id');
    }

    // ========================================
    // ACCESSEURS POUR LES CHAMPS ESSENTIELS DU RECORD
    // ========================================

    /**
     * Get the title from the associated record
     */
    public function getTitleAttribute()
    {
        return $this->record?->name ?? 'Titre non disponible';
    }

    /**
     * Get the reference code from the associated record
     */
    public function getCodeAttribute()
    {
        return $this->record?->code ?? '';
    }

    /**
     * Get the content/description from the associated record
     */
    public function getContentAttribute()
    {
        return $this->record?->content ?? '';
    }

    /**
     * Get the start date from the associated record
     */
    public function getDateStartAttribute()
    {
        return $this->record?->date_start;
    }

    /**
     * Get the end date from the associated record
     */
    public function getDateEndAttribute()
    {
        return $this->record?->date_end;
    }

    /**
     * Get the exact date from the associated record
     */
    public function getDateExactAttribute()
    {
        return $this->record?->date_exact;
    }

    /**
     * Get formatted date range for display
     */
    public function getFormattedDateRangeAttribute()
    {
        if ($this->record?->date_exact) {
            return $this->record->date_exact;
        }

        $start = $this->record?->date_start;
        $end = $this->record?->date_end;

        if ($start && $end) {
            return $start . ' - ' . $end;
        } elseif ($start) {
            return 'Depuis ' . $start;
        } elseif ($end) {
            return 'Jusqu\'à ' . $end;
        }

        return 'Date non précisée';
    }

    /**
     * Get the biographical history from the associated record
     */
    public function getBiographicalHistoryAttribute()
    {
        return $this->record?->biographical_history ?? '';
    }

    /**
     * Get the language from the associated record
     */
    public function getLanguageMaterialAttribute()
    {
        return $this->record?->language_material ?? '';
    }

    /**
     * Get access conditions from the associated record
     */
    public function getAccessConditionsAttribute()
    {
        return $this->record?->access_conditions ?? '';
    }

    /**
     * Check if the public record is expired
     */
    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at < now();
    }

    /**
     * Check if the public record is currently available
     */
    public function getIsAvailableAttribute()
    {
        return !$this->is_expired && $this->published_at && $this->published_at <= now();
    }

    /**
     * Get the authors from the associated record
     */
    public function getAuthorsAttribute()
    {
        if (!$this->record) {
            return '';
        }

        return $this->record->authors->pluck('name')->implode(', ');
    }

    /**
     * Get the publisher name from the publisher relation
     */
    public function getPublisherNameAttribute()
    {
        return $this->publisher?->name ?? 'Inconnu';
    }

    /**
     * Get the publication year from the record dates
     */
    public function getPublicationYearAttribute()
    {
        if ($this->record?->date_exact) {
            return date('Y', strtotime($this->record->date_exact));
        }
        if ($this->record?->date_start) {
            return date('Y', strtotime($this->record->date_start));
        }
        return '';
    }

    /**
     * Get the description (alias for content)
     */
    public function getDescriptionAttribute()
    {
        return $this->content;
    }

    /**
     * Get subjects from thesaurus concepts
     */
    public function getSubjectsAttribute()
    {
        if (!$this->record) {
            return '';
        }

        return $this->record->thesaurusConcepts->pluck('term')->implode(', ');
    }

    /**
     * Get cover image from attachments
     */
    public function getCoverImageAttribute()
    {
        if (!$this->record) {
            return null;
        }

        $imageAttachment = $this->record->attachments()
            ->where('type', 'image')
            ->orWhere('mime_type', 'like', 'image/%')
            ->first();

        return $imageAttachment?->path;
    }

    /**
     * Check availability based on current status
     */
    public function getAvailabilityAttribute()
    {
        // For now, assume all public records are available
        // This can be extended based on business logic
        return $this->is_available;
    }

    /**
     * Get essential record data as array for easy manipulation
     */
    public function getEssentialDataAttribute()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'code' => $this->code,
            'content' => $this->content,
            'authors' => $this->authors,
            'publisher_name' => $this->publisher_name,
            'publication_year' => $this->publication_year,
            'description' => $this->description,
            'subjects' => $this->subjects,
            'cover_image' => $this->cover_image,
            'availability' => $this->availability,
            'date_start' => $this->date_start,
            'date_end' => $this->date_end,
            'date_exact' => $this->date_exact,
            'formatted_date_range' => $this->formatted_date_range,
            'biographical_history' => $this->biographical_history,
            'language_material' => $this->language_material,
            'access_conditions' => $this->access_conditions,
            'published_at' => $this->published_at,
            'expires_at' => $this->expires_at,
            'publication_notes' => $this->publication_notes,
            'is_expired' => $this->is_expired,
            'is_available' => $this->is_available,
        ];
    }

    // ========================================
    // SCOPES UTILITAIRES
    // ========================================

    /**
     * Scope to get only available (non-expired) records
     */
    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        })->where('published_at', '<=', now());
    }

    /**
     * Scope to search in record content
     */
    public function scopeSearchContent($query, $searchTerm)
    {
        return $query->whereHas('record', function ($q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
              ->orWhere('content', 'like', "%{$searchTerm}%")
              ->orWhere('code', 'like', "%{$searchTerm}%")
              ->orWhere('biographical_history', 'like', "%{$searchTerm}%")
              ->orWhere('note', 'like', "%{$searchTerm}%");
        });
    }
}
