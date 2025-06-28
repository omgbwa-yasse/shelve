<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Organisation;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SuperadminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Utilise Spatie Laravel Permission pour créer le superadmin
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $this->command->info('🚀 Création du compte superadmin avec Spatie Permission...');

        // 1. Créer l'organisation "Direction générale" si elle n'existe pas
        $directionGenerale = Organisation::firstOrCreate(
            ['code' => 'DIR-GEN'],
            [
                'name' => 'Direction générale',
                'description' => 'Direction générale de l\'organisation',
                'parent_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->command->info('✅ Organisation "Direction générale" créée ou trouvée (ID: ' . $directionGenerale->id . ')');

        // 2. S'assurer que les permissions existent (exécuter PermissionSeeder)
        $this->command->info('📋 Vérification des permissions...');
        $this->call(PermissionSeeder::class);

        // 2.1. Créer les nouvelles permissions pour les policies modernisées
        $this->createModernPermissions();

        // 3. Migrer les permissions existantes vers Spatie si nécessaire
        $this->migratePermissionsToSpatie();

        // 4. Créer le rôle "superadmin" avec Spatie
        $superadminRole = Role::firstOrCreate(
            ['name' => 'superadmin'],
            ['guard_name' => 'web']
        );

        $this->command->info('✅ Rôle "superadmin" créé ou trouvé (ID: ' . $superadminRole->id . ')');

        // 5. Attribuer toutes les permissions au rôle
        $allPermissions = Permission::all();
        $superadminRole->syncPermissions($allPermissions);

        $this->command->info('✅ Toutes les permissions (' . $allPermissions->count() . ') attribuées au rôle superadmin');

        // 6. Créer l'utilisateur superadmin
        $superadminUser = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super',
                'surname' => 'Admin',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('superadmin'),
                'birthday' => Carbon::parse('1990-01-01'),
                'current_organisation_id' => $directionGenerale->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->command->info('✅ Utilisateur superadmin créé ou trouvé (ID: ' . $superadminUser->id . ')');

        // 7. Attribuer le rôle à l'utilisateur avec Spatie
        $superadminUser->assignRole('superadmin');

        $this->command->info('✅ Rôle superadmin attribué à l\'utilisateur');

        // 8. Afficher un résumé
        $this->displaySummary($superadminUser, $directionGenerale, $allPermissions->count());
    }

    /**
     * Migrer les permissions de l'ancien système vers Spatie
     */
    private function migratePermissionsToSpatie()
    {
        // Récupérer les permissions de l'ancien système
        $oldPermissions = DB::table('permissions')->get();

        if ($oldPermissions->isEmpty()) {
            $this->command->warn('Aucune permission trouvée dans l\'ancien système');
            return;
        }

        // Mettre à jour les permissions existantes pour ajouter guard_name si manquant
        DB::table('permissions')
            ->whereNull('guard_name')
            ->orWhere('guard_name', '')
            ->update(['guard_name' => 'web']);

        $this->command->info('✅ Guard_name ajouté aux permissions existantes');

        $migratedCount = 0;
        foreach ($oldPermissions as $oldPermission) {
            $spatiePermission = Permission::firstOrCreate(
                ['name' => $oldPermission->name, 'guard_name' => 'web'],
                [
                    'guard_name' => 'web',
                ]
            );

            if ($spatiePermission->wasRecentlyCreated) {
                $migratedCount++;
            }
        }

        $this->command->info("✅ Permissions migrées: {$migratedCount} nouvelles, " . ($oldPermissions->count() - $migratedCount) . " existantes");
    }

    /**
     * Créer les nouvelles permissions utilisées dans les policies modernisées
     */
    private function createModernPermissions()
    {
        $modernPermissions = [
            // Permissions utilisateurs (remplace user_*)
            ['name' => 'users.view', 'description' => 'Voir les utilisateurs'],
            ['name' => 'users.create', 'description' => 'Créer des utilisateurs'],
            ['name' => 'users.update', 'description' => 'Modifier les utilisateurs'],
            ['name' => 'users.delete', 'description' => 'Supprimer les utilisateurs'],
            ['name' => 'users.force_delete', 'description' => 'Supprimer définitivement les utilisateurs'],

            // Permissions organisations (remplace organisation_*)
            ['name' => 'organisations.view', 'description' => 'Voir les organisations'],
            ['name' => 'organisations.create', 'description' => 'Créer des organisations'],
            ['name' => 'organisations.update', 'description' => 'Modifier les organisations'],
            ['name' => 'organisations.delete', 'description' => 'Supprimer les organisations'],
            ['name' => 'organisations.force_delete', 'description' => 'Supprimer définitivement les organisations'],

            // Permissions records (remplace record_*)
            ['name' => 'records.view', 'description' => 'Voir les records'],
            ['name' => 'records.create', 'description' => 'Créer des records'],
            ['name' => 'records.update', 'description' => 'Modifier les records'],
            ['name' => 'records.delete', 'description' => 'Supprimer les records'],
            ['name' => 'records.force_delete', 'description' => 'Supprimer définitivement les records'],
            ['name' => 'records.archive', 'description' => 'Archiver les records'],

            // Permissions système
            ['name' => 'system.settings', 'description' => 'Accéder aux paramètres système'],
            ['name' => 'system.logs', 'description' => 'Voir les logs système'],
            ['name' => 'system.maintenance', 'description' => 'Effectuer la maintenance système'],

            // Permissions d'accès aux modules
            ['name' => 'access.users', 'description' => 'Accéder au module utilisateurs'],
            ['name' => 'access.organisations', 'description' => 'Accéder au module organisations'],
            ['name' => 'access.records', 'description' => 'Accéder au module records'],
            ['name' => 'access.admin', 'description' => 'Accéder à l\'administration'],

            // Permissions rôles
            ['name' => 'roles.view', 'description' => 'Voir les rôles'],
            ['name' => 'roles.create', 'description' => 'Créer des rôles'],
            ['name' => 'roles.update', 'description' => 'Modifier les rôles'],
            ['name' => 'roles.delete', 'description' => 'Supprimer les rôles'],
        ];

        $createdCount = 0;
        foreach ($modernPermissions as $permissionData) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                [
                    'guard_name' => 'web',
                    'description' => $permissionData['description']
                ]
            );

            if ($permission->wasRecentlyCreated) {
                $createdCount++;
            }
        }

        $this->command->info("✅ Permissions modernes: {$createdCount} créées, " . (count($modernPermissions) - $createdCount) . " existantes");
    }

    /**
     * Afficher le résumé de création
     */
    private function displaySummary($user, $organisation, $permissionCount)
    {
        $this->command->line('');
        $this->command->line('=== RÉSUMÉ DE LA CRÉATION DU COMPTE SUPERADMIN (SPATIE) ===');
        $this->command->line('ID Utilisateur: ' . $user->id);
        $this->command->line('Email: ' . $user->email);
        $this->command->line('Mot de passe: superadmin');
        $this->command->line('Nom: ' . $user->name . ' ' . $user->surname);
        $this->command->line('Organisation: ' . $organisation->name);
        $this->command->line('Rôle: superadmin (Spatie)');
        $this->command->line('Permissions: ' . $permissionCount . ' permissions attribuées');
        $this->command->line('');
        $this->command->info('✅ Le superadmin peut maintenant utiliser toutes les méthodes Spatie :');
        $this->command->line('   - $user->hasRole("superadmin")');
        $this->command->line('   - $user->hasPermissionTo("permission_name")');
        $this->command->line('   - $user->can("permission_name")');
        $this->command->line('===============================================================');
    }
}
