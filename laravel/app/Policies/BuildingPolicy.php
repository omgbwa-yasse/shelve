<?php

namespace App\Policies;

use App\Models\Building;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class BuildingPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'building_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Building $building): bool|Response
    {
        return $this->canView($user, $building, 'building_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'building_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Building $building): bool|Response
    {
        return $this->canUpdate($user, $building, 'building_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Building $building): bool|Response
    {
        return $this->canDelete($user, $building, 'building_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Building $building): bool|Response
    {
        return $this->canForceDelete($user, $building, 'building_force_delete');
    }
}
