<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'remind_at',
        'reminder_type',
        'message',
        'is_sent',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'is_sent' => 'boolean',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public $timestamps = false; // Using custom timestamp field

    /**
     * Relations
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('is_sent', false);
    }

    public function scopeSent($query)
    {
        return $query->where('is_sent', true);
    }

    public function scopeDue($query)
    {
        return $query->where('remind_at', '<=', now())
            ->where('is_sent', false);
    }

    public function scopeType($query, string $type)
    {
        return $query->where('reminder_type', $type);
    }

    /**
     * Helpers
     */
    public function markAsSent(): void
    {
        $this->update([
            'is_sent' => true,
            'sent_at' => now(),
        ]);
    }
}
