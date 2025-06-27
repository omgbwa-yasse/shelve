<?php

namespace App\Policies;

use App\Models\Communication;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class CommunicationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('communication_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Communication $communication): bool
    {
        return $user->hasPermissionTo('communication_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $communication);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('communication_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Communication $communication): bool
    {
        return $user->hasPermissionTo('communication_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $communication);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Communication $communication): bool
    {
        return $user->hasPermissionTo('communication_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $communication);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Communication $communication): bool
    {
        return $user->hasPermissionTo('communication_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $communication);
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Communication $communication): bool
    {
        $cacheKey = "communication_org_access:{$user->id}:{$communication->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $communication) {
            // For models directly linked to organisations
            if (method_exists($communication, 'organisations')) {
                foreach($communication->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($communication->organisation_id)) {
                return $communication->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($communication, 'activity') && $communication->activity) {
                foreach($communication->activity->organisations as $organisation) {
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
