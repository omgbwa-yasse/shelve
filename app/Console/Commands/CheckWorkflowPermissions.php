<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Permission;

class CheckWorkflowPermissions extends Command
{
    protected $signature = 'permissions:check-workflow';
    protected $description = 'Vérifier que le rôle superadmin a toutes les permissions workflow';

    public function handle()
    {
        $this->info('Vérification des permissions workflow pour le rôle superadmin...');

        $role = Role::where('name', 'superadmin')->first();
        if (!$role) {
            $this->error('Rôle superadmin introuvable !');
            return 1;
        }

        $workflowPermissions = Permission::where('category', 'workflow')->get();
        $totalWorkflowPermissions = $workflowPermissions->count();

        $this->info("Nombre total de permissions workflow: {$totalWorkflowPermissions}");

        $rolePermissionIds = $role->permissions->pluck('id')->toArray();

        $missingPermissions = [];
        foreach ($workflowPermissions as $permission) {
            if (!in_array($permission->id, $rolePermissionIds)) {
                $missingPermissions[] = $permission->name;
            }
        }

        if (empty($missingPermissions)) {
            $this->info('Le rôle superadmin a toutes les permissions workflow ✅');
            $this->info("Total: {$totalWorkflowPermissions} permissions workflow attribuées");
        } else {
            $this->error('Le rôle superadmin manque des permissions workflow:');
            foreach ($missingPermissions as $permission) {
                $this->warn(" - {$permission}");
            }
            $this->line('');
            $this->info(count($missingPermissions) . " permissions manquantes sur {$totalWorkflowPermissions}");
        }

        return 0;
    }
}
