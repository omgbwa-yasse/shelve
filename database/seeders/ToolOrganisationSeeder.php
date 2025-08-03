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
        $directionGenerale = Organisation::create(['name' => 'Direction Générale']);

        // Création des Directions
        $directions = [
            'Direction des Finances' => [
                'Service de la Comptabilité',
                'Service du Budget',
            ],
            'Direction des Ressources Humaines' => [
                'Service de la Paie',
                'Service du Recrutement',
                'Service de la Formation',
            ],
            'Direction des Communications' => [
                'Service de la Communication Interne',
                'Service des Relations Publiques',
            ],
            'Direction des Projets' => [
                'Service de la Planification',
                'Service du Suivi-Évaluation',
            ],
            'Direction des Systèmes d\'Information' => [
                'Service du Développement',
                'Service de l\'Infrastructure et Réseaux',
            ],
        ];

        foreach ($directions as $dirName => $services) {
            $direction = Organisation::create([
                'name' => $dirName,
                'parent_id' => $directionGenerale->id,
            ]);

            foreach ($services as $serviceName) {
                Organisation::create([
                    'name' => $serviceName,
                    'parent_id' => $direction->id,
                ]);
            }
        }

        $this->command->info('Organisation structure seeded successfully!');
    }
}
