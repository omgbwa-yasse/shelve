<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectMilestone extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'due_date',
        'status',
        'reached_at',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'reached_at' => 'datetime',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(ProjectDeliverable::class, 'milestone_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reach(): void
    {
        $this->update(['status' => 'reached', 'reached_at' => now()]);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->due_date !== null && $this->due_date->isPast();
    }
}
