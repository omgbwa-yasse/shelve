<?php

namespace App\Traits;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Trait BelongsToOrganisation
 *
 * Applied to models with a single `organisation_id` FK.
 * Provides:
 * - Auto-assignment of organisation_id on creating
 * - scopeByOrganisation() query scope
 * - organisation() BelongsTo relationship
 * - isOwnedByCurrentOrg accessor
 */
trait BelongsToOrganisation
{
    /**
     * Boot the trait: auto-assign organisation_id on model creation.
     */
    public static function bootBelongsToOrganisation(): void
    {
        static::creating(function ($model) {
            if (empty($model->organisation_id) && Auth::check()) {
                $model->organisation_id = Auth::user()->current_organisation_id;
            }
        });
    }

    /**
     * Scope: filter by organisation_id.
     *
     * Usage: Model::byOrganisation($orgId)->get()
     */
    public function scopeByOrganisation($query, $organisationId)
    {
        return $query->where($this->getTable() . '.organisation_id', $organisationId);
    }

    /**
     * Relationship: the organisation that owns this model.
     */
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_id');
    }

    /**
     * Accessor: check if the model belongs to the current user's org.
     */
    public function getIsOwnedByCurrentOrgAttribute(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return $this->organisation_id === Auth::user()->current_organisation_id;
    }
}
