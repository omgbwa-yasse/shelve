<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrganisationSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Main organizations
        $organisations = [
            [
                'id' => 1,
                'code' => 'MAIN-ADM',
                'name' => 'Administration Centrale',
                'description' => 'Organisation principale de gestion des archives',
                'parent_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 2,
                'code' => 'DEP-FIN',
                'name' => 'Département des Finances',
                'description' => 'Gestion des documents financiers',
                'parent_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 3,
                'code' => 'DEP-RH',
                'name' => 'Département des Ressources Humaines',
                'description' => 'Gestion des documents du personnel',
                'parent_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 4,
                'code' => 'DEP-TECH',
                'name' => 'Département Technique',
                'description' => 'Documents techniques et plans',
                'parent_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 5,
                'code' => 'DIV-ARCH',
                'name' => 'Division des Archives',
                'description' => 'Conservation et gestion des archives historiques',
                'parent_id' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Sub-organizations
        $subOrganisations = [
            [
                'id' => 6,
                'code' => 'FIN-COMPT',
                'name' => 'Service Comptabilité',
                'description' => 'Gestion de la comptabilité',
                'parent_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 7,
                'code' => 'FIN-BUDG',
                'name' => 'Service Budget',
                'description' => 'Gestion du budget',
                'parent_id' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 8,
                'code' => 'RH-RECR',
                'name' => 'Service Recrutement',
                'description' => 'Recrutement du personnel',
                'parent_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 9,
                'code' => 'RH-FORM',
                'name' => 'Service Formation',
                'description' => 'Formation du personnel',
                'parent_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 10,
                'code' => 'TECH-INFO',
                'name' => 'Service Informatique',
                'description' => 'Gestion des systèmes informatiques',
                'parent_id' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 11,
                'code' => 'TECH-MAIN',
                'name' => 'Service Maintenance',
                'description' => 'Maintenance des équipements',
                'parent_id' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 12,
                'code' => 'ARCH-HIST',
                'name' => 'Service Archives Historiques',
                'description' => 'Conservation des documents historiques',
                'parent_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => 13,
                'code' => 'ARCH-NUM',
                'name' => 'Service Numérisation',
                'description' => 'Numérisation des archives',
                'parent_id' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('organisations')->insert($organisations);
        DB::table('organisations')->insert($subOrganisations);
    }
}
