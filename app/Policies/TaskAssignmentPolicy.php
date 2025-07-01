<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TaskAssignment;
use App\Models\Task;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TaskAssignmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any task assignments.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $user && $user->can('task-assignment.view');
    }

    /**
     * Determine whether the user can view a specific task assignment.
     */
    public function view(?User $user, TaskAssignment $assignment): bool|Response
    {
        if (!$user) {
            return false;
        }

        // Admin peut voir toutes les assignations
        if ($user->can('task-assignment.view-all')) {
            return true;
        }

        // L'utilisateur est le créateur de la tâche
        if ($user->can('task-assignment.view') && $assignment->task->created_by === $user->id) {
            return true;
        }

        // L'utilisateur est l'assigné
        if ($user->can('task-assignment.view') &&
            $assignment->assignee_type === 'user' && $assignment->assignee_id === $user->id) {
            return true;
        }

        // L'utilisateur fait partie de l'organisation assignée
        if ($user->can('task-assignment.view') &&
            $assignment->assignee_type === 'organisation') {
            $userOrganizationIds = $user->organisations->pluck('id')->toArray();
            if (in_array($assignment->assignee_id, $userOrganizationIds)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create a task assignment.
     */
    public function create(?User $user, ?Task $task = null): bool|Response
    {
        if (!$user) {
            return false;
        }

        // Admin peut assigner n'importe quelle tâche
        if ($user->can('task-assignment.create-any')) {
            return true;
        }

        // Si une tâche est spécifiée, vérifier si l'utilisateur peut l'assigner
        if ($task && $user->can('task-assignment.create') && $task->created_by === $user->id) {
            return true;
        }

        return $user->can('task-assignment.create');
    }

    /**
     * Determine whether the user can update a task assignment.
     */
    public function update(?User $user, TaskAssignment $assignment): bool|Response
    {
        if (!$user) {
            return false;
        }

        // Admin peut modifier n'importe quelle assignation
        if ($user->can('task-assignment.update-any')) {
            return true;
        }

        // Le créateur de la tâche peut modifier les assignations
        if ($user->can('task-assignment.update') && $assignment->task->created_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete a task assignment.
     */
    public function delete(?User $user, TaskAssignment $assignment): bool|Response
    {
        if (!$user) {
            return false;
        }

        // Admin peut supprimer n'importe quelle assignation
        if ($user->can('task-assignment.delete-any')) {
            return true;
        }

        // Le créateur de la tâche peut supprimer les assignations
        if ($user->can('task-assignment.delete') && $assignment->task->created_by === $user->id) {
            return true;
        }

        return false;
    }
}
