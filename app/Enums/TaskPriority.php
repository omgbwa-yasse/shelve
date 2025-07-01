<?php

namespace App\Enums;

enum TaskPriority: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case URGENT = 'urgent';

    /**
     * Get the label for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::LOW => 'Faible',
            self::MEDIUM => 'Moyenne',
            self::HIGH => 'Haute',
            self::URGENT => 'Urgente',
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
            self::LOW => 'bg-success',
            self::MEDIUM => 'bg-info',
            self::HIGH => 'bg-warning',
            self::URGENT => 'bg-danger',
        };
    }

    /**
     * Get the order value for sorting.
     *
     * @return int
     */
    public function order(): int
    {
        return match($this) {
            self::LOW => 1,
            self::MEDIUM => 2,
            self::HIGH => 3,
            self::URGENT => 4,
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
     * Check if the priority is high or urgent.
     *
     * @return bool
     */
    public function isHighPriority(): bool
    {
        return in_array($this, [self::HIGH, self::URGENT]);
    }
}
