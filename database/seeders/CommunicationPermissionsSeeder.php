<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\Permission;

class CommunicationsPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();
        
        // Définition des permissions manquantes pour le module communications
        $permissions = [
            [
                'name' => 'communication_config',
                'description' => 'Autorisation de configurer le module communications',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'communication_export',
                'description' => 'Autorisation d\'exporter des données de communications',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'reservation_config',
                'description' => 'Autorisation de configurer les réservations',
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'name' => 'reservation_export',
                'description' => 'Autorisation d\'exporter des réservations',
                'created_at' => $now,
                'updated_at' => $now
            ],
        ];

        // Utiliser updateOrInsert pour chaque permission
        foreach ($permissions as $permission) {
            $existingPermission = DB::table('permissions')
                ->where('name', $permission['name'])
                ->first();

            if (!$existingPermission) {
                // Si la permission n'existe pas, l'insérer
                DB::table('permissions')->insert($permission);
                $this->command->info('✅ Permission ' . $permission['name'] . ' créée');
            } else {
                // Sinon, mettre à jour la description
                DB::table('permissions')
                    ->where('name', $permission['name'])
                    ->update([
                        'description' => $permission['description'],
                        'updated_at' => $now
                    ]);
                $this->command->info('🔄 Permission ' . $permission['name'] . ' mise à jour');
            }
        }

        // Récupérer le rôle superadmin
        $superadminRole = Role::where('name', 'superadmin')->first();

        if ($superadminRole) {
            // Attribuer toutes les nouvelles permissions au superadmin
            $createdPermissions = Permission::whereIn('name', array_column($permissions, 'name'))->get();
            
            foreach ($createdPermissions as $permission) {
                $superadminRole->givePermissionTo($permission);
            }
            
            $this->command->info('✅ Permissions de communication attribuées au rôle superadmin');
        } else {
            $this->command->error('❌ Rôle superadmin non trouvé');
        }
    }
}
