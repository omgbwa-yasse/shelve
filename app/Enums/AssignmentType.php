<?php

namespace App\Enums;

enum AssignmentType: string
{
    case ORGANISATION = 'organisation';
    case USER = 'user';
    case BOTH = 'both';

    /**
     * Get the label for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::ORGANISATION => 'Organisation',
            self::USER => 'Utilisateur',
            self::BOTH => 'Organisation et Utilisateur',
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
     * Check if the assignment requires a user.
     *
     * @return bool
     */
    public function requiresUser(): bool
    {
        return in_array($this, [self::USER, self::BOTH]);
    }

    /**
     * Check if the assignment requires an organisation.
     *
     * @return bool
     */
    public function requiresOrganisation(): bool
    {
        return in_array($this, [self::ORGANISATION, self::BOTH]);
    }
}
