<?php

namespace App\Services;

use App\Models\RecordDigitalDocumentType;
use App\Models\RecordDigitalFolderType;
use App\Models\MetadataDefinition;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MetadataValidationService
{
    /**
     * Validate metadata for a document type.
     *
     * @param RecordDigitalDocumentType $documentType
     * @param array $metadata
     * @return array Validated metadata
     * @throws ValidationException
     */
    public function validateDocumentMetadata(RecordDigitalDocumentType $documentType, array $metadata): array
    {
        $profiles = $documentType->metadataProfiles()->with('metadataDefinition')->ordered()->get();

        $rules = [];
        $messages = [];
        $attributes = [];

        foreach ($profiles as $profile) {
            $definition = $profile->metadataDefinition;
            $fieldName = $definition->code;

            // Build validation rules
            $fieldRules = [];

            if ($profile->mandatory) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Add data type validation
            $fieldRules = array_merge($fieldRules, $this->getDataTypeRules($definition->data_type, $definition));

            // Add custom validation rules from profile
            if ($profile->validation_rules) {
                $fieldRules = array_merge($fieldRules, $profile->validation_rules);
            }

            // Add custom validation rules from definition
            if ($definition->validation_rules) {
                $fieldRules = array_merge($fieldRules, $definition->validation_rules);
            }

            $rules[$fieldName] = $fieldRules;
            $attributes[$fieldName] = $definition->name;
        }

        $validator = Validator::make($metadata, $rules, $messages, $attributes);

        return $validator->validate();
    }

    /**
     * Validate metadata for a folder type.
     *
     * @param RecordDigitalFolderType $folderType
     * @param array $metadata
     * @return array Validated metadata
     * @throws ValidationException
     */
    public function validateFolderMetadata(RecordDigitalFolderType $folderType, array $metadata): array
    {
        $profiles = $folderType->metadataProfiles()->with('metadataDefinition')->ordered()->get();

        $rules = [];
        $messages = [];
        $attributes = [];

        foreach ($profiles as $profile) {
            $definition = $profile->metadataDefinition;
            $fieldName = $definition->code;

            // Build validation rules
            $fieldRules = [];

            if ($profile->mandatory) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            // Add data type validation
            $fieldRules = array_merge($fieldRules, $this->getDataTypeRules($definition->data_type, $definition));

            // Add custom validation rules from profile
            if ($profile->validation_rules) {
                $fieldRules = array_merge($fieldRules, $profile->validation_rules);
            }

            // Add custom validation rules from definition
            if ($definition->validation_rules) {
                $fieldRules = array_merge($fieldRules, $definition->validation_rules);
            }

            $rules[$fieldName] = $fieldRules;
            $attributes[$fieldName] = $definition->name;
        }

        $validator = Validator::make($metadata, $rules, $messages, $attributes);

        return $validator->validate();
    }

    /**
     * Get validation rules based on data type.
     *
     * @param string $dataType
     * @param MetadataDefinition $definition
     * @return array
     */
    protected function getDataTypeRules(string $dataType, MetadataDefinition $definition): array
    {
        $rules = [];

        switch ($dataType) {
            case 'text':
                $rules[] = 'string';
                $rules[] = 'max:255';
                break;

            case 'textarea':
                $rules[] = 'string';
                break;

            case 'number':
                $rules[] = 'numeric';
                break;

            case 'date':
                $rules[] = 'date';
                break;

            case 'datetime':
                $rules[] = 'date';
                break;

            case 'boolean':
                $rules[] = 'boolean';
                break;

            case 'email':
                $rules[] = 'email';
                break;

            case 'url':
                $rules[] = 'url';
                break;

            case 'select':
                if ($definition->options && is_array($definition->options)) {
                    $rules[] = 'in:' . implode(',', $definition->options);
                }
                break;

            case 'multi_select':
                $rules[] = 'array';
                if ($definition->options && is_array($definition->options)) {
                    $rules[] = 'in:' . implode(',', $definition->options);
                }
                break;

            case 'reference_list':
                if ($definition->reference_list_id) {
                    $rules[] = 'exists:reference_values,code,list_id,' . $definition->reference_list_id . ',active,1';
                }
                break;
        }

        return $rules;
    }

    /**
     * Get metadata form fields for a document type.
     *
     * @param RecordDigitalDocumentType $documentType
     * @return array
     */
    public function getDocumentMetadataFields(RecordDigitalDocumentType $documentType): array
    {
        $profiles = $documentType->metadataProfiles()
            ->with(['metadataDefinition.referenceList.activeValues'])
            ->visible()
            ->ordered()
            ->get();

        return $this->buildMetadataFields($profiles);
    }

    /**
     * Get metadata form fields for a folder type.
     *
     * @param RecordDigitalFolderType $folderType
     * @return array
     */
    public function getFolderMetadataFields(RecordDigitalFolderType $folderType): array
    {
        $profiles = $folderType->metadataProfiles()
            ->with(['metadataDefinition.referenceList.activeValues'])
            ->visible()
            ->ordered()
            ->get();

        return $this->buildMetadataFields($profiles);
    }

    /**
     * Build metadata fields array from profiles.
     *
     * @param \Illuminate\Support\Collection $profiles
     * @return array
     */
    protected function buildMetadataFields($profiles): array
    {
        $fields = [];

        foreach ($profiles as $profile) {
            $definition = $profile->metadataDefinition;

            $field = [
                'code' => $definition->code,
                'name' => $definition->name,
                'description' => $definition->description,
                'data_type' => $definition->data_type,
                'mandatory' => $profile->mandatory,
                'readonly' => $profile->readonly,
                'default_value' => $profile->default_value,
                'searchable' => $definition->searchable,
            ];

            // Add options for select fields
            if (in_array($definition->data_type, ['select', 'multi_select'])) {
                $field['options'] = $definition->options ?? [];
            }

            // Add reference list values
            if ($definition->data_type === 'reference_list' && $definition->referenceList) {
                $field['options'] = $definition->referenceList->activeValues->map(function ($value) {
                    return [
                        'code' => $value->code,
                        'value' => $value->value,
                        'description' => $value->description,
                    ];
                })->toArray();
            }

            $fields[] = $field;
        }

        return $fields;
    }

    /**
     * Apply default values to metadata.
     *
     * @param RecordDigitalDocumentType|RecordDigitalFolderType $type
     * @param array $metadata
     * @return array
     */
    public function applyDefaultValues($type, array $metadata): array
    {
        $profiles = $type->metadataProfiles()->with('metadataDefinition')->get();

        foreach ($profiles as $profile) {
            $code = $profile->metadataDefinition->code;

            // Apply default value if field is empty and default exists
            if (empty($metadata[$code]) && !empty($profile->default_value)) {
                $metadata[$code] = $profile->default_value;
            }
        }

        return $metadata;
    }
}
