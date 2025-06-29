<?php

namespace App\Policies;

use App\Models\Deposit;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class DepositPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'deposit_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Deposit $deposit): bool|Response
    {
        return $this->canView($user, $deposit, 'deposit_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'deposit_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Deposit $deposit): bool|Response
    {
        return $this->canUpdate($user, $deposit, 'deposit_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Deposit $deposit): bool|Response
    {
        return $this->canDelete($user, $deposit, 'deposit_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Deposit $deposit): bool|Response
    {
        return $this->canForceDelete($user, $deposit, 'deposit_force_delete');
    }

    /**
     */
}
