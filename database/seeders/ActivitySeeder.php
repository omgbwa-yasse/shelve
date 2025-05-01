<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Communication statuses
        $communicabilities = [
            [
                'id' => 1,
                'code' => 'LIB',
                'name' => 'Librement communicable',
                'duration' => 0,
                'description' => 'Document librement communicable sans délai',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'code' => 'CONF-25',
                'name' => 'Confidentiel - 25 ans',
                'duration' => 25,
                'description' => 'Document communicable après 25 ans',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'code' => 'SEC-50',
                'name' => 'Secret - 50 ans',
                'duration' => 50,
                'description' => 'Document communicable après 50 ans',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'code' => 'CONF-VIE',
                'name' => 'Confidentiel - Vie privée',
                'duration' => 75,
                'description' => 'Document contenant des informations sur la vie privée - 75 ans',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'code' => 'RESTR',
                'name' => 'Accès restreint',
                'duration' => 100,
                'description' => 'Document accessible uniquement sur autorisation',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Sorts
        $sorts = [
            [
                'id' => 1,
                'code' => 'CONS',
                'name' => 'Conservation',
                'description' => 'Conservation définitive',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'code' => 'CONS-ECH',
                'name' => 'Conservation échantillon',
                'description' => 'Conservation d\'un échantillon représentatif',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'code' => 'DEST',
                'name' => 'Destruction',
                'description' => 'Destruction après durée légale',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'code' => 'TRI',
                'name' => 'Tri',
                'description' => 'Tri selon critères définis',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Retentions
        $retentions = [
            [
                'id' => 1,
                'code' => 'DUA-3',
                'name' => 'Durée d\'utilité administrative de 3 ans',
                'duration' => 3,
                'sort_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'code' => 'DUA-5',
                'name' => 'Durée d\'utilité administrative de 5 ans',
                'duration' => 5,
                'sort_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'code' => 'DUA-10',
                'name' => 'Durée d\'utilité administrative de 10 ans',
                'duration' => 10,
                'sort_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'code' => 'DUA-15',
                'name' => 'Durée d\'utilité administrative de 15 ans',
                'duration' => 15,
                'sort_id' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'code' => 'DUA-30',
                'name' => 'Durée d\'utilité administrative de 30 ans',
                'duration' => 30,
                'sort_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'code' => 'DUA-70',
                'name' => 'Durée d\'utilité administrative de 70 ans',
                'duration' => 70,
                'sort_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Activities
        $activities = [
            [
                'id' => 1,
                'code' => 'DIR',
                'name' => 'Direction',
                'observation' => 'Documents de direction et stratégie',
                'parent_id' => null,
                'communicability_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'code' => 'FIN',
                'name' => 'Finances',
                'observation' => 'Documents financiers et comptables',
                'parent_id' => null,
                'communicability_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'code' => 'RH',
                'name' => 'Ressources Humaines',
                'observation' => 'Gestion du personnel',
                'parent_id' => null,
                'communicability_id' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'code' => 'TECH',
                'name' => 'Technique',
                'observation' => 'Documents techniques',
                'parent_id' => null,
                'communicability_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'code' => 'LEGAL',
                'name' => 'Juridique',
                'observation' => 'Documents juridiques',
                'parent_id' => null,
                'communicability_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 6,
                'code' => 'PROJ',
                'name' => 'Projets',
                'observation' => 'Gestion de projets',
                'parent_id' => null,
                'communicability_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 7,
                'code' => 'DIR-PV',
                'name' => 'Procès-verbaux',
                'observation' => 'Procès-verbaux des réunions de direction',
                'parent_id' => 1,
                'communicability_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 8,
                'code' => 'DIR-RAP',
                'name' => 'Rapports d\'activité',
                'observation' => 'Rapports d\'activité annuels',
                'parent_id' => 1,
                'communicability_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 9,
                'code' => 'FIN-BUD',
                'name' => 'Budget',
                'observation' => 'Documents budgétaires',
                'parent_id' => 2,
                'communicability_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 10,
                'code' => 'FIN-COMP',
                'name' => 'Comptabilité',
                'observation' => 'Documents comptables',
                'parent_id' => 2,
                'communicability_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 11,
                'code' => 'RH-PERS',
                'name' => 'Dossiers du personnel',
                'observation' => 'Dossiers individuels du personnel',
                'parent_id' => 3,
                'communicability_id' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 12,
                'code' => 'RH-FORM',
                'name' => 'Formation',
                'observation' => 'Documents de formation',
                'parent_id' => 3,
                'communicability_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 13,
                'code' => 'TECH-PLAN',
                'name' => 'Plans',
                'observation' => 'Plans techniques',
                'parent_id' => 4,
                'communicability_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 14,
                'code' => 'TECH-MAIN',
                'name' => 'Maintenance',
                'observation' => 'Dossiers de maintenance',
                'parent_id' => 4,
                'communicability_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 15,
                'code' => 'LEGAL-CONT',
                'name' => 'Contrats',
                'observation' => 'Contrats et conventions',
                'parent_id' => 5,
                'communicability_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 16,
                'code' => 'LEGAL-LIT',
                'name' => 'Litiges',
                'observation' => 'Dossiers de litiges',
                'parent_id' => 5,
                'communicability_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 17,
                'code' => 'PROJ-DEV',
                'name' => 'Développement',
                'observation' => 'Projets de développement',
                'parent_id' => 6,
                'communicability_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 18,
                'code' => 'PROJ-RECH',
                'name' => 'Recherche',
                'observation' => 'Projets de recherche',
                'parent_id' => 6,
                'communicability_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Organisation-Activity relationships
        $organisationActivities = [
            // Direction - across all main departments
            ['organisation_id' => 1, 'activity_id' => 1, 'creator_id' => 1, ],
            ['organisation_id' => 2, 'activity_id' => 1, 'creator_id' => 1, ],
            ['organisation_id' => 3, 'activity_id' => 1, 'creator_id' => 1, ],
            ['organisation_id' => 4, 'activity_id' => 1, 'creator_id' => 1, ],
            ['organisation_id' => 5, 'activity_id' => 1, 'creator_id' => 1, ],

            // Finances - main finance department and sub-departments
            ['organisation_id' => 2, 'activity_id' => 2, 'creator_id' => 2, ],
            ['organisation_id' => 6, 'activity_id' => 2, 'creator_id' => 2, ],
            ['organisation_id' => 7, 'activity_id' => 2, 'creator_id' => 2, ],
            ['organisation_id' => 6, 'activity_id' => 10, 'creator_id' => 2, ],
            ['organisation_id' => 7, 'activity_id' => 9, 'creator_id' => 2, ],

            // RH - main HR department and sub-departments
            ['organisation_id' => 3, 'activity_id' => 3, 'creator_id' => 3, ],
            ['organisation_id' => 8, 'activity_id' => 3, 'creator_id' => 3, ],
            ['organisation_id' => 9, 'activity_id' => 3, 'creator_id' => 3, ],
            ['organisation_id' => 8, 'activity_id' => 11, 'creator_id' => 3, ],
            ['organisation_id' => 9, 'activity_id' => 12, 'creator_id' => 3, ],

            // Technical - main technical department and sub-departments
            ['organisation_id' => 4, 'activity_id' => 4, 'creator_id' => 4, ],
            ['organisation_id' => 10, 'activity_id' => 4, 'creator_id' => 4, ],
            ['organisation_id' => 11, 'activity_id' => 4, 'creator_id' => 4, ],
            ['organisation_id' => 10, 'activity_id' => 13, 'creator_id' => 4, ],
            ['organisation_id' => 11, 'activity_id' => 14, 'creator_id' => 4, ],

            // Archives - archive activities
            ['organisation_id' => 5, 'activity_id' => 6, 'creator_id' => 5, ],
            ['organisation_id' => 12, 'activity_id' => 17, 'creator_id' => 5, ],
            ['organisation_id' => 13, 'activity_id' => 18, 'creator_id' => 5, ],

            // Legal - across multiple departments
            ['organisation_id' => 1, 'activity_id' => 5, 'creator_id' => 1, ],
            ['organisation_id' => 2, 'activity_id' => 15, 'creator_id' => 2, ],
            ['organisation_id' => 3, 'activity_id' => 15, 'creator_id' => 3, ],
            ['organisation_id' => 4, 'activity_id' => 15, 'creator_id' => 4, ],
            ['organisation_id' => 1, 'activity_id' => 16, 'creator_id' => 1, ],
        ];

        // Insert the data
        DB::table('communicabilities')->insert($communicabilities);
        DB::table('sorts')->insert($sorts);
        DB::table('retentions')->insert($retentions);
        DB::table('activities')->insert($activities);
        DB::table('organisation_activity')->insert($organisationActivities);

        // Setup retention-activity relationships
        $retentionActivities = [
            // Direction activities
            ['retention_id' => 5, 'activity_id' => 1, ],
            ['retention_id' => 5, 'activity_id' => 7, ],
            ['retention_id' => 5, 'activity_id' => 8, ],

            // Finance activities
            ['retention_id' => 3, 'activity_id' => 2, ],
            ['retention_id' => 3, 'activity_id' => 9, ],
            ['retention_id' => 4, 'activity_id' => 10, ],

            // HR activities
            ['retention_id' => 6, 'activity_id' => 3, ],
            ['retention_id' => 6, 'activity_id' => 11, ],
            ['retention_id' => 2, 'activity_id' => 12, ],

            // Technical activities
            ['retention_id' => 5, 'activity_id' => 4, ],
            ['retention_id' => 5, 'activity_id' => 13, ],
            ['retention_id' => 3, 'activity_id' => 14, ],

            // Legal activities
            ['retention_id' => 4, 'activity_id' => 5, ],
            ['retention_id' => 4, 'activity_id' => 15, ],
            ['retention_id' => 5, 'activity_id' => 16, ],

            // Project activities
            ['retention_id' => 3, 'activity_id' => 6, ],
            ['retention_id' => 3, 'activity_id' => 17, ],
            ['retention_id' => 5, 'activity_id' => 18, ],
        ];

        DB::table('retention_activity')->insert($retentionActivities);
    }
}
