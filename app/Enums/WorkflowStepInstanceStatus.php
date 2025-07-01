<?php

namespace App\Enums;

enum WorkflowStepInstanceStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case SKIPPED = 'skipped';
    case FAILED = 'failed';

    /**
     * Get the label for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::IN_PROGRESS => 'En cours',
            self::COMPLETED => 'Terminé',
            self::SKIPPED => 'Ignoré',
            self::FAILED => 'Échoué',
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
            self::PENDING => 'bg-warning',
            self::IN_PROGRESS => 'bg-info',
            self::COMPLETED => 'bg-success',
            self::SKIPPED => 'bg-secondary',
            self::FAILED => 'bg-danger',
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
     * Check if the step is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return in_array($this, [self::PENDING, self::IN_PROGRESS]);
    }

    /**
     * Check if the step is done (completed or skipped).
     *
     * @return bool
     */
    public function isDone(): bool
    {
        return in_array($this, [self::COMPLETED, self::SKIPPED]);
    }

    /**
     * Check if the step needs attention.
     *
     * @return bool
     */
    public function needsAttention(): bool
    {
        return $this === self::FAILED;
    }
}
