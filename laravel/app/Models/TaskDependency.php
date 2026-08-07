<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Dépendance entre deux tâches (prédécesseur → successeur), à la manière de
 * MS Project : `finish_to_start` (par défaut), `start_to_start`,
 * `finish_to_finish`, `start_to_finish`, avec un décalage (`lag_days`).
 */
class TaskDependency extends Model
{
    use HasFactory;

    public const TYPE_FINISH_TO_START = 'finish_to_start';
    public const TYPE_START_TO_START = 'start_to_start';
    public const TYPE_FINISH_TO_FINISH = 'finish_to_finish';
    public const TYPE_START_TO_FINISH = 'start_to_finish';

    protected $fillable = [
        'predecessor_id',
        'successor_id',
        'type',
        'lag_days',
    ];

    protected $casts = [
        'lag_days' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function predecessor(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'predecessor_id');
    }

    public function successor(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'successor_id');
    }
}
