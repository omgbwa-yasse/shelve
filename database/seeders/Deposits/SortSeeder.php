<?php

namespace Database\Seeders\Deposits;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SortSeeder extends Seeder
{
    /**
     * Remplit la table sorts avec les sorts finaux de base pour la gestion documentaire.
     * E = Ã‰limination, T = Tri, C = Conservation
     *
     * @return void
     */
    public function run(): void
    {
        $now = Carbon::now();

        $sorts = [
            [
                'code' => 'E',
                'name' => 'Ã‰limination',
                'description' => 'Documents destinÃ©s Ã  Ãªtre Ã©liminÃ©s aprÃ¨s expiration de la durÃ©e de rÃ©tention',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'code' => 'T',
                'name' => 'Tri',
                'description' => 'Documents nÃ©cessitant un tri pour dÃ©terminer ceux Ã  conserver dÃ©finitivement',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'code' => 'C',
                'name' => 'Conservation',
                'description' => 'Documents Ã  conserver dÃ©finitivement pour leur valeur historique ou administrative',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];

        // VÃ©rifier si des sorts existent dÃ©jÃ  pour Ã©viter les doublons
        if (DB::table('sorts')->count() === 0) {
            DB::table('sorts')->insertOrIgnore($sorts);
            $this->command->info('Sorts finaux crÃ©Ã©s avec succÃ¨s: E (Ã‰limination), T (Tri), C (Conservation)');
        } else {
            $this->command->info('Des sorts existent dÃ©jÃ  dans la base de donnÃ©es. Insertion ignorÃ©e.');
        }
    }
}

