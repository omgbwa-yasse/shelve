<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRisk extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'task_id',
        'title',
        'description',
        'category',
        'probability',
        'impact',
        'status',
        'mitigation_plan',
        'owner_id',
        'review_date',
        'resolved_at',
        'created_by',
    ];

    protected $casts = [
        'review_date' => 'date',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    private const LEVEL_WEIGHTS = ['low' => 1, 'medium' => 2, 'high' => 3];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Score de criticité 1-9 (probabilité × impact) — matrice de risques classique. */
    public function getScoreAttribute(): int
    {
        return (self::LEVEL_WEIGHTS[$this->probability] ?? 2) * (self::LEVEL_WEIGHTS[$this->impact] ?? 2);
    }

    /** low (score<3) | medium (3-5) | high (>=6) — seuils standards d'une matrice 3x3. */
    public function getCriticalityAttribute(): string
    {
        $score = $this->score;

        return match (true) {
            $score >= 6 => 'high',
            $score >= 3 => 'medium',
            default => 'low',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'open' && $this->review_date !== null && $this->review_date->isPast();
    }

    public function mitigate(?string $plan = null): void
    {
        $this->update([
            'status' => 'mitigated',
            'mitigation_plan' => $plan ?? $this->mitigation_plan,
        ]);
    }

    public function close(): void
    {
        $this->update(['status' => 'closed', 'resolved_at' => now()]);
    }

    public function markOccurred(): void
    {
        $this->update(['status' => 'occurred', 'resolved_at' => now()]);
    }
}
