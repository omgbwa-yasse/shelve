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
            CommunicationsPermissionsSeeder::class, // Ajout des permissions manquantes pour le module Communications

            ActivitySeeder::class,
            AuthorSeeder::class,

            CommonDataSeeder::class,

            CommunicationSeeder::class,
            BulletinBoardSeeder::class,

            SuperAdminSeeder::class, // Seeder pour créer le superadmin avec toutes les permissions
        ]);
    }
}
