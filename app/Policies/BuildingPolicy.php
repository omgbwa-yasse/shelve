<?php

namespace App\Policies;

use App\Models\Building;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class BuildingPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('building_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Building $building): bool
    {
        return $user->hasPermissionTo('building_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $building);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('building_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Building $building): bool
    {
        return $user->hasPermissionTo('building_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $building);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Building $building): bool
    {
        return $user->hasPermissionTo('building_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $building);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Building $building): bool
    {
        return $user->hasPermissionTo('building_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $building);
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Building $building): bool
    {
        $cacheKey = "building_org_access:{$user->id}:{$building->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $building) {
            // For models directly linked to organisations
            if (method_exists($building, 'organisations')) {
                foreach($building->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($building->organisation_id)) {
                return $building->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($building, 'activity') && $building->activity) {
                foreach($building->activity->organisations as $organisation) {
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
