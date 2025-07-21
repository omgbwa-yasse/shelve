<?php

namespace App\Enums;

enum NotificationModule: string
{
    case BULLETIN_BOARDS = 'bulletin_boards';
    case MAILS = 'mails';
    case RECORDS = 'records';
    case COMMUNICATIONS = 'communications';
    case TRANSFERS = 'transfers';
    case DEPOSITS = 'deposits';
    case TOOLS = 'tools';
    case DOLLIES = 'dollies';
    case WORKFLOWS = 'workflows';
    case CONTACTS = 'contacts';
    case AI = 'ai';
    case PUBLIC = 'public';
    case SETTINGS = 'settings';

    public function label(): string
    {
        return match($this) {
            self::BULLETIN_BOARDS => 'Bulletin Boards',
            self::MAILS => 'Mails',
            self::RECORDS => 'Records',
            self::COMMUNICATIONS => 'Communications',
            self::TRANSFERS => 'Transfers',
            self::DEPOSITS => 'Deposits',
            self::TOOLS => 'Tools',
            self::DOLLIES => 'Dollies',
            self::WORKFLOWS => 'Workflows',
            self::CONTACTS => 'Contacts',
            self::AI => 'AI',
            self::PUBLIC => 'Public',
            self::SETTINGS => 'Settings',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::BULLETIN_BOARDS => 'bi-card-text',
            self::MAILS => 'bi-envelope',
            self::RECORDS => 'bi-folder',
            self::COMMUNICATIONS => 'bi-chat-dots',
            self::TRANSFERS => 'bi-arrow-left-right',
            self::DEPOSITS => 'bi-building',
            self::TOOLS => 'bi-tools',
            self::DOLLIES => 'bi-cart3',
            self::WORKFLOWS => 'bi-diagram-3',
            self::CONTACTS => 'bi-people',
            self::AI => 'bi-robot',
            self::PUBLIC => 'bi-globe',
            self::SETTINGS => 'bi-gear',
        };
    }
}
