<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiModelMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'ai_model_id', 'metric_date', 'total_interactions', 'successful_interactions',
        'failed_interactions', 'total_tokens', 'average_response_time',
        'average_temperature', 'performance_stats'
    ];

    protected $casts = [
        'metric_date' => 'date',
        'performance_stats' => 'array',
        'average_response_time' => 'integer',
        'average_temperature' => 'decimal:2'
    ];

    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class);
    }

    public function getSuccessRateAttribute(): float
    {
        if ($this->total_interactions === 0) return 0;
        return ($this->successful_interactions / $this->total_interactions) * 100;
    }
}