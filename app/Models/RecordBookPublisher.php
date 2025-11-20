<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordBookPublisher extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'record_book_publishers';

    protected $fillable = [
        'name',
        'original_name',
        'country',
        'city',
        'founded_year',
        'ceased_year',
        'description',
        'website',
        'logo',
        'status',
        'metadata',
    ];

    protected $casts = [
        'founded_year' => 'integer',
        'ceased_year' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function books(): BelongsToMany
    {
        return $this->belongsToMany(RecordBook::class, 'record_book_publisher', 'publisher_id', 'book_id')
            ->withTimestamps();
    }

    public function series(): HasMany
    {
        return $this->hasMany(RecordBookPublisherSeries::class, 'publisher_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCountry($query, string $country)
    {
        return $query->where('country', $country);
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('original_name', 'like', "%{$search}%");
        });
    }

    /**
     * Accessors
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->original_name && $this->original_name !== $this->name) {
            return "{$this->name} ({$this->original_name})";
        }
        return $this->name;
    }

    public function getFullLocationAttribute(): ?string
    {
        if ($this->city && $this->country) {
            return "{$this->city}, {$this->country}";
        }
        return $this->city ?? $this->country;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getHasCeasedAttribute(): bool
    {
        return $this->status === 'ceased' || !is_null($this->ceased_year);
    }

    public function getAgeAttribute(): ?int
    {
        if (!$this->founded_year) {
            return null;
        }

        $endYear = $this->ceased_year ?? date('Y');
        return $endYear - $this->founded_year;
    }

    /**
     * Methods
     */
    public function updateBookCount(): void
    {
        $this->update([
            'metadata' => array_merge($this->metadata ?? [], [
                'total_books' => $this->books()->count(),
                'active_books' => $this->books()->where('status', 'active')->count(),
            ])
        ]);
    }

    public function getActiveSeries()
    {
        return $this->series()->where('status', 'active')->get();
    }

    public function getTotalBooks(): int
    {
        return $this->books()->count();
    }

    public function markAsInactive(): void
    {
        $this->update(['status' => 'inactive']);
    }

    public function markAsCeased(?int $year = null): void
    {
        $this->update([
            'status' => 'ceased',
            'ceased_year' => $year ?? date('Y'),
        ]);
    }

    /**
     * Static methods
     */
    public static function findOrCreateByName(string $name, array $attributes = []): self
    {
        return static::firstOrCreate(
            ['name' => $name],
            array_merge(['status' => 'active'], $attributes)
        );
    }

    public static function mostPublished(int $limit = 10)
    {
        return static::withCount('books')
            ->orderBy('books_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function byCountryStats()
    {
        return static::selectRaw('country, COUNT(*) as total')
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderBy('total', 'desc')
            ->get();
    }
}
