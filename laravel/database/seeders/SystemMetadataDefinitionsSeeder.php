<?php

namespace Database\Seeders;

use App\Models\MetadataDefinition;
use App\Models\RecordType;
use App\Models\RecordTypeMetadataProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Convertit les ~27 anciennes colonnes descriptives de `records` (ISAD(G) +
 * descriptifs non-ISAD) en `MetadataDefinition` système (is_system=true),
 * attachées à tous les `RecordType` actifs existants via `RecordTypeMetadataProfile`
 * (facultatif, visible — comportement identique à avant, où aucun de ces champs
 * n'était obligatoire). Idempotent : rejouable sans doublon (updateOrCreate par
 * code, firstOrCreate par paire type/définition).
 */
class SystemMetadataDefinitionsSeeder extends Seeder
{
    /**
     * code => [name, data_type, sort_order]
     */
    private const DEFINITIONS = [
        // Contexte
        'biographical_history' => ['Notice biographique', 'textarea', 10],
        'archival_history' => ['Historique de conservation', 'textarea', 20],
        'acquisition_source' => ['Source d\'acquisition', 'textarea', 30],
        // Contenu et structure
        'content' => ['Contenu', 'textarea', 40],
        'appraisal' => ['Évaluation', 'textarea', 50],
        'accrual' => ['Accroissement', 'textarea', 60],
        'arrangement' => ['Classement', 'textarea', 70],
        // Conditions d'accès et d'utilisation
        'access_conditions' => ['Conditions d\'accès', 'text', 80],
        'reproduction_conditions' => ['Conditions de reproduction', 'text', 90],
        'language_material' => ['Langue du document', 'text', 100],
        'characteristic' => ['Caractéristiques', 'text', 110],
        'finding_aids' => ['Instruments de recherche', 'text', 120],
        // Sources complémentaires
        'location_original' => ['Conservation des originaux', 'text', 130],
        'location_copy' => ['Conservation des copies', 'text', 140],
        'related_unit' => ['Unités de description apparentées', 'text', 150],
        'publication_note' => ['Note de publication', 'textarea', 160],
        // Notes
        'note' => ['Note', 'textarea', 170],
        'archivist_note' => ['Note de l\'archiviste', 'textarea', 180],
        'rule_convention' => ['Règles et conventions', 'text', 190],
        // Descriptifs non-ISAD
        'extent' => ['Importance matérielle', 'text', 200],
        'category_precision' => ['Précision de catégorie', 'text', 210],
        'table_of_contents' => ['Table des matières', 'textarea', 220],
        'quantity' => ['Quantité', 'text', 230],
        'dimension' => ['Dimension', 'text', 240],
        'publisher' => ['Éditeur', 'text', 250],
        'sort_value' => ['Valeur de tri', 'text', 260],
        'geographic_scope' => ['Couverture géographique', 'textarea', 270],
    ];

    public function run(): void
    {
        $userId = User::query()->value('id') ?? 1;

        $definitionIds = [];

        foreach (self::DEFINITIONS as $code => [$name, $dataType, $sortOrder]) {
            $definition = MetadataDefinition::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'data_type' => $dataType,
                    'searchable' => true,
                    'active' => true,
                    'is_system' => true,
                    'sort_order' => $sortOrder,
                    'created_by' => $userId,
                ]
            );

            $definitionIds[$code] = $definition->id;
        }

        $recordTypes = RecordType::query()->where('is_active', true)->get(['id']);

        foreach ($recordTypes as $recordType) {
            foreach ($definitionIds as $code => $definitionId) {
                RecordTypeMetadataProfile::firstOrCreate(
                    [
                        'record_type_id' => $recordType->id,
                        'metadata_definition_id' => $definitionId,
                    ],
                    [
                        'mandatory' => false,
                        'visible' => true,
                        'sort_order' => self::DEFINITIONS[$code][2],
                        'created_by' => $userId,
                    ]
                );
            }
        }

        $this->command?->info(sprintf(
            '%d définitions système, attachées à %d types de notice.',
            count($definitionIds),
            $recordTypes->count()
        ));
    }
}
