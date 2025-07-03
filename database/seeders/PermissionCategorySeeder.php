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
        $this->createModulePermissions();
        $this->createDashboardPermissions();
        $this->createMailPermissions();
        $this->createRecordsPermissions();
        $this->createCommunicationsPermissions();
        $this->createReservationsPermissions();
        $this->createUsersPermissions();
        $this->createSettingsPermissions();
        $this->createSystemPermissions();
        $this->createBackupsPermissions();
        $this->createWorkflowPermissions();
        $this->createAdditionalPermissions();

        $this->command->info('Permissions avec catégories créées avec succès!');
    }

    private function createModulePermissions()
    {
        $permissions = [
            [
                'name' => 'module_bulletin_boards_access',
                'category' => 'system',
                'description' => 'Accès au module Tableaux d\'affichage'
            ],
            [
                'name' => 'module_mails_access',
                'category' => 'system',
                'description' => 'Accès au module Courriers'
            ],
            [
                'name' => 'module_repositories_access',
                'category' => 'system',
                'description' => 'Accès au module Dépôts'
            ],
            [
                'name' => 'module_communications_access',
                'category' => 'system',
                'description' => 'Accès au module Communications'
            ],
            [
                'name' => 'module_transferrings_access',
                'category' => 'system',
                'description' => 'Accès au module Transferts'
            ],
            [
                'name' => 'module_deposits_access',
                'category' => 'system',
                'description' => 'Accès au module Dépôts physiques'
            ],
            [
                'name' => 'module_tools_access',
                'category' => 'system',
                'description' => 'Accès au module Outils'
            ],
            [
                'name' => 'module_dollies_access',
                'category' => 'system',
                'description' => 'Accès au module Chariots'
            ],
            [
                'name' => 'module_ai_access',
                'category' => 'system',
                'description' => 'Accès au module Intelligence Artificielle'
            ],
            [
                'name' => 'module_public_access',
                'category' => 'system',
                'description' => 'Accès au module Public'
            ],
            [
                'name' => 'module_settings_access',
                'category' => 'system',
                'description' => 'Accès au module Paramètres'
            ],
            [
                'name' => 'module_workflow_access',
                'category' => 'system',
                'description' => 'Accès au module Workflow'
            ],
        ];

        $this->insertPermissions($permissions);
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
            [
                'name' => 'records_import',
                'category' => 'records',
                'description' => 'Importer des dossiers'
            ],
            [
                'name' => 'records_search',
                'category' => 'records',
                'description' => 'Rechercher des dossiers'
            ],
            [
                'name' => 'records_lifecycle',
                'category' => 'records',
                'description' => 'Gérer le cycle de vie des dossiers'
            ],
            [
                'name' => 'authors_view',
                'category' => 'records',
                'description' => 'Voir les producteurs'
            ],
            [
                'name' => 'authors_create',
                'category' => 'records',
                'description' => 'Créer des producteurs'
            ],
            [
                'name' => 'mcp_features',
                'category' => 'records',
                'description' => 'Utiliser les fonctionnalités MCP/IA'
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

    private function createAdditionalPermissions()
    {
        $permissions = [
            // BulletinBoard permissions
            [
                'name' => 'bulletinboards_view',
                'category' => 'system',
                'description' => 'Voir les tableaux d\'affichage'
            ],
            [
                'name' => 'bulletinboards_create',
                'category' => 'system',
                'description' => 'Créer des tableaux d\'affichage'
            ],
            [
                'name' => 'bulletinboards_update',
                'category' => 'system',
                'description' => 'Modifier des tableaux d\'affichage'
            ],
            [
                'name' => 'bulletinboards_delete',
                'category' => 'system',
                'description' => 'Supprimer des tableaux d\'affichage'
            ],
            [
                'name' => 'bulletinboards_restore',
                'category' => 'system',
                'description' => 'Restaurer des tableaux d\'affichage'
            ],
            [
                'name' => 'bulletinboards_force_delete',
                'category' => 'system',
                'description' => 'Supprimer définitivement des tableaux d\'affichage'
            ],
            // Organisation permissions
            [
                'name' => 'organisations_view',
                'category' => 'organizations',
                'description' => 'Voir les organisations'
            ],
            [
                'name' => 'organisations_create',
                'category' => 'organizations',
                'description' => 'Créer des organisations'
            ],
            [
                'name' => 'organisations_update',
                'category' => 'organizations',
                'description' => 'Modifier des organisations'
            ],
            [
                'name' => 'organisations_delete',
                'category' => 'organizations',
                'description' => 'Supprimer des organisations'
            ],
            [
                'name' => 'organisations_force_delete',
                'category' => 'organizations',
                'description' => 'Supprimer définitivement des organisations'
            ],
            // Additional record permissions
            [
                'name' => 'records_update',
                'category' => 'records',
                'description' => 'Modifier des dossiers'
            ],
            [
                'name' => 'records_force_delete',
                'category' => 'records',
                'description' => 'Supprimer définitivement des dossiers'
            ],
            [
                'name' => 'records_archive',
                'category' => 'records',
                'description' => 'Archiver des dossiers'
            ],
            // Additional user permissions
            [
                'name' => 'users_update',
                'category' => 'users',
                'description' => 'Modifier des utilisateurs'
            ],
            [
                'name' => 'users_delete',
                'category' => 'users',
                'description' => 'Supprimer des utilisateurs'
            ],
            [
                'name' => 'users_force_delete',
                'category' => 'users',
                'description' => 'Supprimer définitivement des utilisateurs'
            ],
        ];

        $this->insertPermissions($permissions);
    }



    private function createWorkflowPermissions()
    {
        $permissions = [
            // Accès au tableau de bord workflow
            [
                'name' => 'workflow_dashboard',
                'category' => 'workflow',
                'description' => 'Accès au tableau de bord des workflows'
            ],

            // Permissions pour les modèles de workflow
            [
                'name' => 'workflow_template_viewAny',
                'category' => 'workflow',
                'description' => 'Voir tous les modèles de workflow'
            ],
            [
                'name' => 'workflow_template_view',
                'category' => 'workflow',
                'description' => 'Voir un modèle de workflow'
            ],
            [
                'name' => 'workflow_template_create',
                'category' => 'workflow',
                'description' => 'Créer des modèles de workflow'
            ],
            [
                'name' => 'workflow_template_update',
                'category' => 'workflow',
                'description' => 'Modifier des modèles de workflow'
            ],
            [
                'name' => 'workflow_template_delete',
                'category' => 'workflow',
                'description' => 'Supprimer des modèles de workflow'
            ],
            [
                'name' => 'workflow_template_duplicate',
                'category' => 'workflow',
                'description' => 'Dupliquer des modèles de workflow'
            ],

            // Permissions pour les étapes de workflow
            [
                'name' => 'workflow_step_viewAny',
                'category' => 'workflow',
                'description' => 'Voir toutes les étapes de workflow'
            ],
            [
                'name' => 'workflow_step_view',
                'category' => 'workflow',
                'description' => 'Voir une étape de workflow'
            ],
            [
                'name' => 'workflow_step_create',
                'category' => 'workflow',
                'description' => 'Créer des étapes de workflow'
            ],
            [
                'name' => 'workflow_step_update',
                'category' => 'workflow',
                'description' => 'Modifier des étapes de workflow'
            ],
            [
                'name' => 'workflow_step_delete',
                'category' => 'workflow',
                'description' => 'Supprimer des étapes de workflow'
            ],
            [
                'name' => 'workflow_step_reorder',
                'category' => 'workflow',
                'description' => 'Réorganiser les étapes de workflow'
            ],

            // Permissions pour les instances de workflow
            [
                'name' => 'workflow_instance_viewAny',
                'category' => 'workflow',
                'description' => 'Voir toutes les instances de workflow'
            ],
            [
                'name' => 'workflow_instance_view',
                'category' => 'workflow',
                'description' => 'Voir une instance de workflow'
            ],
            [
                'name' => 'workflow_instance_create',
                'category' => 'workflow',
                'description' => 'Créer des instances de workflow'
            ],
            [
                'name' => 'workflow_instance_update',
                'category' => 'workflow',
                'description' => 'Modifier des instances de workflow'
            ],
            [
                'name' => 'workflow_instance_delete',
                'category' => 'workflow',
                'description' => 'Supprimer des instances de workflow'
            ],
            [
                'name' => 'workflow_instance_start',
                'category' => 'workflow',
                'description' => 'Démarrer une instance de workflow'
            ],
            [
                'name' => 'workflow_instance_cancel',
                'category' => 'workflow',
                'description' => 'Annuler une instance de workflow'
            ],
            [
                'name' => 'workflow_instance_pause',
                'category' => 'workflow',
                'description' => 'Mettre en pause une instance de workflow'
            ],
            [
                'name' => 'workflow_instance_resume',
                'category' => 'workflow',
                'description' => 'Reprendre une instance de workflow en pause'
            ],

            // Permissions pour les instances d'étapes de workflow
            [
                'name' => 'workflow_step_instance_view',
                'category' => 'workflow',
                'description' => 'Voir une instance d\'étape de workflow'
            ],
            [
                'name' => 'workflow_step_instance_update',
                'category' => 'workflow',
                'description' => 'Mettre à jour une instance d\'étape de workflow'
            ],
            [
                'name' => 'workflow_step_instance_complete',
                'category' => 'workflow',
                'description' => 'Marquer une étape comme complétée'
            ],
            [
                'name' => 'workflow_step_instance_reject',
                'category' => 'workflow',
                'description' => 'Rejeter une étape de workflow'
            ],
            [
                'name' => 'workflow_step_instance_reassign',
                'category' => 'workflow',
                'description' => 'Réassigner une étape de workflow'
            ],

            // Permissions pour les tâches
            [
                'name' => 'task_viewAny',
                'category' => 'workflow',
                'description' => 'Voir toutes les tâches'
            ],
            [
                'name' => 'task_view',
                'category' => 'workflow',
                'description' => 'Voir une tâche'
            ],
            [
                'name' => 'task_viewOwn',
                'category' => 'workflow',
                'description' => 'Voir ses propres tâches'
            ],
            [
                'name' => 'task_create',
                'category' => 'workflow',
                'description' => 'Créer des tâches'
            ],
            [
                'name' => 'task_update',
                'category' => 'workflow',
                'description' => 'Modifier des tâches'
            ],
            [
                'name' => 'task_delete',
                'category' => 'workflow',
                'description' => 'Supprimer des tâches'
            ],
            [
                'name' => 'task_complete',
                'category' => 'workflow',
                'description' => 'Marquer une tâche comme terminée'
            ],

            // Permissions pour les commentaires de tâches
            [
                'name' => 'task_comment_viewAny',
                'category' => 'workflow',
                'description' => 'Voir tous les commentaires de tâches'
            ],
            [
                'name' => 'task_comment_create',
                'category' => 'workflow',
                'description' => 'Ajouter un commentaire à une tâche'
            ],
            [
                'name' => 'task_comment_update',
                'category' => 'workflow',
                'description' => 'Modifier un commentaire de tâche'
            ],
            [
                'name' => 'task_comment_delete',
                'category' => 'workflow',
                'description' => 'Supprimer un commentaire de tâche'
            ],

            // Permissions pour les assignations de tâches
            [
                'name' => 'task_assignment_viewAny',
                'category' => 'workflow',
                'description' => 'Voir toutes les assignations de tâches'
            ],
            [
                'name' => 'task_assignment_create',
                'category' => 'workflow',
                'description' => 'Assigner une tâche'
            ],
            [
                'name' => 'task_assignment_update',
                'category' => 'workflow',
                'description' => 'Modifier une assignation de tâche'
            ],
            [
                'name' => 'task_assignment_delete',
                'category' => 'workflow',
                'description' => 'Supprimer une assignation de tâche'
            ],

            // Permissions pour les notifications
            [
                'name' => 'notification_viewAny',
                'category' => 'workflow',
                'description' => 'Voir toutes les notifications'
            ],
            [
                'name' => 'notification_view',
                'category' => 'workflow',
                'description' => 'Voir une notification'
            ],
            [
                'name' => 'notification_mark_read',
                'category' => 'workflow',
                'description' => 'Marquer une notification comme lue'
            ],
            [
                'name' => 'notification_mark_unread',
                'category' => 'workflow',
                'description' => 'Marquer une notification comme non lue'
            ],
            [
                'name' => 'notification_delete',
                'category' => 'workflow',
                'description' => 'Supprimer une notification'
            ],

            // Permissions pour les notifications système
            [
                'name' => 'systemNotification_viewAny',
                'category' => 'workflow',
                'description' => 'Voir toutes les notifications système'
            ],
            [
                'name' => 'systemNotification_view',
                'category' => 'workflow',
                'description' => 'Voir une notification système'
            ],
            [
                'name' => 'systemNotification_create',
                'category' => 'workflow',
                'description' => 'Créer une notification système'
            ],
            [
                'name' => 'systemNotification_update',
                'category' => 'workflow',
                'description' => 'Modifier une notification système'
            ],
            [
                'name' => 'systemNotification_delete',
                'category' => 'workflow',
                'description' => 'Supprimer une notification système'
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
