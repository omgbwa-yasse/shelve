<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Task;
use App\Enums\TaskStatus;
use App\Enums\TaskPriority;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $user && $user->can('task.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Task $task): bool|Response
    {
        if (!$user) {
            return false;
        }

        // Admin peut voir toutes les tâches
        if ($user->can('task.view-all')) {
            return true;
        }

        // Utilisateur peut voir les tâches qui lui sont assignées
        if ($user->can('task.view')) {
            // Tâche créée par l'utilisateur
            if ($task->created_by === $user->id) {
                return true;
            }

            // Tâche assignée à l'utilisateur
            if ($task->assignments()->where('assignee_type', 'user')
                ->where('assignee_id', $user->id)->exists()) {
                return true;
            }

            // Tâche assignée à une organisation de l'utilisateur
            $userOrganizationIds = $user->organisations->pluck('id')->toArray();
            if (!empty($userOrganizationIds) && $task->assignments()->where('assignee_type', 'organisation')
                ->whereIn('assignee_id', $userOrganizationIds)->exists()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool|Response
    {
        return $user && $user->can('task.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Task $task): bool|Response
    {
        if (!$user) {
            return false;
        }

        // Admin peut mettre à jour toutes les tâches
        if ($user->can('task.update-all')) {
            return true;
        }

        // Utilisateur peut mettre à jour ses propres tâches
        if ($user->can('task.update-own') && $task->created_by === $user->id) {
            return true;
        }

        // L'utilisateur assigné peut mettre à jour certains champs de la tâche
        if ($user->can('task.update-assigned') &&
            $task->assignments()->where('assignee_type', 'user')
                ->where('assignee_id', $user->id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Task $task): bool|Response
    {
        if (!$user) {
            return false;
        }

        // Admin peut supprimer toutes les tâches
        if ($user->can('task.delete-all')) {
            return true;
        }

        // Utilisateur peut supprimer ses propres tâches
        if ($user->can('task.delete-own') && $task->created_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Task $task): bool|Response
    {
        return $user && $user->can('task.force-delete');
    }

    /**
     * Détermine si l'utilisateur peut assigner une tâche
     */
    public function assign(?User $user, Task $task): bool|Response
    {
        // Admin peut assigner toutes les tâches
        if ($user && $user->can('task.assign')) {
            return true;
        }

        // Créateur peut assigner ses propres tâches
        if ($user && $user->can('task.assign-own') && $task->created_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut compléter une tâche
     */
    public function complete(?User $user, Task $task): bool|Response
    {
        if (!$user) {
            return false;
        }

        // Admin peut compléter toutes les tâches
        if ($user->can('task.complete-any')) {
            return true;
        }

        // La tâche ne doit pas être déjà complétée ou annulée
        if ($task->status && in_array($task->status, [
            TaskStatus::DONE,
            TaskStatus::CANCELLED
        ])) {
            return false;
        }

        // Le créateur peut compléter sa propre tâche
        if ($user->can('task.complete-own') && $task->created_by === $user->id) {
            return true;
        }

        // L'assigné peut compléter la tâche
        if ($user->can('task.complete') &&
            $task->assignments()->where('assignee_type', 'user')
                ->where('assignee_id', $user->id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Détermine si l'utilisateur peut commenter une tâche
     */
    public function comment(?User $user, Task $task): bool|Response
    {
        if (!$user) {
            return false;
        }

        // Admin peut commenter toutes les tâches
        if ($user->can('task.comment-any')) {
            return true;
        }

        // La tâche ne doit pas être annulée
        if ($task->status && $task->status === TaskStatus::CANCELLED) {
            return false;
        }

        // Le créateur peut commenter sa propre tâche
        if ($user->can('task.comment-own') && $task->created_by === $user->id) {
            return true;
        }

        // L'assigné peut commenter la tâche
        if ($user->can('task.comment') &&
            $task->assignments()->where('assignee_type', 'user')
                ->where('assignee_id', $user->id)->exists()) {
            return true;
        }

        return false;
    }
}
