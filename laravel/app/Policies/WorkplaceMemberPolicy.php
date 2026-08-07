<?php

namespace App\Policies;

use App\Models\WorkplaceMember;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class WorkplaceMemberPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'workplace_member_viewAny');
    }

    public function view(?User $user, WorkplaceMember $workplaceMember): bool|Response
    {
        return $this->canView($user, $workplaceMember, 'workplace_member_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'workplace_member_create');
    }

    public function update(?User $user, WorkplaceMember $workplaceMember): bool|Response
    {
        return $this->canUpdate($user, $workplaceMember, 'workplace_member_update');
    }

    public function delete(?User $user, WorkplaceMember $workplaceMember): bool|Response
    {
        return $this->canDelete($user, $workplaceMember, 'workplace_member_delete');
    }

    public function forceDelete(?User $user, WorkplaceMember $workplaceMember): bool|Response
    {
        return $this->canForceDelete($user, $workplaceMember, 'workplace_member_force_delete');
    }
}
