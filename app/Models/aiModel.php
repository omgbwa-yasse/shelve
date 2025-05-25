<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiModel extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable = [
        'name', 'provider', 'version', 'api_type', 'capabilities', 'is_active',
        'model_family', 'parameter_size', 'file_size', 'quantization',
        'model_modified_at', 'digest', 'model_details', 'supports_streaming',
        'max_context_length', 'default_temperature'
    ];

    protected $casts = [
        'capabilities' => 'array',
        'model_details' => 'array',
        'is_active' => 'boolean',
        'supports_streaming' => 'boolean',
        'model_modified_at' => 'datetime',
        'parameter_size' => 'integer',
        'file_size' => 'integer',
        'max_context_length' => 'integer',
        'default_temperature' => 'decimal:2'
    ];



    // Relations
    public function interactions(): HasMany
    {
        return $this->hasMany(AiInteraction::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(AiJob::class);
    }

    public function chats(): HasMany
    {
        return $this->hasMany(AiChat::class);
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(AiModelMetric::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOllama($query)
    {
        return $query->where('provider', 'ollama');
    }

    public function scopeByFamily($query, string $family)
    {
        return $query->where('model_family', $family);
    }

    public function scopeByParameterSize($query, int $minSize = null, int $maxSize = null)
    {
        if ($minSize) {
            $query->where('parameter_size', '>=', $minSize);
        }
        if ($maxSize) {
            $query->where('parameter_size', '<=', $maxSize);
        }
        return $query;
    }

    // Accessors
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) return 'Unknown';

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $size = $this->file_size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    public function getParameterSizeFormattedAttribute(): string
    {
        if (!$this->parameter_size) return 'Unknown';

        if ($this->parameter_size >= 1000000000) {
            return round($this->parameter_size / 1000000000, 1) . 'B';
        } elseif ($this->parameter_size >= 1000000) {
            return round($this->parameter_size / 1000000, 1) . 'M';
        } elseif ($this->parameter_size >= 1000) {
            return round($this->parameter_size / 1000, 1) . 'K';
        }

        return $this->parameter_size;
    }

    // Méthodes utilitaires
    public function isOllama(): bool
    {
        return $this->provider === 'ollama';
    }

    public function supportsStreaming(): bool
    {
        return $this->supports_streaming && $this->isOllama();
    }

    public function getDefaultOptions(): array
    {
        return [
            'temperature' => $this->default_temperature ?? 0.7,
            'top_p' => 0.9,
            'top_k' => 40,
        ];
    }

    public function getUsageStats(int $days = 30): array
    {
        $fromDate = now()->subDays($days);

        return [
            'total_interactions' => $this->interactions()->where('created_at', '>=', $fromDate)->count(),
            'successful_interactions' => $this->interactions()->where('created_at', '>=', $fromDate)->where('status', 'completed')->count(),
            'failed_interactions' => $this->interactions()->where('created_at', '>=', $fromDate)->where('status', 'failed')->count(),
            'average_response_time' => $this->interactions()->where('created_at', '>=', $fromDate)->where('total_duration', '>', 0)->avg('total_duration'),
            'total_tokens' => $this->interactions()->where('created_at', '>=', $fromDate)->sum('tokens_used'),
        ];
    }

}
