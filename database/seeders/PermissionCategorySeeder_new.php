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
                'description' => 'Voir le tableau de bord'
            ],
            [
                'name' => 'dashboard_manage',
                'category' => 'dashboard',
                'description' => 'Gérer le tableau de bord'
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
                'description' => 'Voir les courriers'
            ],
            [
                'name' => 'mail_create',
                'category' => 'mail',
                'description' => 'Créer des courriers'
            ],
            [
                'name' => 'mail_edit',
                'category' => 'mail',
                'description' => 'Modifier des courriers'
            ],
            [
                'name' => 'mail_delete',
                'category' => 'mail',
                'description' => 'Supprimer des courriers'
            ],
            [
                'name' => 'mail_config',
                'category' => 'mail',
                'description' => 'Configurer les paramètres courrier'
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
                'description' => 'Voir les dossiers'
            ],
            [
                'name' => 'records_create',
                'category' => 'records',
                'description' => 'Créer des dossiers'
            ],
            [
                'name' => 'records_edit',
                'category' => 'records',
                'description' => 'Modifier des dossiers'
            ],
            [
                'name' => 'records_delete',
                'category' => 'records',
                'description' => 'Supprimer des dossiers'
            ],
            [
                'name' => 'records_export',
                'category' => 'records',
                'description' => 'Exporter des dossiers'
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
                'description' => 'Voir les communications'
            ],
            [
                'name' => 'communications_create',
                'category' => 'communications',
                'description' => 'Créer des communications'
            ],
            [
                'name' => 'communications_edit',
                'category' => 'communications',
                'description' => 'Modifier des communications'
            ],
            [
                'name' => 'communications_delete',
                'category' => 'communications',
                'description' => 'Supprimer des communications'
            ],
            [
                'name' => 'communications_send',
                'category' => 'communications',
                'description' => 'Envoyer des communications'
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
                'description' => 'Voir les réservations'
            ],
            [
                'name' => 'reservations_create',
                'category' => 'reservations',
                'description' => 'Créer des réservations'
            ],
            [
                'name' => 'reservations_edit',
                'category' => 'reservations',
                'description' => 'Modifier des réservations'
            ],
            [
                'name' => 'reservations_delete',
                'category' => 'reservations',
                'description' => 'Supprimer des réservations'
            ],
            [
                'name' => 'reservations_manage',
                'category' => 'reservations',
                'description' => 'Gérer les réservations'
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
                'description' => 'Voir les utilisateurs'
            ],
            [
                'name' => 'users_create',
                'category' => 'users',
                'description' => 'Créer des utilisateurs'
            ],
            [
                'name' => 'users_edit',
                'category' => 'users',
                'description' => 'Modifier des utilisateurs'
            ],
            [
                'name' => 'users_delete',
                'category' => 'users',
                'description' => 'Supprimer des utilisateurs'
            ],
            [
                'name' => 'users_manage',
                'category' => 'users',
                'description' => 'Gérer les utilisateurs'
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
                'description' => 'Voir les paramètres'
            ],
            [
                'name' => 'settings_edit',
                'category' => 'settings',
                'description' => 'Modifier les paramètres'
            ],
            [
                'name' => 'settings_manage',
                'category' => 'settings',
                'description' => 'Gérer les paramètres système'
            ],
            [
                'name' => 'settings_roles',
                'category' => 'settings',
                'description' => 'Gérer les rôles et permissions'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createSystemPermissions()
    {
        $permissions = [
            [
                'name' => 'system_logs',
                'category' => 'system',
                'description' => 'Voir les logs système'
            ],
            [
                'name' => 'system_maintenance',
                'category' => 'system',
                'description' => 'Effectuer la maintenance système'
            ],
            [
                'name' => 'system_monitoring',
                'category' => 'system',
                'description' => 'Surveiller le système'
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
                'description' => 'Voir les sauvegardes'
            ],
            [
                'name' => 'backups_create',
                'category' => 'backups',
                'description' => 'Créer des sauvegardes'
            ],
            [
                'name' => 'backups_delete',
                'category' => 'backups',
                'description' => 'Supprimer des sauvegardes'
            ],
            [
                'name' => 'backups_restore',
                'category' => 'backups',
                'description' => 'Restaurer des sauvegardes'
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
