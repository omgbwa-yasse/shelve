<?php

use App\Services\MetadataValidationService;

if (!function_exists('metadata_service')) {
    /**
     * Get the metadata validation service instance
     *
     * @return MetadataValidationService
     */
    function metadata_service(): MetadataValidationService
    {
        return app(MetadataValidationService::class);
    }
}

if (!function_exists('apply_metadata_defaults')) {
    /**
     * Apply default values to metadata
     *
     * @param array $metadata
     * @param array $fieldConfigs
     * @return array
     */
    function apply_metadata_defaults(array $metadata, array $fieldConfigs): array
    {
        return metadata_service()->applyDefaultValues($metadata, $fieldConfigs);
    }
}

if (!function_exists('get_metadata_data_types')) {
    /**
     * Get all available metadata data types
     *
     * @return array
     */
    function get_metadata_data_types(): array
    {
        return [
            'text' => 'Texte court',
            'textarea' => 'Texte long',
            'number' => 'Nombre',
            'date' => 'Date',
            'datetime' => 'Date et heure',
            'boolean' => 'Oui/Non',
            'select' => 'Liste à choix unique',
            'multi_select' => 'Liste à choix multiples',
            'reference_list' => 'Liste de référence',
            'email' => 'Email',
            'url' => 'URL',
        ];
    }
}

if (!function_exists('format_metadata_value')) {
    /**
     * Format a metadata value for display
     *
     * @param mixed $value
     * @param string $dataType
     * @return string
     */
    function format_metadata_value($value, string $dataType): string
    {
        if (is_null($value)) {
            return '-';
        }

        return match ($dataType) {
            'boolean' => $value ? 'Oui' : 'Non',
            'date' => \Carbon\Carbon::parse($value)->format('d/m/Y'),
            'datetime' => \Carbon\Carbon::parse($value)->format('d/m/Y H:i'),
            'multi_select' => is_array($value) ? implode(', ', $value) : $value,
            default => (string) $value,
        };
    }
}
