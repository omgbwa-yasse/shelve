<?php

namespace App\Enums;

enum TaskAssigneeRole: string
{
    case ASSIGNEE = 'assignee';
    case REVIEWER = 'reviewer';
    case OBSERVER = 'observer';
    case COLLABORATOR = 'collaborator';

    /**
     * Get the label for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::ASSIGNEE => 'Responsable',
            self::REVIEWER => 'Relecteur',
            self::OBSERVER => 'Observateur',
            self::COLLABORATOR => 'Collaborateur',
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
            self::ASSIGNEE => 'Responsable principal de la tâche',
            self::REVIEWER => 'Doit valider la tâche',
            self::OBSERVER => 'Peut suivre la progression',
            self::COLLABORATOR => 'Peut contribuer à la tâche',
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
     * Check if the role can edit the task.
     *
     * @return bool
     */
    public function canEdit(): bool
    {
        return in_array($this, [self::ASSIGNEE, self::COLLABORATOR]);
    }

    /**
     * Check if the role can complete the task.
     *
     * @return bool
     */
    public function canComplete(): bool
    {
        return $this === self::ASSIGNEE;
    }

    /**
     * Check if the role can approve the task.
     *
     * @return bool
     */
    public function canApprove(): bool
    {
        return $this === self::REVIEWER;
    }
}
