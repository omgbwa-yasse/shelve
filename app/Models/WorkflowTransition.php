<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowTransition extends Model
{
    use HasFactory;

    protected $fillable = [
        'definition_id',
        'from_task_key',
        'to_task_key',
        'name',
        'condition',
        'sequence_order',
        'is_default',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sequence_order' => 'integer',
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = false; // Using custom timestamp fields

    /**
     * Relations
     */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'definition_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scopes
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeFrom($query, string $taskKey)
    {
        return $query->where('from_task_key', $taskKey);
    }

    public function scopeTo($query, string $taskKey)
    {
        return $query->where('to_task_key', $taskKey);
    }
}
