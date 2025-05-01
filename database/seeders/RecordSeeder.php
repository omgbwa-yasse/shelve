<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecordSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Record statuses
        $recordStatuses = [
            [
                'id' => 1,
                'name' => 'Brouillon',
                'description' => 'Enregistrement en cours d\'édition',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Validé',
                'description' => 'Enregistrement validé',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Archivé',
                'description' => 'Enregistrement archivé',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'À réviser',
                'description' => 'Enregistrement nécessitant une révision',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'name' => 'Éliminé',
                'description' => 'Enregistrement éliminé',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Record supports
        $recordSupports = [
            [
                'id' => 1,
                'name' => 'Papier',
                'description' => 'Document papier',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Numérique',
                'description' => 'Document numérique',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Microfilm',
                'description' => 'Document sur microfilm',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'Photographie',
                'description' => 'Document photographique',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'name' => 'Mixte',
                'description' => 'Document sur supports multiples',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Record levels
        $recordLevels = [
            [
                'id' => 1,
                'name' => 'Fonds',
                'description' => 'Ensemble de documents',
                'child_id' => 2,
                'has_child' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Sous-fonds',
                'description' => 'Subdivision d\'un fonds',
                'child_id' => 3,
                'has_child' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Série',
                'description' => 'Ensemble de dossiers',
                'child_id' => 4,
                'has_child' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'Sous-série',
                'description' => 'Subdivision d\'une série',
                'child_id' => 5,
                'has_child' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'name' => 'Dossier',
                'description' => 'Ensemble de pièces',
                'child_id' => 6,
                'has_child' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'name' => 'Pièce',
                'description' => 'Document individuel',
                'child_id' => null,
                'has_child' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Keywords
        $keywords = [
            ['id' => 1, 'name' => 'budget', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'rapport', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'personnel', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'contrat', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'formation', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'name' => 'projet', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'name' => 'procès-verbal', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'name' => 'compte-rendu', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 9, 'name' => 'statistique', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'name' => 'convention', 'created_at' => $now, 'updated_at' => $now],
        ];

        // Insert base data
        DB::table('record_statuses')->insert($recordStatuses);
        DB::table('record_supports')->insert($recordSupports);
        DB::table('record_levels')->insert($recordLevels);
        DB::table('keywords')->insert($keywords);

        // Now create records (50 records with hierarchy)
        $records = [];
        $recordKeywords = [];
        $recordContainers = [];
        $recordAuthors = [];

        // Track used keyword combinations to avoid duplicates
        $usedKeywordCombos = [];

        // Top level fonds (5)
        for ($i = 1; $i <= 5; $i++) {
            $activityId = $i;
            $records[] = [
                'id' => $i,
                'code' => 'F' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'name' => 'Fonds ' . $this->getActivityName($activityId),
                'date_format' => 'Y',
                'date_start' => '1950',
                'date_end' => '2020',
                'date_exact' => null,
                'level_id' => 1,
                'width' => null,
                'width_description' => '15 mètres linéaires',
                'biographical_history' => 'Historique administratif du service ' . $this->getActivityName($activityId),
                'archival_history' => 'Versement effectué en 2021',
                'acquisition_source' => 'Versement réglementaire',
                'content' => 'Ensemble des documents produits par le service ' . $this->getActivityName($activityId),
                'appraisal' => 'Tri effectué selon le tableau de gestion',
                'accrual' => 'Versements complémentaires prévus',
                'arrangement' => 'Classement chronologique et thématique',
                'access_conditions' => 'Selon réglementation en vigueur',
                'reproduction_conditions' => 'Sur autorisation',
                'language_material' => 'français',
                'characteristic' => null,
                'finding_aids' => 'Inventaire détaillé disponible',
                'location_original' => null,
                'location_copy' => null,
                'related_unit' => null,
                'publication_note' => null,
                'note' => null,
                'archivist_note' => 'Traitement réalisé par le service des archives',
                'rule_convention' => 'ISAD(G)',
                'status_id' => 3,
                'support_id' => 5,
                'activity_id' => $activityId,
                'parent_id' => null,
                'container_id' => null,
                'user_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Add keywords and authors for this record with uniqueness checks
            for ($j = 0; $j < 2; $j++) {
                $keywordId = rand(1, 10);
                $comboKey = $i . '-' . $keywordId;

                if (!in_array($comboKey, $usedKeywordCombos)) {
                    $recordKeywords[] = ['record_id' => $i, 'keyword_id' => $keywordId];
                    $usedKeywordCombos[] = $comboKey;
                }
            }

            $recordAuthors[] = ['author_id' => rand(1, 12), 'record_id' => $i];
        }

        // Sub-fonds (10) - 2 per fonds
        $recordId = 6;
        for ($fondsId = 1; $fondsId <= 5; $fondsId++) {
            for ($j = 1; $j <= 2; $j++) {
                $activitySubId = $this->getSubActivityId($fondsId, $j);
                $startYear = 1950 + ($j-1) * 35;
                $endYear = $startYear + 34;

                $records[] = [
                    'id' => $recordId,
                    'code' => 'SF' . str_pad($recordId, 3, '0', STR_PAD_LEFT),
                    'name' => 'Sous-fonds ' . $this->getActivityName($activitySubId),
                    'date_format' => 'Y',
                    'date_start' => (string)$startYear,
                    'date_end' => (string)$endYear,
                    'date_exact' => null,
                    'level_id' => 2,
                    'width' => null,
                    'width_description' => '7 mètres linéaires',
                    'biographical_history' => 'Historique du sous-service',
                    'archival_history' => 'Intégré au versement général',
                    'acquisition_source' => 'Versement réglementaire',
                    'content' => 'Documents relatifs à ' . $this->getActivityName($activitySubId),
                    'appraisal' => 'Tri effectué selon le tableau de gestion',
                    'accrual' => null,
                    'arrangement' => 'Classement chronologique',
                    'access_conditions' => 'Selon réglementation en vigueur',
                    'reproduction_conditions' => 'Sur autorisation',
                    'language_material' => 'français',
                    'characteristic' => null,
                    'finding_aids' => null,
                    'location_original' => null,
                    'location_copy' => null,
                    'related_unit' => null,
                    'publication_note' => null,
                    'note' => null,
                    'archivist_note' => null,
                    'rule_convention' => 'ISAD(G)',
                    'status_id' => 3,
                    'support_id' => 5,
                    'activity_id' => $activitySubId,
                    'parent_id' => $fondsId,
                    'container_id' => null,
                    'user_id' => 5,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // Add keywords with uniqueness check
                for ($k = 0; $k < 2; $k++) {
                    $keywordId = rand(1, 10);
                    $comboKey = $recordId . '-' . $keywordId;

                    if (!in_array($comboKey, $usedKeywordCombos)) {
                        $recordKeywords[] = ['record_id' => $recordId, 'keyword_id' => $keywordId];
                        $usedKeywordCombos[] = $comboKey;
                    }
                }

                $recordAuthors[] = ['author_id' => rand(1, 12), 'record_id' => $recordId];

                $recordId++;
            }
        }

        // Series (20) - 2 per sub-fonds
        for ($subFondsId = 6; $subFondsId <= 15; $subFondsId++) {
            for ($k = 1; $k <= 2; $k++) {
                $parentFondsId = floor(($subFondsId - 6) / 2) + 1;
                $activityId = $this->getSubActivityId($parentFondsId, ceil(($subFondsId - 5) / 2) % 2 + 1);
                $decade = 1950 + (($subFondsId - 6) % 2) * 20 + ($k-1) * 10;

                $records[] = [
                    'id' => $recordId,
                    'code' => 'S' . str_pad($recordId, 3, '0', STR_PAD_LEFT),
                    'name' => 'Série - ' . $decade . '-' . ($decade+9) . ' - ' . $this->getSeriesName($k),
                    'date_format' => 'Y',
                    'date_start' => (string)$decade,
                    'date_end' => (string)($decade+9),
                    'date_exact' => null,
                    'level_id' => 3,
                    'width' => null,
                    'width_description' => '3 mètres linéaires',
                    'biographical_history' => null,
                    'archival_history' => null,
                    'acquisition_source' => null,
                    'content' => 'Série de documents couvrant la période ' . $decade . '-' . ($decade+9),
                    'appraisal' => null,
                    'accrual' => null,
                    'arrangement' => 'Classement chronologique',
                    'access_conditions' => null,
                    'reproduction_conditions' => null,
                    'language_material' => 'français',
                    'characteristic' => null,
                    'finding_aids' => null,
                    'location_original' => null,
                    'location_copy' => null,
                    'related_unit' => null,
                    'publication_note' => null,
                    'note' => null,
                    'archivist_note' => null,
                    'rule_convention' => 'ISAD(G)',
                    'status_id' => 3,
                    'support_id' => 1,
                    'activity_id' => $activityId,
                    'parent_id' => $subFondsId,
                    'container_id' => null,
                    'user_id' => 5,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                // Add keywords with uniqueness check
                for ($m = 0; $m < 2; $m++) {
                    $keywordId = rand(1, 10);
                    $comboKey = $recordId . '-' . $keywordId;

                    if (!in_array($comboKey, $usedKeywordCombos)) {
                        $recordKeywords[] = ['record_id' => $recordId, 'keyword_id' => $keywordId];
                        $usedKeywordCombos[] = $comboKey;
                    }
                }

                $recordAuthors[] = ['author_id' => rand(1, 12), 'record_id' => $recordId];

                $recordId++;
            }
        }

        // Dossiers (15) - scattered across series
        for ($l = 1; $l <= 15; $l++) {
            $seriesId = rand(16, 35);
            $year = rand(1950, 2020);
            $containerNum = rand(1, 80);

            $records[] = [
                'id' => $recordId,
                'code' => 'D' . str_pad($recordId, 3, '0', STR_PAD_LEFT),
                'name' => 'Dossier ' . $year . ' - ' . $this->getDossierName($l),
                'date_format' => 'Y',
                'date_start' => (string)$year,
                'date_end' => (string)$year,
                'date_exact' => null,
                'level_id' => 5,
                'width' => 5,
                'width_description' => '5 cm',
                'biographical_history' => null,
                'archival_history' => null,
                'acquisition_source' => null,
                'content' => 'Dossier contenant les documents relatifs à ' . $this->getDossierName($l) . ' pour l\'année ' . $year,
                'appraisal' => null,
                'accrual' => null,
                'arrangement' => 'Classement chronologique',
                'access_conditions' => null,
                'reproduction_conditions' => null,
                'language_material' => 'français',
                'characteristic' => null,
                'finding_aids' => null,
                'location_original' => null,
                'location_copy' => null,
                'related_unit' => null,
                'publication_note' => null,
                'note' => null,
                'archivist_note' => null,
                'rule_convention' => null,
                'status_id' => 3,
                'support_id' => 1,
                'activity_id' => rand(7, 18),
                'parent_id' => $seriesId,
                'container_id' => $containerNum,
                'user_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Add keywords with uniqueness check
            for ($n = 0; $n < 2; $n++) {
                $keywordId = rand(1, 10);
                $comboKey = $recordId . '-' . $keywordId;

                if (!in_array($comboKey, $usedKeywordCombos)) {
                    $recordKeywords[] = ['record_id' => $recordId, 'keyword_id' => $keywordId];
                    $usedKeywordCombos[] = $comboKey;
                }
            }

            $recordAuthors[] = ['author_id' => rand(1, 12), 'record_id' => $recordId];
            $recordContainers[] = [
                'record_id' => $recordId,
                'container_id' => $containerNum,
                'description' => 'Stockage du dossier ' . $recordId,
                'creator_id' => 5,
                'created_at' => $now,
                'updated_at' => $now
            ];

            $recordId++;
        }

        DB::table('records')->insert($records);
        DB::table('record_keyword')->insert($recordKeywords);
        DB::table('record_container')->insert($recordContainers);
        DB::table('record_author')->insert($recordAuthors);
    }

    private function getActivityName($id)
    {
        $activities = [
            1 => 'Direction',
            2 => 'Finances',
            3 => 'Ressources Humaines',
            4 => 'Technique',
            5 => 'Juridique',
            6 => 'Projets',
            7 => 'Procès-verbaux',
            8 => 'Rapports d\'activité',
            9 => 'Budget',
            10 => 'Comptabilité',
            11 => 'Dossiers du personnel',
            12 => 'Formation',
            13 => 'Plans',
            14 => 'Maintenance',
            15 => 'Contrats',
            16 => 'Litiges',
            17 => 'Développement',
            18 => 'Recherche',
        ];

        return $activities[$id] ?? 'Activité ' . $id;
    }

    private function getSubActivityId($fondsId, $subNum)
    {
        $mapping = [
            1 => [7, 8],   // Direction -> PV, Rapports
            2 => [9, 10],  // Finances -> Budget, Comptabilité
            3 => [11, 12], // RH -> Dossiers personnel, Formation
            4 => [13, 14], // Technique -> Plans, Maintenance
            5 => [15, 16], // Juridique -> Contrats, Litiges
        ];

        return $mapping[$fondsId][$subNum - 1];
    }

    private function getSeriesName($num)
    {
        $series = [
            1 => 'Administration',
            2 => 'Opérationnel',
            3 => 'Projets',
            4 => 'Correspondance',
            5 => 'Rapports',
            6 => 'Finances',
        ];

        return $series[$num] ?? 'Série ' . $num;
    }

    private function getDossierName($num)
    {
        $dossiers = [
            1 => 'Budget annuel',
            2 => 'Contrats fournisseurs',
            3 => 'Personnel permanent',
            4 => 'Personnel temporaire',
            5 => 'Projets d\'infrastructure',
            6 => 'Maintenance bâtiments',
            7 => 'Formations du personnel',
            8 => 'Comités de direction',
            9 => 'Relations externes',
            10 => 'Conventions partenaires',
            11 => 'Audits',
            12 => 'Statistiques annuelles',
            13 => 'Marchés publics',
            14 => 'Communications',
            15 => 'Études et recherches',
        ];

        return $dossiers[$num] ?? 'Dossier ' . $num;
    }
}
