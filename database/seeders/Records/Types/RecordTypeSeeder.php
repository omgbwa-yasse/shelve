<?php

namespace Database\Seeders\Records\Types;

use App\Models\RecordDigitalDocumentMetadataProfile;
use App\Models\RecordDigitalDocumentType;
use App\Models\RecordDigitalFolderMetadataProfile;
use App\Models\RecordDigitalFolderType;
use App\Models\RecordType;
use App\Models\RecordTypeMetadataProfile;
use App\Models\ReferenceList;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Phase 1 — Backfill additif et non destructif vers `record_types`.
 *
 * 1. Copie `record_digital_document_types` → `record_types` (is_container=false).
 * 2. Copie `record_digital_folder_types` → `record_types` (is_container=true).
 * 3. Crée les types génériques physiques ("Fonds", "Dossier papier", "Pièce/document papier").
 * 4. Copie les profils de métadonnées (document + dossier) → `record_type_metadata_profiles`.
 *
 * Idempotent : rejouable sans doublon grâce à la clé unique `legacy_type` / `code`.
 */
class RecordTypeSeeder extends Seeder
{
    public function run(): void
    {
        $referenceList = $this->ensureReferenceList();

        $this->backfillFolderTypes($referenceList);
        $this->backfillDocumentTypes($referenceList);
        $this->ensurePhysicalTypes($referenceList);

        $this->copyMetadataProfiles();
    }

