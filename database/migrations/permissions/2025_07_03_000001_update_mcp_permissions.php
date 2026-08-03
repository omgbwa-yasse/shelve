<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        // 3. Attribuer ces permissions au rôle superadmin.
        // Le pivot du système de permissions natif est `role_permissions`
        // (cf. App\Models\Role::permissions()). L'ancien code visait une table
        // `permission_role` qui n'existe nulle part : la migration plantait dès
        // qu'un rôle superadmin était présent en base.
        if (!Schema::hasTable('roles') || !Schema::hasTable('role_permissions')) {
            return;
        }

        $superadminRoles = DB::table('roles')
            ->where('name', 'superadmin')
            ->get();

        if ($superadminRoles->isEmpty()) {
            return;
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', array_column($newPermissions, 'name'))
            ->pluck('id');

        foreach ($superadminRoles as $role) {
            foreach ($permissionIds as $permissionId) {
                DB::table('role_permissions')->updateOrInsert(
                    ['role_id' => $role->id, 'permission_id' => $permissionId],
                    ['created_at' => $now, 'updated_at' => $now]
                );
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
        $names = [
            'records_import',
            'records_search',
            'records_lifecycle',
            'authors_view',
            'authors_create',
            'mcp_features',
        ];

        $ids = DB::table('permissions')->whereIn('name', $names)->pluck('id');

        if ($ids->isEmpty()) {
            return;
        }

        // On retire explicitement les lignes de pivot : sous SQLite, les contraintes
        // FK ne sont pas toujours actives, donc le CASCADE n'est pas garanti.
        if (Schema::hasTable('role_permissions')) {
            DB::table('role_permissions')->whereIn('permission_id', $ids)->delete();
        }

        DB::table('permissions')->whereIn('id', $ids)->delete();
    }
}
