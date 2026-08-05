# Migrations historiques (archivées, non chargées)

Ces 128 migrations couvrent l'historique du schéma jusqu'au 2026-08-04. Elles ont été
retirées de `database/migrations/` et remplacées par la migration unique
`0001_01_01_000000_baseline_schema.php`.

## Pourquoi

Elles ne rejouaient **pas** sur une base vierge :

```
2023_10_21_000000_create_prompt_categories_table
  → SQLSTATE[HY000] 1824: Failed to open the referenced table 'organisations'
```

`prompt_categories` déclare une clé étrangère vers `organisations`, table créée par une
migration postérieure. La base de développement n'a donc pas été construite par un
`php artisan migrate` intégral, et aucun environnement neuf ne pouvait l'être :
ni CI, ni base de test, ni conteneur jetable pour la phase 3 de la migration.

Voir [evolution/RISQUES.md](../../evolution/RISQUES.md) — risque **R24**.

## Statut

- **Conservées** pour l'historique et la traçabilité : quand une colonne a été ajoutée,
  par quelle migration, avec quelle intention.
- **Jamais exécutées** : ce dossier n'est pas dans les chemins de migration de Laravel.
- **Ne pas y ajouter de nouvelle migration.** Les nouvelles migrations s'écrivent
  normalement dans `database/migrations/`, après la baseline.

## Reconstruire un environnement

```bash
php artisan migrate            # base vierge → charge la baseline
scripts/setup-test-db.sh       # reconstruit shelve_test de zéro
```

## Régénérer la baseline

Après avoir ajouté des migrations que l'on souhaite intégrer au socle :

```bash
scripts/setup-test-db.sh regenerate-schema
```

Cela reprend une photographie de `shelve_db` dans `database/schema/mysql-schema.sql`.
