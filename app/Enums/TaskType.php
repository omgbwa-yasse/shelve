<?php

namespace App\Enums;

enum TaskType: string
{
    case GENERAL = 'general';
    case APPROVAL = 'approval';
    case DOCUMENT = 'document';
    case INFORMATION = 'information';
    case REVIEW = 'review';
    case TECHNICAL = 'technical';

    public function label(): string
    {
        return match($this) {
            self::GENERAL => 'Général',
            self::APPROVAL => 'Approbation',
            self::DOCUMENT => 'Document',
            self::INFORMATION => 'Information',
            self::REVIEW => 'Revue',
            self::TECHNICAL => 'Technique',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::GENERAL => '#6c757d', // gris
            self::APPROVAL => '#28a745', // vert
            self::DOCUMENT => '#17a2b8', // turquoise
            self::INFORMATION => '#ffc107', // jaune
            self::REVIEW => '#007bff', // bleu
            self::TECHNICAL => '#dc3545', // rouge
        };
    }
}
