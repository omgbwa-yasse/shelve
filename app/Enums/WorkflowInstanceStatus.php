<?php

namespace App\Enums;

enum WorkflowInstanceStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case ON_HOLD = 'on_hold';

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
            self::CANCELLED => 'Annulé',
            self::ON_HOLD => 'En pause',
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
            self::CANCELLED => 'bg-danger',
            self::ON_HOLD => 'bg-secondary',
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
     * Check if the workflow is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return in_array($this, [self::PENDING, self::IN_PROGRESS]);
    }

    /**
     * Check if the workflow is completed or cancelled.
     *
     * @return bool
     */
    public function isFinished(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED]);
    }

    /**
     * Check if the workflow can be resumed.
     *
     * @return bool
     */
    public function canBeResumed(): bool
    {
        return in_array($this, [self::PENDING, self::ON_HOLD]);
    }
}
