<?php

namespace App\Enums;

enum AssigneeType: string
{
    case USER = 'user';
    case ORGANISATION = 'organisation';
    case ROLE = 'role';
    case DEPARTMENT = 'department';
    case AUTO = 'auto';

    /**
     * Get the label for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::USER => 'Utilisateur',
            self::ORGANISATION => 'Organisation',
            self::ROLE => 'Rôle',
            self::DEPARTMENT => 'Département',
            self::AUTO => 'Automatique',
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
     * Check if the assignee type is an entity that requires specific assignment.
     *
     * @return bool
     */
    public function requiresAssignee(): bool
    {
        return in_array($this, [self::USER, self::ORGANISATION, self::ROLE, self::DEPARTMENT]);
    }

    /**
     * Check if the assignee type is automatic.
     *
     * @return bool
     */
    public function isAutomatic(): bool
    {
        return $this === self::AUTO;
    }
}
