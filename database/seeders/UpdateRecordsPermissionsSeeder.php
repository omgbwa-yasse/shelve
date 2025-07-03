<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateRecordsPermissionsSeeder extends Seeder
{
    /**
     * Exécuter les seeds.
     *
     * Cette seed met à jour les permissions du module Records/Repositories
     * pour refléter la nouvelle structure du menu.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Mise à jour des permissions du module Records/Repositories...');

        $now = Carbon::now();

        // Supprimer les permissions dupliquées
        $this->command->info('Résolution des permissions dupliquées...');
        DB::table('permissions')
            ->where('name', 'records_delete')
            ->where('category', '!=', 'records')
            ->delete();

        $newPermissions = [
            [
                'name' => 'records_import',
                'category' => 'records',
                'description' => 'Importer des dossiers',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'records_export',
                'category' => 'records',
                'description' => 'Exporter des dossiers',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'records_search',
                'category' => 'records',
                'description' => 'Rechercher des dossiers',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'records_lifecycle',
                'category' => 'records',
                'description' => 'Gérer le cycle de vie des dossiers',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'authors_view',
                'category' => 'records',
                'description' => 'Voir les producteurs',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'authors_create',
                'category' => 'records',
                'description' => 'Créer des producteurs',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'mcp_features',
                'category' => 'records',
                'description' => 'Utiliser les fonctionnalités MCP/IA',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Insérer ou mettre à jour les permissions
        foreach ($newPermissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Obtenir tous les ID des rôles superadmin
        $superadminRoles = DB::table('roles')
            ->where('name', 'superadmin')
            ->get();

        // Attribuer les nouvelles permissions au(x) rôle(s) superadmin
        foreach ($superadminRoles as $role) {
            $this->command->info("Attribution des nouvelles permissions au rôle superadmin (ID: {$role->id})...");

            // Obtenir les IDs des nouvelles permissions
            $permissionIds = DB::table('permissions')
                ->whereIn('name', array_column($newPermissions, 'name'))
                ->pluck('id');

            // Pour chaque permission, vérifier si l'association existe déjà
            foreach ($permissionIds as $permissionId) {
                $exists = DB::table('permission_role')
                    ->where('role_id', $role->id)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if (!$exists) {
                    DB::table('permission_role')->insert([
                        'role_id' => $role->id,
                        'permission_id' => $permissionId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        $this->command->info('✅ Mise à jour des permissions du module Records/Repositories terminée!');
    }
}
