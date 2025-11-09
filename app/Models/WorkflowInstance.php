<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'definition_id',
        'name',
        'status',
        'current_state',
        'started_by',
        'updated_by',
        'completed_by',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'current_state' => 'array',
        'started_at' => 'datetime',
        'updated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public $timestamps = false; // Using custom timestamp fields

    /**
     * Relations
     */
    public function definition(): BelongsTo
    {
        return $this->belongsTo(WorkflowDefinition::class, 'definition_id');
    }

    public function starter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'workflow_instance_id');
    }

    /**
     * Scopes
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePaused($query)
    {
        return $query->where('status', 'paused');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Accessors
     */
    public function getIsRunningAttribute(): bool
    {
        return $this->status === 'running';
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsPausedAttribute(): bool
    {
        return $this->status === 'paused';
    }

    /**
     * Helpers
     */
    public function complete(?int $userId = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_by' => $userId ?? auth()->id(),
            'completed_at' => now(),
        ]);
    }

    public function pause(): void
    {
        $this->update(['status' => 'paused']);
    }

    public function resume(): void
    {
        $this->update(['status' => 'running']);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }
}
