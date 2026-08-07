<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Task $task;
    protected string $comment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Task $task, string $comment)
    {
        $this->task = $task;
        $this->comment = $comment;
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
        return (new MailMessage)
            ->subject('New comment on task: ' . $this->task->title)
            ->line("A new comment was added to task: {$this->task->title}")
            ->line("Comment: " . substr($this->comment, 0, 100) . (strlen($this->comment) > 100 ? '...' : ''))
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
            'event_type' => 'comment',
            'comment_preview' => substr($this->comment, 0, 100),
        ];
    }
}
