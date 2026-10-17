<?php

namespace Database\Seeders\Mails;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Rôles minimaux du circuit Courrier. Aucun compte de démonstration ni mot de
 * passe n'est créé ici : les utilisateurs sont affectés ensuite depuis l'écran
 * d'administration des organisations.
 */
class MailWorkflowRolesSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'DG' => [
                'mail_viewAny', 'mail_view', 'mail_create', 'mail_update', 'mail_delete', 'mail_config',
                'module_mails_access', 'organisations_view', 'organisations_update',
            ],
            'directeur' => [
                'mail_viewAny', 'mail_view', 'mail_create', 'mail_update',
                'module_mails_access', 'organisations_view',
            ],
            'responsable' => [
                'mail_viewAny', 'mail_view', 'mail_create', 'mail_update',
                'module_mails_access',
            ],
            'agent' => [
                'mail_viewAny', 'mail_view', 'mail_create', 'module_mails_access',
            ],
        ];

        foreach ($permissions as $name => $permissionNames) {
            $role = Role::firstOrCreate(
                ['name' => $name],
                ['description' => 'Rôle hiérarchique du circuit Courrier : '.$name]
            );

            $role->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', $permissionNames)->pluck('id')
            );
        }
    }
}
