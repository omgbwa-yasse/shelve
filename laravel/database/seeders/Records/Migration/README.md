# Migration Seeders

Seeders pour les migrations de données ponctuelles (one-time use).

⚠️ **ATTENTION**: Ces seeders NE doivent PAS être exécutés automatiquement par `DatabaseSeeder.php`

## 📋 Fichiers

### MigrateDigitalTypesSeeder
- **Purpose**: Migration une seule fois des types de dossiers/documents
- **Taille**: 17 KB
- **Contexte**: Utilisé lors d'une migration d'un ancien système
- **Fréquence**: Une seule fois en production

### MigrateDocumentsSeeder
- **Purpose**: Migration une seule fois des documents numériques
- **Taille**: 15 KB
- **Contexte**: Utilisé lors d'une migration d'un ancien système
- **Fréquence**: Une seule fois en production

### MigrateFoldersSeeder
- **Purpose**: Migration une seule fois des dossiers numériques
- **Taille**: 13 KB
- **Contexte**: Utilisé lors d'une migration d'un ancien système
- **Fréquence**: Une seule fois en production

## 🚨 IMPORTANT

Ces seeders sont destinés à:
- ✅ Migrations de données ponctuelles
- ✅ Import massif d'une source externe
- ✅ Peuplement initial de production
- ❌ Exécution répétée
- ❌ Développement courant

## 📦 Exécution Manuelle

**NUNCA exécuter avec `php artisan migrate:fresh --seed`**

Exécution sélective:
```bash
# Une seule fois en production
php artisan db:seed --class="Database\Seeders\Records\Migration\MigrateDigitalTypesSeeder"
php artisan db:seed --class="Database\Seeders\Records\Migration\MigrateDocumentsSeeder"
php artisan db:seed --class="Database\Seeders\Records\Migration\MigrateFoldersSeeder"
```

## ⚙️ Processus de Migration Recommandé

```bash
# 1. Sauvegarder la base actuelle
mysqldump -u root -p shelve > backup.sql

# 2. Créer une nouvelle base vide
php artisan migrate

# 3. Exécuter UNIQUEMENT les seeders de configuration
php artisan db:seed --class="Database\Seeders\Records\Configuration\RecordStatusSeeder"
php artisan db:seed --class="Database\Seeders\Records\Configuration\RecordLevelSeeder"
php artisan db:seed --class="Database\Seeders\Records\Configuration\RecordSupportSeeder"

# 4. Exécuter les migrations (ONE TIME)
php artisan db:seed --class="Database\Seeders\Records\Migration\MigrateDigitalTypesSeeder"
php artisan db:seed --class="Database\Seeders\Records\Migration\MigrateDocumentsSeeder"
php artisan db:seed --class="Database\Seeders\Records\Migration\MigrateFoldersSeeder"

# 5. Valider et nettoyer
php artisan tinker
> DB::table('record_digital_folders')->count()
```

## 🔍 Vérification Avant Exécution

```php
// Dans un seeder ou artisan tinker
$folderCount = DB::table('record_digital_folders')->count();
$documentCount = DB::table('record_digital_documents')->count();

if ($folderCount > 0 || $documentCount > 0) {
    echo "ATTENTION: Données existantes détectées!";
    echo "Supprimer avant migration:";
    echo "php artisan migrate:fresh";
}
```

## ❌ Erreurs Courantes à Éviter

1. ❌ Exécuter 2 fois le même seeder de migration
   ```bash
   # Mauvais
   php artisan db:seed --class="...MigrateDigitalTypesSeeder"
   php artisan db:seed --class="...MigrateDigitalTypesSeeder"  // Doublon!
   ```

2. ❌ Mélanger seeders de migration et normaux
   ```bash
   # Mauvais - dans DatabaseSeeder.php
   MigrateDigitalTypesSeeder::class,  // À COMMENTER
   ```

3. ❌ Oublier la sauvegarde avant migration
   ```bash
   # Toujours faire:
   php artisan backup:run
   ```

## 📋 Checklist Migration

- [ ] Sauvegarde de la base actuelle
- [ ] Nouvelle base créée et vide
- [ ] Seeders de configuration exécutés
- [ ] Migrations exécutées une seule fois
- [ ] Validation des données
- [ ] Nettoyage des doublons
- [ ] Tests fonctionnels

## 🔄 Idempoténce

Ces seeders NE sont PAS idempotents:
- Chaque exécution ajoute des données
- Risque de duplicatas
- À exécuter une seule fois

## 💾 Stockage des Données Migrées

Après migration réussie:
1. Archiver le seeder (version control)
2. Supprimer les sources de données temporaires
3. Documenter la date de migration
4. Archiver le seeder en sous-dossier "archive"

## 📝 Documentation Recommandée

Créer un fichier `MIGRATION_LOG.md`:
```markdown
# Logs de Migration

## Migration 2026-01-08
- Exécuté par: Admin
- Source: old_system.sql
- Dossiers migrés: 42
- Documents migrés: 1.250
- Erreurs: 0
- Durée: 2min 15s
```

## 🚀 Après Migration

Commenter ou supprimer du DatabaseSeeder:
```php
// AVANT
$this->call([
    MigrateDigitalTypesSeeder::class,   // ← À COMMENTER
    MigrateDocumentsSeeder::class,      // ← À COMMENTER
    MigrateFoldersSeeder::class,        // ← À COMMENTER
]);

// APRÈS
$this->call([
    // Migration: 2026-01-08 - Completed, commented out
    // MigrateDigitalTypesSeeder::class,
    // MigrateDocumentsSeeder::class,
    // MigrateFoldersSeeder::class,
]);
```

## ⚠️ Support Production

Avant exécution en production:
1. Informer tous les utilisateurs
2. Prévoir une fenêtre de maintenance
3. Notifier le support utilisateur
4. Préparer plan de rollback
