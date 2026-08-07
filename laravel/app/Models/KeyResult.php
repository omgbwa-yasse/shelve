<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeyResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'objective_id',
        'title',
        'metric_type',
        'start_value',
        'target_value',
        'current_value',
        'unit',
        'status',
        'due_date',
        'sort_order',
    ];

    protected $casts = [
        'start_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'due_date' => 'date',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function objective(): BelongsTo
    {
        return $this->belongsTo(Objective::class);
    }

    /**
     * Progression bornée à [0, 1] — jamais stockée en dur, toujours recalculée
     * depuis les valeurs de départ/actuelle/cible pour ne jamais désynchroniser.
     */
    public function getProgressAttribute(): float
    {
        $start = (float) $this->start_value;
        $target = (float) $this->target_value;
        $current = (float) $this->current_value;

        if ($target === $start) {
            return $current >= $target ? 1.0 : 0.0;
        }

        $progress = ($current - $start) / ($target - $start);

        return round(max(0.0, min(1.0, $progress)), 4);
    }
}
