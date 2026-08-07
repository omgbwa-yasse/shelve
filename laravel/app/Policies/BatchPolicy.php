<?php

namespace App\Policies;

use App\Models\Batch;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class BatchPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'batch_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Batch $batch): bool|Response
    {
        return $this->canView($user, $batch, 'batch_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'batch_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Batch $batch): bool|Response
    {
        return $this->canUpdate($user, $batch, 'batch_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Batch $batch): bool|Response
    {
        return $this->canDelete($user, $batch, 'batch_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Batch $batch): bool|Response
    {
        return $this->canForceDelete($user, $batch, 'batch_force_delete');
    }


}
