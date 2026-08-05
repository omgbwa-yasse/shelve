<?php

namespace App\Policies;

use App\Models\ReservationRecord;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class ReservationRecordPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'reservation_record_viewAny');
    }

    public function view(?User $user, ReservationRecord $reservationRecord): bool|Response
    {
        return $this->canView($user, $reservationRecord, 'reservation_record_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'reservation_record_create');
    }

    public function update(?User $user, ReservationRecord $reservationRecord): bool|Response
    {
        return $this->canUpdate($user, $reservationRecord, 'reservation_record_update');
    }

    public function delete(?User $user, ReservationRecord $reservationRecord): bool|Response
    {
        return $this->canDelete($user, $reservationRecord, 'reservation_record_delete');
    }

    public function forceDelete(?User $user, ReservationRecord $reservationRecord): bool|Response
    {
        return $this->canForceDelete($user, $reservationRecord, 'reservation_record_force_delete');
    }
}
