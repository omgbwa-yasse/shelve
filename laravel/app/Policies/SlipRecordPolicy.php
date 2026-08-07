<?php

namespace App\Policies;

use App\Models\SlipRecord;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class SlipRecordPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'slip_record_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, SlipRecord $slipRecord): bool|Response
    {
        return $this->canView($user, $slipRecord, 'slip_record_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'slip_record_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, SlipRecord $slipRecord): bool|Response
    {
        return $this->canUpdate($user, $slipRecord, 'slip_record_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, SlipRecord $slipRecord): bool|Response
    {
        return $this->canDelete($user, $slipRecord, 'slip_record_delete');
    }

    /**
     * Determine whether the user can restore the model.
     * Note: Uses update permission as there is no restore permission in the seeder
     */
    public function restore(?User $user, SlipRecord $slipRecord): bool|Response
    {
        $result = $this->canUpdate($user, $slipRecord, 'slip_record_update');
        return is_bool($result) ? $this->allow() : $result;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, SlipRecord $slipRecord): bool|Response
    {
        return $this->canForceDelete($user, $slipRecord, 'slip_record_force_delete');
    }

    /**
     */
}
