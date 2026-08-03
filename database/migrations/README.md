# Migrations — organisation par module

Les migrations sont rangées par domaine fonctionnel. **Le rangement ne change rien au
comportement de Laravel** : le nom d'une migration est son nom de fichier, sans le chemin
(`Migrator::getMigrationName()`), et le migrateur trie *toutes* les migrations par ce nom,
tous dossiers confondus. L'ordre chronologique est donc préservé, et le journal `migrations`
d'une base déjà déployée reste valide.

Les sous-dossiers sont enregistrés dans `AppServiceProvider::loadModuleMigrations()`.

| Dossier | Contenu |
|---|---|
| `core/` | Socle : utilisateurs, cache, jobs, notifications, réglages, versions, sauvegardes, index de recherche |
| `permissions/` | Rôles, permissions, pivots `role_permissions` / `role_has_permissions` |
| `organisations/` | Organisations, services, salles et bâtiments, contacts, intérims des responsables |
| `plan-classement/` | Référentiel archivistique : activités, rétentions, communicabilité, plans de classement |
| `records/` | Archives physiques et numériques, pièces jointes, métadonnées, référentiels de valeurs |
| `mails/` | Courrier : courriers, suivi, cotations, signature DG, correspondants externes |
| `thesaurus/` | Thésaurus, concepts, termes, collections, imports |
| `depots-transferts/` | Dépôts, contenants, versements, bordereaux, chariots |
| `communications/` | Communications et réservations de documents |
| `portail-public/` | OPAC, portail public, actualités, pages, gabarits |
| `ia/` | Prompts, interactions LLM, conversations |
| `workflow/` | Workflows et tâches |

## Nettoyages déjà effectués

- **Tables mortes supprimées** (migration `core/2026_08_03_140000_drop_unused_tables`) :
  20 tables vides, sans modèle Eloquent ni aucune référence dans le code — module LDAP
  jamais développé, module « ouvrages », reliquats du module workflow retiré, pivots du
  tableau d'affichage, tables du thésaurus prévues puis abandonnées. Le schéma passe de
  220 à 200 tables.
- **Chaîne des notifications réduite de 7 à 2 fichiers** : les 5 migrations qui
  construisaient l'ancienne table `notifications` ont été retirées, puisque
  `2025_07_22_200925_drop_notifications_tables` la supprime immédiatement après et que
  `2025_07_22_205745` crée la table définitive. État final rigoureusement identique,
  vérifié par `migrate:fresh`.
- **Chantier courrier regroupé** : 5 migrations fusionnées en 3.

Bilan : 132 → 125 fichiers.

## Aller plus loin : le « squash » de schéma

Le seul moyen de descendre franchement sous la centaine de fichiers est
`php artisan schema:dump --prune` : Laravel écrit l'état complet du schéma dans
`database/schema/{connexion}-schema.sql` et supprime les migrations correspondantes.
Sur une base neuve, il charge le dump puis ne joue que les migrations postérieures.

**Condition à remplir avant de le faire ici** : le dump porte le nom de la *connexion*,
et le projet n'utilise pas le même moteur partout — SQLite en développement, MySQL en
production. Il faut donc **deux dumps** commités ensemble :

```bash
php artisan schema:dump          # en local  -> database/schema/sqlite-schema.sql
php artisan schema:dump --prune  # sur le VPS -> database/schema/mysql-schema.sql
```

Tant que le dump MySQL n'existe pas, **ne pas utiliser `--prune`** : une installation
MySQL neuve n'aurait plus rien à exécuter. À faire après la démo, à froid.

## Créer une nouvelle migration

`php artisan make:migration` dépose le fichier à la **racine** de `database/migrations`.
Déplacez-le ensuite dans le dossier du module concerné — c'est tout, aucun autre changement
n'est nécessaire.

## Règles à respecter

1. **Idempotence.** `up()` doit pouvoir être rejoué sans erreur : entourer les créations de
   `Schema::hasTable()` et les ajouts de colonne de `Schema::hasColumn()`. Une migration
   `migrate` rejouée sur une base partiellement à jour ne doit jamais planter.
2. **Ne jamais écrire dans la table `migrations`.** Laravel enregistre la migration lui-même ;
   une insertion manuelle crée un doublon et fausse le numéro de batch (donc le rollback).
3. **`down()` ne défait que ce que `up()` a fait.** Ne pas supprimer une colonne ou une table
   appartenant à une autre migration : cela casse son propre `down()`.
4. **Portabilité SQLite / MySQL.** Le développement tourne sur SQLite, la production sur
   MySQL. SQLite ne sait pas supprimer une colonne portant une contrainte de clé étrangère :
   entourer ces opérations d'une garde `DB::getDriverName() !== 'sqlite'`, et ne pas supprimer
   une table encore référencée par une FK résiduelle.
5. **Ne jamais renommer ni supprimer une migration déjà déployée.** Son nom est inscrit dans
   le journal de la production ; la renommer la ferait rejouer. Corriger **en place**.
