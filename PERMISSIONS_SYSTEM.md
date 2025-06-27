# Système de Permissions - Application Shelves

## Vue d'ensemble

Le système de permissions de l'application Shelves utilise le package **Spatie Laravel Permission** pour gérer l'accès granulaire aux différents modules et fonctionnalités.

## Structure des Permissions

### 1. Permissions Métiers (222 permissions)

Les permissions métiers suivent le pattern : `{modèle}_{action}` où :
- **modèle** : Le nom de l'entité métier (user, role, organisation, activity, etc.)
- **action** : L'action autorisée (viewAny, view, create, update, delete, force_delete)

Exemples :
- `user_viewAny` : Voir tous les utilisateurs
- `user_create` : Créer un nouvel utilisateur
- `record_update` : Modifier un dossier/archive
- `mail_delete` : Supprimer un courrier

### 2. Permissions d'Accès aux Modules (21 permissions)

Les permissions d'accès contrôlent la visibilité des modules dans le menu principal :

| Module | Permission | Description |
|--------|------------|-------------|
| Tableaux d'affichage | `module_bulletin_boards_access` | Accès au module bulletins |
| Courrier | `module_mails_access` | Accès au module courrier |
| Dossiers/Archives | `module_repositories_access` | Accès aux dossiers et archives |
| Communications | `module_communications_access` | Accès aux communications |
| Transferts/Bordereaux | `module_transferrings_access` | Accès aux transferts |
| Bâtiments/Dépôts | `module_deposits_access` | Accès aux bâtiments |
| Outils/Activités | `module_tools_access` | Accès aux outils |
| Chariots | `module_dollies_access` | Accès aux chariots |
| Intelligence Artificielle | `module_ai_access` | Accès au module IA |
| Portail Public | `module_public_access` | Accès au portail public |
| Paramètres | `module_settings_access` | Accès aux paramètres |

### 3. Permissions Transversales (10 permissions)

- `module_users_access` : Gestion des utilisateurs
- `module_search_access` : Recherche globale
- `module_advanced_search_access` : Recherche avancée
- `module_org_switching_access` : Changement d'organisation
- `module_language_switching_access` : Changement de langue
- `module_profile_access` : Gestion du profil
- `module_navigation_access` : Navigation globale
- `module_import_export_access` : Import/Export

## Implémentation dans les Vues

### Layout Principal (app.blade.php)

Chaque élément du menu principal est protégé par une directive `@can` :

```blade
@can('module_mails_access')
<div class="header-nav-item">
    <a class="header-nav-link..." href="{{ route('mail-received.index') }}">
        <i class="bi bi-envelope"></i>
    </a>
</div>
@endcan
```

### Contrôleurs et Middleware

Les contrôleurs peuvent utiliser les permissions pour contrôler l'accès aux actions :

```php
// Dans un contrôleur
$this->authorize('mail_create');

// Ou avec des policies
Gate::authorize('create', Mail::class);
```

## Structure de la Base de Données

### Tables Principales

- **permissions** : Stocke toutes les permissions (243 au total)
- **roles** : Définit les rôles d'utilisateurs
- **model_has_permissions** : Relation directe utilisateur ↔ permission
- **model_has_roles** : Relation utilisateur ↔ rôle
- **role_has_permissions** : Relation rôle ↔ permissions

### Modèle User

Le modèle `User` utilise le trait `HasRoles` de Spatie :

```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
    
    // Méthodes personnalisées existantes...
}
```

## Utilisation

### Vérifier une Permission

```php
// Dans une vue Blade
@can('module_mails_access')
    <!-- Contenu visible seulement si autorisé -->
@endcan

// Dans un contrôleur
if (auth()->user()->can('mail_create')) {
    // Action autorisée
}
```

### Assigner des Permissions

```php
// Assigner une permission directement
$user->givePermissionTo('module_mails_access');

// Assigner un rôle avec permissions
$user->assignRole('administrateur');
```

## Migration et Seeding

### Seeder Principal

Le fichier `PermissionSeeder.php` contient toutes les permissions organisées par catégories :

```bash
php artisan db:seed --class=PermissionSeeder
```

### Structure des Permissions dans le Seeder

- `getUserManagementPermissions()` : Gestion utilisateurs/rôles/organisations
- `getContentManagementPermissions()` : Gestion des contenus métiers
- `getCommunicationPermissions()` : Communications et courrier
- `getLocationManagementPermissions()` : Bâtiments et localisations
- `getSystemManagementPermissions()` : Paramètres système
- `getPortalPermissions()` : Portail public
- `getTechnicalPermissions()` : Fonctionnalités techniques
- `getModuleAccessPermissions()` : **Accès aux modules du menu principal**

## Personnalisation et Extension

### Ajouter une Nouvelle Permission

1. Ajouter la permission dans le seeder approprié
2. Exécuter le seeder : `php artisan db:seed --class=PermissionSeeder`
3. Assigner la permission aux rôles concernés
4. Implémenter la vérification dans les vues/contrôleurs

### Créer un Nouveau Rôle

```php
$role = Role::create(['name' => 'nouvel_role']);
$role->givePermissionTo(['permission1', 'permission2']);
```

## Sécurité

- Toutes les permissions utilisent le guard `web`
- Les permissions sont vérifiées côté serveur (PHP) et côté client (Blade)
- Le système respecte la séparation des organisations (multi-tenant)
- Les permissions d'accès aux modules empêchent l'affichage des menus non autorisés

## Total des Permissions

- **Permissions métiers** : 222
- **Permissions modules** : 11
- **Permissions transversales** : 10
- **TOTAL** : 243 permissions
