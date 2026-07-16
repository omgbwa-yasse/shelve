<?php

namespace Database\Seeders\Settings;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Building;
use App\Models\Floor;
use App\Models\Room;
use App\Models\Shelf;
use App\Models\Container;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Utilise le système natif Laravel pour créer le superadmin
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $this->command->info('🚀 Création du compte superadmin avec le système natif Laravel...');

        // 1. Récupérer les organisations
        $directionGenerale = Organisation::where('code', 'DG')->first();
        $directionSI = Organisation::where('code', 'DSI')->first();
        $directionRH = Organisation::where('code', 'DRH')->first();
        $directionAG = Organisation::where('code', 'DAG')->first();

        if (!$directionGenerale || !$directionSI || !$directionRH || !$directionAG) {
            $this->command->error('Les organisations doivent être créées avant ce seeder');
            return;
        }

        $this->command->info('✅ Organisations trouvées');

        // 2. Vérifier que les permissions existent
        $this->command->info('📋 Vérification des permissions...');
        $permissionCount = Permission::count();
        if ($permissionCount == 0) {
            $this->command->error('Les permissions doivent être créées avant ce seeder (PermissionCategorySeeder)');
            return;
        }
        $this->command->info('✅ ' . $permissionCount . ' permissions trouvées');

        // 3. Créer le rôle "superadmin" avec système natif
        $superadminRole = Role::firstOrCreate(
            ['name' => 'superadmin'],
            [
                'name' => 'superadmin',
                'description' => 'Super administrateur avec tous les droits',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->command->info('✅ Rôle "superadmin" créé ou trouvé (ID: ' . $superadminRole->id . ')');

        // 4. Attribuer TOUTES les permissions au rôle superadmin
        $this->command->info('📋 Attribution de toutes les permissions au rôle superadmin...');

        $allPermissions = Permission::all();
        $permissionIds = $allPermissions->pluck('id')->toArray();

        // Synchroniser les permissions du rôle
        $superadminRole->permissions()->sync($permissionIds);

        // Vérification que toutes les permissions sont bien attribuées
        $assignedPermissions = $superadminRole->permissions()->count();
        if ($assignedPermissions !== $allPermissions->count()) {
            $this->command->error('❌ Erreur: Toutes les permissions ne sont pas attribuées au rôle superadmin');
            $this->command->error('Permissions totales: ' . $allPermissions->count() . ', Permissions attribuées: ' . $assignedPermissions);
            return;
        }

        $this->command->info('✅ Toutes les permissions (' . $allPermissions->count() . ') attribuées au rôle superadmin');

        // Afficher les catégories de permissions attribuées
        $this->displayPermissionCategories($allPermissions);

        // 5. Créer l'utilisateur superadmin principal
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

        // 6. Attribuer le rôle à l'utilisateur avec système natif
        $superadminUser->assignRole('superadmin');

        // Vérification que l'utilisateur a bien le rôle
        if (!$superadminUser->hasRole('superadmin')) {
            $this->command->error('❌ Erreur: Le rôle superadmin n\'a pas été correctement attribué à l\'utilisateur');
            return;
        }

        // Vérification de toutes les permissions de modules
        $modulePermissions = Permission::where('name', 'like', 'module_%_access')->get();
        $missingPermissions = [];

        foreach ($modulePermissions as $permission) {
            if (!$superadminUser->hasPermissionTo($permission->name)) {
                $missingPermissions[] = $permission->name;
            }
        }

        if (!empty($missingPermissions)) {
            $this->command->error('❌ Erreur: Permissions de modules manquantes: ' . implode(', ', $missingPermissions));
        } else {
            $this->command->info('✅ Toutes les permissions de modules vérifiées pour le superadmin (' . $modulePermissions->count() . ' modules)');
        }

        // Vérification de quelques permissions critiques supplémentaires
        $criticalPermissions = [
            'settings_manage',
            'users_manage',
            'records_view',
            'records_create',
            'system_maintenance'
        ];        foreach ($criticalPermissions as $permission) {
            if (!$superadminUser->hasPermissionTo($permission)) {
                $missingPermissions[] = $permission;
            }
        }

        if (empty($missingPermissions)) {
            $this->command->info('✅ Rôle et permissions critiques vérifiés pour le superadmin');
        }

        // 7. Affecter le superadmin à toutes les organisations
        $allOrganisations = [$directionGenerale, $directionSI, $directionRH, $directionAG];
        foreach ($allOrganisations as $org) {
            // Vérifier si l'association existe déjà
            if (!$superadminUser->organisations()->where('organisation_id', $org->id)->exists()) {
                $superadminUser->organisations()->attach($org->id, [
                    'role_id' => $superadminRole->id,
                    'creator_id' => $superadminUser->id
                ]);
            }
        }

        $this->command->info('✅ Superadmin affecté à toutes les directions');

        // 8. Les utilisateurs metier des directions (DG, directeur, responsable, agent)
        //    sont crees par RoleHierarchySeeder avec leurs veritables roles hierarchiques.
        //    Ce seeder ne cree que le compte superadmin technique.

        // 9. Mettre à jour les creator_id dans l'infrastructure physique
        $this->updateInfrastructureCreators($superadminUser->id);

        // 10. Mettre à jour les creator_id des activités
        $this->updateActivityCreators($superadminUser->id);

        // 11. Afficher un résumé
        $this->displaySummary($superadminUser, $directionGenerale, $allPermissions->count());
    }

    /**
     * Mettre à jour les creator_id dans l'infrastructure physique
     */
    private function updateInfrastructureCreators($userId)
    {
        $this->command->info('🔧 Mise à jour des creator_id...');

        // Mettre à jour Building
        DB::table('buildings')->where('creator_id', 999999)->update(['creator_id' => $userId]);

        // Mettre à jour Floor
        DB::table('floors')->where('creator_id', 999999)->update(['creator_id' => $userId]);

        // Mettre à jour Room
        DB::table('rooms')->where('creator_id', 999999)->update(['creator_id' => $userId]);

        // Mettre à jour Shelf
        DB::table('shelves')->where('creator_id', 999999)->update(['creator_id' => $userId]);

        // Mettre à jour Container
        DB::table('containers')->where('creator_id', 999999)->update(['creator_id' => $userId]);

        // Mettre à jour ContainerProperty
        DB::table('container_properties')->where('creator_id', 999999)->update(['creator_id' => $userId]);

        $this->command->info('✅ Creator_id mis à jour pour toute l\'infrastructure');
    }

    /**
     * Mettre à jour les creator_id des activités
     */
    private function updateActivityCreators($userId)
    {
        $this->command->info('🔧 Mise à jour des creator_id des activités...');

        // Mettre à jour la table pivot organisation_activity
        DB::table('organisation_activity')->where('creator_id', 999999)->update(['creator_id' => $userId]);

        $this->command->info('✅ Creator_id mis à jour pour toutes les activités');
    }

    /**
     * Afficher les catégories de permissions attribuées
     */
    private function displayPermissionCategories($allPermissions)
    {
        $this->command->info('');
        $this->command->info('📊 Répartition des permissions par catégorie :');

        $categories = $allPermissions->groupBy('category');
        foreach ($categories as $category => $permissions) {
            $categoryName = $category ?: 'Non catégorisée';
            $this->command->line('   • ' . ucfirst($categoryName) . ': ' . $permissions->count() . ' permissions');
        }
        $this->command->info('');
    }

    /**
     * Afficher le résumé de création
     */
    private function displaySummary($user, $organisation, $permissionCount)
    {
        $this->command->line('');
        $this->command->line('=== RÉSUMÉ DE LA CRÉATION DU SYSTÈME COMPLET ===');
        $this->command->line('ID Utilisateur Principal: ' . $user->id);
        $this->command->line('Email: ' . $user->email);
        $this->command->line('Mot de passe: superadmin');
        $this->command->line('Nom: ' . $user->name . ' ' . $user->surname);
        $this->command->line('Organisation principale: ' . $organisation->name);
        $this->command->line('Rôle: superadmin (Système natif)');
        $this->command->line('Permissions: ' . $permissionCount . ' permissions attribuées (TOUTES)');

        // Afficher tous les modules disponibles
        $modulePermissions = Permission::where('name', 'like', 'module_%_access')->pluck('name');
        $this->command->line('');
        $this->command->info('✅ Modules accessibles :');
        foreach ($modulePermissions as $modulePerm) {
            $moduleName = str_replace(['module_', '_access'], '', $modulePerm);
            $this->command->line('   - ' . ucfirst(str_replace('_', ' ', $moduleName)));
        }
        $this->command->line('');
        $this->command->line('✅ Infrastructure créée :');
        $this->command->line('   - 4 Organisations (DG, DF, DRH, DADA)');
        $this->command->line('   - 1 Bâtiment avec 3 étages');
        $this->command->line('   - 3 Salles d\'archives');
        $this->command->line('   - 30 Étagères (10 par salle)');
        $this->command->line('   - 300 Boîtes d\'archives (10 par étagère)');
        $this->command->line('   - Creator_id mis à jour pour toute l\'infrastructure');
        $this->command->line('');
        $this->command->info('✅ Utilisateurs créés :');
        $this->command->line('   - superadmin@example.com (Multi-directions)');
        $this->command->line('   - df@example.com (Direction des Finances)');
        $this->command->line('   - drh@example.com (Direction RH)');
        $this->command->line('   - dada@example.com (Direction Archives)');
        $this->command->line('   - Mot de passe identique pour tous : superadmin');
        $this->command->line('');
        $this->command->info('✅ Plan de classement créé avec activités hiérarchisées par direction');
        $this->command->line('   - Creator_id mis à jour pour toutes les activités');
        $this->command->line('===============================================================');
    }
}

