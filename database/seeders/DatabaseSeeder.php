<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SettingSeeder::class,
            OrganisationSeeder::class,
            PermissionCategorySeeder::class, // Seeder avec catégories et nomenclature unifiée
            UserSeeder::class,
            ActivitySeeder::class,
            AuthorSeeder::class,
            LocationSeeder::class,
            ContainerSeeder::class,
            CommonDataSeeder::class,
            TermSeeder::class,
            RecordSeeder::class,
            MailSeeder::class,
            CommunicationSeeder::class,
            BulletinBoardSeeder::class,
            PublicPortalSeeder::class,
            SuperAdminSeeder::class, // Seeder pour créer le superadmin avec toutes les permissions
        ]);
    }
}
