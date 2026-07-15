<?php

namespace Database\Seeders\Settings;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Role;
use App\Models\Permission;

/**
 * Crée les rôles métier hiérarchiques (DG, directeur, responsable, agent) et
 * quelques utilisateurs de démonstration rattachés aux directions via le pivot
 * user_organisation_role (role_id contextuel), prérequis du workflow courrier.
 *
 * Idempotent : rejouable sans dupliquer (firstOrCreate + attach conditionnel).
 */
class RoleHierarchySeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // 1. Rôles métier + permissions courrier associées.
        $rolePermissions = [
            'DG'          => ['mail_viewAny', 'mail_view', 'mail_create', 'mail_update', 'mail_delete'],
            'directeur'   => ['mail_viewAny', 'mail_view', 'mail_create', 'mail_update'],
            'responsable' => ['mail_viewAny', 'mail_view', 'mail_create', 'mail_update'],
            'agent'       => ['mail_viewAny', 'mail_view', 'mail_create'],
        ];

        $roles = [];
        foreach ($rolePermissions as $roleName => $permNames) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['description' => 'Rôle métier ' . $roleName]
            );

            $permIds = Permission::whereIn('name', $permNames)->pluck('id');
            $role->permissions()->syncWithoutDetaching($permIds);

            $roles[$roleName] = $role;
        }

        $this->command->info('Rôles métier créés : ' . implode(', ', array_keys($roles)));

        // 2. Organisations de référence.
        //    Pour la DG, on retient l'organisation racine effective (celle qui porte
        //    des sous-directions), afin d'être robuste à d'éventuels doublons de code.
        $dg = Organisation::where('code', 'DG')
            ->withCount('children')
            ->orderByDesc('children_count')
            ->first();
        $df   = Organisation::where('code', 'DF')->first();
        $drh  = Organisation::where('code', 'DRH')->first();

        if (!$dg) {
            $this->command->warn('Organisation DG absente : lancez d\'abord OrganisationSeeder.');
            return;
        }

        // 3. Utilisateurs de démonstration (rôle global + rôle contextuel par organisation).
        //    [email, nom, prénom, organisation, rôle]
        $demoUsers = [
            ['dg.demo@example.com',   'Général',   'Directeur',  $dg,  'DG'],
            ['dir.df@example.com',    'Finances',  'Directeur',  $df,  'directeur'],
            ['resp.df@example.com',   'Compta',    'Responsable', $df, 'responsable'],
            ['agent.df@example.com',  'Dupont',    'Agent',      $df,  'agent'],
            ['dir.drh@example.com',   'RH',        'Directeur',  $drh, 'directeur'],
            ['agent.drh@example.com', 'Martin',    'Agent',      $drh, 'agent'],
        ];

        foreach ($demoUsers as [$email, $name, $surname, $org, $roleName]) {
            if (!$org) {
                continue;
            }

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'surname' => $surname,
                    'password' => Hash::make('password'),
                    'birthday' => Carbon::parse('1985-01-01'),
                    'current_organisation_id' => $org->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            // Rôle global (pour les permissions courrier via le système natif).
            $user->assignRole($roleName);

            // Rôle contextuel dans l'organisation (pivot user_organisation_role).
            if (!$user->organisations()->where('organisation_id', $org->id)->exists()) {
                $user->organisations()->attach($org->id, [
                    'role_id' => $roles[$roleName]->id,
                    'creator_id' => $user->id,
                ]);
            }
        }

        $this->command->info('Utilisateurs de démonstration hiérarchiques créés : ' . count($demoUsers));
    }
}
