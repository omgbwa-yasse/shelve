# Configuration Seeders

Seeders pour les tables de configuration de base du système de gestion des dossiers et documents numériques.

## 📋 Fichiers

### RecordStatusSeeder
- **Peuple**: `record_statuses`
- **Données**: États des dossiers (Brouillon, Validation, Publié, Archivé)
- **Dépendances**: Aucune
- **Obligatoire**: ✅ OUI

### RecordLevelSeeder
- **Peuple**: `record_levels`
- **Données**: Niveaux hiérarchiques ISAD(G) (Fonds, Série, Item, etc.)
- **Dépendances**: Aucune
- **Obligatoire**: ✅ OUI

### RecordSupportSeeder
- **Peuple**: `record_supports`
- **Données**: Types de supports physiques (Papier, Parchemin, Film, Microfilm, Numérique, etc.)
- **Dépendances**: Aucune
- **Obligatoire**: ✅ OUI

## 🔄 Ordre d'Exécution

```php
RecordStatusSeeder
RecordLevelSeeder
RecordSupportSeeder
```

Aucune dépendance - peuvent s'exécuter en parallèle.

## 📦 Exécution Manuelle

```bash
php artisan db:seed --class="Database\Seeders\Records\Configuration\RecordStatusSeeder"
php artisan db:seed --class="Database\Seeders\Records\Configuration\RecordLevelSeeder"
php artisan db:seed --class="Database\Seeders\Records\Configuration\RecordSupportSeeder"
```

## ⚙️ Configuration

Ces seeders ne nécessitent aucune configuration externe. Les données sont codées en dur et idempotentes.

## 🔑 Clés Primaires

- RecordStatus: `code` (string, unique)
- RecordLevel: `code` (string, unique)
- RecordSupport: `code` (string, unique)

## ✅ Validation

Après exécution, vérifier:
```sql
SELECT COUNT(*) FROM record_statuses;   -- Attendu: 4-5
SELECT COUNT(*) FROM record_levels;     -- Attendu: 8-10
SELECT COUNT(*) FROM record_supports;   -- Attendu: 6-8
```
