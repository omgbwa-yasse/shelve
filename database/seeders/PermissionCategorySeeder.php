<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createDashboardPermissions();
        $this->createMailPermissions();
        $this->createRecordsPermissions();
        $this->createCommunicationsPermissions();
        $this->createReservationsPermissions();
        $this->createUsersPermissions();
        $this->createSettingsPermissions();
        $this->createSystemPermissions();
        $this->createBackupsPermissions();

        $this->command->info('Permissions avec catégories créées avec succès!');
    }

    private function createDashboardPermissions()
    {
        $permissions = [
            [
                'name' => 'dashboard_view',
                'category' => 'dashboard',
                'description' => 'Voir le tableau de bord',
                'guard_name' => 'web'
            ],
            [
                'name' => 'dashboard_manage',
                'category' => 'dashboard',
                'description' => 'Gérer le tableau de bord',
                'guard_name' => 'web'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createMailPermissions()
    {
        $permissions = [
            [
                'name' => 'mail_view',
                'category' => 'mail',
                'description' => 'Voir les courriers',
                'guard_name' => 'web'
            ],
            [
                'name' => 'mail_create',
                'category' => 'mail',
                'description' => 'Créer des courriers',
                'guard_name' => 'web'
            ],
            [
                'name' => 'mail_edit',
                'category' => 'mail',
                'description' => 'Modifier des courriers',
                'guard_name' => 'web'
            ],
            [
                'name' => 'mail_delete',
                'category' => 'mail',
                'description' => 'Supprimer des courriers',
                'guard_name' => 'web'
            ],
            [
                'name' => 'mail_config',
                'category' => 'mail',
                'description' => 'Configurer les paramètres courrier',
                'guard_name' => 'web'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createRecordsPermissions()
    {
        $permissions = [
            [
                'name' => 'records_view',
                'category' => 'records',
                'description' => 'Voir les documents',
                'guard_name' => 'web'
            ],
            [
                'name' => 'records_create',
                'category' => 'records',
                'description' => 'Créer des documents',
                'guard_name' => 'web'
            ],
            [
                'name' => 'records_edit',
                'category' => 'records',
                'description' => 'Modifier des documents',
                'guard_name' => 'web'
            ],
            [
                'name' => 'records_delete',
                'category' => 'records',
                'description' => 'Supprimer des documents',
                'guard_name' => 'web'
            ],
            [
                'name' => 'records_transfer',
                'category' => 'records',
                'description' => 'Transférer des documents',
                'guard_name' => 'web'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createCommunicationsPermissions()
    {
        $permissions = [
            [
                'name' => 'communications_view',
                'category' => 'communications',
                'description' => 'Voir les communications',
                'guard_name' => 'web'
            ],
            [
                'name' => 'communications_create',
                'category' => 'communications',
                'description' => 'Créer des communications',
                'guard_name' => 'web'
            ],
            [
                'name' => 'communications_edit',
                'category' => 'communications',
                'description' => 'Modifier des communications',
                'guard_name' => 'web'
            ],
            [
                'name' => 'communications_delete',
                'category' => 'communications',
                'description' => 'Supprimer des communications',
                'guard_name' => 'web'
            ],
            [
                'name' => 'communications_phantom',
                'category' => 'communications',
                'description' => 'Générer des documents fantômes',
                'guard_name' => 'web'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createReservationsPermissions()
    {
        $permissions = [
            [
                'name' => 'reservations_view',
                'category' => 'reservations',
                'description' => 'Voir les réservations',
                'guard_name' => 'web'
            ],
            [
                'name' => 'reservations_create',
                'category' => 'reservations',
                'description' => 'Créer des réservations',
                'guard_name' => 'web'
            ],
            [
                'name' => 'reservations_approve',
                'category' => 'reservations',
                'description' => 'Approuver des réservations',
                'guard_name' => 'web'
            ],
            [
                'name' => 'reservations_manage',
                'category' => 'reservations',
                'description' => 'Gérer les réservations',
                'guard_name' => 'web'
            ],
            [
                'name' => 'reservations_return',
                'category' => 'reservations',
                'description' => 'Gérer les retours de réservations',
                'guard_name' => 'web'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createUsersPermissions()
    {
        $permissions = [
            [
                'name' => 'users_view',
                'category' => 'users',
                'description' => 'Voir les utilisateurs',
                'guard_name' => 'web'
            ],
            [
                'name' => 'users_create',
                'category' => 'users',
                'description' => 'Créer des utilisateurs',
                'guard_name' => 'web'
            ],
            [
                'name' => 'users_edit',
                'category' => 'users',
                'description' => 'Modifier des utilisateurs',
                'guard_name' => 'web'
            ],
            [
                'name' => 'users_delete',
                'category' => 'users',
                'description' => 'Supprimer des utilisateurs',
                'guard_name' => 'web'
            ],
            [
                'name' => 'users_manage',
                'category' => 'users',
                'description' => 'Gérer les utilisateurs',
                'guard_name' => 'web'
            ],
            [
                'name' => 'roles_view',
                'category' => 'users',
                'description' => 'Voir les rôles',
                'guard_name' => 'web'
            ],
            [
                'name' => 'roles_create',
                'category' => 'users',
                'description' => 'Créer des rôles',
                'guard_name' => 'web'
            ],
            [
                'name' => 'roles_edit',
                'category' => 'users',
                'description' => 'Modifier des rôles',
                'guard_name' => 'web'
            ],
            [
                'name' => 'roles_delete',
                'category' => 'users',
                'description' => 'Supprimer des rôles',
                'guard_name' => 'web'
            ],
            [
                'name' => 'permissions_view',
                'category' => 'users',
                'description' => 'Voir les permissions',
                'guard_name' => 'web'
            ],
            [
                'name' => 'permissions_create',
                'category' => 'users',
                'description' => 'Créer des permissions',
                'guard_name' => 'web'
            ],
            [
                'name' => 'permissions_assign',
                'category' => 'users',
                'description' => 'Assigner des permissions',
                'guard_name' => 'web'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createSettingsPermissions()
    {
        $permissions = [
            [
                'name' => 'settings_view',
                'category' => 'settings',
                'description' => 'Voir les paramètres',
                'guard_name' => 'web'
            ],
            [
                'name' => 'settings_manage',
                'category' => 'settings',
                'description' => 'Gérer les paramètres',
                'guard_name' => 'web'
            ],
            [
                'name' => 'settings_mail',
                'category' => 'settings',
                'description' => 'Gérer les paramètres courrier',
                'guard_name' => 'web'
            ],
            [
                'name' => 'settings_records',
                'category' => 'settings',
                'description' => 'Gérer les paramètres documents',
                'guard_name' => 'web'
            ],
            [
                'name' => 'settings_thesaurus',
                'category' => 'settings',
                'description' => 'Gérer le thésaurus',
                'guard_name' => 'web'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createSystemPermissions()
    {
        $permissions = [
            [
                'name' => 'system_view',
                'category' => 'system',
                'description' => 'Voir les informations système',
                'guard_name' => 'web'
            ],
            [
                'name' => 'system_manage',
                'category' => 'system',
                'description' => 'Gérer le système',
                'guard_name' => 'web'
            ],
            [
                'name' => 'system_logs',
                'category' => 'system',
                'description' => 'Voir les logs système',
                'guard_name' => 'web'
            ],
            [
                'name' => 'system_maintenance',
                'category' => 'system',
                'description' => 'Mode maintenance',
                'guard_name' => 'web'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createBackupsPermissions()
    {
        $permissions = [
            [
                'name' => 'backups_view',
                'category' => 'backups',
                'description' => 'Voir les sauvegardes',
                'guard_name' => 'web'
            ],
            [
                'name' => 'backups_create',
                'category' => 'backups',
                'description' => 'Créer des sauvegardes',
                'guard_name' => 'web'
            ],
            [
                'name' => 'backups_delete',
                'category' => 'backups',
                'description' => 'Supprimer des sauvegardes',
                'guard_name' => 'web'
            ],
            [
                'name' => 'backups_restore',
                'category' => 'backups',
                'description' => 'Restaurer des sauvegardes',
                'guard_name' => 'web'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function insertPermissions(array $permissions)
    {
        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                array_merge($permission, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
