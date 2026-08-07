<?php

namespace App\Enums;

enum DependencyType: string
{
    case FINISH_TO_START = 'finish_to_start';
    case START_TO_START = 'start_to_start';
    case FINISH_TO_FINISH = 'finish_to_finish';
    case START_TO_FINISH = 'start_to_finish';

    /**
     * Get the label for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::FINISH_TO_START => 'Fin à Début',
            self::START_TO_START => 'Début à Début',
            self::FINISH_TO_FINISH => 'Fin à Fin',
            self::START_TO_FINISH => 'Début à Fin',
        };
    }

    /**
     * Get the description for the enum value.
     *
     * @return string
     */
    public function description(): string
    {
        return match($this) {
            self::FINISH_TO_START => 'La tâche dépendante ne peut commencer que lorsque la tâche prérequise est terminée',
            self::START_TO_START => 'La tâche dépendante ne peut commencer que lorsque la tâche prérequise a commencé',
            self::FINISH_TO_FINISH => 'La tâche dépendante ne peut se terminer que lorsque la tâche prérequise est terminée',
            self::START_TO_FINISH => 'La tâche dépendante ne peut se terminer que lorsque la tâche prérequise a commencé',
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
     * Check if the dependency requires the prerequisite task to be finished.
     *
     * @return bool
     */
    public function requiresPrerequisiteFinished(): bool
    {
        return in_array($this, [self::FINISH_TO_START, self::FINISH_TO_FINISH]);
    }

    /**
     * Check if the dependency relates to the start of the dependent task.
     *
     * @return bool
     */
    public function relatesToDependentStart(): bool
    {
        return in_array($this, [self::FINISH_TO_START, self::START_TO_START]);
    }
}
