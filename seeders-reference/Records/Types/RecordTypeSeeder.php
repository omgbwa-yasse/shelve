<?php

namespace Database\Seeders\Records\Types;

use App\Models\RecordType;
use App\Models\ReferenceList;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Types génériques physiques ("Fonds", "Dossier papier", "Pièce/document papier")
 * pour le modèle unifié `record_types`.
 *
 * Idempotent : rejouable sans doublon grâce à la clé unique `code`.
 *
 * Les méthodes `backfillFolderTypes`/`backfillDocumentTypes`/`copyMetadataProfiles`
 * (Phase 1, copie depuis `record_digital_folder_types`/`record_digital_document_types`
 * et leurs profils de métadonnées) ont été retirées le 2026-08-06 : leurs tables
 * source ont été supprimées avec `RecordDigitalFolder`/`RecordDigitalDocument`
 * (voir `App\Models\Record`, le modèle unifié qui les remplace) — ce backfill,
 * déjà exécuté avant la suppression, ne peut de toute façon plus être rejoué.
 */
class RecordTypeSeeder extends Seeder
{
    public function run(): void
    {
        $referenceList = $this->ensureReferenceList();

        $this->ensurePhysicalTypes($referenceList);
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
}
