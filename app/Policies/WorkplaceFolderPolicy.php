<?php

namespace App\Policies;

use App\Models\WorkplaceFolder;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class WorkplaceFolderPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'workplace_folder_viewAny');
    }

    public function view(?User $user, WorkplaceFolder $workplaceFolder): bool|Response
    {
        return $this->canView($user, $workplaceFolder, 'workplace_folder_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'workplace_folder_create');
    }

    public function update(?User $user, WorkplaceFolder $workplaceFolder): bool|Response
    {
        return $this->canUpdate($user, $workplaceFolder, 'workplace_folder_update');
    }

    public function delete(?User $user, WorkplaceFolder $workplaceFolder): bool|Response
    {
        return $this->canDelete($user, $workplaceFolder, 'workplace_folder_delete');
    }

    public function forceDelete(?User $user, WorkplaceFolder $workplaceFolder): bool|Response
    {
        return $this->canForceDelete($user, $workplaceFolder, 'workplace_folder_force_delete');
    }
}
