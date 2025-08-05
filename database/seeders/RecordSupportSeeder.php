<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecordSupportSeeder extends Seeder
{
    /**
     * Remplit la table record_supports avec les supports physiques pour les documents d'archives.
     *
     * @return void
     */
    public function run(): void
    {
        $now = Carbon::now();

        $supports = [
            [
                'name' => 'Papier',
                'description' => 'Document sur support papier classique',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Parchemin',
                'description' => 'Document sur parchemin (peau animale préparée)',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Microfilm',
                'description' => 'Document reproduit sur microfilm',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Photographie',
                'description' => 'Support photographique (négatif, diapositive, tirage)',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Bande magnétique',
                'description' => 'Enregistrement sur bande magnétique (audio ou vidéo)',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Disque optique',
                'description' => 'Support de type CD, DVD, Blu-ray',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Disquette',
                'description' => 'Support de stockage informatique obsolète',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Numérique',
                'description' => 'Document né-numérique sans support physique spécifique',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Carte et plan',
                'description' => 'Document cartographique',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Objet',
                'description' => 'Objet physique conservé comme archive',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];

        DB::table('record_supports')->insertOrIgnore($supports);
    }
}
