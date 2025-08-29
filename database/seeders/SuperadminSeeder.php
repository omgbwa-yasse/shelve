<?php

namespace Database\Seeders;

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
        $directionFinances = Organisation::where('code', 'DF')->first();
        $directionRH = Organisation::where('code', 'DRH')->first();
        $directionArchives = Organisation::where('code', 'DADA')->first();

        if (!$directionGenerale || !$directionFinances || !$directionRH || !$directionArchives) {
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

        // 4. Attribuer toutes les permissions au rôle
        $allPermissions = Permission::all();
        $permissionIds = $allPermissions->pluck('id')->toArray();

        // Synchroniser les permissions du rôle
        $superadminRole->permissions()->sync($permissionIds);

        $this->command->info('✅ Toutes les permissions (' . $allPermissions->count() . ') attribuées au rôle superadmin');

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

        // 7. Affecter le superadmin à toutes les organisations
        $allOrganisations = [$directionGenerale, $directionFinances, $directionRH, $directionArchives];
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

        // 8. Créer des utilisateurs spécialisés pour chaque direction
        $this->createDirectionUsers($directionFinances, $now);
        $this->createDirectionUsers($directionRH, $now);
        $this->createDirectionUsers($directionArchives, $now);

        // 9. Mettre à jour les creator_id dans l'infrastructure physique
        $this->updateInfrastructureCreators($superadminUser->id);

        // 10. Mettre à jour les creator_id des activités
        $this->updateActivityCreators($superadminUser->id);

        // 11. Afficher un résumé
        $this->displaySummary($superadminUser, $directionGenerale, $allPermissions->count());
    }

    /**
     * Créer des utilisateurs spécialisés pour chaque direction
     */
    private function createDirectionUsers($organisation, $now)
    {
        $this->command->info('👤 Création de l\'utilisateur pour ' . $organisation->name . '...');

        $userCode = strtolower($organisation->code);
        $userName = $this->getDirectionUserName($organisation->code);

        $user = User::firstOrCreate(
            ['email' => $userCode . '@example.com'],
            [
                'name' => $userName['name'],
                'surname' => $userName['surname'],
                'email' => $userCode . '@example.com',
                'password' => Hash::make('superadmin'), // Même mot de passe que le superadmin
                'birthday' => Carbon::parse('1990-01-01'),
                'current_organisation_id' => $organisation->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // Attribuer le rôle superadmin
        $user->assignRole('superadmin');

        // Récupérer le rôle superadmin pour l'association
        $superadminRole = Role::where('name', 'superadmin')->first();

        // Affecter l'utilisateur à son organisation
        if (!$user->organisations()->where('organisation_id', $organisation->id)->exists()) {
            $user->organisations()->attach($organisation->id, [
                'role_id' => $superadminRole->id,
                'creator_id' => $user->id
            ]);
        }

        $this->command->info('✅ Utilisateur ' . $userName['name'] . ' ' . $userName['surname'] . ' créé pour ' . $organisation->name);
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
        $this->command->line('Permissions: ' . $permissionCount . ' permissions attribuées');
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
