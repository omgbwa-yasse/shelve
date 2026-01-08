<?php

namespace Database\Seeders\Contacts;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AddressTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Types d'adresses basÃ©s sur les normes ISAD(G) et ISAAR(CPF)
     */
    public function run(): void
    {
        // VÃ©rifier si la table address_types existe
        if (Schema::hasTable('address_types')) {
            $addressTypes = [
                [
                    'name' => 'Adresse lÃ©gale',
                    'description' => 'Adresse officielle ou siÃ¨ge social d\'une entitÃ©.',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'Adresse postale',
                    'description' => 'Adresse utilisÃ©e pour la correspondance.',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'Adresse physique',
                    'description' => 'Emplacement physique oÃ¹ l\'entitÃ© exerce ses activitÃ©s.',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'Domicile',
                    'description' => 'Adresse de rÃ©sidence pour une personne physique.',
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
                    'description' => 'Type d\'adresse non spÃ©cifiÃ© dans les catÃ©gories standard.',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ];

            DB::table('address_types')->insertOrIgnore($addressTypes);
        }
    }
}

