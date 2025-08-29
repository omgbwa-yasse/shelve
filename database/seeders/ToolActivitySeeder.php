<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\Organisation;
use Illuminate\Support\Facades\DB;

class ToolActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // Supprimer toutes les activités existantes
            $this->command->info('🗑️ Suppression des activités existantes...');
            Activity::query()->delete();

            // Récupérer les organisations
            $organisations = Organisation::whereIn('code', ['DF', 'DRH', 'DADA'])->get()->keyBy('code');

            if ($organisations->count() != 3) {
                $this->command->error('Les organisations DF, DRH et DADA doivent être créées avant ce seeder');
                return;
            }

            // Créer les activités pour chaque direction
            $this->createFinanceActivities($organisations['DF']);
            $this->createHRActivities($organisations['DRH']);
            $this->createArchivesActivities($organisations['DADA']);

            DB::commit();
            $this->command->info('✅ Activités créées avec succès pour toutes les directions');

        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('❌ Erreur lors de la création des activités: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Créer les activités pour la Direction des Finances
     */
    private function createFinanceActivities($organisation)
    {
        $this->command->info('💰 Création des activités pour la Direction des Finances...');

        $activities = [
            [
                'code' => 'DF-01000',
                'name' => 'GESTION BUDGÉTAIRE',
                'children' => [
                    [
                        'code' => 'DF-01100',
                        'name' => 'PRÉPARATION DU BUDGET',
                        'children' => [
                            ['code' => 'DF-01110', 'name' => 'COLLECTE DES PRÉVISIONS BUDGÉTAIRES'],
                            ['code' => 'DF-01120', 'name' => 'ANALYSE DES BESOINS FINANCIERS'],
                            ['code' => 'DF-01130', 'name' => 'ÉLABORATION DU BUDGET PRIMITIF']
                        ]
                    ],
                    [
                        'code' => 'DF-01200',
                        'name' => 'EXÉCUTION BUDGÉTAIRE',
                        'children' => [
                            ['code' => 'DF-01210', 'name' => 'SUIVI DES ENGAGEMENTS'],
                            ['code' => 'DF-01220', 'name' => 'CONTRÔLE DES DÉPENSES'],
                            ['code' => 'DF-01230', 'name' => 'GESTION DES RECETTES']
                        ]
                    ]
                ]
            ],
            [
                'code' => 'DF-02000',
                'name' => 'COMPTABILITÉ GÉNÉRALE',
                'children' => [
                    [
                        'code' => 'DF-02100',
                        'name' => 'TENUE DES COMPTES',
                        'children' => [
                            ['code' => 'DF-02110', 'name' => 'SAISIE DES ÉCRITURES COMPTABLES'],
                            ['code' => 'DF-02120', 'name' => 'RAPPROCHEMENTS BANCAIRES']
                        ]
                    ],
                    [
                        'code' => 'DF-02200',
                        'name' => 'ÉTATS FINANCIERS',
                        'children' => [
                            ['code' => 'DF-02210', 'name' => 'BILAN COMPTABLE'],
                            ['code' => 'DF-02220', 'name' => 'COMPTE DE RÉSULTAT']
                        ]
                    ]
                ]
            ],
            [
                'code' => 'DF-03000',
                'name' => 'MARCHÉS PUBLICS',
                'children' => [
                    [
                        'code' => 'DF-03100',
                        'name' => 'PROCÉDURES D\'APPEL D\'OFFRES',
                        'children' => [
                            ['code' => 'DF-03110', 'name' => 'PUBLICATION DES AVIS'],
                            ['code' => 'DF-03120', 'name' => 'ÉVALUATION DES OFFRES']
                        ]
                    ]
                ]
            ]
        ];

        $this->createActivitiesRecursive($activities, null, $organisation);
    }

    /**
     * Créer les activités pour la Direction des Ressources Humaines
     */
    private function createHRActivities($organisation)
    {
        $this->command->info('👥 Création des activités pour la Direction des Ressources Humaines...');

        $activities = [
            [
                'code' => 'DRH-01000',
                'name' => 'GESTION DU PERSONNEL',
                'children' => [
                    [
                        'code' => 'DRH-01100',
                        'name' => 'RECRUTEMENT',
                        'children' => [
                            ['code' => 'DRH-01110', 'name' => 'DÉFINITION DES POSTES'],
                            ['code' => 'DRH-01120', 'name' => 'SÉLECTION DES CANDIDATS'],
                            ['code' => 'DRH-01130', 'name' => 'INTÉGRATION DES NOUVEAUX EMPLOYÉS']
                        ]
                    ],
                    [
                        'code' => 'DRH-01200',
                        'name' => 'GESTION DES CARRIÈRES',
                        'children' => [
                            ['code' => 'DRH-01210', 'name' => 'ÉVALUATIONS PROFESSIONNELLES'],
                            ['code' => 'DRH-01220', 'name' => 'PROMOTIONS ET MUTATIONS'],
                            ['code' => 'DRH-01230', 'name' => 'GESTION DES COMPÉTENCES']
                        ]
                    ]
                ]
            ],
            [
                'code' => 'DRH-02000',
                'name' => 'ADMINISTRATION DU PERSONNEL',
                'children' => [
                    [
                        'code' => 'DRH-02100',
                        'name' => 'DOSSIERS INDIVIDUELS',
                        'children' => [
                            ['code' => 'DRH-02110', 'name' => 'CONSTITUTION DES DOSSIERS'],
                            ['code' => 'DRH-02120', 'name' => 'MISE À JOUR DES INFORMATIONS']
                        ]
                    ],
                    [
                        'code' => 'DRH-02200',
                        'name' => 'PAIE ET AVANTAGES',
                        'children' => [
                            ['code' => 'DRH-02210', 'name' => 'CALCUL DES SALAIRES'],
                            ['code' => 'DRH-02220', 'name' => 'GESTION DES AVANTAGES SOCIAUX']
                        ]
                    ]
                ]
            ],
            [
                'code' => 'DRH-03000',
                'name' => 'FORMATION ET DÉVELOPPEMENT',
                'children' => [
                    [
                        'code' => 'DRH-03100',
                        'name' => 'PLANIFICATION DES FORMATIONS',
                        'children' => [
                            ['code' => 'DRH-03110', 'name' => 'IDENTIFICATION DES BESOINS'],
                            ['code' => 'DRH-03120', 'name' => 'ORGANISATION DES SESSIONS']
                        ]
                    ]
                ]
            ]
        ];

        $this->createActivitiesRecursive($activities, null, $organisation);
    }

    /**
     * Créer les activités pour la Direction des Archives et Documents Administratifs
     */
    private function createArchivesActivities($organisation)
    {
        $this->command->info('📚 Création des activités pour la Direction des Archives et Documents Administratifs...');

        $activities = [
            [
                'code' => 'DADA-01000',
                'name' => 'GESTION DOCUMENTAIRE',
                'children' => [
                    [
                        'code' => 'DADA-01100',
                        'name' => 'COLLECTE ET RÉCEPTION',
                        'children' => [
                            ['code' => 'DADA-01110', 'name' => 'RÉCEPTION DES VERSEMENTS'],
                            ['code' => 'DADA-01120', 'name' => 'CONTRÔLE DE CONFORMITÉ'],
                            ['code' => 'DADA-01130', 'name' => 'ENREGISTREMENT DES ENTRÉES']
                        ]
                    ],
                    [
                        'code' => 'DADA-01200',
                        'name' => 'TRAITEMENT DOCUMENTAIRE',
                        'children' => [
                            ['code' => 'DADA-01210', 'name' => 'CLASSEMENT ET INDEXATION'],
                            ['code' => 'DADA-01220', 'name' => 'DESCRIPTION ARCHIVISTIQUE'],
                            ['code' => 'DADA-01230', 'name' => 'NUMÉRISATION']
                        ]
                    ]
                ]
            ],
            [
                'code' => 'DADA-02000',
                'name' => 'CONSERVATION',
                'children' => [
                    [
                        'code' => 'DADA-02100',
                        'name' => 'PRÉSERVATION PHYSIQUE',
                        'children' => [
                            ['code' => 'DADA-02110', 'name' => 'CONDITIONNEMENT'],
                            ['code' => 'DADA-02120', 'name' => 'CONTRÔLE CLIMATIQUE']
                        ]
                    ],
                    [
                        'code' => 'DADA-02200',
                        'name' => 'PRÉSERVATION NUMÉRIQUE',
                        'children' => [
                            ['code' => 'DADA-02210', 'name' => 'MIGRATION DES FORMATS'],
                            ['code' => 'DADA-02220', 'name' => 'SAUVEGARDE DES DONNÉES']
                        ]
                    ]
                ]
            ],
            [
                'code' => 'DADA-03000',
                'name' => 'COMMUNICATION ET ACCÈS',
                'children' => [
                    [
                        'code' => 'DADA-03100',
                        'name' => 'RECHERCHE ET CONSULTATION',
                        'children' => [
                            ['code' => 'DADA-03110', 'name' => 'ACCUEIL DES CHERCHEURS'],
                            ['code' => 'DADA-03120', 'name' => 'AIDE À LA RECHERCHE']
                        ]
                    ]
                ]
            ]
        ];

        $this->createActivitiesRecursive($activities, null, $organisation);
    }

    /**
     * Créer les activités de manière récursive et les associer à l'organisation via la table pivot
     */
    private function createActivitiesRecursive($activities, $parentId = null, $organisation = null)
    {
        foreach ($activities as $activityData) {
            $activity = Activity::create([
                'code' => $activityData['code'],
                'name' => $activityData['name'],
                'parent_id' => $parentId
            ]);

            // Associer l'activité à l'organisation via la table pivot
            if ($organisation) {
                $activity->organisations()->attach($organisation->id, ['creator_id' => 999999]);
            }

            if (isset($activityData['children']) && !empty($activityData['children'])) {
                $this->createActivitiesRecursive($activityData['children'], $activity->id, $organisation);
            }
        }
    }
}
