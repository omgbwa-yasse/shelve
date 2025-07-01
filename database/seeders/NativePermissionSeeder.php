<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NativePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Appel du seeder de permissions avec catégories
        $this->call(PermissionCategorySeeder::class);

        $this->command->info('✅ Permissions natives créées avec succès via PermissionCategorySeeder');
    }
}
