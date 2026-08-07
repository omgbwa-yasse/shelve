<?php

namespace App\Enums;

enum MailStatusEnum: string
{
    case DRAFT = 'draft';
    case PENDING_REVIEW = 'pending_review';
    case IN_PROGRESS = 'in_progress';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case TRANSMITTED = 'transmitted';
    case COMPLETED = 'completed';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case OVERDUE = 'overdue';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Brouillon',
            self::PENDING_REVIEW => 'En attente de révision',
            self::IN_PROGRESS => 'En cours de traitement',
            self::PENDING_APPROVAL => 'En attente d\'approbation',
            self::APPROVED => 'Approuvé',
            self::TRANSMITTED => 'Transmis',
            self::COMPLETED => 'Terminé',
            self::REJECTED => 'Rejeté',
            self::CANCELLED => 'Annulé',
            self::OVERDUE => 'En retard',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::PENDING_REVIEW => 'yellow',
            self::IN_PROGRESS => 'blue',
            self::PENDING_APPROVAL => 'orange',
            self::APPROVED => 'green',
            self::TRANSMITTED => 'purple',
            self::COMPLETED => 'emerald',
            self::REJECTED => 'red',
            self::CANCELLED => 'slate',
            self::OVERDUE => 'red',
        };
    }

    public function canTransitionTo(self $status): bool
    {
        return match($this) {
            self::DRAFT => in_array($status, [self::PENDING_REVIEW, self::IN_PROGRESS, self::CANCELLED]),
            self::PENDING_REVIEW => in_array($status, [self::IN_PROGRESS, self::REJECTED, self::DRAFT]),
            self::IN_PROGRESS => in_array($status, [self::PENDING_APPROVAL, self::TRANSMITTED, self::COMPLETED, self::OVERDUE]),
            self::PENDING_APPROVAL => in_array($status, [self::APPROVED, self::REJECTED, self::IN_PROGRESS]),
            self::APPROVED => in_array($status, [self::TRANSMITTED, self::COMPLETED]),
            self::TRANSMITTED => in_array($status, [self::COMPLETED]),
            self::COMPLETED => false, // État final
            self::REJECTED => in_array($status, [self::DRAFT, self::IN_PROGRESS]),
            self::CANCELLED => false, // État final
            self::OVERDUE => in_array($status, [self::IN_PROGRESS, self::COMPLETED, self::REJECTED]),
        };
    }

    public function isActive(): bool
    {
        return !in_array($this, [self::COMPLETED, self::CANCELLED, self::REJECTED]);
    }

    public function requiresAction(): bool
    {
        return in_array($this, [self::PENDING_REVIEW, self::IN_PROGRESS, self::PENDING_APPROVAL, self::OVERDUE]);
    }
}
