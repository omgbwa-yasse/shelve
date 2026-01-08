<?php

namespace Database\Seeders\Settings;

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
        // Trouver le rÃ´le super admin
        $superAdminRole = DB::table('roles')->where('name', 'super_admin')->first();

        if (!$superAdminRole) {
            $this->command->error('Le rÃ´le super_admin n\'existe pas. Veuillez d\'abord exÃ©cuter les seeders de rÃ´les.');
            return;
        }

        // RÃ©cupÃ©rer toutes les permissions du module public
        $publicPermissions = DB::table('permissions')
            ->where('category', 'public')
            ->orWhere('name', 'like', 'public.%')
            ->get();

        if ($publicPermissions->isEmpty()) {
            $this->command->error('Aucune permission publique trouvÃ©e. Veuillez d\'abord exÃ©cuter le PermissionCategorySeeder.');
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

        $this->command->info('Toutes les permissions du module public ont Ã©tÃ© attribuÃ©es au rÃ´le super_admin.');
        $this->command->info("Total des permissions attribuÃ©es : {$publicPermissions->count()}");

        // Afficher la liste des permissions attribuÃ©es
        $this->command->info('Permissions attribuÃ©es :');
        foreach ($publicPermissions as $permission) {
            $this->command->info("  - {$permission->name}: {$permission->description}");
        }
    }
}

