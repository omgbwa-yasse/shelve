<?php

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class ActivityPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'activity_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Activity $activity): bool|Response
    {
        return $this->canView($user, $activity, 'activity_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'activity_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Activity $activity): bool|Response
    {
        return $this->canUpdate($user, $activity, 'activity_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Activity $activity): bool|Response
    {
        return $this->canDelete($user, $activity, 'activity_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Activity $activity): bool|Response
    {
        return $this->canForceDelete($user, $activity, 'activity_force_delete');
    }
}
