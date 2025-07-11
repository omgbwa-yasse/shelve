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
        'max_context_length', 'default_temperature', 'api_endpoint', 'api_key',
        'api_headers', 'api_parameters', 'external_model_id', 'cost_per_token_input',
        'cost_per_token_output', 'is_default', 'model_type'
    ];

    protected $casts = [
        'capabilities' => 'array',
        'model_details' => 'array',
        'api_headers' => 'array',
        'api_parameters' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'supports_streaming' => 'boolean',
        'model_modified_at' => 'datetime',
        'parameter_size' => 'integer',
        'file_size' => 'integer',
        'max_context_length' => 'integer',
        'default_temperature' => 'decimal:2',
        'cost_per_token_input' => 'decimal:8',
        'cost_per_token_output' => 'decimal:8'
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

    // Méthodes pour les modèles API
    public function isApiModel(): bool
    {
        return $this->model_type === 'api';
    }

    public function isLocalModel(): bool
    {
        return $this->model_type === 'local';
    }

    public function getApiEndpoint(): ?string
    {
        return $this->api_endpoint;
    }

    public function getApiKey(): ?string
    {
        if ($this->api_key) {
            try {
                return decrypt($this->api_key);
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    public function setApiKey(?string $apiKey): void
    {
        $this->api_key = $apiKey ? encrypt($apiKey) : null;
    }

    public function hasValidApiConfig(): bool
    {
        return $this->isApiModel() && $this->api_endpoint && $this->getApiKey();
    }

    // Scope pour les modèles par défaut
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeApiModels($query)
    {
        return $query->where('model_type', 'api');
    }

    public function scopeLocalModels($query)
    {
        return $query->where('model_type', 'local');
    }

    // Méthode statique pour récupérer le modèle par défaut
    public static function getDefaultModel(): ?self
    {
        // Essayer d'abord par la table de configuration
        $defaultId = \App\Models\AiGlobalSetting::getDefaultModelId();
        if ($defaultId) {
            $model = static::find($defaultId);
            if ($model && $model->is_active) {
                return $model;
            }
        }

        // Fallback: premier modèle marqué comme défaut
        $model = static::where('is_default', true)->where('is_active', true)->first();
        if ($model) {
            return $model;
        }

        // Dernier fallback: premier modèle actif Ollama
        return static::where('provider', 'ollama')->where('is_active', true)->first();
    }

    // Calculer le coût estimé pour un nombre de tokens
    public function estimateCost(int $inputTokens, int $outputTokens = 0): float
    {
        if (!$this->isApiModel()) {
            return 0.0; // Les modèles locaux sont gratuits
        }

        $inputCost = ($this->cost_per_token_input ?? 0) * $inputTokens;
        $outputCost = ($this->cost_per_token_output ?? 0) * $outputTokens;

        return $inputCost + $outputCost;
    }

}
