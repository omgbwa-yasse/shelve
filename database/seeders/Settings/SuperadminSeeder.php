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
     * Utilise le systÃ¨me natif Laravel pour crÃ©er le superadmin
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $this->command->info('ðŸš€ CrÃ©ation du compte superadmin avec le systÃ¨me natif Laravel...');

        // 1. RÃ©cupÃ©rer les organisations
        $directionGenerale = Organisation::where('code', 'DG')->first();
        $directionFinances = Organisation::where('code', 'DF')->first();
        $directionRH = Organisation::where('code', 'DRH')->first();
        $directionArchives = Organisation::where('code', 'DADA')->first();

        if (!$directionGenerale || !$directionFinances || !$directionRH || !$directionArchives) {
            $this->command->error('Les organisations doivent Ãªtre crÃ©Ã©es avant ce seeder');
            return;
        }

        $this->command->info('âœ… Organisations trouvÃ©es');

        // 2. VÃ©rifier que les permissions existent
        $this->command->info('ðŸ“‹ VÃ©rification des permissions...');
        $permissionCount = Permission::count();
        if ($permissionCount == 0) {
            $this->command->error('Les permissions doivent Ãªtre crÃ©Ã©es avant ce seeder (PermissionCategorySeeder)');
            return;
        }
        $this->command->info('âœ… ' . $permissionCount . ' permissions trouvÃ©es');

        // 3. CrÃ©er le rÃ´le "superadmin" avec systÃ¨me natif
        $superadminRole = Role::firstOrCreate(
            ['name' => 'superadmin'],
            [
                'name' => 'superadmin',
                'description' => 'Super administrateur avec tous les droits',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->command->info('âœ… RÃ´le "superadmin" crÃ©Ã© ou trouvÃ© (ID: ' . $superadminRole->id . ')');

        // 4. Attribuer TOUTES les permissions au rÃ´le superadmin
        $this->command->info('ðŸ“‹ Attribution de toutes les permissions au rÃ´le superadmin...');

        $allPermissions = Permission::all();
        $permissionIds = $allPermissions->pluck('id')->toArray();

        // Synchroniser les permissions du rÃ´le
        $superadminRole->permissions()->sync($permissionIds);

        // VÃ©rification que toutes les permissions sont bien attribuÃ©es
        $assignedPermissions = $superadminRole->permissions()->count();
        if ($assignedPermissions !== $allPermissions->count()) {
            $this->command->error('âŒ Erreur: Toutes les permissions ne sont pas attribuÃ©es au rÃ´le superadmin');
            $this->command->error('Permissions totales: ' . $allPermissions->count() . ', Permissions attribuÃ©es: ' . $assignedPermissions);
            return;
        }

        $this->command->info('âœ… Toutes les permissions (' . $allPermissions->count() . ') attribuÃ©es au rÃ´le superadmin');

        // Afficher les catÃ©gories de permissions attribuÃ©es
        $this->displayPermissionCategories($allPermissions);

        // 5. CrÃ©er l'utilisateur superadmin principal
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

        $this->command->info('âœ… Utilisateur superadmin crÃ©Ã© ou trouvÃ© (ID: ' . $superadminUser->id . ')');

        // 6. Attribuer le rÃ´le Ã  l'utilisateur avec systÃ¨me natif
        $superadminUser->assignRole('superadmin');

        // VÃ©rification que l'utilisateur a bien le rÃ´le
        if (!$superadminUser->hasRole('superadmin')) {
            $this->command->error('âŒ Erreur: Le rÃ´le superadmin n\'a pas Ã©tÃ© correctement attribuÃ© Ã  l\'utilisateur');
            return;
        }

        // VÃ©rification de toutes les permissions de modules
        $modulePermissions = Permission::where('name', 'like', 'module_%_access')->get();
        $missingPermissions = [];

        foreach ($modulePermissions as $permission) {
            if (!$superadminUser->hasPermissionTo($permission->name)) {
                $missingPermissions[] = $permission->name;
            }
        }

        if (!empty($missingPermissions)) {
            $this->command->error('âŒ Erreur: Permissions de modules manquantes: ' . implode(', ', $missingPermissions));
        } else {
            $this->command->info('âœ… Toutes les permissions de modules vÃ©rifiÃ©es pour le superadmin (' . $modulePermissions->count() . ' modules)');
        }

        // VÃ©rification de quelques permissions critiques supplÃ©mentaires
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
            $this->command->info('âœ… RÃ´le et permissions critiques vÃ©rifiÃ©s pour le superadmin');
        }

        // 7. Affecter le superadmin Ã  toutes les organisations
        $allOrganisations = [$directionGenerale, $directionFinances, $directionRH, $directionArchives];
        foreach ($allOrganisations as $org) {
            // VÃ©rifier si l'association existe dÃ©jÃ 
            if (!$superadminUser->organisations()->where('organisation_id', $org->id)->exists()) {
                $superadminUser->organisations()->attach($org->id, [
                    'role_id' => $superadminRole->id,
                    'creator_id' => $superadminUser->id
                ]);
            }
        }

        $this->command->info('âœ… Superadmin affectÃ© Ã  toutes les directions');

        // 8. CrÃ©er des utilisateurs spÃ©cialisÃ©s pour chaque direction
        $this->createDirectionUsers($directionFinances, $now);
        $this->createDirectionUsers($directionRH, $now);
        $this->createDirectionUsers($directionArchives, $now);

        // 9. Mettre Ã  jour les creator_id dans l'infrastructure physique
        $this->updateInfrastructureCreators($superadminUser->id);

        // 10. Mettre Ã  jour les creator_id des activitÃ©s
        $this->updateActivityCreators($superadminUser->id);

        // 11. Afficher un rÃ©sumÃ©
        $this->displaySummary($superadminUser, $directionGenerale, $allPermissions->count());
    }

    /**
     * CrÃ©er des utilisateurs spÃ©cialisÃ©s pour chaque direction
     */
    private function createDirectionUsers($organisation, $now)
    {
        $this->command->info('ðŸ‘¤ CrÃ©ation de l\'utilisateur pour ' . $organisation->name . '...');

        $userCode = strtolower($organisation->code);
        $userName = $this->getDirectionUserName($organisation->code);

        $user = User::firstOrCreate(
            ['email' => $userCode . '@example.com'],
            [
                'name' => $userName['name'],
                'surname' => $userName['surname'],
                'email' => $userCode . '@example.com',
                'password' => Hash::make('superadmin'), // MÃªme mot de passe que le superadmin
                'birthday' => Carbon::parse('1990-01-01'),
                'current_organisation_id' => $organisation->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // Attribuer le rÃ´le superadmin
        $user->assignRole('superadmin');

        // RÃ©cupÃ©rer le rÃ´le superadmin pour l'association
        $superadminRole = Role::where('name', 'superadmin')->first();

        // Affecter l'utilisateur Ã  son organisation
        if (!$user->organisations()->where('organisation_id', $organisation->id)->exists()) {
            $user->organisations()->attach($organisation->id, [
                'role_id' => $superadminRole->id,
                'creator_id' => $user->id
            ]);
        }

        $this->command->info('âœ… Utilisateur ' . $userName['name'] . ' ' . $userName['surname'] . ' crÃ©Ã© pour ' . $organisation->name);
    }

    /**
     * Obtenir les noms selon le code de l'organisation
     */
    private function getDirectionUserName($code)
    {
        $names = [
            'DF' => ['name' => 'Directeur', 'surname' => 'Finances'],
            'DRH' => ['name' => 'Directeur', 'surname' => 'RessourcesHumaines'],
            'DADA' => ['name' => 'Directeur', 'surname' => 'Archives']
        ];

        return $names[$code] ?? ['name' => 'Directeur', 'surname' => 'Direction'];
    }

    /**
     * Mettre Ã  jour les creator_id dans l'infrastructure physique
     */
    private function updateInfrastructureCreators($userId)
    {
        $this->command->info('ðŸ”§ Mise Ã  jour des creator_id...');

        // Mettre Ã  jour Building
        DB::table('buildings')->where('creator_id', 999999)->update(['creator_id' => $userId]);

        // Mettre Ã  jour Floor
        DB::table('floors')->where('creator_id', 999999)->update(['creator_id' => $userId]);

        // Mettre Ã  jour Room
        DB::table('rooms')->where('creator_id', 999999)->update(['creator_id' => $userId]);

        // Mettre Ã  jour Shelf
        DB::table('shelves')->where('creator_id', 999999)->update(['creator_id' => $userId]);

        // Mettre Ã  jour Container
        DB::table('containers')->where('creator_id', 999999)->update(['creator_id' => $userId]);

        // Mettre Ã  jour ContainerProperty
        DB::table('container_properties')->where('creator_id', 999999)->update(['creator_id' => $userId]);

        $this->command->info('âœ… Creator_id mis Ã  jour pour toute l\'infrastructure');
    }

    /**
     * Mettre Ã  jour les creator_id des activitÃ©s
     */
    private function updateActivityCreators($userId)
    {
        $this->command->info('ðŸ”§ Mise Ã  jour des creator_id des activitÃ©s...');

        // Mettre Ã  jour la table pivot organisation_activity
        DB::table('organisation_activity')->where('creator_id', 999999)->update(['creator_id' => $userId]);

        $this->command->info('âœ… Creator_id mis Ã  jour pour toutes les activitÃ©s');
    }

    /**
     * Afficher les catÃ©gories de permissions attribuÃ©es
     */
    private function displayPermissionCategories($allPermissions)
    {
        $this->command->info('');
        $this->command->info('ðŸ“Š RÃ©partition des permissions par catÃ©gorie :');

        $categories = $allPermissions->groupBy('category');
        foreach ($categories as $category => $permissions) {
            $categoryName = $category ?: 'Non catÃ©gorisÃ©e';
            $this->command->line('   â€¢ ' . ucfirst($categoryName) . ': ' . $permissions->count() . ' permissions');
        }
        $this->command->info('');
    }

    /**
     * Afficher le rÃ©sumÃ© de crÃ©ation
     */
    private function displaySummary($user, $organisation, $permissionCount)
    {
        $this->command->line('');
        $this->command->line('=== RÃ‰SUMÃ‰ DE LA CRÃ‰ATION DU SYSTÃˆME COMPLET ===');
        $this->command->line('ID Utilisateur Principal: ' . $user->id);
        $this->command->line('Email: ' . $user->email);
        $this->command->line('Mot de passe: superadmin');
        $this->command->line('Nom: ' . $user->name . ' ' . $user->surname);
        $this->command->line('Organisation principale: ' . $organisation->name);
        $this->command->line('RÃ´le: superadmin (SystÃ¨me natif)');
        $this->command->line('Permissions: ' . $permissionCount . ' permissions attribuÃ©es (TOUTES)');

        // Afficher tous les modules disponibles
        $modulePermissions = Permission::where('name', 'like', 'module_%_access')->pluck('name');
        $this->command->line('');
        $this->command->info('âœ… Modules accessibles :');
        foreach ($modulePermissions as $modulePerm) {
            $moduleName = str_replace(['module_', '_access'], '', $modulePerm);
            $this->command->line('   - ' . ucfirst(str_replace('_', ' ', $moduleName)));
        }
        $this->command->line('');
        $this->command->line('âœ… Infrastructure crÃ©Ã©e :');
        $this->command->line('   - 4 Organisations (DG, DF, DRH, DADA)');
        $this->command->line('   - 1 BÃ¢timent avec 3 Ã©tages');
        $this->command->line('   - 3 Salles d\'archives');
        $this->command->line('   - 30 Ã‰tagÃ¨res (10 par salle)');
        $this->command->line('   - 300 BoÃ®tes d\'archives (10 par Ã©tagÃ¨re)');
        $this->command->line('   - Creator_id mis Ã  jour pour toute l\'infrastructure');
        $this->command->line('');
        $this->command->info('âœ… Utilisateurs crÃ©Ã©s :');
        $this->command->line('   - superadmin@example.com (Multi-directions)');
        $this->command->line('   - df@example.com (Direction des Finances)');
        $this->command->line('   - drh@example.com (Direction RH)');
        $this->command->line('   - dada@example.com (Direction Archives)');
        $this->command->line('   - Mot de passe identique pour tous : superadmin');
        $this->command->line('');
        $this->command->info('âœ… Plan de classement crÃ©Ã© avec activitÃ©s hiÃ©rarchisÃ©es par direction');
        $this->command->line('   - Creator_id mis Ã  jour pour toutes les activitÃ©s');
        $this->command->line('===============================================================');
    }
}

