<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;

class CheckPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check permissions for superadmin';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== VÉRIFICATION DES PERMISSIONS ===');
        
        // Total des permissions
        $totalPermissions = Permission::count();
        $this->info("Total permissions dans la base: $totalPermissions");
        
        // Stats par catégorie
        $permissions = Permission::selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->get();
            
        $this->info("\nPermissions par catégorie:");
        foreach ($permissions as $perm) {
            $this->line("  - {$perm->category}: {$perm->count}");
        }
        
        // Superadmin
        $superadmin = User::where('email', 'superadmin@example.com')->first();
        if ($superadmin) {
            $this->info("\n=== SUPERADMIN ===");
            $this->info("Email: {$superadmin->email}");
            $this->info("Rôles: " . $superadmin->roles()->count());
            
            $roles = $superadmin->roles()->get();
            foreach ($roles as $role) {
                $this->line("  - Rôle: {$role->name}");
                $this->line("    Permissions: " . $role->permissions()->count());
            }
            
            $this->info("Permissions directes: " . $superadmin->permissions()->count());
            
            // Test de quelques permissions spécifiques
            $testPermissions = ['dashboard_view', 'mail_create', 'users_manage'];
            $this->info("\nTest de permissions:");
            foreach ($testPermissions as $perm) {
                $hasPermission = $superadmin->hasPermissionTo($perm) ? '✅' : '❌';
                $this->line("  $hasPermission $perm");
            }
            
            // Test du rôle superadmin
            $hasRole = $superadmin->hasRole('superadmin') ? '✅' : '❌';
            $this->line("\n$hasRole Rôle superadmin");
        } else {
            $this->error("Superadmin non trouvé!");
        }
        
        return 0;
    }
}
