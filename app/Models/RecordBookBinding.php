<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordBookBinding extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_en',
        'description',
        'category',
        'durability_rating',
        'relative_cost',
        'notes',
        'total_books',
        'status',
    ];

    protected $casts = [
        'durability_rating' => 'integer',
        'relative_cost' => 'decimal:2',
        'total_books' => 'integer',
    ];

    // Relations

    /**
     * Get all books with this binding
     */
    public function books()
    {
        return $this->hasMany(RecordBook::class, 'binding_id');
    }

    // Scopes

    /**
     * Scope for active bindings
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
     * Scope for hardcover bindings
     */
    public function scopeHardcover($query)
    {
        return $query->where('category', 'hard');
    }

    /**
     * Scope for softcover bindings
     */
    public function scopeSoftcover($query)
    {
        return $query->where('category', 'soft');
    }

    /**
     * Scope by minimum durability rating
     */
    public function scopeMinDurability($query, $rating)
    {
        return $query->where('durability_rating', '>=', $rating);
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
     * Get durability label
     */
    public function getDurabilityLabelAttribute(): ?string
    {
        if (!$this->durability_rating) {
            return null;
        }

        if ($this->durability_rating >= 8) return 'Excellente';
        if ($this->durability_rating >= 6) return 'Bonne';
        if ($this->durability_rating >= 4) return 'Moyenne';
        return 'Faible';
    }

    /**
     * Get cost label
     */
    public function getCostLabelAttribute(): ?string
    {
        if (!$this->relative_cost) {
            return null;
        }

        if ($this->relative_cost >= 1.5) return 'Élevé';
        if ($this->relative_cost >= 1.2) return 'Moyen';
        return 'Économique';
    }

    /**
     * Check if binding is hardcover
     */
    public function getIsHardcoverAttribute(): bool
    {
        return $this->category === 'hard';
    }

    /**
     * Check if binding is softcover
     */
    public function getIsSoftcoverAttribute(): bool
    {
        return $this->category === 'soft';
    }

    /**
     * Get full description with ratings
     */
    public function getFullDescriptionAttribute(): string
    {
        $parts = [$this->display_name];

        if ($this->durability_label) {
            $parts[] = "Durabilité: {$this->durability_label}";
        }

        if ($this->cost_label) {
            $parts[] = "Coût: {$this->cost_label}";
        }

        return implode(' | ', $parts);
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
     * Mark binding as deprecated
     */
    public function markAsDeprecated(): void
    {
        $this->update(['status' => 'deprecated']);
    }

    /**
     * Mark binding as historical
     */
    public function markAsHistorical(): void
    {
        $this->update(['status' => 'historical']);
    }

    // Static methods

    /**
     * Find or create a binding by its name
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
     * Get most used bindings
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
     * Get bindings ordered by durability
     */
    public static function orderByDurability(string $direction = 'desc')
    {
        return static::whereNotNull('durability_rating')
            ->orderBy('durability_rating', $direction)
            ->get();
    }

    /**
     * Get bindings ordered by cost
     */
    public static function orderByCost(string $direction = 'asc')
    {
        return static::whereNotNull('relative_cost')
            ->orderBy('relative_cost', $direction)
            ->get();
    }
}
