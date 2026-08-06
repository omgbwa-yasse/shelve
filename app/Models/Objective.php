<?php

namespace App\Models;

use App\Traits\BelongsToOrganisation;
use App\Traits\HasAttachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Le "O" d'OKR. Rattachable directement (Workplace/Organisation/User, via
 * `HasAttachable`) OU via un projet (`project_id` nullable) — un OKR d'équipe
 * n'a pas toujours de projet formel derrière lui.
 */
class Objective extends Model
{
    use HasFactory, SoftDeletes, BelongsToOrganisation, HasAttachable;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'period_start',
        'period_end',
        'status',
        'owner_id',
        'attachable_type',
        'attachable_id',
        'organisation_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

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

    public function keyResults(): HasMany
    {
        return $this->hasMany(KeyResult::class)->orderBy('sort_order');
    }

    /**
     * Progression globale de l'objectif = moyenne de la progression de ses
     * résultats clés (0 si aucun résultat clé, pour éviter une division par 0).
     */
    public function getProgressAttribute(): float
    {
        $keyResults = $this->relationLoaded('keyResults') ? $this->keyResults : $this->keyResults()->get();

        if ($keyResults->isEmpty()) {
            return 0.0;
        }

        return round($keyResults->avg(fn (KeyResult $kr) => $kr->progress), 4);
    }
}
