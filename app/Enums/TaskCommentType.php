<?php

namespace App\Enums;

enum TaskCommentType: string
{
    case COMMENT = 'comment';
    case STATUS_CHANGE = 'status_change';
    case ASSIGNMENT_CHANGE = 'assignment_change';
    case SYSTEM = 'system';

    /**
     * Get the label for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::COMMENT => 'Commentaire',
            self::STATUS_CHANGE => 'Changement de statut',
            self::ASSIGNMENT_CHANGE => 'Changement d\'assignation',
            self::SYSTEM => 'Message système',
        };
    }

    /**
     * Get the icon for the enum value.
     *
     * @return string
     */
    public function icon(): string
    {
        return match($this) {
            self::COMMENT => 'fa-comment',
            self::STATUS_CHANGE => 'fa-exchange-alt',
            self::ASSIGNMENT_CHANGE => 'fa-user-edit',
            self::SYSTEM => 'fa-cog',
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
     * Check if the comment is user-generated.
     *
     * @return bool
     */
    public function isUserGenerated(): bool
    {
        return $this === self::COMMENT;
    }

    /**
     * Check if the comment is system-generated.
     *
     * @return bool
     */
    public function isSystemGenerated(): bool
    {
        return in_array($this, [self::STATUS_CHANGE, self::ASSIGNMENT_CHANGE, self::SYSTEM]);
    }
}
