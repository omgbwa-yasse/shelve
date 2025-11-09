<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'priority',
        'assigned_to',
        'workflow_instance_id',
        'task_key',
        'form_data',
        'sequence_order',
        'parent_task_id',
        'taskable_type',
        'taskable_id',
        'due_date',
        'created_by',
        'updated_by',
        'completed_by',
        'completed_at',
    ];

    protected $casts = [
        'form_data' => 'array',
        'sequence_order' => 'integer',
        'due_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public $timestamps = false; // Using custom timestamp fields

    /**
     * Relations
     */
    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class, 'workflow_instance_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function completer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function parentTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'parent_task_id');
    }

    public function subTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'parent_task_id');
    }

    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    public function history(): HasMany
    {
        return $this->hasMany(TaskHistory::class, 'task_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class, 'task_id');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(TaskReminder::class, 'task_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'task_id');
    }

    public function watchers(): HasMany
    {
        return $this->hasMany(TaskWatcher::class, 'task_id');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeGeneral($query)
    {
        return $query->whereNull('workflow_instance_id');
    }

    public function scopeWorkflow($query)
    {
        return $query->whereNotNull('workflow_instance_id');
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Accessors
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() &&
            !in_array($this->status, ['completed', 'cancelled']);
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsGeneralTaskAttribute(): bool
    {
        return $this->workflow_instance_id === null;
    }

    public function getIsWorkflowTaskAttribute(): bool
    {
        return $this->workflow_instance_id !== null;
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

    public function assignTo(int $userId): void
    {
        $this->update(['assigned_to' => $userId]);
    }

    public function addWatcher(int $userId, ?int $addedBy = null): void
    {
        TaskWatcher::firstOrCreate([
            'task_id' => $this->id,
            'user_id' => $userId,
        ], [
            'added_by' => $addedBy ?? auth()->id(),
        ]);
    }
}
