<?php

namespace App\Enums;

enum TaskAssignmentActionType: string
{
    case ASSIGN = 'assign';
    case REASSIGN = 'reassign';
    case DELEGATE = 'delegate';
    case UNASSIGN = 'unassign';
    case AUTO_ASSIGN = 'auto_assign';

    /**
     * Get the label for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::ASSIGN => 'Assigné',
            self::REASSIGN => 'Réassigné',
            self::DELEGATE => 'Délégué',
            self::UNASSIGN => 'Désassigné',
            self::AUTO_ASSIGN => 'Assigné automatiquement',
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
            self::ASSIGN => 'La tâche a été assignée initialement',
            self::REASSIGN => 'La tâche a été réassignée à quelqu\'un d\'autre',
            self::DELEGATE => 'La tâche a été déléguée temporairement',
            self::UNASSIGN => 'La tâche a été désassignée',
            self::AUTO_ASSIGN => 'La tâche a été assignée automatiquement par le système',
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
     * Check if the action requires a new assignee.
     *
     * @return bool
     */
    public function requiresNewAssignee(): bool
    {
        return in_array($this, [self::ASSIGN, self::REASSIGN, self::DELEGATE, self::AUTO_ASSIGN]);
    }

    /**
     * Check if the action is manual.
     *
     * @return bool
     */
    public function isManual(): bool
    {
        return $this !== self::AUTO_ASSIGN;
    }
}
