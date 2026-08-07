<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Task $task;
    protected string $eventType;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, string $eventType = 'update')
    {
        $this->task = $task;
        $this->eventType = $eventType;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = match($this->eventType) {
            'comment' => "A new comment was added to task: {$this->task->title}",
            'completion' => "Task has been completed: {$this->task->title}",
            default => "Task has been updated: {$this->task->title}",
        };

        return (new MailMessage)
            ->subject('Task Notification: ' . $this->task->title)
            ->line($message)
            ->action('View Task', route('tasks.show', $this->task))
            ->line('You are receiving this notification because you are watching this task.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'event_type' => $this->eventType,
            'message' => match($this->eventType) {
                'comment' => 'New comment added',
                'completion' => 'Task completed',
                default => 'Task updated',
            },
        ];
    }
}
