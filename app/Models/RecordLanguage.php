<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecordLanguage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'name_en',
        'native_name',
        'script',
        'direction',
        'iso_639_1',
        'iso_639_2',
        'iso_639_3',
        'total_books',
        'status',
    ];

    protected $casts = [
        'total_books' => 'integer',
    ];

    // Scopes

    /**
     * Scope for active languages
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for RTL languages
     */
    public function scopeRtl($query)
    {
        return $query->where('direction', 'rtl');
    }

    /**
     * Scope for LTR languages
     */
    public function scopeLtr($query)
    {
        return $query->where('direction', 'ltr');
    }

    /**
     * Scope by script
     */
    public function scopeByScript($query, $script)
    {
        return $query->where('script', $script);
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('name_en', 'like', "%{$search}%")
              ->orWhere('native_name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        });
    }

    // Accessors

    /**
     * Get display name with native name in parentheses
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->native_name && $this->native_name !== $this->name) {
            return "{$this->name} ({$this->native_name})";
        }
        return $this->name;
    }

    /**
     * Check if language is RTL
     */
    public function getIsRtlAttribute(): bool
    {
        return $this->direction === 'rtl';
    }

    /**
     * Check if language is LTR
     */
    public function getIsLtrAttribute(): bool
    {
        return $this->direction === 'ltr';
    }

    /**
     * Get flag emoji (if available) based on ISO code
     */
    public function getFlagAttribute(): ?string
    {
        // Map common language codes to country flags
        $flags = [
            'en' => '🇬🇧',
            'fr' => '🇫🇷',
            'es' => '🇪🇸',
            'de' => '🇩🇪',
            'it' => '🇮🇹',
            'pt' => '🇵🇹',
            'ru' => '🇷🇺',
            'ar' => '🇸🇦',
            'zh' => '🇨🇳',
            'ja' => '🇯🇵',
            'ko' => '🇰🇷',
            'hi' => '🇮🇳',
            'he' => '🇮🇱',
            'tr' => '🇹🇷',
            'pl' => '🇵🇱',
            'nl' => '🇳🇱',
            'sv' => '🇸🇪',
            'no' => '🇳🇴',
            'da' => '🇩🇰',
            'fi' => '🇫🇮',
        ];

        return $flags[$this->iso_639_1] ?? null;
    }

    /**
     * Get full display with flag
     */
    public function getFullDisplayAttribute(): string
    {
        $flag = $this->flag;
        return $flag ? "{$flag} {$this->display_name}" : $this->display_name;
    }

    // Methods

    /**
     * Mark language as deprecated
     */
    public function markAsDeprecated(): void
    {
        $this->update(['status' => 'deprecated']);
    }

    /**
     * Mark language as historical
     */
    public function markAsHistorical(): void
    {
        $this->update(['status' => 'historical']);
    }

    // Static methods

    /**
     * Find or create a language by its code
     */
    public static function findOrCreateByCode(string $code, array $attributes = []): self
    {
        return static::firstOrCreate(
            ['code' => $code],
            array_merge([
                'name' => $code,
                'name_en' => $code,
                'status' => 'active',
            ], $attributes)
        );
    }

    /**
     * Get most used languages
     */
    public static function mostUsed(int $limit = 10)
    {
        return static::where('total_books', '>', 0)
            ->orderBy('total_books', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get statistics by script
     */
    public static function byScriptStats()
    {
        return static::selectRaw('script, COUNT(*) as count, SUM(total_books) as total_books')
            ->groupBy('script')
            ->orderBy('total_books', 'desc')
            ->get();
    }

    /**
     * Get statistics by direction
     */
    public static function byDirectionStats()
    {
        return static::selectRaw('direction, COUNT(*) as count, SUM(total_books) as total_books')
            ->groupBy('direction')
            ->orderBy('total_books', 'desc')
            ->get();
    }
}
