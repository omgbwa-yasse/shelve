<?php

namespace App\Policies;

use App\Models\Shelf;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class ShelfPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('shelf_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Shelf $shelf): bool
    {
        return $user->hasPermissionTo('shelf_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $shelf);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('shelf_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Shelf $shelf): bool
    {
        return $user->hasPermissionTo('shelf_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $shelf);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Shelf $shelf): bool
    {
        return $user->hasPermissionTo('shelf_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $shelf);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Shelf $shelf): bool
    {
        return $user->hasPermissionTo('shelf_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $shelf);
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Shelf $shelf): bool
    {
        $cacheKey = "shelf_org_access:{$user->id}:{$shelf->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $shelf) {
            // For models directly linked to organisations
            if (method_exists($shelf, 'organisations')) {
                foreach($shelf->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($shelf->organisation_id)) {
                return $shelf->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($shelf, 'activity') && $shelf->activity) {
                foreach($shelf->activity->organisations as $organisation) {
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
