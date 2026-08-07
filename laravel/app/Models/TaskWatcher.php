<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskWatcher extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'notify_on_update',
        'notify_on_comment',
        'notify_on_completion',
        'added_by',
        'added_at',
    ];

    protected $casts = [
        'notify_on_update' => 'boolean',
        'notify_on_comment' => 'boolean',
        'notify_on_completion' => 'boolean',
        'added_at' => 'datetime',
    ];

    public $timestamps = false; // Using custom timestamp field

    /**
     * Relations
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function adder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Scopes
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeNotifyOnUpdates($query)
    {
        return $query->where('notify_on_update', true);
    }

    public function scopeNotifyOnComments($query)
    {
        return $query->where('notify_on_comment', true);
    }

    public function scopeNotifyOnCompletion($query)
    {
        return $query->where('notify_on_completion', true);
    }

    /**
     * Helpers
     */
    public function shouldNotifyFor(string $event): bool
    {
        return match($event) {
            'update' => $this->notify_on_update,
            'comment' => $this->notify_on_comment,
            'completion' => $this->notify_on_completion,
            default => false,
        };
    }
}
