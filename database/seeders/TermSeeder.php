<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TermSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Languages
        $languages = [
            [
                'id' => 1,
                'code' => 'fr',
                'name' => 'Français',
                'native_name' => 'Français',
                'description' => 'Langue française',
            ],
            [
                'id' => 2,
                'code' => 'en',
                'name' => 'Anglais',
                'native_name' => 'English',
                'description' => 'Langue anglaise',
            ],
            [
                'id' => 3,
                'code' => 'de',
                'name' => 'Allemand',
                'native_name' => 'Deutsch',
                'description' => 'Langue allemande',
            ],
            [
                'id' => 4,
                'code' => 'es',
                'name' => 'Espagnol',
                'native_name' => 'Español',
                'description' => 'Langue espagnole',
            ],
            [
                'id' => 5,
                'code' => 'it',
                'name' => 'Italien',
                'native_name' => 'Italiano',
                'description' => 'Langue italienne',
            ],
        ];

        // Term categories
        $termCategories = [
            [
                'id' => 1,
                'name' => 'Sujets',
                'description' => 'Termes liés aux sujets traités dans les documents',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'name' => 'Lieux',
                'description' => 'Termes géographiques',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'name' => 'Personnes',
                'description' => 'Termes liés aux personnes',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'name' => 'Organisations',
                'description' => 'Termes liés aux organisations',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'name' => 'Périodes',
                'description' => 'Termes liés aux périodes historiques',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Term types
        $termTypes = [
            [
                'id' => 1,
                'code' => 'TOP',
                'name' => 'Terme principal',
                'description' => 'Terme de premier niveau dans la hiérarchie',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'code' => 'SUB',
                'name' => 'Sous-terme',
                'description' => 'Terme subordonné à un terme principal',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'code' => 'USE',
                'name' => 'Terme préférentiel',
                'description' => 'Terme à utiliser de préférence',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'code' => 'REL',
                'name' => 'Terme associé',
                'description' => 'Terme en relation sémantique',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'code' => 'SYN',
                'name' => 'Synonyme',
                'description' => 'Terme équivalent',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Term equivalent types
        $termEquivalentTypes = [
            [
                'id' => 1,
                'code' => 'UP',
                'name' => 'Utilisé pour',
                'description' => 'Terme non préférentiel',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'code' => 'EP',
                'name' => 'Employé pour',
                'description' => 'Terme préférentiel',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'code' => 'VG',
                'name' => 'Variante graphique',
                'description' => 'Variation orthographique',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'code' => 'AB',
                'name' => 'Abréviation',
                'description' => 'Forme abrégée',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'code' => 'TS',
                'name' => 'Terme spécifique',
                'description' => 'Terme plus spécifique',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Terms
        $terms = [];
        $termTranslations = [];
        $termEquivalents = [];
        $termRelated = [];

        // Add some terms for each category
        for ($categoryId = 1; $categoryId <= 5; $categoryId++) {
            // Add 5 main terms per category
            for ($i = 1; $i <= 5; $i++) {
                $termId = ($categoryId - 1) * 10 + $i;

                $terms[] = [
                    'id' => $termId,
                    'name' => $this->getTermName($categoryId, $i, 'main'),
                    'description' => 'Terme principal pour la catégorie ' . $categoryId,
                    'language_id' => 1, // French
                    'category_id' => $categoryId,
                    'type_id' => 1, // Main term
                    'parent_id' => 0, // Use 0 instead of null for top-level terms
                ];

                // Add English translation
                $translationId = $termId + 100;
                $terms[] = [
                    'id' => $translationId,
                    'name' => $this->getTermName($categoryId, $i, 'main', 'en'),
                    'description' => 'Main term for category ' . $categoryId,
                    'language_id' => 2, // English
                    'category_id' => $categoryId,
                    'type_id' => 1, // Main term
                    'parent_id' => 0, // Use 0 instead of null for top-level terms
                ];

                $termTranslations[] = [
                    'term1_id' => $termId,
                    'term1_language_id' => 1,
                    'term2_id' => $translationId,
                    'term2_language_id' => 2,
                ];

                // Add 1 sub-term per main term
                $subTermId = $termId + 5;
                $terms[] = [
                    'id' => $subTermId,
                    'name' => $this->getTermName($categoryId, $i, 'sub'),
                    'description' => 'Sous-terme de ' . $this->getTermName($categoryId, $i, 'main'),
                    'language_id' => 1, // French
                    'category_id' => $categoryId,
                    'type_id' => 2, // Sub term
                    'parent_id' => $termId,
                ];

                // Add relations between terms
                if ($i > 1) {
                    $termRelated[] = [
                        'id' => $termId,
                        'term_id' => $termId,
                        'term_related_id' => $termId - 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                // Add some equivalents
                if ($i % 2 == 0) {
                    $termEquivalents[] = [
                        'id' => $termId,
                        'term_id' => $termId,
                        'term_used' => $this->getTermName($categoryId, $i, 'equivalent'),
                        'equivalent_type_id' => rand(1, 5),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        DB::table('languages')->insert($languages);
        DB::table('term_categories')->insert($termCategories);
        DB::table('term_types')->insert($termTypes);
        DB::table('term_equivalent_types')->insert($termEquivalentTypes);
        DB::table('terms')->insert($terms);
        DB::table('term_translations')->insert($termTranslations);
        DB::table('term_equivalent')->insert($termEquivalents);
        DB::table('term_related')->insert($termRelated);
    }

    private function getTermName($categoryId, $index, $type, $lang = 'fr')
    {
        $terms = [
            1 => [ // Subjects
                'main' => [
                    'fr' => ['Administration', 'Finance', 'Personnel', 'Infrastructure', 'Projet'],
                    'en' => ['Administration', 'Finance', 'Personnel', 'Infrastructure', 'Project'],
                ],
                'sub' => ['Organisation', 'Budget', 'Recrutement', 'Bâtiment', 'Développement'],
                'equivalent' => ['Gestion', 'Comptabilité', 'Employés', 'Construction', 'Plan'],
            ],
            2 => [ // Places
                'main' => [
                    'fr' => ['Paris', 'Lyon', 'Marseille', 'Bordeaux', 'Lille'],
                    'en' => ['Paris', 'Lyon', 'Marseille', 'Bordeaux', 'Lille'],
                ],
                'sub' => ['Île-de-France', 'Rhône-Alpes', 'PACA', 'Aquitaine', 'Nord-Pas-de-Calais'],
                'equivalent' => ['Capitale', 'Ville des Lumières', 'Cité Phocéenne', 'Port de la Lune', 'Capitale des Flandres'],
            ],
            3 => [ // People
                'main' => [
                    'fr' => ['Directeur', 'Archiviste', 'Secrétaire', 'Technicien', 'Chercheur'],
                    'en' => ['Director', 'Archivist', 'Secretary', 'Technician', 'Researcher'],
                ],
                'sub' => ['Directeur adjoint', 'Assistant archiviste', 'Assistant administratif', 'Maintenance', 'Doctorant'],
                'equivalent' => ['Chef', 'Conservateur', 'Administration', 'Agent technique', 'Étudiant'],
            ],
            4 => [ // Organizations
                'main' => [
                    'fr' => ['Ministère', 'Direction', 'Service', 'Département', 'Division'],
                    'en' => ['Ministry', 'Directorate', 'Service', 'Department', 'Division'],
                ],
                'sub' => ['Secrétariat général', 'Sous-direction', 'Unité', 'Bureau', 'Section'],
                'equivalent' => ['Administration centrale', 'Management', 'Entité', 'Pôle', 'Groupe'],
            ],
            5 => [ // Periods
                'main' => [
                    'fr' => ['XXe siècle', 'Après-guerre', 'Années 1960', 'Années 1980', 'Contemporain'],
                    'en' => ['20th century', 'Post-war', '1960s', '1980s', 'Contemporary'],
                ],
                'sub' => ['Première moitié', 'Reconstruction', 'Mai 68', 'Décennie 80', 'Actuel'],
                'equivalent' => ['1900-2000', '1945-1960', 'Sixties', 'Eighties', 'Présent'],
            ],
        ];

        if ($type == 'main') {
            return $terms[$categoryId]['main'][$lang][$index - 1] ?? 'Terme ' . $categoryId . '-' . $index;
        } else {
            return $terms[$categoryId][$type][$index - 1] ?? 'Terme ' . $categoryId . '-' . $index . '-' . $type;
        }
    }
}