    /**
     * S'assure que la liste de référence "Types de documents" existe (DomaineValeurs équivalent).
     */
    private function ensureReferenceList(): ?ReferenceList
    {
        $user = User::query()->first();

        if (!$user) {
            return null;
        }

        return ReferenceList::firstOrCreate(
            ['code' => 'DOCUMENT_TYPES'],
            [
                'name' => 'Types de documents',
                'description' => 'Catalogue unifié des types de notices (aligné TypeDocument IntelliGID)',
                'active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]
        );
    }

    /**
     * Copie record_digital_folder_types → record_types (is_container=true).
     */
    private function backfillFolderTypes(?ReferenceList $referenceList): void
    {
        $rows = RecordDigitalFolderType::withTrashed()->get();

        foreach ($rows as $source) {
            $legacyType = 'digital_folder_type:' . $source->id;

            RecordType::withTrashed()->firstOrCreate(
                ['legacy_type' => $legacyType],
                [
                    'code' => $source->code,
                    'name' => $source->name,
                    'description' => $source->description,
                    'reference_list_id' => $referenceList?->id,
                    'is_container' => true,
                    'icon' => $source->icon,
                    'color' => $source->color,
                    'code_prefix' => $source->code_prefix ?? $source->code,
                    'code_pattern' => $source->code_pattern,
                    'default_access_level' => $source->default_access_level,
                    'requires_approval' => $source->requires_approval,
                    'is_active' => $source->is_active,
                    'display_order' => $source->display_order,
                    'created_by' => $source->created_by,
                ]
            );
        }
    }

    /**
     * Copie record_digital_document_types → record_types (is_container=false).
     */
    private function backfillDocumentTypes(?ReferenceList $referenceList): void
    {
        $rows = RecordDigitalDocumentType::withTrashed()->get();

        foreach ($rows as $source) {
            $legacyType = 'digital_document_type:' . $source->id;

            RecordType::withTrashed()->firstOrCreate(
                ['legacy_type' => $legacyType],
                [
                    'code' => $source->code,
                    'name' => $source->name,
                    'description' => $source->description,
                    'reference_list_id' => $referenceList?->id,
                    'is_container' => false,
                    'icon' => $source->icon,
                    'color' => $source->color,
                    'code_prefix' => $source->code_prefix ?? $source->code,
                    'code_pattern' => $source->code_pattern,
                    'allowed_mime_types' => $source->allowed_mime_types,
                    'allowed_extensions' => $source->allowed_extensions,
                    'max_file_size' => $source->max_file_size,
                    'requires_versioning' => $source->enable_versioning ?? false,
                    'requires_approval' => $source->requires_approval,
                    'requires_signature' => $source->requires_signature,
                    'default_access_level' => $source->default_access_level,
                    'is_active' => $source->is_active,
                    'display_order' => $source->display_order,
                    'created_by' => $source->created_by,
                ]
            );
        }
    }

    /**
     * Types génériques physiques + niveau "Fonds" (FondsDocumentaire IntelliGID).
     */
    private function ensurePhysicalTypes(?ReferenceList $referenceList): void
    {
        $user = User::query()->first();
        $creatorId = $user?->id;

        $types = [
            [
                'code' => 'FONDS',
                'name' => 'Fonds',
                'description' => 'Niveau supérieur ISAD(G) (alignement FondsDocumentaire)',
                'is_container' => true,
                'icon' => 'fa-archive',
                'color' => '#1F2937',
                'display_order' => 1,
            ],
            [
                'code' => 'PAPER_FOLDER',
                'name' => 'Dossier papier',
                'description' => 'Dossier physique (série, dossier, sous-dossier)',
                'is_container' => true,
                'icon' => 'fa-folder',
                'color' => '#374151',
                'display_order' => 2,
            ],
            [
                'code' => 'PAPER_RECORD',
                'name' => 'Pièce / document papier',
                'description' => 'Notice physique de niveau pièce',
                'is_container' => false,
                'icon' => 'fa-file',
                'color' => '#6B7280',
                'display_order' => 3,
            ],
        ];

        foreach ($types as $type) {
            RecordType::withTrashed()->firstOrCreate(
                ['code' => $type['code']],
                array_merge($type, [
                    'reference_list_id' => $referenceList?->id,
                    'code_prefix' => $type['code'],
                    'code_pattern' => '{{PREFIX}}-{{YEAR}}-{{SEQ}}',
                    'default_access_level' => 'internal',
                    'is_active' => true,
                    'created_by' => $creatorId,
                ])
            );
        }
    }

    /**
     * Copie les profils de métadonnées (documents + dossiers) vers record_type_metadata_profiles.
     */
    private function copyMetadataProfiles(): void
    {
        $this->copyDocumentProfiles();
        $this->copyFolderProfiles();
    }

    private function copyDocumentProfiles(): void
    {
        $profiles = RecordDigitalDocumentMetadataProfile::all();

        foreach ($profiles as $profile) {
            $recordType = RecordType::where('legacy_type', 'digital_document_type:' . $profile->document_type_id)->first();

            if (!$recordType) {
                continue;
            }

            RecordTypeMetadataProfile::firstOrCreate(
                [
                    'record_type_id' => $recordType->id,
                    'metadata_definition_id' => $profile->metadata_definition_id,
                ],
                [
                    'mandatory' => $profile->mandatory,
                    'visible' => $profile->visible,
                    'readonly' => $profile->readonly,
                    'default_value' => $profile->default_value,
                    'validation_rules' => $profile->validation_rules,
                    'sort_order' => $profile->sort_order,
                    'created_by' => $profile->created_by,
                ]
            );
        }
    }

    private function copyFolderProfiles(): void
    {
        $profiles = RecordDigitalFolderMetadataProfile::all();

        foreach ($profiles as $profile) {
            $recordType = RecordType::where('legacy_type', 'digital_folder_type:' . $profile->folder_type_id)->first();

            if (!$recordType) {
                continue;
            }

            RecordTypeMetadataProfile::firstOrCreate(
                [
                    'record_type_id' => $recordType->id,
                    'metadata_definition_id' => $profile->metadata_definition_id,
                ],
                [
                    'mandatory' => $profile->mandatory,
                    'visible' => $profile->visible,
                    'readonly' => $profile->readonly,
                    'default_value' => $profile->default_value,
                    'validation_rules' => $profile->validation_rules,
                    'sort_order' => $profile->sort_order,
                    'created_by' => $profile->created_by,
                ]
            );
        }
    }
}
