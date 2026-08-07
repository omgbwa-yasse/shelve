<?php

namespace App\Policies;

use App\Models\WorkplaceBookmark;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class WorkplaceBookmarkPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'workplace_bookmark_viewAny');
    }

    public function view(?User $user, WorkplaceBookmark $workplaceBookmark): bool|Response
    {
        return $this->canView($user, $workplaceBookmark, 'workplace_bookmark_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'workplace_bookmark_create');
    }

    public function update(?User $user, WorkplaceBookmark $workplaceBookmark): bool|Response
    {
        return $this->canUpdate($user, $workplaceBookmark, 'workplace_bookmark_update');
    }

    public function delete(?User $user, WorkplaceBookmark $workplaceBookmark): bool|Response
    {
        return $this->canDelete($user, $workplaceBookmark, 'workplace_bookmark_delete');
    }

    public function forceDelete(?User $user, WorkplaceBookmark $workplaceBookmark): bool|Response
    {
        return $this->canForceDelete($user, $workplaceBookmark, 'workplace_bookmark_force_delete');
    }
}
