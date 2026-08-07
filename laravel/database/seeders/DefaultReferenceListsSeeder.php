<?php

namespace Database\Seeders;

use App\Models\RecordType;
use App\Models\ReferenceList;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Étape 2 — Dictionnaire des domaines de valeurs par défaut (équivalent Constellio).
 *
 * Idempotent (`firstOrCreate` par code). Crée les domaines manquants aux côtés de
 * `DOCUMENT_TYPES` (déjà existant) et associe le « Schéma lié » (RecordType) sur les
 * 5 domaines système éligibles dont le schéma correspondant existe.
 */
class DefaultReferenceListsSeeder extends Seeder
{
    /**
     * code => [name, description, linkedSchemaCode?]
     */
    private const DOMAINS = [
        'CONTAINER_TYPES' => ['Types de contenants', 'Types de contenants physiques (boîtes, cartons, dossiers suspendus, ...)', 'CONTAINER'],
        'FOLDER_TYPES' => ['Types de dossiers', 'Typologies de dossiers d\'archives', null],
        'LOCATION_TYPES' => ['Types d\'emplacements', 'Types de lieux de conservation', 'LOCATION'],
        'TASK_TYPES' => ['Types de tâches', 'Typologies des tâches de workflow', 'TASK'],
        'SUPPORT_TYPES' => ['Types de supports', 'Supports documentaires (papier, numérique, ...)', 'SUPPORT'],
        'TASK_STATUS' => ['Statut d\'une tâche', 'Statuts possibles d\'une tâche (en attente, en cours, ...)', null],
        'YEAR_TYPES' => ['Types d\'années', 'Types d\'années de gestion des documents', null],
        'PRIORITY_TYPES' => ['Types de priorités', 'Niveaux de priorité des tâches', null],
    ];

    public function run(): void
    {
        $userId = User::query()->value('id');

        if (! $userId) {
            $this->command?->warn('⚠️  Aucun utilisateur trouvé. Exécutez SuperAdminSeeder avant ce seeder.');

            return;
        }

        // Domaine existant (créé par DocumentTypologySeeder) — garanti présent.
        ReferenceList::firstOrCreate(
            ['code' => 'DOCUMENT_TYPES'],
            [
                'name' => 'Types de documents',
                'description' => 'Catalogue unifié des types de notices',
                'active' => true,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]
        );

        foreach (self::DOMAINS as $code => [$name, $description, $linkedSchemaCode]) {
            $list = ReferenceList::firstOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'description' => $description,
                    'active' => true,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]
            );

            if ($linkedSchemaCode) {
                $schema = RecordType::where('code', $linkedSchemaCode)->first();

                if ($schema) {
                    $list->update(['linked_schema_id' => $schema->id]);
                }
            }
        }
    }
}
