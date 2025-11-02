<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuperAdminPublicPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Trouver le rôle super admin
        $superAdminRole = DB::table('roles')->where('name', 'super_admin')->first();

        if (!$superAdminRole) {
            $this->command->error('Le rôle super_admin n\'existe pas. Veuillez d\'abord exécuter les seeders de rôles.');
            return;
        }

        // Récupérer toutes les permissions du module public
        $publicPermissions = DB::table('permissions')
            ->where('category', 'public')
            ->orWhere('name', 'like', 'public.%')
            ->get();

        if ($publicPermissions->isEmpty()) {
            $this->command->error('Aucune permission publique trouvée. Veuillez d\'abord exécuter le PermissionCategorySeeder.');
            return;
        }

        // Attribuer toutes les permissions publiques au super admin
        foreach ($publicPermissions as $permission) {
            DB::table('permission_role')->updateOrInsert(
                [
                    'permission_id' => $permission->id,
                    'role_id' => $superAdminRole->id
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        $this->command->info('Toutes les permissions du module public ont été attribuées au rôle super_admin.');
        $this->command->info("Total des permissions attribuées : {$publicPermissions->count()}");

        // Afficher la liste des permissions attribuées
        $this->command->info('Permissions attribuées :');
        foreach ($publicPermissions as $permission) {
            $this->command->info("  - {$permission->name}: {$permission->description}");
        }
    }
}
