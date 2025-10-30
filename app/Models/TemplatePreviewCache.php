<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class TemplatePreviewCache extends Model
{
    protected $table = 'template_preview_cache';

    protected $fillable = [
        'template_id',
        'cache_key',
        'device_type',
        'rendered_html',
        'css_compiled',
        'variables_used',
        'file_size',
        'expires_at'
    ];

    protected $casts = [
        'variables_used' => 'array',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relations
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    // Scopes
    public function scopeNotExpired($query)
    {
        return $query->where('expires_at', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeForDevice($query, string $deviceType)
    {
        return $query->where('device_type', $deviceType);
    }

    public function scopeByCacheKey($query, string $cacheKey)
    {
        return $query->where('cache_key', $cacheKey);
    }

    // Methods
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function extend(int $minutes = 60): bool
    {
        return $this->update([
            'expires_at' => now()->addMinutes($minutes)
        ]);
    }

    public function getFormattedSizeAttribute(): string
    {
        if ($this->file_size < 1024) {
            return $this->file_size . ' B';
        } elseif ($this->file_size < 1048576) {
            return round($this->file_size / 1024, 2) . ' KB';
        } else {
            return round($this->file_size / 1048576, 2) . ' MB';
        }
    }

    public function getRemainingTimeAttribute(): string
    {
        if ($this->isExpired()) {
            return 'Expiré';
        }

        $diff = $this->expires_at->diffInMinutes(now());

        if ($diff < 60) {
            return $diff . ' min';
        } elseif ($diff < 1440) {
            return round($diff / 60, 1) . ' h';
        } else {
            return round($diff / 1440, 1) . ' j';
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cache) {
            $cache->file_size = strlen($cache->rendered_html ?? '');

            if (!$cache->expires_at) {
                $cache->expires_at = now()->addHour();
            }
        });

        static::updating(function ($cache) {
            if ($cache->isDirty('rendered_html')) {
                $cache->file_size = strlen($cache->rendered_html ?? '');
            }
        });
    }

    // Static methods pour la gestion du cache
    public static function cleanExpired(): int
    {
        return self::expired()->delete();
    }

    public static function getCachedPreview(string $cacheKey): ?self
    {
        return self::notExpired()
                  ->byCacheKey($cacheKey)
                  ->first();
    }

    public static function storePreview(
        int $templateId,
        string $cacheKey,
        string $renderedHtml,
        string $deviceType = 'desktop',
        array $variablesUsed = [],
        string $cssCompiled = null,
        int $expirationMinutes = 60
    ): self {
        return self::updateOrCreate(
            ['cache_key' => $cacheKey],
            [
                'template_id' => $templateId,
                'device_type' => $deviceType,
                'rendered_html' => $renderedHtml,
                'css_compiled' => $cssCompiled,
                'variables_used' => $variablesUsed,
                'expires_at' => now()->addMinutes($expirationMinutes)
            ]
        );
    }
}
