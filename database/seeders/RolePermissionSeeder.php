<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Permissions
        $permissions = [
            // User management
            ['id' => 1, 'name' => 'user-view', 'description' => 'Voir les utilisateurs', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'user-create', 'description' => 'Créer des utilisateurs', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'user-edit', 'description' => 'Modifier des utilisateurs', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'user-delete', 'description' => 'Supprimer des utilisateurs', 'created_at' => $now, 'updated_at' => $now],

            // Organisation management
            ['id' => 5, 'name' => 'organisation-view', 'description' => 'Voir les organisations', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'name' => 'organisation-create', 'description' => 'Créer des organisations', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'name' => 'organisation-edit', 'description' => 'Modifier des organisations', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'name' => 'organisation-delete', 'description' => 'Supprimer des organisations', 'created_at' => $now, 'updated_at' => $now],

            // Record management
            ['id' => 9, 'name' => 'record-view', 'description' => 'Voir les documents', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 10, 'name' => 'record-create', 'description' => 'Créer des documents', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 11, 'name' => 'record-edit', 'description' => 'Modifier des documents', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 12, 'name' => 'record-delete', 'description' => 'Supprimer des documents', 'created_at' => $now, 'updated_at' => $now],

            // Mail management
            ['id' => 13, 'name' => 'mail-view', 'description' => 'Voir les courriers', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 14, 'name' => 'mail-create', 'description' => 'Créer des courriers', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 15, 'name' => 'mail-edit', 'description' => 'Modifier des courriers', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 16, 'name' => 'mail-delete', 'description' => 'Supprimer des courriers', 'created_at' => $now, 'updated_at' => $now],

            // Communication management
            ['id' => 17, 'name' => 'communication-view', 'description' => 'Voir les communications', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 18, 'name' => 'communication-create', 'description' => 'Créer des communications', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 19, 'name' => 'communication-edit', 'description' => 'Modifier des communications', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 20, 'name' => 'communication-delete', 'description' => 'Supprimer des communications', 'created_at' => $now, 'updated_at' => $now],

            // Location management
            ['id' => 21, 'name' => 'location-view', 'description' => 'Voir les localisations', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 22, 'name' => 'location-create', 'description' => 'Créer des localisations', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 23, 'name' => 'location-edit', 'description' => 'Modifier des localisations', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 24, 'name' => 'location-delete', 'description' => 'Supprimer des localisations', 'created_at' => $now, 'updated_at' => $now],

            // Settings management
            ['id' => 25, 'name' => 'settings-view', 'description' => 'Voir les paramètres', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 26, 'name' => 'settings-edit', 'description' => 'Modifier les paramètres', 'created_at' => $now, 'updated_at' => $now],

            // Bulletin board management
            ['id' => 27, 'name' => 'bulletin-view', 'description' => 'Voir les tableaux d\'affichage', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 28, 'name' => 'bulletin-create', 'description' => 'Créer des tableaux d\'affichage', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 29, 'name' => 'bulletin-edit', 'description' => 'Modifier des tableaux d\'affichage', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 30, 'name' => 'bulletin-delete', 'description' => 'Supprimer des tableaux d\'affichage', 'created_at' => $now, 'updated_at' => $now],

            // Public portal management
            ['id' => 31, 'name' => 'portal-manage', 'description' => 'Gérer le portail public', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 32, 'name' => 'portal-view-requests', 'description' => 'Voir les demandes du portail', 'created_at' => $now, 'updated_at' => $now],
        ];

        // Roles
        $roles = [
            ['id' => 1, 'name' => 'Super Admin', 'description' => 'Administrateur global du système', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Admin', 'description' => 'Administrateur d\'une organisation', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Archiviste', 'description' => 'Gestionnaire des archives', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'Producteur', 'description' => 'Producteur de documents', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'Consulteur', 'description' => 'Utilisateur avec accès en lecture seule', 'created_at' => $now, 'updated_at' => $now],
        ];

        // Role permissions
        $rolePermissions = [];

        // Super Admin - All permissions
        for ($i = 1; $i <= 32; $i++) {
            $rolePermissions[] = [
                'role_id' => 1,
                'permission_id' => $i,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        // Admin - Most permissions except super admin features
        for ($i = 1; $i <= 32; $i++) {
            if (!in_array($i, [6, 8, 26])) { // Exclude create/delete orgs and edit system settings
                $rolePermissions[] = [
                    'role_id' => 2,
                    'permission_id' => $i,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
        }

        // Archiviste - Archive management permissions
        $archivistePermissions = [1, 5, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 27, 32];
        foreach ($archivistePermissions as $permId) {
            $rolePermissions[] = [
                'role_id' => 3,
                'permission_id' => $permId,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        // Producteur - Basic document creation permissions
        $producteurPermissions = [1, 5, 9, 10, 11, 13, 14, 15, 17, 18, 21, 27];
        foreach ($producteurPermissions as $permId) {
            $rolePermissions[] = [
                'role_id' => 4,
                'permission_id' => $permId,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        // Consulteur - View-only permissions
        $consulteurPermissions = [1, 5, 9, 13, 17, 21, 27];
        foreach ($consulteurPermissions as $permId) {
            $rolePermissions[] = [
                'role_id' => 5,
                'permission_id' => $permId,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        DB::table('permissions')->insert($permissions);
        DB::table('roles')->insert($roles);
        DB::table('role_permissions')->insert($rolePermissions);
    }
}
