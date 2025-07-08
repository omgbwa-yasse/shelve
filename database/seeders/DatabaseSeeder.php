<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionCategorySeeder::class, // Seeder avec catégories et nomenclature unifiée
            // CommunicationsPermissionsSeeder::class, // Seeder manquant - commenté temporairement
            // ActivitySeeder::class, // Seeder manquant - commenté temporairement  
            // AuthorSeeder::class, // Seeder manquant - commenté temporairement
            // CommonDataSeeder::class, // Seeder manquant - commenté temporairement
            // CommunicationSeeder::class, // Seeder manquant - commenté temporairement
            // BulletinBoardSeeder::class, // Seeder manquant - commenté temporairement
            SuperAdminSeeder::class, // Seeder pour créer le superadmin avec toutes les permissions
            ThesaurusSeeder::class, // Seeder pour les données de test du thésaurus
        ]);
    }
}
