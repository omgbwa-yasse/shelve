<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy extends BasePolicy
{
    public function viewAny(?User $user): bool|Response
    {
        return $this->canViewAny($user, 'task_viewAny');
    }

    public function view(?User $user, Task $task): bool|Response
    {
        return $this->canView($user, $task, 'task_view');
    }

    public function create(?User $user): bool|Response
    {
        return $this->canCreate($user, 'task_create');
    }

    public function update(?User $user, Task $task): bool|Response
    {
        return $this->canUpdate($user, $task, 'task_update');
    }

    public function delete(?User $user, Task $task): bool|Response
    {
        return $this->canDelete($user, $task, 'task_delete');
    }

    public function forceDelete(?User $user, Task $task): bool|Response
    {
        return $this->canForceDelete($user, $task, 'task_force_delete');
    }
}
