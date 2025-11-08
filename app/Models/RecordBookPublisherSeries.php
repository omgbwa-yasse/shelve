<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordBookPublisherSeries extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'record_book_publisher_series';

    protected $fillable = [
        'publisher_id',
        'name',
        'description',
        'issn',
        'started_year',
        'ended_year',
        'editor',
        'subjects',
        'total_volumes',
        'status',
        'metadata',
    ];

    protected $casts = [
        'publisher_id' => 'integer',
        'started_year' => 'integer',
        'ended_year' => 'integer',
        'subjects' => 'array',
        'total_volumes' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(RecordBookPublisher::class, 'publisher_id');
    }

    public function books(): HasMany
    {
        return $this->hasMany(RecordBook::class, 'series_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByPublisher($query, int $publisherId)
    {
        return $query->where('publisher_id', $publisherId);
    }

    public function scopeBySubject($query, string $subject)
    {
        return $query->whereJsonContains('subjects', $subject);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    /**
     * Accessors
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->publisher->name} - {$this->name}";
    }

    public function getFormattedIssnAttribute(): ?string
    {
        if (!$this->issn) {
            return null;
        }

        // Format ISSN as XXXX-XXXX
        $issn = preg_replace('/[^0-9X]/', '', strtoupper($this->issn));
        if (strlen($issn) === 8) {
            return substr($issn, 0, 4) . '-' . substr($issn, 4);
        }
        return $this->issn;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed' || !is_null($this->ended_year);
    }

    public function getDurationYearsAttribute(): ?int
    {
        if (!$this->started_year) {
            return null;
        }

        $endYear = $this->ended_year ?? date('Y');
        return $endYear - $this->started_year;
    }

    public function getYearsRangeAttribute(): ?string
    {
        if (!$this->started_year) {
            return null;
        }

        if ($this->ended_year) {
            return "{$this->started_year}-{$this->ended_year}";
        }

        return "{$this->started_year}-" . __('present');
    }

    /**
     * Methods
     */
    public function updateVolumeCount(): void
    {
        $this->update([
            'total_volumes' => $this->books()->count()
        ]);
    }

    public function incrementVolumeCount(): void
    {
        $this->increment('total_volumes');
    }

    public function markAsCompleted(?int $year = null): void
    {
        $this->update([
            'status' => 'completed',
            'ended_year' => $year ?? date('Y'),
        ]);
    }

    public function markAsDiscontinued(?int $year = null): void
    {
        $this->update([
            'status' => 'discontinued',
            'ended_year' => $year ?? date('Y'),
        ]);
    }

    public function getBooksByYear(int $year)
    {
        return $this->books()
            ->where('publication_year', $year)
            ->get();
    }

    public function hasSubject(string $subject): bool
    {
        if (!$this->subjects) {
            return false;
        }

        return in_array($subject, $this->subjects);
    }

    /**
     * Static methods
     */
    public static function findOrCreateBySeries(
        int $publisherId,
        string $name,
        array $attributes = []
    ): self {
        return static::firstOrCreate(
            [
                'publisher_id' => $publisherId,
                'name' => $name
            ],
            array_merge(['status' => 'active'], $attributes)
        );
    }

    public static function mostPopular(int $limit = 10)
    {
        return static::withCount('books')
            ->orderBy('books_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function activeSeries()
    {
        return static::where('status', 'active')
            ->with('publisher')
            ->orderBy('name')
            ->get();
    }
}
