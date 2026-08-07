<?php

namespace App\Policies;

use App\Models\Floor;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class FloorPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'floor_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Floor $floor): bool|Response
    {
        return $this->canView($user, $floor, 'floor_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'floor_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Floor $floor): bool|Response
    {
        return $this->canUpdate($user, $floor, 'floor_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Floor $floor): bool|Response
    {
        return $this->canDelete($user, $floor, 'floor_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Floor $floor): bool|Response
    {
        return $this->canForceDelete($user, $floor, 'floor_force_delete');
    }

    /**
     */
}
