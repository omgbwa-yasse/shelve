<?php

namespace App\Enums;

enum CommunicationStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case IN_CONSULTATION = 'in_consultation';
    case RETURNED = 'returned';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Demande en cours',
            self::APPROVED => 'Validée',
            self::REJECTED => 'Rejetée',
            self::IN_CONSULTATION => 'En consultation',
            self::RETURNED => 'Retournée',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::IN_CONSULTATION => 'info',
            self::RETURNED => 'primary',
        };
    }

    public static function getAll(): array
    {
        return [
            self::PENDING,
            self::APPROVED,
            self::REJECTED,
            self::IN_CONSULTATION,
            self::RETURNED,
        ];
    }
}
