<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordBookFormat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_en',
        'description',
        'category',
        'width_cm',
        'height_cm',
        'dimensions_range',
        'notes',
        'total_books',
        'status',
    ];

    protected $casts = [
        'width_cm' => 'decimal:2',
        'height_cm' => 'decimal:2',
        'total_books' => 'integer',
    ];

    // Relations

    /**
     * Get all books with this format
     */
    public function books()
    {
        return $this->hasMany(RecordBook::class, 'format_id');
    }

    // Scopes

    /**
     * Scope for active formats
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for pocket formats
     */
    public function scopePocket($query)
    {
        return $query->where('category', 'pocket');
    }

    /**
     * Scope for large formats
     */
    public function scopeLarge($query)
    {
        return $query->where('category', 'large');
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('name_en', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // Accessors

    /**
     * Get display name with English name if available
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->name_en && $this->name_en !== $this->name) {
            return "{$this->name} ({$this->name_en})";
        }
        return $this->name;
    }

    /**
     * Get formatted dimensions
     */
    public function getFormattedDimensionsAttribute(): ?string
    {
        if ($this->width_cm && $this->height_cm) {
            return "{$this->width_cm} × {$this->height_cm} cm";
        }
        return $this->dimensions_range;
    }

    /**
     * Check if format is pocket size
     */
    public function getIsPocketAttribute(): bool
    {
        return $this->category === 'pocket';
    }

    /**
     * Check if format is large
     */
    public function getIsLargeAttribute(): bool
    {
        return in_array($this->category, ['large', 'folio']);
    }

    /**
     * Get approximate surface area in cm²
     */
    public function getSurfaceAreaAttribute(): ?float
    {
        if ($this->width_cm && $this->height_cm) {
            return round($this->width_cm * $this->height_cm, 2);
        }
        return null;
    }

    // Methods

    /**
     * Update the total_books counter
     */
    public function updateBookCount(): void
    {
        $this->update([
            'total_books' => $this->books()->count(),
        ]);
    }

    /**
     * Mark format as deprecated
     */
    public function markAsDeprecated(): void
    {
        $this->update(['status' => 'deprecated']);
    }

    /**
     * Mark format as historical
     */
    public function markAsHistorical(): void
    {
        $this->update(['status' => 'historical']);
    }

    // Static methods

    /**
     * Find or create a format by its name
     */
    public static function findOrCreateByName(string $name, array $attributes = []): self
    {
        return static::firstOrCreate(
            ['name' => $name],
            array_merge([
                'status' => 'active',
            ], $attributes)
        );
    }

    /**
     * Get most used formats
     */
    public static function mostUsed(int $limit = 10)
    {
        return static::where('total_books', '>', 0)
            ->orderBy('total_books', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get statistics by category
     */
    public static function byCategoryStats()
    {
        return static::selectRaw('category, COUNT(*) as count, SUM(total_books) as total_books')
            ->groupBy('category')
            ->orderBy('total_books', 'desc')
            ->get();
    }

    /**
     * Get formats ordered by size (smallest to largest)
     */
    public static function orderBySize(string $direction = 'asc')
    {
        return static::whereNotNull('width_cm')
            ->whereNotNull('height_cm')
            ->orderByRaw('width_cm * height_cm ' . $direction)
            ->get();
    }
}
