<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordArtifactConditionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'artifact_id',
        'report_date',
        'overall_condition',
        'observations',
        'recommendations',
        'treatment_performed',
        'inspector_id',
        'conservator_id',
    ];

    protected $casts = [
        'report_date' => 'date',
    ];

    /**
     * Relations
     */
    public function artifact(): BelongsTo
    {
        return $this->belongsTo(RecordArtifact::class, 'artifact_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function conservator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conservator_id');
    }

    /**
     * Scopes
     */
    public function scopeByCondition($query, $condition)
    {
        return $query->where('overall_condition', $condition);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('report_date', '>=', now()->subDays($days));
    }

    public function scopeRequiringAttention($query)
    {
        return $query->whereIn('overall_condition', ['poor', 'critical']);
    }

    /**
     * Business logic methods
     */
    public function requiresUrgentAction(): bool
    {
        return $this->overall_condition === 'critical';
    }

    public function requiresMonitoring(): bool
    {
        return in_array($this->overall_condition, ['poor', 'fair']);
    }
}
