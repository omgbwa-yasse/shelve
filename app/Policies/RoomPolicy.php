<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class RoomPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'room_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Room $room): bool|Response
    {
        return $this->canView($user, $room, 'room_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'room_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Room $room): bool|Response
    {
        return $this->canUpdate($user, $room, 'room_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Room $room): bool|Response
    {
        return $this->canDelete($user, $room, 'room_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Room $room): bool|Response
    {
        return $this->canForceDelete($user, $room, 'room_force_delete');
    }

    /**
     */
}
