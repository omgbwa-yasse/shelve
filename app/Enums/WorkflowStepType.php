<?php

namespace App\Enums;

enum WorkflowStepType: string
{
    case MANUAL = 'manual';
    case AUTOMATIC = 'automatic';
    case APPROVAL = 'approval';
    case NOTIFICATION = 'notification';
    case CONDITIONAL = 'conditional';

    /**
     * Get the label for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::MANUAL => 'Étape manuelle',
            self::AUTOMATIC => 'Étape automatique',
            self::APPROVAL => 'Approbation',
            self::NOTIFICATION => 'Notification',
            self::CONDITIONAL => 'Étape conditionnelle',
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
     * Check if the step type is manual.
     *
     * @return bool
     */
    public function isManual(): bool
    {
        return $this === self::MANUAL;
    }

    /**
     * Check if the step type requires user intervention.
     *
     * @return bool
     */
    public function requiresUserIntervention(): bool
    {
        return in_array($this, [self::MANUAL, self::APPROVAL]);
    }

    /**
     * Check if the step can be automated.
     *
     * @return bool
     */
    public function canBeAutomated(): bool
    {
        return in_array($this, [self::AUTOMATIC, self::NOTIFICATION, self::CONDITIONAL]);
    }
}
