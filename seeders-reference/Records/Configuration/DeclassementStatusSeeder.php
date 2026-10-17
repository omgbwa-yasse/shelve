<?php

namespace Database\Seeders\Records\Configuration;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeclassementStatusSeeder extends Seeder
{
    /**
     * Remplit la table declassement_statuses avec les étapes du circuit d'approbation.
     *
     * @return void
     */
    public function run(): void
    {
        $now = Carbon::now();

        $statuses = [
            [
                'name' => 'Brouillon',
                'description' => 'Liste de déclassement en cours de constitution',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => "Demande d'approbation soumise",
                'description' => "La liste a été soumise pour approbation",
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Approuvé',
                'description' => 'La liste a été approuvée',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Validé',
                'description' => 'La liste a été validée',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Traité',
                'description' => "La liste a été traitée : les dossiers/documents ont été éliminés",
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Rejeté',
                'description' => 'La liste a été rejetée et renvoyée pour correction',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('declassement_statuses')->insertOrIgnore($statuses);
    }
}
