<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AddressTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Types d'adresses basés sur les normes ISAD(G) et ISAAR(CPF)
     */
    public function run(): void
    {
        // Vérifier si la table address_types existe
        if (Schema::hasTable('address_types')) {
            $addressTypes = [
                [
                    'name' => 'Adresse légale',
                    'description' => 'Adresse officielle ou siège social d\'une entité.',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'Adresse postale',
                    'description' => 'Adresse utilisée pour la correspondance.',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'Adresse physique',
                    'description' => 'Emplacement physique où l\'entité exerce ses activités.',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'Domicile',
                    'description' => 'Adresse de résidence pour une personne physique.',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'Bureau',
                    'description' => 'Adresse professionnelle ou lieu de travail.',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'Archives',
                    'description' => 'Localisation des archives ou collections.',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'Historique',
                    'description' => 'Ancienne adresse ayant une importance historique.',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'Autre',
                    'description' => 'Type d\'adresse non spécifié dans les catégories standard.',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ];

            DB::table('address_types')->insert($addressTypes);
        }
    }
}
