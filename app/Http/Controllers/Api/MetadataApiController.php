<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RecordDigitalFolderType;
use App\Models\RecordDigitalDocumentType;
use App\Models\MetadataDefinition;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * API Controller for Metadata
 *
 * Provides endpoints for retrieving metadata definitions for folder and document types
 */
class MetadataApiController extends Controller
{
    /**
     * Get metadata definitions for a specific folder type
     *
     * @param int $typeId
     * @return JsonResponse
     */
    public function getFolderTypeMetadata(int $typeId): JsonResponse
    {
        try {
            $type = RecordDigitalFolderType::with([
                'metadataDefinitions' => function ($query) {
                    $query->with('referenceList.values')
                        ->orderBy('record_digital_folder_metadata_profiles.sort_order');
                }
            ])->findOrFail($typeId);

            $metadata = $type->metadataDefinitions->map(function ($definition) {
                $profile = $definition->pivot;
                
                return [
                    'id' => $definition->id,
                    'name' => $definition->name,
                    'label' => $definition->label,
                    'data_type' => $definition->data_type,
                    'description' => $definition->description,
                    'mandatory' => $profile->mandatory ?? false,
                    'visible' => $profile->visible ?? true,
                    'readonly' => $profile->readonly ?? false,
                    'default_value' => $profile->default_value,
                    'validation_rules' => $profile->validation_rules ? json_decode($profile->validation_rules, true) : null,
                    'sort_order' => $profile->sort_order ?? 0,
                    'reference_list' => $definition->referenceList ? [
                        'id' => $definition->referenceList->id,
                        'name' => $definition->referenceList->name,
                        'values' => $definition->referenceList->values->map(function ($value) {
                            return [
                                'id' => $value->id,
                                'value' => $value->value,
                                'display_value' => $value->display_value,
                                'sort_order' => $value->sort_order,
                            ];
                        })
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $metadata,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving folder type metadata',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get metadata definitions for a specific document type
     *
     * @param int $typeId
     * @return JsonResponse
     */
    public function getDocumentTypeMetadata(int $typeId): JsonResponse
    {
        try {
            $type = RecordDigitalDocumentType::with([
                'metadataDefinitions' => function ($query) {
                    $query->with('referenceList.values')
                        ->orderBy('record_digital_document_metadata_profiles.sort_order');
                }
            ])->findOrFail($typeId);

            $metadata = $type->metadataDefinitions->map(function ($definition) {
                $profile = $definition->pivot;
                
                return [
                    'id' => $definition->id,
                    'name' => $definition->name,
                    'label' => $definition->label,
                    'data_type' => $definition->data_type,
                    'description' => $definition->description,
                    'mandatory' => $profile->mandatory ?? false,
                    'visible' => $profile->visible ?? true,
                    'readonly' => $profile->readonly ?? false,
                    'default_value' => $profile->default_value,
                    'validation_rules' => $profile->validation_rules ? json_decode($profile->validation_rules, true) : null,
                    'sort_order' => $profile->sort_order ?? 0,
                    'reference_list' => $definition->referenceList ? [
                        'id' => $definition->referenceList->id,
                        'name' => $definition->referenceList->name,
                        'values' => $definition->referenceList->values->map(function ($value) {
                            return [
                                'id' => $value->id,
                                'value' => $value->value,
                                'display_value' => $value->display_value,
                                'sort_order' => $value->sort_order,
                            ];
                        })
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $metadata,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving document type metadata',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get all metadata definitions
     *
     * @return JsonResponse
     */
    public function getAllMetadata(): JsonResponse
    {
        try {
            $definitions = MetadataDefinition::with('referenceList.values')
                ->orderBy('name')
                ->get();

            $metadata = $definitions->map(function ($definition) {
                return [
                    'id' => $definition->id,
                    'name' => $definition->name,
                    'label' => $definition->label,
                    'data_type' => $definition->data_type,
                    'description' => $definition->description,
                    'reference_list' => $definition->referenceList ? [
                        'id' => $definition->referenceList->id,
                        'name' => $definition->referenceList->name,
                        'values' => $definition->referenceList->values->map(function ($value) {
                            return [
                                'id' => $value->id,
                                'value' => $value->value,
                                'display_value' => $value->display_value,
                                'sort_order' => $value->sort_order,
                            ];
                        })
                    ] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $metadata,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving metadata definitions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
