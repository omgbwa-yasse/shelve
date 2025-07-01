<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */    public function up(): void
    {
        // Vérifier si la permission existe déjà
        $permissionExists = DB::table('permissions')->where('name', 'ai_configure')->exists();

        if (!$permissionExists) {
            // Trouver le prochain ID disponible
            $maxId = DB::table('permissions')->max('id');
            $nextId = $maxId + 1;

            // Ajouter la permission ai_configure
            DB::table('permissions')->insert([
                'id' => $nextId,
                'name' => 'ai_configure',
                'description' => 'Autorisation de configurer les paramètres du système d\'intelligence artificielle',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Attribuer cette permission au rôle superadmin
            $superadminRole = DB::table('roles')->where('name', 'superadmin')->first();
            if ($superadminRole) {
                // Vérifier si la relation existe déjà
                $relationExists = DB::table('role_has_permissions')
                    ->where('permission_id', $nextId)
                    ->where('role_id', $superadminRole->id)
                    ->exists();

                if (!$relationExists) {
                    DB::table('role_has_permissions')->insert([
                        'permission_id' => $nextId,
                        'role_id' => $superadminRole->id,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Récupérer l'ID de la permission ai_configure
        $permission = DB::table('permissions')->where('name', 'ai_configure')->first();

        if ($permission) {
            // Supprimer toutes les associations de cette permission
            DB::table('role_has_permissions')
                ->where('permission_id', $permission->id)
                ->delete();

            // Supprimer la permission elle-même
            DB::table('permissions')->where('id', $permission->id)->delete();
        }
    }
};
