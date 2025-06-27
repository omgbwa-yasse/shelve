<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use App\Policies\BasePolicy;

class TaskPolicy extends BasePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool|Response
    {
        return $this->canViewAny($user, 'task_viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool|Response
    {
        return $this->canView($user, $task, 'task_view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool|Response
    {
        return $this->canCreate($user, 'task_create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool|Response
    {
        return $this->canUpdate($user, $task, 'task_update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool|Response
    {
        return $this->canDelete($user, $task, 'task_delete');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool|Response
    {
        return $this->canForceDelete($user, $task, 'task_force_delete');
    }

    /**
     * Check if the user has access to the model within their current organisation.
     */
    private function checkOrganisationAccess(User $user, Task $task): bool
    {
        $cacheKey = "task_org_access:{$user->id}:{$task->id}:{$user->current_organisation_id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function() use ($user, $task) {
            // For models directly linked to organisations
            if (method_exists($task, 'organisations')) {
                foreach($task->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // For models with organisation_id column
            if (isset($task->organisation_id)) {
                return $task->organisation_id == $user->current_organisation_id;
            }

            // For models linked through activity (like Record)
            if (method_exists($task, 'activity') && $task->activity) {
                foreach($task->activity->organisations as $organisation) {
                    if ($organisation->id == $user->current_organisation_id) {
                        return true;
                    }
                }
            }

            // Default: allow access if no specific organisation restriction
            return true;
        });
    }
}
