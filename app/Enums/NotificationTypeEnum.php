<?php

namespace App\Enums;

enum NotificationTypeEnum: string
{
    case MAIL_ASSIGNED = 'mail_assigned';
    case MAIL_DEADLINE_APPROACHING = 'mail_deadline_approaching';
    case MAIL_OVERDUE = 'mail_overdue';
    case MAIL_STATUS_CHANGED = 'mail_status_changed';
    case MAIL_PRIORITY_CHANGED = 'mail_priority_changed';
    case MAIL_REQUIRES_APPROVAL = 'mail_requires_approval';
    case MAIL_APPROVED = 'mail_approved';
    case MAIL_REJECTED = 'mail_rejected';
    case MAIL_TRANSMITTED = 'mail_transmitted';
    case MAIL_COMPLETED = 'mail_completed';
    case ACTION_DEADLINE_APPROACHING = 'action_deadline_approaching';
    case ACTION_OVERDUE = 'action_overdue';

    public function title(): string
    {
        return match($this) {
            self::MAIL_ASSIGNED => 'Nouveau courrier assigné',
            self::MAIL_DEADLINE_APPROACHING => 'Échéance approchante',
            self::MAIL_OVERDUE => 'Courrier en retard',
            self::MAIL_STATUS_CHANGED => 'Statut modifié',
            self::MAIL_PRIORITY_CHANGED => 'Priorité modifiée',
            self::MAIL_REQUIRES_APPROVAL => 'Approbation requise',
            self::MAIL_APPROVED => 'Courrier approuvé',
            self::MAIL_REJECTED => 'Courrier rejeté',
            self::MAIL_TRANSMITTED => 'Courrier transmis',
            self::MAIL_COMPLETED => 'Courrier terminé',
            self::ACTION_DEADLINE_APPROACHING => 'Action à effectuer bientôt',
            self::ACTION_OVERDUE => 'Action en retard',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::MAIL_ASSIGNED => 'mail',
            self::MAIL_DEADLINE_APPROACHING => 'clock',
            self::MAIL_OVERDUE => 'exclamation-triangle',
            self::MAIL_STATUS_CHANGED => 'arrow-right',
            self::MAIL_PRIORITY_CHANGED => 'flag',
            self::MAIL_REQUIRES_APPROVAL => 'check-circle',
            self::MAIL_APPROVED => 'check',
            self::MAIL_REJECTED => 'x',
            self::MAIL_TRANSMITTED => 'send',
            self::MAIL_COMPLETED => 'check-circle',
            self::ACTION_DEADLINE_APPROACHING => 'clock',
            self::ACTION_OVERDUE => 'exclamation-triangle',
        };
    }

    public function priority(): int
    {
        return match($this) {
            self::MAIL_OVERDUE, self::ACTION_OVERDUE => 5, // Critique
            self::MAIL_DEADLINE_APPROACHING, self::ACTION_DEADLINE_APPROACHING => 4, // Élevée
            self::MAIL_REQUIRES_APPROVAL, self::MAIL_ASSIGNED => 3, // Normale
            self::MAIL_STATUS_CHANGED, self::MAIL_PRIORITY_CHANGED => 2, // Basse
            default => 1, // Très basse
        };
    }
}
