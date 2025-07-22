<?php

namespace App\Enums;

enum NotificationModule: string
{
    case BULLETIN_BOARDS = 'BulletinBoards';
    case MAILS = 'Mails';
    case RECORDS = 'Records';
    case COMMUNICATIONS = 'Communications';
    case TRANSFERS = 'Transfers';
    case DEPOSITS = 'Deposits';
    case TOOLS = 'Tools';
    case DOLLIES = 'Dollies';
    case WORKFLOWS = 'Workflows';
    case CONTACTS = 'Contacts';
    case AI = 'AI';
    case PUBLIC = 'Public';
    case SETTINGS = 'Settings';

    public function label(): string
    {
        return match($this) {
            self::BULLETIN_BOARDS => 'Tableaux d\'affichage',
            self::MAILS => 'Courriers',
            self::RECORDS => 'Documents',
            self::COMMUNICATIONS => 'Communications',
            self::TRANSFERS => 'Transferts',
            self::DEPOSITS => 'Versements',
            self::TOOLS => 'Outils',
            self::DOLLIES => 'Chariots',
            self::WORKFLOWS => 'Flux de travail',
            self::CONTACTS => 'Contacts',
            self::AI => 'Intelligence Artificielle',
            self::PUBLIC => 'Portail Public',
            self::SETTINGS => 'Paramètres',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::BULLETIN_BOARDS => 'bi-clipboard-data',
            self::MAILS => 'bi-envelope',
            self::RECORDS => 'bi-file-earmark-text',
            self::COMMUNICATIONS => 'bi-chat-dots',
            self::TRANSFERS => 'bi-arrow-left-right',
            self::DEPOSITS => 'bi-archive',
            self::TOOLS => 'bi-tools',
            self::DOLLIES => 'bi-cart',
            self::WORKFLOWS => 'bi-diagram-3',
            self::CONTACTS => 'bi-person-lines-fill',
            self::AI => 'bi-cpu',
            self::PUBLIC => 'bi-globe',
            self::SETTINGS => 'bi-gear',
        };
    }
}
