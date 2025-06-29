<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

class UserPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     * Supports guest users with optional type-hint.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'users.view');
    }

    /**
     * Determine whether the user can view the model.
     * Supports guest users with optional type-hint.
     */
    public function view(?User $user, User $targetUser): bool|Response
    {
        return $this->canView($user, $targetUser, 'users.view');
    }

    /**
     * Determine whether the user can create models.
     * Supports guest users with optional type-hint.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'users.create');
    }

    /**
     * Determine whether the user can update the model.
     * Supports guest users with optional type-hint.
     */
    public function update(?User $user, User $targetUser): bool|Response
    {
        return $this->canUpdate($user, $targetUser, 'users.update');
    }

    /**
     * Determine whether the user can delete the model.
     * Supports guest users with optional type-hint.
     */
    public function delete(?User $user, User $targetUser): bool|Response
    {
        if (!$user) {
            return $this->deny('Vous devez être connecté pour supprimer un utilisateur.');
        }

        // Protection spéciale : empêcher de supprimer un superadmin si on n'est pas soi-même superadmin
        if ($this->isSuperAdmin($targetUser) && !Gate::forUser($user)->allows('is-superadmin')) {
            return $this->deny('Seul un superadmin peut supprimer un autre superadmin.');
        }

        return $this->canDelete($user, $targetUser, 'users.delete');
    }

    /**
     * Determine whether the user can restore the model.
     * Supports guest users with optional type-hint.
     */
    public function restore(?User $user, User $targetUser): bool|Response
    {
        return $this->canUpdate($user, $targetUser, 'users.update');
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Supports guest users with optional type-hint.
     */
    public function forceDelete(?User $user, User $targetUser): bool|Response
    {
        if (!$user) {
            return $this->deny('Vous devez être connecté pour supprimer définitivement un utilisateur.');
        }

        // Protection spéciale : empêcher de force delete un superadmin si on n'est pas soi-même superadmin
        if ($this->isSuperAdmin($targetUser) && !Gate::forUser($user)->allows('is-superadmin')) {
            return $this->deny('Seul un superadmin peut supprimer définitivement un autre superadmin.');
        }

        return $this->canForceDelete($user, $targetUser, 'users.force_delete');
    }

    /**
     * Check if a user is a super administrator.
     */
    private function isSuperAdmin(User $user): bool
    {
        return Gate::forUser($user)->allows('is-superadmin');
    }
}
