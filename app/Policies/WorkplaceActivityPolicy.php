<?php

namespace App\Policies;

use App\Models\WorkplaceActivity;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class WorkplaceActivityPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'workplace_activity_viewAny');
    }

    public function view(?User $user, WorkplaceActivity $workplaceActivity): bool|Response
    {
        return $this->canView($user, $workplaceActivity, 'workplace_activity_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'workplace_activity_create');
    }

    public function update(?User $user, WorkplaceActivity $workplaceActivity): bool|Response
    {
        return $this->canUpdate($user, $workplaceActivity, 'workplace_activity_update');
    }

    public function delete(?User $user, WorkplaceActivity $workplaceActivity): bool|Response
    {
        return $this->canDelete($user, $workplaceActivity, 'workplace_activity_delete');
    }

    public function forceDelete(?User $user, WorkplaceActivity $workplaceActivity): bool|Response
    {
        return $this->canForceDelete($user, $workplaceActivity, 'workplace_activity_force_delete');
    }
}
