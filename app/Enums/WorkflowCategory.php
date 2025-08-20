<?php

namespace App\Enums;

enum WorkflowCategory: string
{
    case MAIL_PROCESSING = 'mail_processing';
    case RECORD_MANAGEMENT = 'record_management';
    case APPROVAL_PROCESS = 'approval_process';
    case DOCUMENT_REVIEW = 'document_review';
    case COMMUNICATION = 'communication';
    case ADMINISTRATION = 'administration';
    case ARCHIVE_MANAGEMENT = 'archive_management';
    case USER_MANAGEMENT = 'user_management';
    case SYSTEM_MAINTENANCE = 'system_maintenance';
    case QUALITY_CONTROL = 'quality_control';

    /**
     * Get the label for the enum value.
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::MAIL_PROCESSING => __('mail_processing'),
            self::RECORD_MANAGEMENT => __('record_management'),
            self::APPROVAL_PROCESS => __('approval_process'),
            self::DOCUMENT_REVIEW => __('document_review'),
            self::COMMUNICATION => __('workflow_communication'),
            self::ADMINISTRATION => __('administration'),
            self::ARCHIVE_MANAGEMENT => __('archive_management'),
            self::USER_MANAGEMENT => __('user_management'),
            self::SYSTEM_MAINTENANCE => __('system_maintenance'),
            self::QUALITY_CONTROL => __('quality_control'),
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
     * Get the description for the enum value.
     *
     * @return string
     */
    public function description(): string
    {
        return match($this) {
            self::MAIL_PROCESSING => __('mail_processing_description'),
            self::RECORD_MANAGEMENT => __('record_management_description'),
            self::APPROVAL_PROCESS => __('approval_process_description'),
            self::DOCUMENT_REVIEW => __('document_review_description'),
            self::COMMUNICATION => __('communication_description'),
            self::ADMINISTRATION => __('administration_description'),
            self::ARCHIVE_MANAGEMENT => __('archive_management_description'),
            self::USER_MANAGEMENT => __('user_management_description'),
            self::SYSTEM_MAINTENANCE => __('system_maintenance_description'),
            self::QUALITY_CONTROL => __('quality_control_description'),
        };
    }

    /**
     * Get the color for the enum value.
     *
     * @return string
     */
    public function color(): string
    {
        return match($this) {
            self::MAIL_PROCESSING => '#007bff',      // Bleu
            self::RECORD_MANAGEMENT => '#28a745',    // Vert
            self::APPROVAL_PROCESS => '#ffc107',     // Jaune
            self::DOCUMENT_REVIEW => '#17a2b8',      // Turquoise
            self::COMMUNICATION => '#6f42c1',        // Violet
            self::ADMINISTRATION => '#dc3545',       // Rouge
            self::ARCHIVE_MANAGEMENT => '#fd7e14',   // Orange
            self::USER_MANAGEMENT => '#20c997',      // Teal
            self::SYSTEM_MAINTENANCE => '#6c757d',   // Gris
            self::QUALITY_CONTROL => '#e83e8c',      // Rose
        };
    }
}
