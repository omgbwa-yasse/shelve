<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TaskComment;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TaskCommentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any task comments.
     */
    public function viewAny(?User $user): bool|Response
    {
        return $user && $user->can('task-comment.view');
    }

    /**
     * Determine whether the user can view a specific task comment.
     */
    public function view(?User $user, TaskComment $comment): bool|Response
    {
        if (!$user) {
            return false;
        }

        // Admin ou propriétaire du commentaire
        if ($user->can('task-comment.view-all') || $comment->user_id === $user->id) {
            return true;
        }

        // L'utilisateur peut voir les commentaires sur ses tâches
        if ($user->can('task-comment.view') && $comment->task->created_by === $user->id) {
            return true;
        }

        // L'utilisateur assigné à la tâche
        if ($user->can('task-comment.view') &&
            $comment->task->assignments()->where('assignee_type', 'user')
                ->where('assignee_id', $user->id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create a task comment.
     */
    public function create(?User $user): bool|Response
    {
        return $user && $user->can('task-comment.create');
    }

    /**
     * Determine whether the user can update a task comment.
     */
    public function update(?User $user, TaskComment $comment): bool|Response
    {
        if (!$user) {
            return false;
        }

        // Seuls les administrateurs ou le propriétaire peuvent modifier un commentaire
        if ($user->can('task-comment.update-all')) {
            return true;
        }

        // Propriétaire du commentaire
        return $user->can('task-comment.update-own') && $comment->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete a task comment.
     */
    public function delete(?User $user, TaskComment $comment): bool|Response
    {
        if (!$user) {
            return false;
        }

        // Administrateurs peuvent supprimer n'importe quel commentaire
        if ($user->can('task-comment.delete-all')) {
            return true;
        }

        // Propriétaire du commentaire
        if ($user->can('task-comment.delete-own') && $comment->user_id === $user->id) {
            return true;
        }

        // Propriétaire de la tâche
        if ($user->can('task-comment.delete-on-own-task') && $comment->task->created_by === $user->id) {
            return true;
        }

        return false;
    }
}
