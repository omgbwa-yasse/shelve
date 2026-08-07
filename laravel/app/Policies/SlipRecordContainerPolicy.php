<?php

namespace App\Policies;

use App\Models\SlipRecordContainer;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class SlipRecordContainerPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'slip_record_container_viewAny');
    }

    public function view(?User $user, SlipRecordContainer $slipRecordContainer): bool|Response
    {
        return $this->canView($user, $slipRecordContainer, 'slip_record_container_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'slip_record_container_create');
    }

    public function update(?User $user, SlipRecordContainer $slipRecordContainer): bool|Response
    {
        return $this->canUpdate($user, $slipRecordContainer, 'slip_record_container_update');
    }

    public function delete(?User $user, SlipRecordContainer $slipRecordContainer): bool|Response
    {
        return $this->canDelete($user, $slipRecordContainer, 'slip_record_container_delete');
    }

    public function forceDelete(?User $user, SlipRecordContainer $slipRecordContainer): bool|Response
    {
        return $this->canForceDelete($user, $slipRecordContainer, 'slip_record_container_force_delete');
    }
}
