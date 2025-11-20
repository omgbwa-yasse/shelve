<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordBook extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'record_books';

    protected $fillable = [
        // ZONE 0
        'typdoc',
        'statut',
        'forme_contenu',
        'type_mediation',

        // ZONE 1 - Titre
        'isbn',
        'title',
        'titre_parallele',
        'subtitle',
        'complement_titre',
        'titre_cle',

        // ZONE 2 - Édition
        'mention_edition',
        'mention_resp_edition',

        // ZONE 3
        'zone_specifique',

        // ZONE 4 - Adresse bibliographique
        'annee_publication',
        'date_publication',
        'date_depot_legal',
        'date_copyright',
        'publication_year', // legacy
        'edition', // legacy
        'place_of_publication', // legacy

        // ZONE 5 - Collation
        'importance_materielle',
        'autre_materiel',
        'format_dimensions',
        'materiel_accompagnement',
        'pages',
        'format', // legacy
        'binding', // legacy
        'language', // legacy
        'dimensions', // legacy

        // ZONE 7 - Notes
        'notes_generales',
        'notes_contenu',
        'notes_bibliographie',
        'notes_resume',
        'notes_public_destine',
        'description', // legacy
        'table_of_contents', // legacy
        'notes', // legacy

        // ZONE 8 - Numéros
        'isbn_errone',
        'ean',
        'issn',
        'numero_editeur',
        'autre_numero',
        'prix',
        'disponibilite',

        // Métadonnées UNIMARC
        'code_langue',
        'code_pays',
        'catalogueur',
        'source_notice',
        'ppn',

        // Relations (legacy)
        'series_id',
        'format_id',
        'binding_id',
        'language_id',

        // Métadonnées système
        'status',
        'creator_id',
        'organisation_id',
    ];

    protected $casts = [
        'publication_year' => 'integer',
        'pages' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
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

    public function publishers(): BelongsToMany
    {
        return $this->belongsToMany(RecordBookPublisher::class, 'record_book_publisher', 'book_id', 'publisher_id')
            ->withTimestamps();
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(RecordBookPublisherSeries::class, 'series_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(RecordLanguage::class, 'language_id');
    }

    public function format(): BelongsTo
    {
        return $this->belongsTo(RecordBookFormat::class, 'format_id');
    }

    public function binding(): BelongsTo
    {
        return $this->belongsTo(RecordBookBinding::class, 'binding_id');
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(RecordAuthor::class, 'record_author_book', 'book_id', 'author_id')
            ->withPivot('responsibility_type', 'function', 'role', 'display_order', 'notes')
            ->withTimestamps()
            ->orderByPivot('display_order');
    }

    public function responsabilites(): HasMany
    {
        return $this->hasMany(RecordAuthorBook::class, 'book_id')
            ->orderBy('display_order');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(RecordSubject::class, 'record_book_subject', 'book_id', 'subject_id')
            ->withPivot('relevance', 'is_primary')
            ->withTimestamps();
    }

    // Relations UNIMARC

    public function classifications(): BelongsToMany
    {
        return $this->belongsToMany(BookClassification::class, 'record_book_classification', 'book_id', 'classification_id')
            ->withPivot('display_order')
            ->orderByPivot('display_order');
    }

    public function terms(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Term::class, 'record_book_term', 'book_id', 'term_id')
            ->withPivot('display_order')
            ->orderByPivot('display_order');
    }

    public function publisherPlaces(): HasMany
    {
        return $this->hasMany(RecordBookPublisherPlace::class, 'book_id')
            ->orderBy('display_order');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(RecordBookPublisherSeries::class, 'record_book_collection', 'book_id', 'collection_id')
            ->withPivot('collection_number');
    }

    public function copies(): HasMany
    {
        return $this->hasMany(RecordBookCopy::class, 'book_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(RecordBookReservation::class, 'book_id');
    }

    public function dollies()
    {
        return $this->belongsToMany(Dolly::class, 'dolly_books', 'book_id', 'dolly_id')
            ->withTimestamps();
    }

    /**
     * Scopes
     */
    public function scopeAvailable($query)
    {
        return $query->where('available_copies', '>', 0)
            ->where('status', 'active');
    }

    public function scopeBySubject($query, $subject)
    {
        return $query->whereJsonContains('subjects', $subject);
    }

    public function scopeByDewey($query, $deweyClass)
    {
        return $query->where('dewey', 'like', $deweyClass . '%');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('publication_year', $year);
    }

    /**
     * Accessors
     */
    public function getFormattedIsbnAttribute(): ?string
    {
        if (!$this->isbn) {
            return null;
        }

        $isbn = preg_replace('/[^0-9X]/', '', $this->isbn);

        if (strlen($isbn) === 13) {
            return substr($isbn, 0, 3) . '-' .
                   substr($isbn, 3, 1) . '-' .
                   substr($isbn, 4, 4) . '-' .
                   substr($isbn, 8, 4) . '-' .
                   substr($isbn, 12, 1);
        }

        return $this->isbn;
    }

    public function getAuthorsStringAttribute(): string
    {
        return $this->authors->pluck('full_name')->implode(', ');
    }

    public function getFullTitleAttribute(): string
    {
        return $this->subtitle
            ? $this->title . ': ' . $this->subtitle
            : $this->title;
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->available_copies > 0 && $this->status === 'active';
    }

    /**
     * Methods
     */
    public function checkAvailability(): bool
    {
        return $this->copies()
            ->where('status', 'available')
            ->where('is_on_loan', false)
            ->exists();
    }

    public function updateCopyStatistics(): void
    {
        $this->total_copies = $this->copies()->count();
        $this->available_copies = $this->copies()
            ->where('status', 'available')
            ->where('is_on_loan', false)
            ->count();
        $this->save();
    }

    public function incrementLoanCount(): void
    {
        $this->increment('loan_count');
    }

    public function incrementReservationCount(): void
    {
        $this->increment('reservation_count');
    }

    public function decrementReservationCount(): void
    {
        $this->decrement('reservation_count');
    }

    public function getAvailableCopy(): ?RecordBookCopy
    {
        return $this->copies()
            ->where('status', 'available')
            ->where('is_on_loan', false)
            ->first();
    }

    public function getMostRecentCopy(): ?RecordBookCopy
    {
        return $this->copies()
            ->orderBy('acquisition_date', 'desc')
            ->first();
    }

    public function getActiveLoans()
    {
        return RecordBookLoan::whereHas('copy', function ($query) {
            $query->where('book_id', $this->id);
        })->where('status', 'active')->get();
    }

    public function getPendingReservations()
    {
        return $this->reservations()
            ->where('status', 'pending')
            ->orderBy('reservation_date')
            ->get();
    }

    /**
     * Static methods
     */
    public static function mostLoaned($limit = 10)
    {
        return self::orderBy('loan_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function recentlyAdded($limit = 10)
    {
        return self::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function byPublisher($publisherName)
    {
        return self::whereHas('publishers', function ($query) use ($publisherName) {
            $query->where('name', 'like', "%{$publisherName}%");
        })->get();
    }
}
