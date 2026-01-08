<?php

namespace Database\Seeders\Settings;

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
        $this->createWorkPlacePermissions();
        $this->createAdditionalPermissions();
        $this->createLibraryPermissions();
        $this->createMuseumPermissions();
        $this->createPublicPermissions();

        $this->command->info('Permissions avec catÃ©gories crÃ©Ã©es avec succÃ¨s!');
    }

    private function createModulePermissions()
    {
        $permissions = [
            [
                'name' => 'module_bulletin_boards_access',
                'category' => 'system',
                'description' => 'AccÃ¨s au module Tableaux d\'affichage'
            ],
            [
                'name' => 'module_mails_access',
                'category' => 'system',
                'description' => 'AccÃ¨s au module Courriers'
            ],
            [
                'name' => 'module_repositories_access',
                'category' => 'system',
                'description' => 'AccÃ¨s au module DÃ©pÃ´ts'
            ],
            [
                'name' => 'module_communications_access',
                'category' => 'system',
                'description' => 'AccÃ¨s au module Communications'
            ],
            [
                'name' => 'module_transferrings_access',
                'category' => 'system',
                'description' => 'AccÃ¨s au module Transferts'
            ],
            [
                'name' => 'module_deposits_access',
                'category' => 'system',
                'description' => 'AccÃ¨s au module DÃ©pÃ´ts physiques'
            ],
            [
                'name' => 'module_tools_access',
                'category' => 'system',
                'description' => 'AccÃ¨s au module Outils'
            ],
            [
                'name' => 'module_dollies_access',
                'category' => 'system',
                'description' => 'AccÃ¨s au module Chariots'
            ],
            [
                'name' => 'module_ai_access',
                'category' => 'system',
                'description' => 'AccÃ¨s au module Intelligence Artificielle'
            ],
            [
                'name' => 'module_public_access',
                'category' => 'system',
                'description' => 'AccÃ¨s au module Public'
            ],
            [
                'name' => 'module_settings_access',
                'category' => 'system',
                'description' => 'AccÃ¨s au module ParamÃ¨tres'
            ],
            [
                'name' => 'module_workflow_access',
                'category' => 'system',
                'description' => 'AccÃ¨s au module Workflow'
            ],
            [
                'name' => 'module_workplace_access',
                'category' => 'system',
                'description' => 'AccÃ¨s au module WorkPlace (Espaces de travail)'
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
                'description' => 'GÃ©rer le tableau de bord'
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
                'description' => 'CrÃ©er des courriers'
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
                'description' => 'Configurer les paramÃ¨tres courrier'
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
                'description' => 'CrÃ©er des dossiers'
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
                'description' => 'GÃ©rer le cycle de vie des dossiers'
            ],
            [
                'name' => 'authors_view',
                'category' => 'records',
                'description' => 'Voir les producteurs'
            ],
            [
                'name' => 'authors_create',
                'category' => 'records',
                'description' => 'CrÃ©er des producteurs'
            ],
            [
                'name' => 'mcp_features',
                'category' => 'records',
                'description' => 'Utiliser les fonctionnalitÃ©s MCP/IA'
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
                'description' => 'CrÃ©er des communications'
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
                'description' => 'Voir les rÃ©servations'
            ],
            [
                'name' => 'reservations_create',
                'category' => 'reservations',
                'description' => 'CrÃ©er des rÃ©servations'
            ],
            [
                'name' => 'reservations_edit',
                'category' => 'reservations',
                'description' => 'Modifier des rÃ©servations'
            ],
            [
                'name' => 'reservations_delete',
                'category' => 'reservations',
                'description' => 'Supprimer des rÃ©servations'
            ],
            [
                'name' => 'reservations_manage',
                'category' => 'reservations',
                'description' => 'GÃ©rer les rÃ©servations'
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
                'description' => 'CrÃ©er des utilisateurs'
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
                'description' => 'GÃ©rer les utilisateurs'
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
                'description' => 'Voir les paramÃ¨tres'
            ],
            [
                'name' => 'settings_edit',
                'category' => 'settings',
                'description' => 'Modifier les paramÃ¨tres'
            ],
            [
                'name' => 'settings_manage',
                'category' => 'settings',
                'description' => 'GÃ©rer les paramÃ¨tres systÃ¨me'
            ],
            [
                'name' => 'settings_roles',
                'category' => 'settings',
                'description' => 'GÃ©rer les rÃ´les et permissions'
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
                'description' => 'Voir les logs systÃ¨me'
            ],
            [
                'name' => 'system_maintenance',
                'category' => 'system',
                'description' => 'Effectuer la maintenance systÃ¨me'
            ],
            [
                'name' => 'system_monitoring',
                'category' => 'system',
                'description' => 'Surveiller le systÃ¨me'
            ],
            [
                'name' => 'system_updates_view',
                'category' => 'system',
                'description' => 'Voir les mises Ã  jour systÃ¨me'
            ],
            [
                'name' => 'system_updates_check',
                'category' => 'system',
                'description' => 'VÃ©rifier les mises Ã  jour disponibles'
            ],
            [
                'name' => 'system_updates_install',
                'category' => 'system',
                'description' => 'Installer les mises Ã  jour systÃ¨me'
            ],
            [
                'name' => 'system_updates_rollback',
                'category' => 'system',
                'description' => 'Effectuer des rollbacks de versions'
            ],
            [
                'name' => 'system_updates_manage',
                'category' => 'system',
                'description' => 'GÃ©rer complÃ¨tement les mises Ã  jour systÃ¨me'
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
                'description' => 'CrÃ©er des sauvegardes'
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
                'description' => 'CrÃ©er des tableaux d\'affichage'
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
                'description' => 'Supprimer dÃ©finitivement des tableaux d\'affichage'
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
                'description' => 'CrÃ©er des organisations'
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
                'description' => 'Supprimer dÃ©finitivement des organisations'
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
                'description' => 'Supprimer dÃ©finitivement des dossiers'
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
                'description' => 'Supprimer dÃ©finitivement des utilisateurs'
            ],
        ];

        $this->insertPermissions($permissions);
    }



    private function createWorkflowPermissions()
    {
        $permissions = [
            // AccÃ¨s au tableau de bord workflow
            [
                'name' => 'workflow_dashboard',
                'category' => 'workflow',
                'description' => 'AccÃ¨s au tableau de bord des workflows'
            ],

            // Permissions pour les modÃ¨les de workflow
            [
                'name' => 'workflow_template_viewAny',
                'category' => 'workflow',
                'description' => 'Voir tous les modÃ¨les de workflow'
            ],
            [
                'name' => 'workflow_template_view',
                'category' => 'workflow',
                'description' => 'Voir un modÃ¨le de workflow'
            ],
            [
                'name' => 'workflow_template_create',
                'category' => 'workflow',
                'description' => 'CrÃ©er des modÃ¨les de workflow'
            ],
            [
                'name' => 'workflow_template_update',
                'category' => 'workflow',
                'description' => 'Modifier des modÃ¨les de workflow'
            ],
            [
                'name' => 'workflow_template_delete',
                'category' => 'workflow',
                'description' => 'Supprimer des modÃ¨les de workflow'
            ],
            [
                'name' => 'workflow_template_duplicate',
                'category' => 'workflow',
                'description' => 'Dupliquer des modÃ¨les de workflow'
            ],

            // Permissions pour les Ã©tapes de workflow
            [
                'name' => 'workflow_step_viewAny',
                'category' => 'workflow',
                'description' => 'Voir toutes les Ã©tapes de workflow'
            ],
            [
                'name' => 'workflow_step_view',
                'category' => 'workflow',
                'description' => 'Voir une Ã©tape de workflow'
            ],
            [
                'name' => 'workflow_step_create',
                'category' => 'workflow',
                'description' => 'CrÃ©er des Ã©tapes de workflow'
            ],
            [
                'name' => 'workflow_step_update',
                'category' => 'workflow',
                'description' => 'Modifier des Ã©tapes de workflow'
            ],
            [
                'name' => 'workflow_step_delete',
                'category' => 'workflow',
                'description' => 'Supprimer des Ã©tapes de workflow'
            ],
            [
                'name' => 'workflow_step_reorder',
                'category' => 'workflow',
                'description' => 'RÃ©organiser les Ã©tapes de workflow'
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
                'description' => 'CrÃ©er des instances de workflow'
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
                'description' => 'DÃ©marrer une instance de workflow'
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

            // Permissions pour les instances d'Ã©tapes de workflow
            [
                'name' => 'workflow_step_instance_view',
                'category' => 'workflow',
                'description' => 'Voir une instance d\'Ã©tape de workflow'
            ],
            [
                'name' => 'workflow_step_instance_update',
                'category' => 'workflow',
                'description' => 'Mettre Ã  jour une instance d\'Ã©tape de workflow'
            ],
            [
                'name' => 'workflow_step_instance_complete',
                'category' => 'workflow',
                'description' => 'Marquer une Ã©tape comme complÃ©tÃ©e'
            ],
            [
                'name' => 'workflow_step_instance_reject',
                'category' => 'workflow',
                'description' => 'Rejeter une Ã©tape de workflow'
            ],
            [
                'name' => 'workflow_step_instance_reassign',
                'category' => 'workflow',
                'description' => 'RÃ©assigner une Ã©tape de workflow'
            ],

            // Permissions pour les tÃ¢ches
            [
                'name' => 'task_viewAny',
                'category' => 'workflow',
                'description' => 'Voir toutes les tÃ¢ches'
            ],
            [
                'name' => 'task_view',
                'category' => 'workflow',
                'description' => 'Voir une tÃ¢che'
            ],
            [
                'name' => 'task_viewOwn',
                'category' => 'workflow',
                'description' => 'Voir ses propres tÃ¢ches'
            ],
            [
                'name' => 'task_create',
                'category' => 'workflow',
                'description' => 'CrÃ©er des tÃ¢ches'
            ],
            [
                'name' => 'task_update',
                'category' => 'workflow',
                'description' => 'Modifier des tÃ¢ches'
            ],
            [
                'name' => 'task_delete',
                'category' => 'workflow',
                'description' => 'Supprimer des tÃ¢ches'
            ],
            [
                'name' => 'task_complete',
                'category' => 'workflow',
                'description' => 'Marquer une tÃ¢che comme terminÃ©e'
            ],

            // Permissions pour les commentaires de tÃ¢ches
            [
                'name' => 'task_comment_viewAny',
                'category' => 'workflow',
                'description' => 'Voir tous les commentaires de tÃ¢ches'
            ],
            [
                'name' => 'task_comment_create',
                'category' => 'workflow',
                'description' => 'Ajouter un commentaire Ã  une tÃ¢che'
            ],
            [
                'name' => 'task_comment_update',
                'category' => 'workflow',
                'description' => 'Modifier un commentaire de tÃ¢che'
            ],
            [
                'name' => 'task_comment_delete',
                'category' => 'workflow',
                'description' => 'Supprimer un commentaire de tÃ¢che'
            ],

            // Permissions pour les assignations de tÃ¢ches
            [
                'name' => 'task_assignment_viewAny',
                'category' => 'workflow',
                'description' => 'Voir toutes les assignations de tÃ¢ches'
            ],
            [
                'name' => 'task_assignment_create',
                'category' => 'workflow',
                'description' => 'Assigner une tÃ¢che'
            ],
            [
                'name' => 'task_assignment_update',
                'category' => 'workflow',
                'description' => 'Modifier une assignation de tÃ¢che'
            ],
            [
                'name' => 'task_assignment_delete',
                'category' => 'workflow',
                'description' => 'Supprimer une assignation de tÃ¢che'
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

            // Permissions pour les notifications systÃ¨me
            [
                'name' => 'systemNotification_viewAny',
                'category' => 'workflow',
                'description' => 'Voir toutes les notifications systÃ¨me'
            ],
            [
                'name' => 'systemNotification_view',
                'category' => 'workflow',
                'description' => 'Voir une notification systÃ¨me'
            ],
            [
                'name' => 'systemNotification_create',
                'category' => 'workflow',
                'description' => 'CrÃ©er une notification systÃ¨me'
            ],
            [
                'name' => 'systemNotification_update',
                'category' => 'workflow',
                'description' => 'Modifier une notification systÃ¨me'
            ],
            [
                'name' => 'systemNotification_delete',
                'category' => 'workflow',
                'description' => 'Supprimer une notification systÃ¨me'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createWorkPlacePermissions()
    {
        $permissions = [
            // WorkPlace Module Access
            [
                'name' => 'workplace_access',
                'category' => 'workplace',
                'description' => 'AccÃ¨s au module WorkPlace'
            ],

            // WorkPlace CRUD Permissions
            [
                'name' => 'workplace_viewAny',
                'category' => 'workplace',
                'description' => 'Voir tous les espaces de travail'
            ],
            [
                'name' => 'workplace_view',
                'category' => 'workplace',
                'description' => 'Voir un espace de travail'
            ],
            [
                'name' => 'workplace_create',
                'category' => 'workplace',
                'description' => 'CrÃ©er des espaces de travail'
            ],
            [
                'name' => 'workplace_update',
                'category' => 'workplace',
                'description' => 'Modifier des espaces de travail'
            ],
            [
                'name' => 'workplace_delete',
                'category' => 'workplace',
                'description' => 'Supprimer des espaces de travail'
            ],
            [
                'name' => 'workplace_archive',
                'category' => 'workplace',
                'description' => 'Archiver des espaces de travail'
            ],
            [
                'name' => 'workplace_settings',
                'category' => 'workplace',
                'description' => 'GÃ©rer les paramÃ¨tres d\'un espace de travail'
            ],

            // WorkPlace Category Permissions
            [
                'name' => 'workplace_category_viewAny',
                'category' => 'workplace',
                'description' => 'Voir toutes les catÃ©gories de workplaces'
            ],
            [
                'name' => 'workplace_category_view',
                'category' => 'workplace',
                'description' => 'Voir une catÃ©gorie de workplace'
            ],
            [
                'name' => 'workplace_category_create',
                'category' => 'workplace',
                'description' => 'CrÃ©er des catÃ©gories de workplaces'
            ],
            [
                'name' => 'workplace_category_update',
                'category' => 'workplace',
                'description' => 'Modifier des catÃ©gories de workplaces'
            ],
            [
                'name' => 'workplace_category_delete',
                'category' => 'workplace',
                'description' => 'Supprimer des catÃ©gories de workplaces'
            ],

            // WorkPlace Member Permissions
            [
                'name' => 'workplace_member_viewAny',
                'category' => 'workplace',
                'description' => 'Voir tous les membres d\'un workplace'
            ],
            [
                'name' => 'workplace_member_view',
                'category' => 'workplace',
                'description' => 'Voir un membre d\'un workplace'
            ],
            [
                'name' => 'workplace_member_add',
                'category' => 'workplace',
                'description' => 'Ajouter des membres Ã  un workplace'
            ],
            [
                'name' => 'workplace_member_update',
                'category' => 'workplace',
                'description' => 'Modifier le rÃ´le/permissions d\'un membre'
            ],
            [
                'name' => 'workplace_member_remove',
                'category' => 'workplace',
                'description' => 'Retirer des membres d\'un workplace'
            ],
            [
                'name' => 'workplace_member_updatePermissions',
                'category' => 'workplace',
                'description' => 'Modifier les permissions d\'un membre'
            ],
            [
                'name' => 'workplace_member_updateNotifications',
                'category' => 'workplace',
                'description' => 'Modifier les prÃ©fÃ©rences de notification d\'un membre'
            ],

            // WorkPlace Invitation Permissions
            [
                'name' => 'workplace_invitation_viewAny',
                'category' => 'workplace',
                'description' => 'Voir toutes les invitations'
            ],
            [
                'name' => 'workplace_invitation_create',
                'category' => 'workplace',
                'description' => 'CrÃ©er des invitations'
            ],
            [
                'name' => 'workplace_invitation_cancel',
                'category' => 'workplace',
                'description' => 'Annuler des invitations'
            ],
            [
                'name' => 'workplace_invitation_resend',
                'category' => 'workplace',
                'description' => 'Renvoyer des invitations'
            ],

            // WorkPlace Content (Folders & Documents) Permissions
            [
                'name' => 'workplace_folder_viewAny',
                'category' => 'workplace',
                'description' => 'Voir tous les dossiers partagÃ©s'
            ],
            [
                'name' => 'workplace_folder_share',
                'category' => 'workplace',
                'description' => 'Partager des dossiers dans un workplace'
            ],
            [
                'name' => 'workplace_folder_unshare',
                'category' => 'workplace',
                'description' => 'Retirer le partage de dossiers'
            ],
            [
                'name' => 'workplace_folder_pin',
                'category' => 'workplace',
                'description' => 'Ã‰pingler/dÃ©sÃ©pingler des dossiers'
            ],
            [
                'name' => 'workplace_document_viewAny',
                'category' => 'workplace',
                'description' => 'Voir tous les documents partagÃ©s'
            ],
            [
                'name' => 'workplace_document_share',
                'category' => 'workplace',
                'description' => 'Partager des documents dans un workplace'
            ],
            [
                'name' => 'workplace_document_unshare',
                'category' => 'workplace',
                'description' => 'Retirer le partage de documents'
            ],
            [
                'name' => 'workplace_document_feature',
                'category' => 'workplace',
                'description' => 'Mettre en vedette des documents'
            ],

            // WorkPlace Activity Permissions
            [
                'name' => 'workplace_activity_viewAny',
                'category' => 'workplace',
                'description' => 'Voir toutes les activitÃ©s d\'un workplace'
            ],
            [
                'name' => 'workplace_activity_view',
                'category' => 'workplace',
                'description' => 'Voir une activitÃ© spÃ©cifique'
            ],

            // WorkPlace Bookmark Permissions
            [
                'name' => 'workplace_bookmark_viewAny',
                'category' => 'workplace',
                'description' => 'Voir tous ses favoris'
            ],
            [
                'name' => 'workplace_bookmark_create',
                'category' => 'workplace',
                'description' => 'CrÃ©er des favoris'
            ],
            [
                'name' => 'workplace_bookmark_delete',
                'category' => 'workplace',
                'description' => 'Supprimer des favoris'
            ],

            // WorkPlace Template Permissions
            [
                'name' => 'workplace_template_viewAny',
                'category' => 'workplace',
                'description' => 'Voir tous les modÃ¨les de workplaces'
            ],
            [
                'name' => 'workplace_template_view',
                'category' => 'workplace',
                'description' => 'Voir un modÃ¨le de workplace'
            ],
            [
                'name' => 'workplace_template_create',
                'category' => 'workplace',
                'description' => 'CrÃ©er des modÃ¨les de workplaces'
            ],
            [
                'name' => 'workplace_template_update',
                'category' => 'workplace',
                'description' => 'Modifier des modÃ¨les de workplaces'
            ],
            [
                'name' => 'workplace_template_delete',
                'category' => 'workplace',
                'description' => 'Supprimer des modÃ¨les de workplaces'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createPublicPermissions()
    {
        $this->createPublicGeneralPermissions();
        $this->createPublicPagesPermissions();
        $this->createPublicNewsPermissions();
        $this->createPublicEventsPermissions();
        $this->createPublicUsersPermissions();
        $this->createPublicTemplatesPermissions();
        $this->createPublicDocumentPermissions();
        $this->createPublicChatPermissions();
        $this->createPublicOpacConfigPermissions();
    }

    private function createLibraryPermissions()
    {
        $permissions = [
            // Library Module Access
            [
                'name' => 'library_access',
                'category' => 'library',
                'description' => 'AccÃ¨s au module Library'
            ],

            // Books Permissions
            [
                'name' => 'books_view',
                'category' => 'library',
                'description' => 'Voir les livres'
            ],
            [
                'name' => 'books_create',
                'category' => 'library',
                'description' => 'CrÃ©er des livres'
            ],
            [
                'name' => 'books_update',
                'category' => 'library',
                'description' => 'Modifier des livres'
            ],
            [
                'name' => 'books_delete',
                'category' => 'library',
                'description' => 'Supprimer des livres'
            ],
            [
                'name' => 'books_import',
                'category' => 'library',
                'description' => 'Importer des livres'
            ],
            [
                'name' => 'books_export',
                'category' => 'library',
                'description' => 'Exporter des livres'
            ],
            [
                'name' => 'books_search',
                'category' => 'library',
                'description' => 'Rechercher des livres'
            ],

            // Book Copies Permissions
            [
                'name' => 'book_copies_view',
                'category' => 'library',
                'description' => 'Voir les exemplaires de livres'
            ],
            [
                'name' => 'book_copies_create',
                'category' => 'library',
                'description' => 'CrÃ©er des exemplaires de livres'
            ],
            [
                'name' => 'book_copies_update',
                'category' => 'library',
                'description' => 'Modifier des exemplaires de livres'
            ],
            [
                'name' => 'book_copies_delete',
                'category' => 'library',
                'description' => 'Supprimer des exemplaires de livres'
            ],
            [
                'name' => 'book_copies_manage',
                'category' => 'library',
                'description' => 'GÃ©rer tous les exemplaires'
            ],

            // Book Loans Permissions
            [
                'name' => 'book_loans_view',
                'category' => 'library',
                'description' => 'Voir les prÃªts de livres'
            ],
            [
                'name' => 'book_loans_create',
                'category' => 'library',
                'description' => 'CrÃ©er des prÃªts de livres'
            ],
            [
                'name' => 'book_loans_update',
                'category' => 'library',
                'description' => 'Modifier des prÃªts de livres'
            ],
            [
                'name' => 'book_loans_delete',
                'category' => 'library',
                'description' => 'Supprimer des prÃªts de livres'
            ],
            [
                'name' => 'book_loans_manage',
                'category' => 'library',
                'description' => 'GÃ©rer tous les prÃªts'
            ],
            [
                'name' => 'book_loans_renew',
                'category' => 'library',
                'description' => 'Renouveler des prÃªts'
            ],
            [
                'name' => 'book_loans_return',
                'category' => 'library',
                'description' => 'Enregistrer les retours de prÃªts'
            ],

            // Book Reservations Permissions
            [
                'name' => 'book_reservations_view',
                'category' => 'library',
                'description' => 'Voir les rÃ©servations de livres'
            ],
            [
                'name' => 'book_reservations_create',
                'category' => 'library',
                'description' => 'CrÃ©er des rÃ©servations de livres'
            ],
            [
                'name' => 'book_reservations_update',
                'category' => 'library',
                'description' => 'Modifier des rÃ©servations de livres'
            ],
            [
                'name' => 'book_reservations_delete',
                'category' => 'library',
                'description' => 'Supprimer des rÃ©servations de livres'
            ],
            [
                'name' => 'book_reservations_manage',
                'category' => 'library',
                'description' => 'GÃ©rer toutes les rÃ©servations'
            ],
            [
                'name' => 'book_reservations_fulfill',
                'category' => 'library',
                'description' => 'Honorer des rÃ©servations'
            ],

            // Periodicals Permissions
            [
                'name' => 'periodics_view',
                'category' => 'library',
                'description' => 'Voir les publications pÃ©riodiques'
            ],
            [
                'name' => 'periodics_create',
                'category' => 'library',
                'description' => 'CrÃ©er des publications pÃ©riodiques'
            ],
            [
                'name' => 'periodics_update',
                'category' => 'library',
                'description' => 'Modifier des publications pÃ©riodiques'
            ],
            [
                'name' => 'periodics_delete',
                'category' => 'library',
                'description' => 'Supprimer des publications pÃ©riodiques'
            ],

            // Periodic Issues Permissions
            [
                'name' => 'periodic_issues_view',
                'category' => 'library',
                'description' => 'Voir les numÃ©ros de pÃ©riodiques'
            ],
            [
                'name' => 'periodic_issues_create',
                'category' => 'library',
                'description' => 'CrÃ©er des numÃ©ros de pÃ©riodiques'
            ],
            [
                'name' => 'periodic_issues_update',
                'category' => 'library',
                'description' => 'Modifier des numÃ©ros de pÃ©riodiques'
            ],
            [
                'name' => 'periodic_issues_delete',
                'category' => 'library',
                'description' => 'Supprimer des numÃ©ros de pÃ©riodiques'
            ],

            // Periodic Articles Permissions
            [
                'name' => 'periodic_articles_view',
                'category' => 'library',
                'description' => 'Voir les articles de pÃ©riodiques'
            ],
            [
                'name' => 'periodic_articles_create',
                'category' => 'library',
                'description' => 'CrÃ©er des articles de pÃ©riodiques'
            ],
            [
                'name' => 'periodic_articles_update',
                'category' => 'library',
                'description' => 'Modifier des articles de pÃ©riodiques'
            ],
            [
                'name' => 'periodic_articles_delete',
                'category' => 'library',
                'description' => 'Supprimer des articles de pÃ©riodiques'
            ],

            // Periodic Subscriptions Permissions
            [
                'name' => 'periodic_subscriptions_view',
                'category' => 'library',
                'description' => 'Voir les abonnements aux pÃ©riodiques'
            ],
            [
                'name' => 'periodic_subscriptions_create',
                'category' => 'library',
                'description' => 'CrÃ©er des abonnements aux pÃ©riodiques'
            ],
            [
                'name' => 'periodic_subscriptions_update',
                'category' => 'library',
                'description' => 'Modifier des abonnements aux pÃ©riodiques'
            ],
            [
                'name' => 'periodic_subscriptions_delete',
                'category' => 'library',
                'description' => 'Supprimer des abonnements aux pÃ©riodiques'
            ],

            // Library Reports & Statistics
            [
                'name' => 'library_reports_view',
                'category' => 'library',
                'description' => 'Voir les rapports de bibliothÃ¨que'
            ],
            [
                'name' => 'library_statistics_view',
                'category' => 'library',
                'description' => 'Voir les statistiques de bibliothÃ¨que'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createMuseumPermissions()
    {
        $permissions = [
            // Museum Module Access
            [
                'name' => 'museum_access',
                'category' => 'museum',
                'description' => 'AccÃ¨s au module Museum'
            ],

            // Artifacts Permissions
            [
                'name' => 'artifacts_view',
                'category' => 'museum',
                'description' => 'Voir les objets de musÃ©e'
            ],
            [
                'name' => 'artifacts_create',
                'category' => 'museum',
                'description' => 'CrÃ©er des objets de musÃ©e'
            ],
            [
                'name' => 'artifacts_update',
                'category' => 'museum',
                'description' => 'Modifier des objets de musÃ©e'
            ],
            [
                'name' => 'artifacts_delete',
                'category' => 'museum',
                'description' => 'Supprimer des objets de musÃ©e'
            ],
            [
                'name' => 'artifacts_search',
                'category' => 'museum',
                'description' => 'Rechercher des objets de musÃ©e'
            ],
            [
                'name' => 'artifacts_import',
                'category' => 'museum',
                'description' => 'Importer des objets de musÃ©e'
            ],
            [
                'name' => 'artifacts_export',
                'category' => 'museum',
                'description' => 'Exporter des objets de musÃ©e'
            ],

            // Artifact Exhibitions Permissions
            [
                'name' => 'artifact_exhibitions_view',
                'category' => 'museum',
                'description' => 'Voir les expositions'
            ],
            [
                'name' => 'artifact_exhibitions_create',
                'category' => 'museum',
                'description' => 'CrÃ©er des expositions'
            ],
            [
                'name' => 'artifact_exhibitions_update',
                'category' => 'museum',
                'description' => 'Modifier des expositions'
            ],
            [
                'name' => 'artifact_exhibitions_delete',
                'category' => 'museum',
                'description' => 'Supprimer des expositions'
            ],
            [
                'name' => 'artifact_exhibitions_manage',
                'category' => 'museum',
                'description' => 'GÃ©rer toutes les expositions'
            ],

            // Artifact Loans Permissions
            [
                'name' => 'artifact_loans_view',
                'category' => 'museum',
                'description' => 'Voir les prÃªts d\'objets'
            ],
            [
                'name' => 'artifact_loans_create',
                'category' => 'museum',
                'description' => 'CrÃ©er des prÃªts d\'objets'
            ],
            [
                'name' => 'artifact_loans_update',
                'category' => 'museum',
                'description' => 'Modifier des prÃªts d\'objets'
            ],
            [
                'name' => 'artifact_loans_delete',
                'category' => 'museum',
                'description' => 'Supprimer des prÃªts d\'objets'
            ],
            [
                'name' => 'artifact_loans_manage',
                'category' => 'museum',
                'description' => 'GÃ©rer tous les prÃªts d\'objets'
            ],
            [
                'name' => 'artifact_loans_return',
                'category' => 'museum',
                'description' => 'Enregistrer les retours de prÃªts'
            ],

            // Artifact Condition Reports Permissions
            [
                'name' => 'artifact_condition_reports_view',
                'category' => 'museum',
                'description' => 'Voir les rapports de conservation'
            ],
            [
                'name' => 'artifact_condition_reports_create',
                'category' => 'museum',
                'description' => 'CrÃ©er des rapports de conservation'
            ],
            [
                'name' => 'artifact_condition_reports_update',
                'category' => 'museum',
                'description' => 'Modifier des rapports de conservation'
            ],
            [
                'name' => 'artifact_condition_reports_delete',
                'category' => 'museum',
                'description' => 'Supprimer des rapports de conservation'
            ],
            [
                'name' => 'artifact_condition_reports_manage',
                'category' => 'museum',
                'description' => 'GÃ©rer tous les rapports de conservation'
            ],

            // Artifact Status Management
            [
                'name' => 'artifacts_status_change',
                'category' => 'museum',
                'description' => 'Changer le statut des objets'
            ],
            [
                'name' => 'artifacts_location_change',
                'category' => 'museum',
                'description' => 'Changer l\'emplacement des objets'
            ],
            [
                'name' => 'artifacts_restoration',
                'category' => 'museum',
                'description' => 'GÃ©rer la restauration des objets'
            ],

            // Museum Reports & Statistics
            [
                'name' => 'museum_reports_view',
                'category' => 'museum',
                'description' => 'Voir les rapports du musÃ©e'
            ],
            [
                'name' => 'museum_statistics_view',
                'category' => 'museum',
                'description' => 'Voir les statistiques du musÃ©e'
            ],
            [
                'name' => 'museum_inventory',
                'category' => 'museum',
                'description' => 'GÃ©rer l\'inventaire du musÃ©e'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createPublicGeneralPermissions()
    {
        $permissions = [
            [
                'name' => 'public.access',
                'category' => 'public',
                'description' => 'AccÃ¨s au module public/OPAC'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createPublicPagesPermissions()
    {
        $permissions = [
            [
                'name' => 'public.pages.view',
                'category' => 'public',
                'description' => 'Voir les pages publiques'
            ],
            [
                'name' => 'public.pages.create',
                'category' => 'public',
                'description' => 'CrÃ©er des pages publiques'
            ],
            [
                'name' => 'public.pages.edit',
                'category' => 'public',
                'description' => 'Modifier des pages publiques'
            ],
            [
                'name' => 'public.pages.delete',
                'category' => 'public',
                'description' => 'Supprimer des pages publiques'
            ],
            [
                'name' => 'public.pages.manage',
                'category' => 'public',
                'description' => 'GÃ©rer toutes les pages publiques'
            ],
            [
                'name' => 'public.pages.bulk_action',
                'category' => 'public',
                'description' => 'Actions en lot sur les pages publiques'
            ],
            [
                'name' => 'public.pages.reorder',
                'category' => 'public',
                'description' => 'RÃ©organiser l\'ordre des pages publiques'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createPublicNewsPermissions()
    {
        $permissions = [
            [
                'name' => 'public.news.view',
                'category' => 'public',
                'description' => 'Voir les actualitÃ©s publiques'
            ],
            [
                'name' => 'public.news.create',
                'category' => 'public',
                'description' => 'CrÃ©er des actualitÃ©s publiques'
            ],
            [
                'name' => 'public.news.edit',
                'category' => 'public',
                'description' => 'Modifier des actualitÃ©s publiques'
            ],
            [
                'name' => 'public.news.delete',
                'category' => 'public',
                'description' => 'Supprimer des actualitÃ©s publiques'
            ],
            [
                'name' => 'public.news.manage',
                'category' => 'public',
                'description' => 'GÃ©rer toutes les actualitÃ©s publiques'
            ],
            [
                'name' => 'public.news.publish',
                'category' => 'public',
                'description' => 'Publier/DÃ©publier des actualitÃ©s'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createPublicEventsPermissions()
    {
        $permissions = [
            [
                'name' => 'public.events.view',
                'category' => 'public',
                'description' => 'Voir les Ã©vÃ©nements publics'
            ],
            [
                'name' => 'public.events.create',
                'category' => 'public',
                'description' => 'CrÃ©er des Ã©vÃ©nements publics'
            ],
            [
                'name' => 'public.events.edit',
                'category' => 'public',
                'description' => 'Modifier des Ã©vÃ©nements publics'
            ],
            [
                'name' => 'public.events.delete',
                'category' => 'public',
                'description' => 'Supprimer des Ã©vÃ©nements publics'
            ],
            [
                'name' => 'public.events.manage',
                'category' => 'public',
                'description' => 'GÃ©rer tous les Ã©vÃ©nements publics'
            ],
            [
                'name' => 'public.events.registrations',
                'category' => 'public',
                'description' => 'GÃ©rer les inscriptions aux Ã©vÃ©nements'
            ],
            [
                'name' => 'public.events.bulk_action',
                'category' => 'public',
                'description' => 'Actions en lot sur les Ã©vÃ©nements'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createPublicUsersPermissions()
    {
        $permissions = [
            [
                'name' => 'public.users.view',
                'category' => 'public',
                'description' => 'Voir les utilisateurs publics'
            ],
            [
                'name' => 'public.users.create',
                'category' => 'public',
                'description' => 'CrÃ©er des utilisateurs publics'
            ],
            [
                'name' => 'public.users.edit',
                'category' => 'public',
                'description' => 'Modifier des utilisateurs publics'
            ],
            [
                'name' => 'public.users.delete',
                'category' => 'public',
                'description' => 'Supprimer des utilisateurs publics'
            ],
            [
                'name' => 'public.users.manage',
                'category' => 'public',
                'description' => 'GÃ©rer tous les utilisateurs publics'
            ],
            [
                'name' => 'public.users.activate',
                'category' => 'public',
                'description' => 'Activer/DÃ©sactiver des utilisateurs publics'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createPublicTemplatesPermissions()
    {
        $permissions = [
            [
                'name' => 'public.templates.view',
                'category' => 'public',
                'description' => 'Voir les templates OPAC'
            ],
            [
                'name' => 'public.templates.create',
                'category' => 'public',
                'description' => 'CrÃ©er des templates OPAC'
            ],
            [
                'name' => 'public.templates.edit',
                'category' => 'public',
                'description' => 'Modifier des templates OPAC'
            ],
            [
                'name' => 'public.templates.delete',
                'category' => 'public',
                'description' => 'Supprimer des templates OPAC'
            ],
            [
                'name' => 'public.templates.manage',
                'category' => 'public',
                'description' => 'GÃ©rer tous les templates OPAC'
            ],
            [
                'name' => 'public.templates.preview',
                'category' => 'public',
                'description' => 'PrÃ©visualiser les templates OPAC'
            ],
            [
                'name' => 'public.templates.duplicate',
                'category' => 'public',
                'description' => 'Dupliquer des templates OPAC'
            ],
            [
                'name' => 'public.templates.export',
                'category' => 'public',
                'description' => 'Exporter des templates OPAC'
            ],
            [
                'name' => 'public.templates.import',
                'category' => 'public',
                'description' => 'Importer des templates OPAC'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createPublicDocumentPermissions()
    {
        $permissions = [
            // Document Requests
            [
                'name' => 'public.document_requests.view',
                'category' => 'public',
                'description' => 'Voir les demandes de documents'
            ],
            [
                'name' => 'public.document_requests.create',
                'category' => 'public',
                'description' => 'CrÃ©er des demandes de documents'
            ],
            [
                'name' => 'public.document_requests.edit',
                'category' => 'public',
                'description' => 'Modifier des demandes de documents'
            ],
            [
                'name' => 'public.document_requests.delete',
                'category' => 'public',
                'description' => 'Supprimer des demandes de documents'
            ],
            [
                'name' => 'public.document_requests.manage',
                'category' => 'public',
                'description' => 'GÃ©rer toutes les demandes de documents'
            ],
            // Records
            [
                'name' => 'public.records.view',
                'category' => 'public',
                'description' => 'Voir les documents publics'
            ],
            [
                'name' => 'public.records.create',
                'category' => 'public',
                'description' => 'CrÃ©er des documents publics'
            ],
            [
                'name' => 'public.records.edit',
                'category' => 'public',
                'description' => 'Modifier des documents publics'
            ],
            [
                'name' => 'public.records.delete',
                'category' => 'public',
                'description' => 'Supprimer des documents publics'
            ],
            [
                'name' => 'public.records.manage',
                'category' => 'public',
                'description' => 'GÃ©rer tous les documents publics'
            ],
            [
                'name' => 'public.records.autocomplete',
                'category' => 'public',
                'description' => 'AccÃ¨s Ã  l\'autocomplÃ©tion des documents'
            ],
            // Responses
            [
                'name' => 'public.responses.view',
                'category' => 'public',
                'description' => 'Voir les rÃ©ponses publiques'
            ],
            [
                'name' => 'public.responses.create',
                'category' => 'public',
                'description' => 'CrÃ©er des rÃ©ponses publiques'
            ],
            [
                'name' => 'public.responses.edit',
                'category' => 'public',
                'description' => 'Modifier des rÃ©ponses publiques'
            ],
            [
                'name' => 'public.responses.delete',
                'category' => 'public',
                'description' => 'Supprimer des rÃ©ponses publiques'
            ],
            [
                'name' => 'public.responses.manage',
                'category' => 'public',
                'description' => 'GÃ©rer toutes les rÃ©ponses publiques'
            ],
            // Response Attachments
            [
                'name' => 'public.response_attachments.view',
                'category' => 'public',
                'description' => 'Voir les piÃ¨ces jointes des rÃ©ponses'
            ],
            [
                'name' => 'public.response_attachments.create',
                'category' => 'public',
                'description' => 'CrÃ©er des piÃ¨ces jointes des rÃ©ponses'
            ],
            [
                'name' => 'public.response_attachments.edit',
                'category' => 'public',
                'description' => 'Modifier des piÃ¨ces jointes des rÃ©ponses'
            ],
            [
                'name' => 'public.response_attachments.delete',
                'category' => 'public',
                'description' => 'Supprimer des piÃ¨ces jointes des rÃ©ponses'
            ],
            [
                'name' => 'public.response_attachments.manage',
                'category' => 'public',
                'description' => 'GÃ©rer toutes les piÃ¨ces jointes des rÃ©ponses'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createPublicChatPermissions()
    {
        $permissions = [
            // Chats
            [
                'name' => 'public.chats.view',
                'category' => 'public',
                'description' => 'Voir les discussions publiques'
            ],
            [
                'name' => 'public.chats.create',
                'category' => 'public',
                'description' => 'CrÃ©er des discussions publiques'
            ],
            [
                'name' => 'public.chats.edit',
                'category' => 'public',
                'description' => 'Modifier des discussions publiques'
            ],
            [
                'name' => 'public.chats.delete',
                'category' => 'public',
                'description' => 'Supprimer des discussions publiques'
            ],
            [
                'name' => 'public.chats.manage',
                'category' => 'public',
                'description' => 'GÃ©rer toutes les discussions publiques'
            ],
            // Chat Messages
            [
                'name' => 'public.chat_messages.view',
                'category' => 'public',
                'description' => 'Voir les messages de discussion'
            ],
            [
                'name' => 'public.chat_messages.create',
                'category' => 'public',
                'description' => 'CrÃ©er des messages de discussion'
            ],
            [
                'name' => 'public.chat_messages.edit',
                'category' => 'public',
                'description' => 'Modifier des messages de discussion'
            ],
            [
                'name' => 'public.chat_messages.delete',
                'category' => 'public',
                'description' => 'Supprimer des messages de discussion'
            ],
            [
                'name' => 'public.chat_messages.manage',
                'category' => 'public',
                'description' => 'GÃ©rer tous les messages de discussion'
            ],
            // Chat Participants
            [
                'name' => 'public.chat_participants.view',
                'category' => 'public',
                'description' => 'Voir les participants des discussions'
            ],
            [
                'name' => 'public.chat_participants.create',
                'category' => 'public',
                'description' => 'Ajouter des participants aux discussions'
            ],
            [
                'name' => 'public.chat_participants.edit',
                'category' => 'public',
                'description' => 'Modifier les participants des discussions'
            ],
            [
                'name' => 'public.chat_participants.delete',
                'category' => 'public',
                'description' => 'Retirer des participants des discussions'
            ],
            [
                'name' => 'public.chat_participants.manage',
                'category' => 'public',
                'description' => 'GÃ©rer tous les participants des discussions'
            ],
        ];

        $this->insertPermissions($permissions);
    }

    private function createPublicOpacConfigPermissions()
    {
        $permissions = [
            [
                'name' => 'public.opac.config.view',
                'category' => 'public',
                'description' => 'Voir la configuration OPAC'
            ],
            [
                'name' => 'public.opac.config.edit',
                'category' => 'public',
                'description' => 'Modifier la configuration OPAC'
            ],
            [
                'name' => 'public.opac.config.manage',
                'category' => 'public',
                'description' => 'GÃ©rer complÃ¨tement la configuration OPAC'
            ],
            [
                'name' => 'public.opac.config.export',
                'category' => 'public',
                'description' => 'Exporter la configuration OPAC'
            ],
            [
                'name' => 'public.opac.config.import',
                'category' => 'public',
                'description' => 'Importer la configuration OPAC'
            ],
            [
                'name' => 'public.opac.config.reset',
                'category' => 'public',
                'description' => 'RÃ©initialiser la configuration OPAC'
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

