<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Organisation;
use Illuminate\Support\Facades\DB;

class ToolOrganisationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Organisation::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Création de la Direction Générale
        $directionGenerale = Organisation::create([
            'code' => 'DG',
            'name' => 'Direction Générale'
        ]);

        // Création des Directions avec codes
        $directions = [
            'Direction des Finances' => [
                'code' => 'DF',
                'services' => [
                    'Service de la Comptabilité' => 'DF-COMPT',
                    'Service du Budget' => 'DF-BUDG',
                ]
            ],
            'Direction des Ressources Humaines' => [
                'code' => 'DRH',
                'services' => [
                    'Service de la Paie' => 'DRH-PAIE',
                    'Service du Recrutement' => 'DRH-RECRU',
                    'Service de la Formation' => 'DRH-FORM',
                ]
            ],
            'Direction des Communications' => [
                'code' => 'DCOM',
                'services' => [
                    'Service de la Communication Interne' => 'DCOM-INT',
                    'Service des Relations Publiques' => 'DCOM-PUB',
                ]
            ],
            'Direction des Projets' => [
                'code' => 'DP',
                'services' => [
                    'Service de la Planification' => 'DP-PLAN',
                    'Service du Suivi-Évaluation' => 'DP-SUIVI',
                ]
            ],
            'Direction des Systèmes d\'Information' => [
                'code' => 'DSI',
                'services' => [
                    'Service du Développement' => 'DSI-DEV',
                    'Service de l\'Infrastructure et Réseaux' => 'DSI-INFRA',
                ]
            ],
        ];

        foreach ($directions as $dirName => $dirData) {
            $direction = Organisation::create([
                'code' => $dirData['code'],
                'name' => $dirName,
                'parent_id' => $directionGenerale->id,
            ]);

            foreach ($dirData['services'] as $serviceName => $serviceCode) {
                Organisation::create([
                    'code' => $serviceCode,
                    'name' => $serviceName,
                    'parent_id' => $direction->id,
                ]);
            }
        }

        $this->command->info('Organisation structure seeded successfully!');
    }
}
