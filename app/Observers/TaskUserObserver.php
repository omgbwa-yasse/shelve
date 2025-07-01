<?php

namespace App\Observers;

use App\Models\TaskUser;
use App\Enums\TaskAssigneeType;
use App\Models\TaskAssignment;
use Illuminate\Support\Facades\DB;

class TaskUserObserver
{
    /**
     * Handle the TaskUser "created" event.
     */
    public function created(TaskUser $taskUser): void
    {
        // Vérifier si une assignation correspondante existe déjà
        $exists = TaskAssignment::where('task_id', $taskUser->task_id)
            ->where('assignee_user_id', $taskUser->user_id)
            ->where('assignee_type', 'user')
            ->exists();
        
        if (!$exists) {
            // Créer une nouvelle assignation
            TaskAssignment::create([
                'task_id' => $taskUser->task_id,
                'assignee_user_id' => $taskUser->user_id,
                'assignee_type' => 'user',
                'role' => 'assignee',  // Valeur par défaut
                'allocation_percentage' => 100,  // Valeur par défaut
                'assigned_by' => \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::user()->id : null,
                'assigned_at' => now(),
            ]);
        }
    }

    /**
     * Handle the TaskUser "deleted" event.
     */
    public function deleted(TaskUser $taskUser): void
    {
        // Supprimer l'assignation correspondante
        TaskAssignment::where('task_id', $taskUser->task_id)
            ->where('assignee_user_id', $taskUser->user_id)
            ->where('assignee_type', 'user')
            ->delete();
    }
}
