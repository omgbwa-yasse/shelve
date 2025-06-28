<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PolicyService
{
    /**
     * Enregistrer toutes les policies via Gate
     * Cette méthode centralise la gestion des autorisations
     */
    public static function registerGates()
    {
        // 1. Gate pour vérifier si l'utilisateur est superadmin
        Gate::define('is-superadmin', function (User $user) {
            return $user->hasRole('superadmin');
        });

        // 2. Gate pour vérifier les permissions Spatie
        Gate::define('has-permission', function (User $user, string $permission) {
            if ($user->hasRole('superadmin')) {
                return true;
            }
            return $user->hasPermissionTo($permission);
        });

        // 3. Gate pour vérifier l'accès aux modules
        Gate::define('access-module', function (User $user, string $module) {
            if ($user->hasRole('superadmin')) {
                return true;
            }
            return $user->hasPermissionTo("access.{$module}");
        });

        // 4. Gate pour la gestion des utilisateurs
        Gate::define('manage-users', function (User $user) {
            if ($user->hasRole('superadmin')) {
                return true;
            }
            return $user->hasAnyPermission([
                'users.create',
                'users.update',
                'users.delete',
                'users.view'
            ]);
        });

        // 5. Gate pour la gestion des organisations
        Gate::define('manage-organisations', function (User $user) {
            if ($user->hasRole('superadmin')) {
                return true;
            }
            return $user->hasAnyPermission([
                'organisations.create',
                'organisations.update',
                'organisations.delete',
                'organisations.view'
            ]);
        });

        // 6. Gate pour l'accès à l'administration système
        Gate::define('access-admin', function (User $user) {
            if ($user->hasRole('superadmin')) {
                return true;
            }
            return $user->hasAnyPermission([
                'system.settings',
                'system.logs',
                'system.maintenance'
            ]);
        });

        // 7. Gate pour vérifier l'accès basé sur l'organisation
        Gate::define('access-in-organisation', function (User $user, $model) {
            if ($user->hasRole('superadmin')) {
                return true;
            }

            // Vérifier si l'utilisateur a une organisation courante
            if (!$user->currentOrganisation) {
                return false;
            }

            // Si le modèle a une relation avec l'organisation
            if (method_exists($model, 'organisations')) {
                return $model->organisations()->where('id', $user->current_organisation_id)->exists();
            }

            // Si le modèle a un champ organisation_id
            if (isset($model->organisation_id)) {
                return $model->organisation_id === $user->current_organisation_id;
            }

            return false;
        });

        // 8. Gate dynamique pour toutes les permissions existantes
        self::registerDynamicPermissionGates();
    }

    /**
     * Enregistrer des Gates dynamiques pour toutes les permissions
     */
    private static function registerDynamicPermissionGates()
    {
        // Récupérer toutes les permissions du système
        $permissions = Permission::where('guard_name', 'web')->get();

        foreach ($permissions as $permission) {
            Gate::define($permission->name, function (User $user) use ($permission) {
                if ($user->hasRole('superadmin')) {
                    return true;
                }
                return $user->hasPermissionTo($permission->name);
            });
        }
    }

    /**
     * Vérifier si un utilisateur peut effectuer une action sur un modèle
     */
    public static function canUserPerformAction(User $user, string $action, $model = null): bool
    {
        // Si c'est un superadmin, autoriser tout
        if (Gate::forUser($user)->allows('is-superadmin')) {
            return true;
        }

        // Vérifier la permission spécifique
        if (Gate::forUser($user)->allows($action)) {
            return true;
        }

        // Si un modèle est fourni, vérifier l'accès dans l'organisation
        if ($model && Gate::forUser($user)->allows('access-in-organisation', $model)) {
            return Gate::forUser($user)->allows($action);
        }

        return false;
    }

    /**
     * Obtenir toutes les permissions d'un utilisateur pour l'interface
     */
    public static function getUserPermissions(User $user): array
    {
        if ($user->hasRole('superadmin')) {
            return Permission::where('guard_name', 'web')->pluck('name')->toArray();
        }

        return $user->getAllPermissions()->pluck('name')->toArray();
    }

    /**
     * Vérifier si l'utilisateur peut accéder à une route spécifique
     */
    public static function canAccessRoute(User $user, string $routeName): bool
    {
        // Mapping des routes vers les permissions
        $routePermissions = [
            'admin.users.index' => 'users.view',
            'admin.users.create' => 'users.create',
            'admin.users.edit' => 'users.update',
            'admin.users.destroy' => 'users.delete',
            'admin.organisations.index' => 'organisations.view',
            'admin.organisations.create' => 'organisations.create',
            'admin.organisations.edit' => 'organisations.update',
            'admin.organisations.destroy' => 'organisations.delete',
            'admin.settings' => 'system.settings',
            'admin.logs' => 'system.logs',
            // Ajouter d'autres mappings selon vos besoins
        ];

        if (isset($routePermissions[$routeName])) {
            return Gate::forUser($user)->allows($routePermissions[$routeName]);
        }

        // Si la route n'est pas mappée, vérifier si c'est un superadmin
        return Gate::forUser($user)->allows('is-superadmin');
    }
}
