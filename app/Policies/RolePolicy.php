<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Cache;
use App\Policies\BasePolicy;

class RolePolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'role_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool|Response
    {
        return $this->canView($user, $role, 'role_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'role_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): bool|Response
    {
        return $this->canUpdate($user, $role, 'role_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool|Response
    {
        return $this->canDelete($user, $role, 'role_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Role $role): bool|Response
    {
        return $this->canForceDelete($user, $role, 'role_force_delete');
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    protected function checkOrganisationAccess(User $user, Model $role): bool
    {
        $cacheKey = "role_org_access:{$user->id}:{$role->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $role) {
            // For models directly linked to organisations
            if (method_exists($role, 'organisations')) {
                foreach($role->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($role->organisation_id)) {
                return $role->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($role, 'activity') && $role->activity) {
                foreach($role->activity->organisations as $organisation) {
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
