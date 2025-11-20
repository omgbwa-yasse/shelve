<?php

namespace App\Observers;

use App\Models\Task;
use App\Models\TaskHistory;
use App\Notifications\TaskUpdatedNotification;
use App\Notifications\TaskCommentNotification;
use Illuminate\Support\Facades\Notification;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        TaskHistory::create([
            'task_id' => $task->id,
            'field_changed' => 'task',
            'old_value' => null,
            'new_value' => 'Task created',
            'action' => 'created',
            'changed_by' => $task->created_by,
            'changed_at' => now(),
        ]);
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        $changes = $task->getChanges();
        $original = $task->getOriginal();

        foreach ($changes as $field => $newValue) {
            // Skip timestamps and internal fields
            if (in_array($field, ['updated_at', 'updated_by'])) {
                continue;
            }

            $oldValue = $original[$field] ?? null;

            // Determine the action based on the field changed
            $action = 'updated';
            if ($field === 'status') {
                $action = 'status_changed';
            } elseif ($field === 'assigned_to') {
                $action = 'assigned';
            } elseif ($field === 'completed_at' && $newValue !== null) {
                $action = 'completed';
            }

            TaskHistory::create([
                'task_id' => $task->id,
                'field_changed' => $field,
                'old_value' => is_array($oldValue) ? json_encode($oldValue) : $oldValue,
                'new_value' => is_array($newValue) ? json_encode($newValue) : $newValue,
                'action' => $action,
                'changed_by' => $task->updated_by ?? auth()->id() ?? $task->created_by,
                'changed_at' => now(),
            ]);
        }

        // Notify watchers if task was updated
        if (!empty($changes)) {
            $this->notifyWatchers($task, 'update');
        }
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        TaskHistory::create([
            'task_id' => $task->id,
            'field_changed' => 'task',
            'old_value' => 'Task existed',
            'new_value' => null,
            'action' => 'deleted',
            'changed_by' => auth()->id() ?? $task->updated_by ?? $task->created_by,
            'changed_at' => now(),
        ]);
    }

    /**
     * Notify watchers based on the event type
     */
    protected function notifyWatchers(Task $task, string $event): void
    {
        $watchers = $task->watchers()->get();

        foreach ($watchers as $watcher) {
            if ($watcher->shouldNotifyFor($event)) {
                // Send notification to watcher
                $watcher->user->notify(new TaskUpdatedNotification($task, $event));
            }
        }
    }
}
