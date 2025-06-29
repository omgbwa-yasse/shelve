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

class SuperadminSeeder extends Seeder
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

        // 2. S'assurer que les permissions existent (exécuter NativePermissionSeeder)
        $this->command->info('📋 Vérification des permissions...');
        $this->call(NativePermissionSeeder::class);

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

        // 5. Créer l'utilisateur superadmin
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

        $this->command->info('✅ Rôle superadmin attribué à l\'utilisateur');

        // 7. Afficher un résumé
        $this->displaySummary($superadminUser, $directionGenerale, $allPermissions->count());
    }

    /**
     * Afficher le résumé de création
     */
    private function displaySummary($user, $organisation, $permissionCount)
    {
        $this->command->line('');
        $this->command->line('=== RÉSUMÉ DE LA CRÉATION DU COMPTE SUPERADMIN (NATIF) ===');
        $this->command->line('ID Utilisateur: ' . $user->id);
        $this->command->line('Email: ' . $user->email);
        $this->command->line('Mot de passe: superadmin');
        $this->command->line('Nom: ' . $user->name . ' ' . $user->surname);
        $this->command->line('Organisation: ' . $organisation->name);
        $this->command->line('Rôle: superadmin (Système natif)');
        $this->command->line('Permissions: ' . $permissionCount . ' permissions attribuées');
        $this->command->line('');
        $this->command->info('✅ Le superadmin peut maintenant utiliser les méthodes natives :');
        $this->command->line('   - $user->hasRole("superadmin")');
        $this->command->line('   - $user->hasPermissionTo("permission_name")');
        $this->command->line('   - Gate::forUser($user)->allows("permission_name")');
        $this->command->line('===============================================================');
    }
}
