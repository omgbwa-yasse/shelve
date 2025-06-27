# Permission Seeders

Ce document explique comment utiliser les seeders de permissions créés pour votre application Laravel.

## Fichiers créés

1. **PermissionSeeder.php** - Seeder principal qui remplace toutes les permissions
2. **PermissionUpdateSeeder.php** - Seeder de mise à jour qui préserve les données existantes
3. **SeedPermissions.php** - Commande Artisan personnalisée pour faciliter l'utilisation

## Utilisation

### Option 1: Utiliser les seeders directement

#### Seeder complet (remplace toutes les permissions)
```bash
php artisan db:seed --class=PermissionSeeder
```

#### Seeder de mise à jour (préserve les données existantes)
```bash
php artisan db:seed --class=PermissionUpdateSeeder
```

### Option 2: Utiliser la commande Artisan personnalisée

#### Mise à jour des permissions (préserve les données existantes)
```bash
php artisan seed:permissions
```

#### Remplacement complet des permissions
```bash
php artisan seed:permissions --fresh
```

### Option 3: Inclure dans le seeder principal

Le `PermissionSeeder` est automatiquement inclus dans le `DatabaseSeeder.php`, donc il sera exécuté lors du seeding complet :

```bash
php artisan db:seed
```

## Permissions incluses

Le seeder inclut 60 permissions organisées par modules :

- **Records** (40-45) : Gestion des enregistrements
- **Mails** (46-51) : Gestion des mails
- **Slips** (52-57) : Gestion des bordereaux
- **Slip Records** (58-63) : Gestion des enregistrements de bordereaux
- **Communications** (64-69) : Gestion des communications
- **Tools** (70-75) : Gestion des outils
- **Transferrings** (76-81) : Gestion des transferts
- **Tasks** (82-87) : Gestion des tâches
- **Deposits** (88-93) : Gestion des dépôts
- **Dollies** (94-99) : Gestion des chariots

Chaque module dispose des permissions suivantes :
- `_update` : Mettre à jour
- `_create` : Créer
- `_view` : Voir un élément spécifique
- `_viewAny` : Voir tous les éléments
- `_delete` : Supprimer
- `_force_delete` : Supprimer définitivement

## Notes importantes

- Le `PermissionSeeder` utilise `truncate()` pour vider la table avant d'insérer les nouvelles permissions
- Le `PermissionUpdateSeeder` utilise `updateOrInsert()` pour préserver les données existantes
- Les IDs sont spécifiés explicitement pour correspondre à votre base de données existante
- Toutes les permissions sont créées avec des timestamps automatiques

## Structure de la base de données

La table `permissions` a la structure suivante :
- `id` : Identifiant unique
- `name` : Nom de la permission (unique)
- `description` : Description de la permission
- `created_at` : Date de création
- `updated_at` : Date de mise à jour
