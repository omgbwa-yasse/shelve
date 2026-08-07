<?php

namespace App\Policies;

use App\Models\Retention;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class RetentionPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'retention_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Retention $retention): bool|Response
    {
        return $this->canView($user, $retention, 'retention_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'retention_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Retention $retention): bool|Response
    {
        return $this->canUpdate($user, $retention, 'retention_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Retention $retention): bool|Response
    {
        return $this->canDelete($user, $retention, 'retention_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Retention $retention): bool|Response
    {
        return $this->canForceDelete($user, $retention, 'retention_force_delete');
    }

    /**
     */
}
