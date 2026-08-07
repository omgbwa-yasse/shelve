<?php

namespace App\Models;

use App\Traits\BelongsToOrganisation;
use App\Traits\HasAttachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganisation, HasAttachable;

    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'budget_planned',
        'owner_id',
        'attachable_type',
        'attachable_id',
        'organisation_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget_planned' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(Objective::class);
    }

    /**
     * Tâches du projet — réutilise `Task.taskable` (déjà polymorphe), aucune
     * colonne dédiée n'est nécessaire sur `tasks`.
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(ProjectMilestone::class)->orderBy('sort_order');
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(ProjectDeliverable::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(ProjectResource::class);
    }

    public function statusReports(): HasMany
    {
        return $this->hasMany(ProjectStatusReport::class)->orderByDesc('reported_at');
    }

    public function risks(): HasMany
    {
        return $this->hasMany(ProjectRisk::class);
    }

    /**
     * Budget réellement consommé = somme des ressources financières/matérielles
     * ayant un montant réel renseigné — jamais stocké en dur, toujours recalculé
     * pour ne pas se désynchroniser des lignes de ressources.
     */
    public function getBudgetActualAttribute(): float
    {
        $resources = $this->relationLoaded('resources') ? $this->resources : $this->resources()->get();

        return (float) $resources->sum(fn (ProjectResource $r) => (float) ($r->actual_amount ?? 0));
    }

    public function getIsOverBudgetAttribute(): bool
    {
        if ($this->budget_planned === null) {
            return false;
        }

        return $this->budget_actual > (float) $this->budget_planned;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
