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
        'isbn',
        'title',
        'subtitle',
        'publisher_id',
        'publication_year',
        'edition',
        'place_of_publication',
        'dewey',
        'lcc',
        'pages',
        'format_id',
        'binding_id',
        'language_id',
        'dimensions',
        'description',
        'table_of_contents',
        'notes',
        'series_id',
        'total_copies',
        'available_copies',
        'loan_count',
        'reservation_count',
        'metadata',
        'access_level',
        'status',
        'creator_id',
        'organisation_id',
    ];

    protected $casts = [
        'metadata' => 'array',
        'publication_year' => 'integer',
        'pages' => 'integer',
        'total_copies' => 'integer',
        'available_copies' => 'integer',
        'loan_count' => 'integer',
        'reservation_count' => 'integer',
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

    public function publisher(): BelongsTo
    {
        return $this->belongsTo(RecordBookPublisher::class, 'publisher_id');
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
            ->withPivot('role', 'display_order', 'notes')
            ->withTimestamps()
            ->orderByPivot('display_order');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(RecordSubject::class, 'record_book_subject', 'book_id', 'subject_id')
            ->withPivot('relevance', 'is_primary')
            ->withTimestamps();
    }

    public function copies(): HasMany
    {
        return $this->hasMany(RecordBookCopy::class, 'book_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(RecordBookReservation::class, 'book_id');
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

    public static function byPublisher($publisher)
    {
        return self::where('publisher', $publisher)->get();
    }
}
