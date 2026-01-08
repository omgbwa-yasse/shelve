<?php

namespace Database\Seeders\Records\Configuration;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RecordStatusSeeder extends Seeder
{
    /**
     * Remplit la table record_statuses avec les statuts de base pour les dossiers.
     *
     * @return void
     */
    public function run(): void
    {
        $now = Carbon::now();

        $statuses = [
            [
                'name' => 'Brouillon',
                'description' => 'Document en cours de rédaction, non publié',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Validation',
                'description' => 'Document en attente de validation',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Publié',
                'description' => 'Document validé et publié',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Archivé',
                'description' => 'Document archivé, visible mais non modifiable',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'Supprimé',
                'description' => 'Document supprimé logiquement',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];

        DB::table('record_statuses')->insertOrIgnore($statuses);
    }
}


