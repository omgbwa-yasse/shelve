<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Demande en cours',
            self::APPROVED => 'Validée',
            self::REJECTED => 'Rejetée',
            self::CANCELLED => 'Annulée',
            self::IN_PROGRESS => 'En cours',
            self::COMPLETED => 'Terminée',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::CANCELLED => 'secondary',
            self::IN_PROGRESS => 'info',
            self::COMPLETED => 'primary',
        };
    }

    public static function getAll(): array
    {
        return [
            self::PENDING,
            self::APPROVED,
            self::REJECTED,
            self::CANCELLED,
            self::IN_PROGRESS,
            self::COMPLETED,
        ];
    }
}
