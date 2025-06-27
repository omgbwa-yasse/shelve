<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('user_viewAny', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $targetUser): bool
    {
        return $user->hasPermissionTo('user_view', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $targetUser);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('user_create', $user->currentOrganisation);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $targetUser): bool
    {
        return $user->hasPermissionTo('user_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $targetUser);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $targetUser): bool
    {
        return $user->hasPermissionTo('user_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $targetUser);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $targetUser): bool
    {
        return $user->hasPermissionTo('user_update', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $targetUser);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $targetUser): bool
    {
        return $user->hasPermissionTo('user_force_delete', $user->currentOrganisation) &&
            $this->checkOrganisationAccess($user, $targetUser);
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, User $targetUser): bool
    {
        $cacheKey = "user_org_access:{$user->id}:{$targetUser->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $targetUser) {
            // For models directly linked to organisations
            if (method_exists($targetUser, 'organisations')) {
                foreach($targetUser->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($targetUser->organisation_id)) {
                return $targetUser->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($targetUser, 'activity') && $targetUser->activity) {
                foreach($targetUser->activity->organisations as $organisation) {
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
