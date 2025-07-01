<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case EMAIL = 'email';
    case SMS = 'sms';
    case PUSH = 'push';
    case IN_APP = 'in_app';

    /**
     * Get the label for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::EMAIL => 'Email',
            self::SMS => 'SMS',
            self::PUSH => 'Notification push',
            self::IN_APP => 'Notification interne',
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
            self::EMAIL => 'fa-envelope',
            self::SMS => 'fa-sms',
            self::PUSH => 'fa-bell',
            self::IN_APP => 'fa-comment-alt',
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
     * Check if the channel is external.
     *
     * @return bool
     */
    public function isExternal(): bool
    {
        return in_array($this, [self::EMAIL, self::SMS, self::PUSH]);
    }

    /**
     * Check if the channel is internal.
     *
     * @return bool
     */
    public function isInternal(): bool
    {
        return $this === self::IN_APP;
    }
}
