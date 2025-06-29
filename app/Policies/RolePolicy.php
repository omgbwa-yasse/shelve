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
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'role_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Role $role): bool|Response
    {
        return $this->canView($user, $role, 'role_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'role_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Role $role): bool|Response
    {
        return $this->canUpdate($user, $role, 'role_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Role $role): bool|Response
    {
        return $this->canDelete($user, $role, 'role_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Role $role): bool|Response
    {
        return $this->canForceDelete($user, $role, 'role_force_delete');
    }
}
