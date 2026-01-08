<?php

namespace Database\Seeders\Settings;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class VerifySuperadminPermissionsSeeder extends Seeder
{
    /**
     * VÃ©rifier que le superadmin a toutes les permissions
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('ðŸ” VÃ©rification des permissions du superadmin...');
        $this->command->line('');

        // 1. VÃ©rifier le rÃ´le superadmin
        $superadminRole = Role::where('name', 'superadmin')->first();

        if (!$superadminRole) {
            $this->command->error('âŒ RÃ´le "superadmin" non trouvÃ©!');
            $this->command->error('ExÃ©cutez: php artisan db:seed --class=SuperadminSeeder');
            return;
        }

        $this->command->info('âœ… RÃ´le "superadmin" trouvÃ© (ID: ' . $superadminRole->id . ')');

        // 2. Compter les permissions
        $totalPermissions = Permission::count();
        $rolePermissions = $superadminRole->permissions()->count();

        $this->command->line('');
        $this->command->info('ðŸ“Š Statistiques des permissions :');
        $this->command->line('   â€¢ Total permissions systÃ¨me : ' . $totalPermissions);
        $this->command->line('   â€¢ Permissions rÃ´le superadmin : ' . $rolePermissions);

        if ($rolePermissions === $totalPermissions) {
            $this->command->info('   âœ… Le rÃ´le superadmin a TOUTES les permissions');
        } else {
            $this->command->error('   âŒ Permissions manquantes : ' . ($totalPermissions - $rolePermissions));
            $this->displayMissingPermissions($superadminRole);
        }

        // 3. VÃ©rifier les utilisateurs superadmin
        $this->command->line('');
        $this->command->info('ðŸ‘¥ Utilisateurs avec rÃ´le superadmin :');

        $superadminUsers = User::whereHas('roles', function ($query) {
            $query->where('name', 'superadmin');
        })->get();

        if ($superadminUsers->isEmpty()) {
            $this->command->error('   âŒ Aucun utilisateur avec le rÃ´le superadmin trouvÃ©!');
            return;
        }

        foreach ($superadminUsers as $user) {
            $this->command->line('   â€¢ ' . $user->email . ' (' . $user->name . ' ' . $user->surname . ')');

            // VÃ©rifier les permissions via le rÃ´le
            $userRoles = $user->roles()->pluck('name')->toArray();
            $hasSuperadminRole = in_array('superadmin', $userRoles);

            if ($hasSuperadminRole) {
                $this->command->info('     âœ… A le rÃ´le superadmin (accÃ¨s Ã  toutes les ' . $totalPermissions . ' permissions)');
            } else {
                $this->command->error('     âŒ N\'a pas le rÃ´le superadmin');
            }
        }

        // 4. VÃ©rifier les permissions Phase 3 spÃ©cifiques
        $this->command->line('');
        $this->command->info('ðŸ” VÃ©rification des permissions Workflow Phase 3 :');

        $phase3Permissions = [
            'digital_records.checkout',
            'digital_records.checkin',
            'digital_records.cancel_checkout',
            'digital_records.sign',
            'digital_records.verify_signature',
            'digital_records.revoke_signature',
            'digital_records.restore',
            'digital_records.download',
            'digital_records.approve',
            'digital_records.reject',
            'digital_records.workflow.admin',
        ];

        $missingPhase3 = [];
        foreach ($phase3Permissions as $permName) {
            $exists = Permission::where('name', $permName)->exists();
            if ($exists) {
                $hasPermission = $superadminRole->permissions()->where('name', $permName)->exists();
                if ($hasPermission) {
                    $this->command->line('   âœ… ' . $permName);
                } else {
                    $this->command->error('   âŒ ' . $permName . ' (non attribuÃ©e au rÃ´le)');
                    $missingPhase3[] = $permName;
                }
            } else {
                $this->command->error('   âŒ ' . $permName . ' (n\'existe pas)');
                $missingPhase3[] = $permName;
            }
        }

        // 5. Afficher les catÃ©gories
        $this->command->line('');
        $this->displayPermissionCategories();

        // 6. RÃ©sumÃ© final
        $this->command->line('');
        $this->command->line('=== RÃ‰SUMÃ‰ FINAL ===');

        if ($rolePermissions === $totalPermissions && empty($missingPhase3)) {
            $this->command->info('âœ… TOUT EST EN ORDRE');
            $this->command->info('Le superadmin a accÃ¨s Ã  toutes les ' . $totalPermissions . ' permissions');
            $this->command->info('Toutes les permissions Phase 3 sont prÃ©sentes et attribuÃ©es');
        } else {
            $this->command->error('âš ï¸  DES CORRECTIONS SONT NÃ‰CESSAIRES');

            if ($rolePermissions !== $totalPermissions) {
                $this->command->line('1. ExÃ©cutez: php artisan db:seed --class=WorkflowPhase3PermissionsSeeder');
            }

            if (!empty($missingPhase3)) {
                $this->command->line('2. VÃ©rifiez que WorkflowPhase3PermissionsSeeder a bien Ã©tÃ© exÃ©cutÃ©');
            }
        }

        $this->command->line('');
    }

    /**
     * Afficher les permissions manquantes
     */
    private function displayMissingPermissions($role)
    {
        $allPermissions = Permission::all()->pluck('name');
        $rolePermissions = $role->permissions()->pluck('name');
        $missing = $allPermissions->diff($rolePermissions);

        if ($missing->isNotEmpty()) {
            $this->command->line('');
            $this->command->error('   Permissions manquantes au rÃ´le superadmin :');
            foreach ($missing as $permName) {
                $this->command->line('      - ' . $permName);
            }
        }
    }

    /**
     * Afficher les catÃ©gories de permissions
     */
    private function displayPermissionCategories()
    {
        $this->command->info('ðŸ“Š RÃ©partition par catÃ©gorie :');

        $categories = Permission::all()->groupBy('category');
        $stats = [];

        foreach ($categories as $category => $permissions) {
            $categoryName = $category ?: 'Non catÃ©gorisÃ©e';
            $stats[$categoryName] = $permissions->count();
        }

        arsort($stats);

        foreach ($stats as $category => $count) {
            $this->command->line('   â€¢ ' . ucfirst($category) . ': ' . $count . ' permissions');
        }
    }
}

