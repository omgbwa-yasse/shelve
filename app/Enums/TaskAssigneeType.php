<?php

namespace App\Enums;

enum TaskAssigneeType: string
{
    case USER = 'user';
    case ORGANISATION = 'organisation';

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
     * Check if the assignee type is a user.
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this === self::USER;
    }

    /**
     * Check if the assignee type is an organisation.
     *
     * @return bool
     */
    public function isOrganisation(): bool
    {
        return $this === self::ORGANISATION;
    }
}
