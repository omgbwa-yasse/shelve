<?php

namespace App\Observers;

use App\Models\TaskAssignment;
use App\Enums\TaskAssigneeType;
use Illuminate\Support\Facades\DB;

class TaskAssignmentObserver
{
    /**
     * Handle the TaskAssignment "created" event.
     */
    public function created(TaskAssignment $taskAssignment): void
    {
        // Si l'assignation est à un utilisateur, ajouter dans task_users
        if ($taskAssignment->assignee_type == 'user' && $taskAssignment->assignee_user_id) {
            DB::table('task_users')->updateOrInsert(
                [
                    'task_id' => $taskAssignment->task_id,
                    'user_id' => $taskAssignment->assignee_user_id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Handle the TaskAssignment "updated" event.
     */
    public function updated(TaskAssignment $taskAssignment): void
    {
        // Si l'assignation est devenue de type utilisateur
        if ($taskAssignment->assignee_type == 'user' && $taskAssignment->assignee_user_id) {
            DB::table('task_users')->updateOrInsert(
                [
                    'task_id' => $taskAssignment->task_id,
                    'user_id' => $taskAssignment->assignee_user_id,
                ],
                [
                    'updated_at' => now(),
                ]
            );
        }
        // Si l'assignation n'est plus à un utilisateur, supprimer de task_users
        elseif ($taskAssignment->assignee_type != 'user' || !$taskAssignment->assignee_user_id) {
            // Vérifie si cette entrée existait avant
            if ($taskAssignment->getOriginal('assignee_type') == 'user' && $taskAssignment->getOriginal('assignee_user_id')) {
                DB::table('task_users')->where([
                    'task_id' => $taskAssignment->task_id,
                    'user_id' => $taskAssignment->getOriginal('assignee_user_id'),
                ])->delete();
            }
        }
    }

    /**
     * Handle the TaskAssignment "deleted" event.
     */
    public function deleted(TaskAssignment $taskAssignment): void
    {
        // Si c'était une assignation à un utilisateur, supprimer de task_users
        if ($taskAssignment->assignee_type == 'user' && $taskAssignment->assignee_user_id) {
            DB::table('task_users')->where([
                'task_id' => $taskAssignment->task_id,
                'user_id' => $taskAssignment->assignee_user_id,
            ])->delete();
        }
    }
}
