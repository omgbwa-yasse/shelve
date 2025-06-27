<?php

namespace App\Policies;

use App\Models\Floor;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class FloorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('floor_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Floor $floor): bool
    {
        return $user->hasPermissionTo('floor_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $floor);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('floor_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Floor $floor): bool
    {
        return $user->hasPermissionTo('floor_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $floor);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Floor $floor): bool
    {
        return $user->hasPermissionTo('floor_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $floor);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Floor $floor): bool
    {
        return $user->hasPermissionTo('floor_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $floor);
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Floor $floor): bool
    {
        $cacheKey = "floor_org_access:{$user->id}:{$floor->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $floor) {
            // For models directly linked to organisations
            if (method_exists($floor, 'organisations')) {
                foreach($floor->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($floor->organisation_id)) {
                return $floor->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($floor, 'activity') && $floor->activity) {
                foreach($floor->activity->organisations as $organisation) {
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
