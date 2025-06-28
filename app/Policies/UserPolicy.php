<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class UserPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'users.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $targetUser): bool|Response
    {
        return $this->canView($user, $targetUser, 'users.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'users.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $targetUser): bool|Response
    {
        return $this->canUpdate($user, $targetUser, 'users.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $targetUser): bool|Response
    {
        // Protection spéciale : empêcher de supprimer un superadmin si on n'est pas soi-même superadmin
        if ($targetUser->hasRole('superadmin') && !Gate::forUser($user)->allows('is-superadmin')) {
            return $this->deny('Seul un superadmin peut supprimer un autre superadmin.');
        }

        return $this->canDelete($user, $targetUser, 'users.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $targetUser): bool|Response
    {
        return $this->canUpdate($user, $targetUser, 'users.update');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $targetUser): bool|Response
    {
        // Protection spéciale : empêcher de force delete un superadmin si on n'est pas soi-même superadmin
        if ($targetUser->hasRole('superadmin') && !Gate::forUser($user)->allows('is-superadmin')) {
            return $this->deny('Seul un superadmin peut supprimer définitivement un autre superadmin.');
        }

        return $this->canForceDelete($user, $targetUser, 'users.force_delete');
    }
}
