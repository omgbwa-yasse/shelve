<?php

namespace App\Traits;

use App\Models\Organisation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Trait HasDualOrganisation
 *
 * Applied to models with TWO organisation FKs (emitter/beneficiary pattern).
 * The model must define:
 *   protected string $emitterOrgField = 'sender_organisation_id';
 *   protected string $beneficiaryOrgField = 'recipient_organisation_id';
 *
 * Provides:
 * - scopeForOrganisation() — WHERE emitter = X OR beneficiary = X
 * - emitterOrganisation() / beneficiaryOrganisation() relationships
 * - involvesOrganisation($orgId) — used by the access-in-organisation Gate
 * - involvesCurrentOrg accessor
 */
trait HasDualOrganisation
{
    /**
     * Get the emitter organisation field name.
     */
    public function getEmitterOrgField(): string
    {
        return $this->emitterOrgField ?? 'sender_organisation_id';
    }

    /**
     * Get the beneficiary organisation field name.
     */
    public function getBeneficiaryOrgField(): string
    {
        return $this->beneficiaryOrgField ?? 'recipient_organisation_id';
    }

    /**
     * Scope: filter where the given org is either emitter or beneficiary.
     *
     * Usage: Model::forOrganisation($orgId)->get()
     */
    public function scopeForOrganisation($query, $organisationId)
    {
        $table = $this->getTable();
        $emitterField = $this->getEmitterOrgField();
        $beneficiaryField = $this->getBeneficiaryOrgField();

        return $query->where(function ($q) use ($table, $emitterField, $beneficiaryField, $organisationId) {
            $q->where($table . '.' . $emitterField, $organisationId)
              ->orWhere($table . '.' . $beneficiaryField, $organisationId);
        });
    }

    /**
     * Check if the given organisation is involved in this model (emitter or beneficiary).
     * Used by the access-in-organisation Gate for policy authorization.
     */
    public function involvesOrganisation($organisationId): bool
    {
        $emitterField = $this->getEmitterOrgField();
        $beneficiaryField = $this->getBeneficiaryOrgField();

        return $this->{$emitterField} == $organisationId
            || $this->{$beneficiaryField} == $organisationId;
    }

    /**
     * Relationship: the emitter organisation.
     */
    public function emitterOrganisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, $this->getEmitterOrgField());
    }

    /**
     * Relationship: the beneficiary organisation.
     */
    public function beneficiaryOrganisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, $this->getBeneficiaryOrgField());
    }

    /**
     * Accessor: check if the model involves the current user's org.
     */
    public function getInvolvesCurrentOrgAttribute(): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return $this->involvesOrganisation(Auth::user()->current_organisation_id);
    }
}
