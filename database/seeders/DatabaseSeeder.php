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
            PermissionSeeder::class, // Seeder de permissions complet et sécurisé (222 permissions)
            RolePermissionSeeder::class,
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
        ]);
    }
}
