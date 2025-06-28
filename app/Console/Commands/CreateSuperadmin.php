<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Role;

class CreateSuperadmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:superadmin
                            {--email=superadmin@example.com : Email du superadmin}
                            {--password=superadmin : Mot de passe du superadmin}
                            {--name=Super : Prénom}
                            {--surname=Admin : Nom de famille}
                            {--org=Direction générale : Nom de l\'organisation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer un compte superadmin avec tous les droits';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $email = $this->option('email');
        $password = $this->option('password');
        $name = $this->option('name');
        $surname = $this->option('surname');
        $orgName = $this->option('org');

        $this->info('Création du compte superadmin...');
        $this->line('Email: ' . $email);
        $this->line('Organisation: ' . $orgName);

        // 1. Créer l'organisation si elle n'existe pas
        $organisation = Organisation::firstOrCreate(
            ['name' => $orgName],
            [
                'code' => 'DIR-GEN',
                'description' => 'Direction générale de l\'organisation',
                'parent_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->info('✓ Organisation "' . $orgName . '" (ID: ' . $organisation->id . ')');

        // 2. Créer le rôle superadmin
        $superadminRole = Role::firstOrCreate(
            ['name' => 'superadmin'],
            [
                'description' => 'Super administrateur avec tous les droits',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->info('✓ Rôle "superadmin" (ID: ' . $superadminRole->id . ')');

        // 3. Attribuer toutes les permissions
        $permissions = DB::table('permissions')->pluck('id');

        if ($permissions->isEmpty()) {
            $this->warn('Aucune permission trouvée. Exécutez d\'abord: php artisan db:seed --class=PermissionSeeder');
            return 1;
        }

        // Supprimer les permissions existantes
        DB::table('role_permissions')->where('role_id', $superadminRole->id)->delete();

        // Attribuer toutes les permissions
        $permissionData = [];
        foreach ($permissions as $permissionId) {
            $permissionData[] = [
                'role_id' => $superadminRole->id,
                'permission_id' => $permissionId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('role_permissions')->insert($permissionData);
        $this->info('✓ ' . count($permissions) . ' permissions attribuées');

        // 4. Créer ou mettre à jour l'utilisateur
        $user = User::where('email', $email)->first();

        if ($user) {
            $this->warn('Utilisateur existant trouvé. Mise à jour...');
            $user->update([
                'password' => Hash::make($password),
                'current_organisation_id' => $organisation->id,
                'updated_at' => $now,
            ]);
        } else {
            $user = User::create([
                'name' => $name,
                'surname' => $surname,
                'email' => $email,
                'password' => Hash::make($password),
                'birthday' => Carbon::parse('1990-01-01'),
                'current_organisation_id' => $organisation->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->info('✓ Utilisateur créé/mis à jour (ID: ' . $user->id . ')');

        // 5. Lier l'utilisateur au rôle
        DB::table('user_organisation_role')->updateOrInsert(
            [
                'user_id' => $user->id,
                'organisation_id' => $organisation->id,
            ],
            [
                'role_id' => $superadminRole->id,
                'creator_id' => $user->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->info('✓ Utilisateur lié au rôle superadmin');

        // Résumé final
        $this->line('');
        $this->comment('=== COMPTE SUPERADMIN CRÉÉ AVEC SUCCÈS ===');
        $this->line('Email: ' . $email);
        $this->line('Mot de passe: ' . $password);
        $this->line('Nom: ' . $name . ' ' . $surname);
        $this->line('Organisation: ' . $orgName);
        $this->line('Permissions: ' . count($permissions));
        $this->comment('==========================================');

        return 0;
    }
}
