<?php

namespace App\Policies;

use App\Models\BatchTransaction;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class BatchTransactionPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'batch_transaction_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, BatchTransaction $batchTransaction): bool|Response
    {
        return $this->canView($user, $batchTransaction, 'batch_transaction_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'batch_transaction_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, BatchTransaction $batchTransaction): bool|Response
    {
        return $this->canUpdate($user, $batchTransaction, 'batch_transaction_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, BatchTransaction $batchTransaction): bool|Response
    {
        return $this->canDelete($user, $batchTransaction, 'batch_transaction_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, BatchTransaction $batchTransaction): bool|Response
    {
        return $this->canForceDelete($user, $batchTransaction, 'batch_transaction_force_delete');
    }
}
