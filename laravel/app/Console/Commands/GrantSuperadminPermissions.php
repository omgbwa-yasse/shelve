<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class GrantSuperadminPermissions extends Command
{
    protected $signature = 'superadmin:grant-all-permissions';
    protected $description = 'Grant all permissions to the superadmin role and user';

    public function handle()
    {
        // Step 1: Find the superadmin role
        $superadminRole = DB::table('roles')->where('name', 'superadmin')->first();
        if (!$superadminRole) {
            $this->error('SuperAdmin role not found!');
            return 1;
        }
        $this->info("SuperAdmin Role: ID={$superadminRole->id}, Name={$superadminRole->name}");

        // Step 2: Get all permissions
        $allPermissions = DB::table('permissions')->get();
        $this->info("Total permissions in database: {$allPermissions->count()}");
        foreach ($allPermissions as $perm) {
            $cat = $perm->category ? " ({$perm->category})" : '';
            $this->line("  [{$perm->id}] {$perm->name}{$cat}");
        }

        // Step 3: Check current state
        $currentCount1 = DB::table('role_has_permissions')->where('role_id', $superadminRole->id)->count();
        $currentCount2 = DB::table('role_permissions')->where('role_id', $superadminRole->id)->count();
        $this->newLine();
        $this->warn("Before: role_has_permissions={$currentCount1}, role_permissions={$currentCount2}");

        // Step 4: Grant all permissions via role_has_permissions (Spatie-style)
        $inserted1 = 0;
        foreach ($allPermissions as $perm) {
            $exists = DB::table('role_has_permissions')
                ->where('role_id', $superadminRole->id)
                ->where('permission_id', $perm->id)
                ->exists();
            if (!$exists) {
                DB::table('role_has_permissions')->insert([
                    'role_id' => $superadminRole->id,
                    'permission_id' => $perm->id,
                ]);
                $inserted1++;
                $this->line("  [role_has_permissions] + {$perm->name}");
            }
        }

        // Step 5: Grant all permissions via role_permissions (custom pivot)
        $inserted2 = 0;
        foreach ($allPermissions as $perm) {
            $exists = DB::table('role_permissions')
                ->where('role_id', $superadminRole->id)
                ->where('permission_id', $perm->id)
                ->exists();
            if (!$exists) {
                DB::table('role_permissions')->insert([
                    'role_id' => $superadminRole->id,
                    'permission_id' => $perm->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $inserted2++;
                $this->line("  [role_permissions] + {$perm->name}");
            }
        }

        // Step 6: Find superadmin user and ensure role + permission assignment
        $superadminUser = User::where('email', 'superadmin@example.com')->first();
        if ($superadminUser) {
            $this->newLine();
            $this->info("SuperAdmin User: {$superadminUser->name} (ID={$superadminUser->id})");

            // Ensure model_has_roles link exists
            $modelType = get_class($superadminUser);
            $hasModelRole = DB::table('model_has_roles')
                ->where('role_id', $superadminRole->id)
                ->where('model_type', $modelType)
                ->where('model_id', $superadminUser->id)
                ->exists();
            if (!$hasModelRole) {
                DB::table('model_has_roles')->insert([
                    'role_id' => $superadminRole->id,
                    'model_type' => $modelType,
                    'model_id' => $superadminUser->id,
                ]);
                $this->line('  [model_has_roles] Linked superadmin role to user');
            } else {
                $this->line('  [model_has_roles] Already linked');
            }

            // Ensure user_roles link exists
            $hasUserRole = DB::table('user_roles')
                ->where('role_id', $superadminRole->id)
                ->where('user_id', $superadminUser->id)
                ->exists();
            if (!$hasUserRole) {
                DB::table('user_roles')->insert([
                    'user_id' => $superadminUser->id,
                    'role_id' => $superadminRole->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->line('  [user_roles] Linked superadmin role to user');
            } else {
                $this->line('  [user_roles] Already linked');
            }

            // Grant all permissions directly to user via model_has_permissions
            $directInserted = 0;
            foreach ($allPermissions as $perm) {
                $exists = DB::table('model_has_permissions')
                    ->where('permission_id', $perm->id)
                    ->where('model_type', $modelType)
                    ->where('model_id', $superadminUser->id)
                    ->exists();
                if (!$exists) {
                    DB::table('model_has_permissions')->insert([
                        'permission_id' => $perm->id,
                        'model_type' => $modelType,
                        'model_id' => $superadminUser->id,
                    ]);
                    $directInserted++;
                }
            }
            $this->line("  [model_has_permissions] Direct user permissions granted: {$directInserted}");

            // Also via user_permissions
            $directInserted2 = 0;
            foreach ($allPermissions as $perm) {
                $exists = DB::table('user_permissions')
                    ->where('permission_id', $perm->id)
                    ->where('user_id', $superadminUser->id)
                    ->exists();
                if (!$exists) {
                    DB::table('user_permissions')->insert([
                        'user_id' => $superadminUser->id,
                        'permission_id' => $perm->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $directInserted2++;
                }
            }
            $this->line("  [user_permissions] Direct user permissions granted: {$directInserted2}");
        } else {
            $this->warn('No superadmin user found at superadmin@example.com');
        }

        // Step 7: Final summary
        $this->newLine();
        $this->info('=== SUMMARY ===');
        $final1 = DB::table('role_has_permissions')->where('role_id', $superadminRole->id)->count();
        $final2 = DB::table('role_permissions')->where('role_id', $superadminRole->id)->count();
        $this->line("role_has_permissions: {$final1} / {$allPermissions->count()}");
        $this->line("role_permissions:     {$final2} / {$allPermissions->count()}");

        if ($superadminUser) {
            $modelType = get_class($superadminUser);
            $mr = DB::table('model_has_roles')->where('model_id', $superadminUser->id)->where('model_type', $modelType)->count();
            $ur = DB::table('user_roles')->where('user_id', $superadminUser->id)->count();
            $mp = DB::table('model_has_permissions')->where('model_id', $superadminUser->id)->where('model_type', $modelType)->count();
            $up = DB::table('user_permissions')->where('user_id', $superadminUser->id)->count();
            $this->line("model_has_roles:      {$mr} role(s)");
            $this->line("user_roles:           {$ur} role(s)");
            $this->line("model_has_permissions: {$mp} / {$allPermissions->count()}");
            $this->line("user_permissions:     {$up} / {$allPermissions->count()}");
        }

        $this->newLine();
        $this->info('All permissions granted to SuperAdmin!');

        return 0;
    }
}
