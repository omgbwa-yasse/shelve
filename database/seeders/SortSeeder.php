<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SortSeeder extends Seeder
{
    /**
     * Remplit la table sorts avec les sorts finaux de base pour la gestion documentaire.
     * E = Élimination, T = Tri, C = Conservation
     *
     * @return void
     */
    public function run(): void
    {
        $now = Carbon::now();

        $sorts = [
            [
                'code' => 'E',
                'name' => 'Élimination',
                'description' => 'Documents destinés à être éliminés après expiration de la durée de rétention',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'code' => 'T',
                'name' => 'Tri',
                'description' => 'Documents nécessitant un tri pour déterminer ceux à conserver définitivement',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'code' => 'C',
                'name' => 'Conservation',
                'description' => 'Documents à conserver définitivement pour leur valeur historique ou administrative',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];

        // Vérifier si des sorts existent déjà pour éviter les doublons
        if (DB::table('sorts')->count() === 0) {
            DB::table('sorts')->insert($sorts);
            $this->command->info('Sorts finaux créés avec succès: E (Élimination), T (Tri), C (Conservation)');
        } else {
            $this->command->info('Des sorts existent déjà dans la base de données. Insertion ignorée.');
        }
    }
}
