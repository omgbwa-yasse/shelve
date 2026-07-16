<?php

namespace Database\Seeders\Tools;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\Organisation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Plan de classement (activités) par direction : DSI, DRH, DAG.
 */
class ToolActivitySeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::beginTransaction();

        try {
            $this->command->info('Suppression des activités existantes...');
            Activity::query()->delete();

            $organisations = Organisation::whereIn('code', ['DSI', 'DRH', 'DAG'])->get()->keyBy('code');

            if ($organisations->count() != 3) {
                $this->command->error('Les organisations DSI, DRH et DAG doivent être créées avant ce seeder');
                Schema::enableForeignKeyConstraints();
                return;
            }

            $this->createSIActivities($organisations['DSI']);
            $this->createHRActivities($organisations['DRH']);
            $this->createAGActivities($organisations['DAG']);

            DB::commit();
            $this->command->info('Activités créées avec succès pour toutes les directions');

        } catch (\Exception $e) {
            DB::rollback();
            $this->command->error('Erreur lors de la création des activités: ' . $e->getMessage());
            Schema::enableForeignKeyConstraints();
            throw $e;
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Activités de la Direction des Systèmes d'Information
     */
    private function createSIActivities($organisation): void
    {
        $this->command->info('Création des activités pour la DSI...');

        $activities = [
            [
                'code' => 'DSI-01000',
                'name' => 'GESTION DES INFRASTRUCTURES',
                'children' => [
                    [
                        'code' => 'DSI-01100',
                        'name' => 'RÉSEAUX ET TÉLÉCOMMUNICATIONS',
                        'children' => [
                            ['code' => 'DSI-01110', 'name' => 'ADMINISTRATION DU RÉSEAU'],
                            ['code' => 'DSI-01120', 'name' => 'SUPERVISION ET MÉTROLOGIE'],
                            ['code' => 'DSI-01130', 'name' => 'SÉCURITÉ DES ACCÈS'],
                        ],
                    ],
                    [
                        'code' => 'DSI-01200',
                        'name' => 'SERVEURS ET STOCKAGE',
                        'children' => [
                            ['code' => 'DSI-01210', 'name' => 'EXPLOITATION DES SERVEURS'],
                            ['code' => 'DSI-01220', 'name' => 'SAUVEGARDE ET RESTAURATION'],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'DSI-02000',
                'name' => 'APPLICATIONS MÉTIER',
                'children' => [
                    [
                        'code' => 'DSI-02100',
                        'name' => 'DÉVELOPPEMENT ET INTÉGRATION',
                        'children' => [
                            ['code' => 'DSI-02110', 'name' => 'EXPRESSION DES BESOINS'],
                            ['code' => 'DSI-02120', 'name' => 'RECETTE ET MISE EN PRODUCTION'],
                        ],
                    ],
                    [
                        'code' => 'DSI-02200',
                        'name' => 'MAINTENANCE APPLICATIVE',
                        'children' => [
                            ['code' => 'DSI-02210', 'name' => 'CORRECTION DES ANOMALIES'],
                            ['code' => 'DSI-02220', 'name' => 'ÉVOLUTIONS FONCTIONNELLES'],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'DSI-03000',
                'name' => 'SUPPORT AUX UTILISATEURS',
                'children' => [
                    [
                        'code' => 'DSI-03100',
                        'name' => 'ASSISTANCE ET INCIDENTS',
                        'children' => [
                            ['code' => 'DSI-03110', 'name' => 'TRAITEMENT DES DEMANDES'],
                            ['code' => 'DSI-03120', 'name' => 'GESTION DU PARC INFORMATIQUE'],
                        ],
                    ],
                ],
            ],
        ];

        $this->createActivitiesRecursive($activities, null, $organisation);
    }

    /**
     * Activités de la Direction des Ressources Humaines
     */
    private function createHRActivities($organisation): void
    {
        $this->command->info('Création des activités pour la DRH...');

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
                            ['code' => 'DRH-01130', 'name' => 'INTÉGRATION DES NOUVEAUX EMPLOYÉS'],
                        ],
                    ],
                    [
                        'code' => 'DRH-01200',
                        'name' => 'GESTION DES CARRIÈRES',
                        'children' => [
                            ['code' => 'DRH-01210', 'name' => 'ÉVALUATIONS PROFESSIONNELLES'],
                            ['code' => 'DRH-01220', 'name' => 'PROMOTIONS ET MUTATIONS'],
                            ['code' => 'DRH-01230', 'name' => 'GESTION DES COMPÉTENCES'],
                        ],
                    ],
                ],
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
                            ['code' => 'DRH-02120', 'name' => 'MISE À JOUR DES INFORMATIONS'],
                        ],
                    ],
                    [
                        'code' => 'DRH-02200',
                        'name' => 'PAIE ET AVANTAGES',
                        'children' => [
                            ['code' => 'DRH-02210', 'name' => 'CALCUL DES SALAIRES'],
                            ['code' => 'DRH-02220', 'name' => 'GESTION DES AVANTAGES SOCIAUX'],
                        ],
                    ],
                ],
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
                            ['code' => 'DRH-03120', 'name' => 'ORGANISATION DES SESSIONS'],
                        ],
                    ],
                ],
            ],
        ];

        $this->createActivitiesRecursive($activities, null, $organisation);
    }

    /**
     * Activités de la Direction des Affaires Générales
     * (courrier, archives, logistique et patrimoine)
     */
    private function createAGActivities($organisation): void
    {
        $this->command->info('Création des activités pour la DAG...');

        $activities = [
            [
                'code' => 'DAG-01000',
                'name' => 'GESTION DU COURRIER',
                'children' => [
                    [
                        'code' => 'DAG-01100',
                        'name' => 'COURRIER ENTRANT',
                        'children' => [
                            ['code' => 'DAG-01110', 'name' => "DÉPÔT ET ENREGISTREMENT À L'ACCUEIL"],
                            ['code' => 'DAG-01120', 'name' => 'TRANSMISSION AU SECRÉTARIAT DU DG'],
                            ['code' => 'DAG-01130', 'name' => 'COTATION ET AFFECTATION'],
                        ],
                    ],
                    [
                        'code' => 'DAG-01200',
                        'name' => 'COURRIER SORTANT',
                        'children' => [
                            ['code' => 'DAG-01210', 'name' => 'VALIDATION HIÉRARCHIQUE'],
                            ['code' => 'DAG-01220', 'name' => 'SIGNATURE DU DIRECTEUR GÉNÉRAL'],
                            ['code' => 'DAG-01230', 'name' => 'EXPÉDITION ET SUIVI'],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'DAG-02000',
                'name' => 'GESTION DOCUMENTAIRE ET ARCHIVES',
                'children' => [
                    [
                        'code' => 'DAG-02100',
                        'name' => 'COLLECTE ET RÉCEPTION',
                        'children' => [
                            ['code' => 'DAG-02110', 'name' => 'RÉCEPTION DES VERSEMENTS'],
                            ['code' => 'DAG-02120', 'name' => 'CONTRÔLE DE CONFORMITÉ'],
                            ['code' => 'DAG-02130', 'name' => 'ENREGISTREMENT DES ENTRÉES'],
                        ],
                    ],
                    [
                        'code' => 'DAG-02200',
                        'name' => 'TRAITEMENT ET CONSERVATION',
                        'children' => [
                            ['code' => 'DAG-02210', 'name' => 'CLASSEMENT ET INDEXATION'],
                            ['code' => 'DAG-02220', 'name' => 'DESCRIPTION ARCHIVISTIQUE'],
                            ['code' => 'DAG-02230', 'name' => 'NUMÉRISATION'],
                        ],
                    ],
                    [
                        'code' => 'DAG-02300',
                        'name' => 'COMMUNICATION ET ACCÈS',
                        'children' => [
                            ['code' => 'DAG-02310', 'name' => 'PRÊT ET BORDEREAUX DE COMMUNICATION'],
                            ['code' => 'DAG-02320', 'name' => 'AIDE À LA RECHERCHE'],
                        ],
                    ],
                ],
            ],
            [
                'code' => 'DAG-03000',
                'name' => 'LOGISTIQUE ET PATRIMOINE',
                'children' => [
                    [
                        'code' => 'DAG-03100',
                        'name' => 'MOYENS GÉNÉRAUX',
                        'children' => [
                            ['code' => 'DAG-03110', 'name' => 'GESTION DES FOURNITURES'],
                            ['code' => 'DAG-03120', 'name' => 'ENTRETIEN DES LOCAUX'],
                        ],
                    ],
                ],
            ],
        ];

        $this->createActivitiesRecursive($activities, null, $organisation);
    }

    /**
     * Créer les activités récursivement et les associer à l'organisation via la table pivot
     */
    private function createActivitiesRecursive($activities, $parentId = null, $organisation = null): void
    {
        foreach ($activities as $activityData) {
            $activity = Activity::create([
                'code' => $activityData['code'],
                'name' => $activityData['name'],
                'parent_id' => $parentId,
            ]);

            if ($organisation) {
                $activity->organisations()->attach($organisation->id, ['creator_id' => 999999]);
            }

            if (isset($activityData['children']) && !empty($activityData['children'])) {
                $this->createActivitiesRecursive($activityData['children'], $activity->id, $organisation);
            }
        }
    }
}
