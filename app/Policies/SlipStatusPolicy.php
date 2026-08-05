<?php

namespace App\Policies;

use App\Models\SlipStatus;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class SlipStatusPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'slip_status_viewAny');
    }

    public function view(?User $user, SlipStatus $slipStatus): bool|Response
    {
        return $this->canView($user, $slipStatus, 'slip_status_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'slip_status_create');
    }

    public function update(?User $user, SlipStatus $slipStatus): bool|Response
    {
        return $this->canUpdate($user, $slipStatus, 'slip_status_update');
    }

    public function delete(?User $user, SlipStatus $slipStatus): bool|Response
    {
        return $this->canDelete($user, $slipStatus, 'slip_status_delete');
    }

    public function forceDelete(?User $user, SlipStatus $slipStatus): bool|Response
    {
        return $this->canForceDelete($user, $slipStatus, 'slip_status_force_delete');
    }
}
