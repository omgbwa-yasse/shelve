# Permission Seeder

Seeder complet et sécurisé pour les permissions de l'application Laravel.

## 🚀 Utilisation

```bash
# Méthode recommandée
php artisan db:seed --class=PermissionSeeder

# Via commande personnalisée
php artisan seed:permissions

# Via DatabaseSeeder complet
php artisan db:seed
```

## 📊 Contenu

- **222 permissions** couvrant tous les modules métiers
- **Sécurisé** : utilise `updateOrInsert()` - ne supprime jamais les données
- **Production-ready** : peut être utilisé en production sans risque
- **Code refactorisé** : méthodes modulaires par domaine

## ✅ Modules couverts

- **Gestion des utilisateurs** (42) : Users, Roles, Organisations, Authors, Activities, Settings, Languages, Terms
- **Gestion du contenu** (102) : Records, Mails, Slips, Tasks, Deposits, Tools, etc.
- **Communication** (18) : Communications, BulletinBoards, Batches
- **Localisation** (30) : Buildings, Floors, Rooms, Shelves, Containers
- **Système & Technique** (30) : PublicPortal, Posts, AI, Barcodes

Chaque module dispose de 6 permissions : `view`, `viewAny`, `create`, `update`, `delete`, `force_delete`
