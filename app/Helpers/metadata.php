<?php

use App\Services\MetadataValidationService;
use App\Models\RecordDigitalDocumentType;
use App\Models\RecordDigitalFolderType;

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

if (!function_exists('validate_document_metadata')) {
    /**
     * Validate document metadata for a given type
     *
     * @param int $typeId
     * @param array $metadata
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    function validate_document_metadata(int $typeId, array $metadata): void
    {
        metadata_service()->validateDocumentMetadata($typeId, $metadata);
    }
}

if (!function_exists('validate_folder_metadata')) {
    /**
     * Validate folder metadata for a given type
     *
     * @param int $typeId
     * @param array $metadata
     * @return void
     * @throws \Illuminate\Validation\ValidationException
     */
    function validate_folder_metadata(int $typeId, array $metadata): void
    {
        metadata_service()->validateFolderMetadata($typeId, $metadata);
    }
}

if (!function_exists('get_document_metadata_fields')) {
    /**
     * Get metadata field configuration for a document type
     *
     * @param int $typeId
     * @return array
     */
    function get_document_metadata_fields(int $typeId): array
    {
        return metadata_service()->getDocumentMetadataFields($typeId);
    }
}

if (!function_exists('get_folder_metadata_fields')) {
    /**
     * Get metadata field configuration for a folder type
     *
     * @param int $typeId
     * @return array
     */
    function get_folder_metadata_fields(int $typeId): array
    {
        return metadata_service()->getFolderMetadataFields($typeId);
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

if (!function_exists('get_document_type_metadata_template')) {
    /**
     * Get the metadata template for a document type
     *
     * @param int|string $typeIdOrCode
     * @return array
     */
    function get_document_type_metadata_template($typeIdOrCode): array
    {
        $type = is_numeric($typeIdOrCode)
            ? RecordDigitalDocumentType::find($typeIdOrCode)
            : RecordDigitalDocumentType::where('code', $typeIdOrCode)->first();

        if (!$type) {
            return [];
        }

        return get_document_metadata_fields($type->id);
    }
}

if (!function_exists('get_folder_type_metadata_template')) {
    /**
     * Get the metadata template for a folder type
     *
     * @param int|string $typeIdOrCode
     * @return array
     */
    function get_folder_type_metadata_template($typeIdOrCode): array
    {
        $type = is_numeric($typeIdOrCode)
            ? RecordDigitalFolderType::find($typeIdOrCode)
            : RecordDigitalFolderType::where('code', $typeIdOrCode)->first();

        if (!$type) {
            return [];
        }

        return get_folder_metadata_fields($type->id);
    }
}
