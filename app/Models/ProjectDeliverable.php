<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDeliverable extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'milestone_id',
        'name',
        'description',
        'status',
        'due_date',
        'attachment_id',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(ProjectMilestone::class, 'milestone_id');
    }

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submit(int $userId): void
    {
        $this->update(['status' => 'submitted', 'submitted_by' => $userId, 'submitted_at' => now()]);
    }

    public function approve(int $userId): void
    {
        $this->update(['status' => 'approved', 'approved_by' => $userId, 'approved_at' => now()]);
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }

    public function getIsOverdueAttribute(): bool
    {
        return !in_array($this->status, ['approved'], true) && $this->due_date !== null && $this->due_date->isPast();
    }
}
