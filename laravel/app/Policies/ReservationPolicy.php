<?php

namespace App\Policies;

use App\Models\Reservation;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class ReservationPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'reservation_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Reservation $reservation): bool|Response
    {
        return $this->canView($user, $reservation, 'reservation_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'reservation_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Reservation $reservation): bool|Response
    {
        return $this->canUpdate($user, $reservation, 'reservation_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Reservation $reservation): bool|Response
    {
        return $this->canDelete($user, $reservation, 'reservation_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Reservation $reservation): bool|Response
    {
        return $this->canForceDelete($user, $reservation, 'reservation_force_delete');
    }

    /**
     */
}
