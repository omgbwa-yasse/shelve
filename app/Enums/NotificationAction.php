<?php

namespace App\Enums;

enum NotificationAction: string
{
    case CREATE = 'CREATE';
    case READ = 'READ';
    case UPDATE = 'UPDATE';
    case DELETE = 'DELETE';

    public function label(): string
    {
        return match($this) {
            self::CREATE => 'Créé',
            self::READ => 'Consulté',
            self::UPDATE => 'Modifié',
            self::DELETE => 'Supprimé',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::CREATE => 'bi-plus-circle',
            self::READ => 'bi-eye',
            self::UPDATE => 'bi-pencil',
            self::DELETE => 'bi-trash',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::CREATE => 'success',
            self::READ => 'info',
            self::UPDATE => 'warning',
            self::DELETE => 'danger',
        };
    }
}
