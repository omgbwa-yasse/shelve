<?php

namespace App\Policies;

use App\Models\Communication;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class CommunicationPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     * Supports guest users with optional type-hint.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'communication_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     * Supports guest users with optional type-hint.
     */
    public function view(?User $user, Communication $communication): bool|Response
    {
        return $this->canView($user, $communication, 'communication_view');
    }

    /**
     * Determine whether the user can create models.
     * Supports guest users with optional type-hint.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'communication_create');
    }

    /**
     * Determine whether the user can update the model.
     * Supports guest users with optional type-hint.
     */
    public function update(?User $user, Communication $communication): bool|Response
    {
        return $this->canUpdate($user, $communication, 'communication_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    /**
     * Determine whether the user can delete the model.
     * Supports guest users with optional type-hint.
     */
    public function delete(?User $user, Communication $communication): bool|Response
    {
        return $this->canDelete($user, $communication, 'communication_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Supports guest users with optional type-hint.
     */
    public function forceDelete(?User $user, Communication $communication): bool|Response
    {
        return $this->canForceDelete($user, $communication, 'communication_force_delete');
    }
}
