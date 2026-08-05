<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateMcpPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $now = Carbon::now();

        // 1. Suppression de la permission dupliquée
        DB::table('permissions')
            ->where('name', 'records_delete')
            ->where('category', '!=', 'records')
            ->delete();

        // 2. Ajout des nouvelles permissions MCP
        $newPermissions = [
            [
                'name' => 'records_import',
                'category' => 'records',
                'description' => 'Importer des dossiers',
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

        // 3. Attribuer ces permissions au rôle superadmin
        $superadminRoles = DB::table('roles')
            ->where('name', 'superadmin')
            ->get();

        foreach ($superadminRoles as $role) {
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Supprimer les permissions MCP
        DB::table('permissions')
            ->whereIn('name', [
                'records_import',
                'records_search',
                'records_lifecycle',
                'authors_view',
                'authors_create',
                'mcp_features',
            ])
            ->delete();

        // Les relations dans permission_role seront supprimées automatiquement si vous avez défini des clés étrangères avec cascade
    }
}
