<?php

namespace App\Policies;

use App\Models\RecordReactivation;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class RecordReactivationPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'record_reactivation_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, RecordReactivation $recordReactivation): bool|Response
    {
        return $this->canView($user, $recordReactivation, 'record_reactivation_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'record_reactivation_create');
    }

    /**
     * Determine whether the user can update (approve/reject) the model.
     */
    public function update(?User $user, RecordReactivation $recordReactivation): bool|Response
    {
        return $this->canUpdate($user, $recordReactivation, 'record_reactivation_approve');
    }
}
