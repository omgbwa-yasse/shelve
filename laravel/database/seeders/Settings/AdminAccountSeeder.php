<?php

namespace Database\Seeders\Settings;

use App\Models\Organisation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Compte administrateur de développement — idempotent.
 *
 * Garantit un accès fonctionnel quel que soit l'état de la base :
 *   admin@example.com         / admin123   (superadmin, toutes permissions)
 *   superadmin@example.com    / superadmin (legacy, réparé + rôle superadmin)
 */
class AdminAccountSeeder extends Seeder
{
    public function run(): void
    {
        $organisation = Organisation::first();

        if (!$organisation) {
            $this->command->error('Aucune organisation trouvée — créez d\'abord une organisation.');

            return;
        }

        $role = Role::firstOrCreate(
            ['name' => 'superadmin'],
            [
                'name' => 'superadmin',
                'description' => 'Super administrateur avec tous les droits',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        if (Permission::count() > 0) {
            $role->permissions()->sync(Permission::pluck('id'));
        }

        $admin = $this->upsertAdmin('admin@example.com', 'admin123', 'Admin', 'Principal', $organisation, $role);

        // Répare l'ancien compte superadmin s'il existe (sans rôle actuellement).
        $legacy = User::where('email', 'superadmin@example.com')->first();
        if ($legacy) {
            $this->upsertAdmin('superadmin@example.com', 'superadmin', 'Super', 'Admin', $organisation, $role, $legacy);
        }

        $this->command->info('==============================================');
        $this->command->info('  LOGIN : ' . $admin->email . ' / admin123');
        $this->command->info('  Rôle  : superadmin (' . Permission::count() . ' permissions)');
        $this->command->info('  Legacy: superadmin@example.com / superadmin');
        $this->command->info('==============================================');
    }

    private function upsertAdmin(
        string $email,
        string $password,
        string $name,
        string $surname,
        Organisation $organisation,
        Role $role,
        ?User $existing = null
    ): User {
        $payload = [
            'name' => $name,
            'surname' => $surname,
            'password' => Hash::make($password),
            'birthday' => Carbon::parse('1990-01-01'),
            'current_organisation_id' => $organisation->id,
            'updated_at' => Carbon::now(),
        ];

        if ($existing) {
            $existing->update($payload);
            $user = $existing;
        } else {
            $user = User::updateOrCreate(
                ['email' => $email],
                array_merge(['email' => $email, 'created_at' => Carbon::now()], $payload)
            );
        }

        $user->assignRole('superadmin');

        if (!$user->organisations()->where('organisation_id', $organisation->id)->exists()) {
            $user->organisations()->attach($organisation->id, [
                'role_id' => $role->id,
                'creator_id' => $user->id,
            ]);
        }

        return $user;
    }
}
