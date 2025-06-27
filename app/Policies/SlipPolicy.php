<?php

namespace App\Policies;

use App\Models\Slip;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class SlipPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('slip_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Slip $slip): bool
    {
        return $user->hasPermissionTo('slip_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $slip);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('slip_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Slip $slip): bool
    {
        return $user->hasPermissionTo('slip_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $slip);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Slip $slip): bool
    {
        return $user->hasPermissionTo('slip_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $slip);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Slip $slip): bool
    {
        return $user->hasPermissionTo('slip_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $slip);
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Slip $slip): bool
    {
        $cacheKey = "slip_org_access:{$user->id}:{$slip->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $slip) {
            // For models directly linked to organisations
            if (method_exists($slip, 'organisations')) {
                foreach($slip->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($slip->organisation_id)) {
                return $slip->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($slip, 'activity') && $slip->activity) {
                foreach($slip->activity->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // Default: allow access if no specific organisation restriction
            return true;
        });
    }
}
