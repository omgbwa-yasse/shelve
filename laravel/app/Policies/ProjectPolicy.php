<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Policies\Concerns\ChecksAttachableAccess;
use Illuminate\Auth\Access\Response;

class ProjectPolicy extends BasePolicy
{
    use ChecksAttachableAccess;

    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'project_viewAny');
    }

    public function view(?User $user, Project $project): bool|Response
    {
        $result = $this->canView($user, $project, 'project_view');

        if ($result !== true) {
            return $result;
        }

        return $this->canAccessAttachable($user, $project->attachable_type, $project->attachable_id)
            ? true
            : $this->denyAsNotFound();
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'project_create');
    }

    public function update(?User $user, Project $project): bool|Response
    {
        $result = $this->canUpdate($user, $project, 'project_update');

        if ($result !== true) {
            return $result;
        }

        return $this->canAccessAttachable($user, $project->attachable_type, $project->attachable_id)
            ? true
            : $this->denyAsNotFound();
    }

    public function delete(?User $user, Project $project): bool|Response
    {
        $result = $this->canDelete($user, $project, 'project_delete');

        if ($result !== true) {
            return $result;
        }

        return $this->canAccessAttachable($user, $project->attachable_type, $project->attachable_id)
            ? true
            : $this->denyAsNotFound();
    }
}
