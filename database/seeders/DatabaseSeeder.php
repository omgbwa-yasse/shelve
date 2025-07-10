<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([


            PermissionCategorySeeder::class, // Seeder avec catégories et nomenclature unifiée
             // Mise à jour des permissions du module Records/Repositories

            SuperAdminSeeder::class, // Seeder pour créer le superadmin avec toutes les permissions

            ExternalContactsSeeder::class, // Seeder pour les contacts externes

            MailSystemSeeder::class, // Seeder pour le système de courriers



        ]);
    }
}
