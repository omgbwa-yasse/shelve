<?php

namespace App\Enums;

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case REVIEW = 'review';
    case DONE = 'done';
    case CANCELLED = 'cancelled';

    /**
     * Get the label for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::TODO => 'À faire',
            self::IN_PROGRESS => 'En cours',
            self::REVIEW => 'En révision',
            self::DONE => 'Terminée',
            self::CANCELLED => 'Annulée',
        };
    }

    /**
     * Get the badge color for the enum value.
     *
     * @return string
     */
    public function color(): string
    {
        return match($this) {
            self::TODO => 'bg-warning',
            self::IN_PROGRESS => 'bg-info',
            self::REVIEW => 'bg-primary',
            self::DONE => 'bg-success',
            self::CANCELLED => 'bg-danger',
        };
    }

    /**
     * Get all cases as an array for select dropdown.
     *
     * @return array
     */
    public static function forSelect(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [
            $case->value => $case->label()
        ])->all();
    }

    /**
     * Check if the task is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return in_array($this, [self::TODO, self::IN_PROGRESS, self::REVIEW]);
    }

    /**
     * Check if the task is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this === self::DONE;
    }

    /**
     * Check if the task can be edited.
     *
     * @return bool
     */
    public function canBeEdited(): bool
    {
        return $this !== self::CANCELLED;
    }
}
