<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class SubmenuPermissions
{
    /**
     * Permissions pour le module Mails
     */
    public static function mailsPermissions(): array
    {
        return [
            // Section Recherche
            'search' => [
                'received_mails' => 'mail_view',
                'sent_mails' => 'mail_view',
                'archived_mails' => 'mail_view',
                'typologies' => 'mail_view',
                'dates' => 'mail_view',
                'archive_boxes' => 'mail_view',
                'my_paraphers' => 'mail_view',
                'advanced' => 'mail_view',
                'externe_sortant' => 'mail_view',
                'externe_entrant' => 'mail_view',
            ],
            // Section Ajout
            'add' => [
                'new_mail' => 'mail_create',
                'import_mail' => 'mail_create',
                'bulk_import' => 'mail_create',
            ],
            // Section Configuration
            'config' => [
                'typologies' => 'mail_config',
                'priorities' => 'mail_config',
                'templates' => 'mail_config',
            ],
            // Section Outils
            'tools' => [
                'export' => 'mail_export',
                'statistics' => 'mail_view',
                'audit' => 'mail_audit',
            ]
        ];
    }

    /**
     * Permissions pour le module Repositories
     */
    public static function repositoriesPermissions(): array
    {
        return [
            // Section Recherche
            'search' => [
                'my_archives' => 'records_view',
                'holders' => 'authors_view',
                'dates' => 'records_view',
                'keywords' => 'records_view',
                'activities' => 'records_view',
                'premises' => 'records_view',
                'recent' => 'records_view',
                'advanced_search' => 'records_view',
            ],
            // Section Ajout
            'add' => [
                'create_record' => 'records_create',
                'create_author' => 'authors_create',
            ],
            // Section Outils
            'tools' => [
                'import' => 'records_import',
                'export' => 'records_export',
            ],
            // Section MCP/IA
            'mcp' => [
                'enrich' => 'mcp_features',
                'extract_keywords' => 'mcp_features',
                'suggest_terms' => 'mcp_features',
                'validate' => 'mcp_features',
                'classify' => 'mcp_features',
                'report' => 'mcp_features',
            ]
        ];
    }

    /**
     * Permissions pour le module Communications
     */
    public static function communicationsPermissions(): array
    {
        return [
            // Section Recherche
            'search' => [
                'all_communications' => 'communication_view',
                'by_status' => 'communication_view',
                'by_date' => 'communication_view',
                'my_communications' => 'communication_view',
            ],
            // Section Ajout
            'add' => [
                'new_communication' => 'communication_create',
                'import_communications' => 'communication_create',
            ],
            // Section Configuration
            'config' => [
                'types' => 'communication_config',
                'statuses' => 'communication_config',
                'workflows' => 'communication_config',
            ],
            // Section Outils
            'tools' => [
                'export' => 'communication_export',
                'statistics' => 'communication_view',
            ]
        ];
    }

    /**
     * Permissions pour le module Deposits
     */
    public static function depositsPermissions(): array
    {
        return [
            // Section Recherche
            'search' => [
                'buildings' => 'deposits.view',
                'floors' => 'deposits.view',
                'rooms' => 'deposits.view',
                'shelves' => 'deposits.view',
                'containers' => 'deposits.view',
            ],
            // Section Ajout
            'add' => [
                'new_building' => 'deposits.create',
                'new_floor' => 'deposits.create',
                'new_room' => 'deposits.create',
                'new_shelf' => 'deposits.create',
                'new_container' => 'deposits.create',
            ],
            // Section Configuration
            'config' => [
                'building_types' => 'deposits.config',
                'room_types' => 'deposits.config',
                'shelf_types' => 'deposits.config',
            ],
            // Section Outils
            'tools' => [
                'capacity_report' => 'deposits.view',
                'occupancy_stats' => 'deposits.view',
                'maintenance' => 'deposits.manage',
            ]
        ];
    }

    /**
     * Permissions pour le module Transferrings
     */
    public static function transferringsPermissions(): array
    {
        return [
            // Section Recherche
            'search' => [
                'all_slips' => 'slips.view',
                'pending_slips' => 'slips.view',
                'completed_slips' => 'slips.view',
                'my_slips' => 'slips.view',
            ],
            // Section Ajout
            'add' => [
                'new_slip' => 'slips.create',
                'bulk_transfer' => 'slips.create',
            ],
            // Section Configuration
            'config' => [
                'transfer_types' => 'slips.config',
                'approval_workflows' => 'slips.config',
            ],
            // Section Cycle de vie
            'lifecycle' => [
                'tostore' => 'records_lifecycle',
                'toretain' => 'records_lifecycle',
                'totransfer' => 'records_lifecycle',
                'toeliminate' => 'records_lifecycle',
                'tokeep' => 'records_lifecycle',
                'tosort' => 'records_lifecycle',
            ],
            // Section Outils
            'tools' => [
                'export' => 'slips.export',
                'statistics' => 'slips.view',
            ]
        ];
    }

    /**
     * Permissions pour le module Settings
     */
    public static function settingsPermissions(): array
    {
        return [
            // Section Utilisateurs
            'users' => [
                'list_users' => 'users.view',
                'create_user' => 'users.create',
                'roles_permissions' => 'users.manage',
            ],
            // Section Organisations
            'organisations' => [
                'list_organisations' => 'organisations.view',
                'create_organisation' => 'organisations.create',
                'manage_organisations' => 'organisations.manage',
            ],
            // Section Système
            'system' => [
                'general_settings' => 'system.settings',
                'logs' => 'system.logs',
                'maintenance' => 'system.maintenance',
                'backups' => 'system.backups',
            ]
        ];
    }

    /**
     * Permissions pour le module AI
     */
    public static function aiPermissions(): array
    {
        return [
            // Section AI
            'ai' => [
                'chats' => 'ai.view',
                'interactions' => 'ai.view',
                'actions' => 'ai.view',
                'action_batches' => 'ai.view',
                'jobs' => 'ai.view',
                'feedback' => 'ai.view',
            ],
            // Section Configuration AI
            'config' => [
                'models' => 'ai.config',
                'action_types' => 'ai.config',
                'prompt_templates' => 'ai.config',
                'integrations' => 'ai.config',
                'training_data' => 'ai.config',
            ]
        ];
    }

    /**
     * Permissions pour le module Public
     */
    public static function publicPermissions(): array
    {
        return [
            // Section Utilisateurs publics
            'users' => [
                'list_users' => 'public.users.view',
                'create_user' => 'public.users.create',
            ],
            // Section Contenu public
            'content' => [
                'news' => 'public.content.manage',
                'pages' => 'public.content.manage',
                'templates' => 'public.content.manage',
                'events' => 'public.content.manage',
            ],
            // Section Documents
            'documents' => [
                'records' => 'records.view',
                'document_requests' => 'public.documents.view',
                'responses' => 'public.documents.view',
                'attachments' => 'public.documents.view',
            ],
            // Section Interaction
            'interaction' => [
                'chats' => 'public.interaction.manage',
                'participants' => 'public.interaction.manage',
                'registrations' => 'public.interaction.manage',
                'feedback' => 'public.interaction.manage',
                'search_logs' => 'public.interaction.manage',
            ]
        ];
    }

    /**
     * Permissions pour le module BulletinBoards
     */
    public static function bulletinBoardsPermissions(): array
    {
        return [
            // Section Recherche
            'search' => [
                'list_boards' => 'bulletinboards.view',
                'create_board' => 'bulletinboards.create',
            ]
        ];
    }

    /**
     * Permissions pour le module Dollies
     */
    public static function dolliesPermissions(): array
    {
        return [
            // Section Recherche
            'search' => [
                'all_carts' => 'dollies.view',
                'mail_carts' => 'dollies.view',
                'record_carts' => 'dollies.view',
                'communication_carts' => 'dollies.view',
                'room_carts' => 'dollies.view',
                'shelf_carts' => 'dollies.view',
                'container_carts' => 'dollies.view',
                'transfer_carts' => 'dollies.view',
            ],
            // Section Création
            'add' => [
                'create_cart' => 'dollies.create',
            ]
        ];
    }

    /**
     * Vérifier si l'utilisateur a une permission spécifique
     */
    public static function hasPermission(string $permission): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return Gate::allows($permission);
    }

    /**
     * Vérifier si l'utilisateur peut accéder à une section de sous-menu
     */
    public static function canAccessSubmenuSection(string $module, string $section): bool
    {
        $permissions = self::getModulePermissions($module);

        if (!isset($permissions[$section])) {
            return false;
        }

        foreach ($permissions[$section] as $permission) {
            if (self::hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifier si l'utilisateur peut accéder à un élément de sous-menu
     */
    public static function canAccessSubmenuItem(string $module, string $section, string $item): bool
    {
        $permissions = self::getModulePermissions($module);

        if (!isset($permissions[$section][$item])) {
            return false;
        }

        return self::hasPermission($permissions[$section][$item]);
    }

    /**
     * Obtenir les permissions d'un module
     */
    private static function getModulePermissions(string $module): array
    {
        return match($module) {
            'mails' => self::mailsPermissions(),
            'repositories' => self::repositoriesPermissions(),
            'communications' => self::communicationsPermissions(),
            'deposits' => self::depositsPermissions(),
            'transferrings' => self::transferringsPermissions(),
            'settings' => self::settingsPermissions(),
            'ai' => self::aiPermissions(),
            'public' => self::publicPermissions(),
            'bulletinboards' => self::bulletinBoardsPermissions(),
            'dollies' => self::dolliesPermissions(),
            default => []
        };
    }
}
