<?php

namespace App\Policies;
use App\Models\Transferring;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class TransferringPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'transferring_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Transferring $transferring): bool|Response
    {
        return $this->canView($user, $transferring, 'transferring_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'transferring_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Transferring $transferring): bool|Response
    {
        return $this->canUpdate($user, $transferring, 'transferring_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Transferring $transferring): bool|Response
    {
        return $this->canDelete($user, $transferring, 'transferring_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Transferring $transferring): bool|Response
    {
        return $this->canForceDelete($user, $transferring, 'transferring_force_delete');
    }
}
