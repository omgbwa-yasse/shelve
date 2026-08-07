<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'attachable_type',
        'attachable_id',
        'description',
        'attached_by',
        'attached_at',
    ];

    protected $casts = [
        'attached_at' => 'datetime',
    ];

    public $timestamps = false; // Using custom timestamp field

    /**
     * Relations
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function attachedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attached_by');
    }

    /**
     * Polymorphic relation to the attached entity
     */
    public function attachable()
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopeType($query, string $type)
    {
        return $query->where('attachable_type', $type);
    }

    public function scopeBooks($query)
    {
        return $query->where('attachable_type', 'Book');
    }

    public function scopeDocuments($query)
    {
        return $query->where('attachable_type', 'Document');
    }

    public function scopeRecords($query)
    {
        return $query->where('attachable_type', 'RecordPhysical');
    }

}
