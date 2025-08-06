<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecordLevelSeeder extends Seeder
{
    /**
     * Remplit la table record_levels avec les niveaux hiérarchiques standards pour les archives.
     * Basé sur la norme ISAD(G) et les pratiques archivistiques courantes.
     *
     * @return void
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Première insertion des niveaux sans relations
        $levels = [
            [
                'id' => 1,
                'name' => 'Fonds',
                'description' => 'Ensemble de documents de toute nature constitué de façon organique par un producteur',
                'child_id' => null,
                'has_child' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 2,
                'name' => 'Sous-fonds',
                'description' => 'Division organique d\'un fonds correspondant aux divisions administratives de l\'institution',
                'child_id' => null,
                'has_child' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 3,
                'name' => 'Série',
                'description' => 'Division d\'un fonds suivant l\'organisation du producteur ou le classement thématique',
                'child_id' => null,
                'has_child' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 4,
                'name' => 'Sous-série',
                'description' => 'Division d\'une série',
                'child_id' => null,
                'has_child' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 5,
                'name' => 'Dossier',
                'description' => 'Ensemble de documents regroupés pour leur utilisation courante ou dans le processus d\'organisation des archives',
                'child_id' => null,
                'has_child' => true,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'id' => 6,
                'name' => 'Pièce',
                'description' => 'Plus petite unité archivistique indivisible (document, lettre, rapport, etc.)',
                'child_id' => null,
                'has_child' => false,
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];

        DB::table('record_levels')->insertOrIgnore($levels);

        // Mise à jour des relations hiérarchiques entre les niveaux
        DB::table('record_levels')->where('id', 1)->update(['child_id' => 2]); // Fonds -> Sous-fonds
        DB::table('record_levels')->where('id', 2)->update(['child_id' => 3]); // Sous-fonds -> Série
        DB::table('record_levels')->where('id', 3)->update(['child_id' => 4]); // Série -> Sous-série
        DB::table('record_levels')->where('id', 4)->update(['child_id' => 5]); // Sous-série -> Dossier
        DB::table('record_levels')->where('id', 5)->update(['child_id' => 6]); // Dossier -> Pièce
    }
}
