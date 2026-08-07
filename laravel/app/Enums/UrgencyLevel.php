<?php

namespace App\Enums;

enum UrgencyLevel: string
{
    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

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
            self::CRITICAL => 'Critique',
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
            self::CRITICAL => 'bg-danger',
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
            self::CRITICAL => 4,
        };
    }

    /**
     * Get the processing time multiplier.
     * This can be used to ajuster les temps estimés en fonction de l'urgence.
     *
     * @return float
     */
    public function processingTimeMultiplier(): float
    {
        return match($this) {
            self::LOW => 1.0,
            self::MEDIUM => 0.9,
            self::HIGH => 0.7,
            self::CRITICAL => 0.5,
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
     * Check if the urgency level is high or critical.
     *
     * @return bool
     */
    public function isUrgent(): bool
    {
        return in_array($this, [self::HIGH, self::CRITICAL]);
    }

    /**
     * Check if the urgency level is critical.
     *
     * @return bool
     */
    public function isCritical(): bool
    {
        return $this === self::CRITICAL;
    }
}
