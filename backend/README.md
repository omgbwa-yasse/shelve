# shelve-backend — Spring Boot (phase 3)

> Réimplémentation Spring Boot de l'API shelve, **prouvée équivalente** à Laravel
> endpoint par endpoint. Voir [PHASE-3-SPRINGBOOT.md](../evolution/PHASE-3-SPRINGBOOT.md).

## État du portage

| Vague | Domaine | Conformité | JUnit | Statut |
|---|---|---|---|---|
| 1 | **D01 Référentiels** (activities, keywords, sorts, reference-lists + values) | ✅ | ✅ | Porté |
| 1 | **D03 Localisation** (buildings, floors, rooms, shelves, containers, container-statuses, container-properties) | ✅ | ✅ | Porté |
| 1 | **D09 Organisation & sécurité** (auth, organisations, users, roles, user-organisation-roles) | ✅ | ✅ | Porté |
| 2 | **D02 Records** — ressource complète (org-scope, soft delete, métadonnées, status) + référentiels | — | ✅ | Porté |
| 2 | **D04 Versements** (slips, slip-statuses, slip-records, slip-record-containers) | — | ✅ | Porté |
| 2 | **D07 Cycle de vie** (retentions, retention-activities, retention-law-articles) | — | ✅ | Porté |
| 3 | **D05 Communications** (communications, communication-records, reservations, reservation-records) | — | ✅ | Porté |
| 3 | **D11 Dolly** (dollies + add/remove/rename/clear, action=501) | — | ✅ | Porté |
| 4 | **D13 Workflow** (workflow-definitions, workflow-instances) | — | ✅ | Porté |
| 4 | **D08 Thésaurus** (thesaurus-schemes, thesaurus-concepts) | — | ✅ | Porté |
| 5 | **D16 Exploitation** (backups, backup-files, backup-plannings, logs) | — | ✅ | Porté |
| 5 | **D14/D18 IA** (ai-skills, ai-templates, prompts, ai-conversations, ai-routines) | — | ✅ | Porté |
| 5 | **D17 Projets** (projects, objectives, kpis) | — | ✅ | Porté |
| 5 | **D06 Courrier** — référentiels (mail-actions, mail-priorities, mail-typologies, mail-containers, batches, batch-transactions) | — | ✅ | CRUD portés |
| 5 | **D12 Collaboration** (workplaces, tasks, workplace-templates, workplace-conversations) | — | ✅ | Porté |
| 5 | **D10 Recherche** (search/records) | — | ✅ | Porté |
| 6 | **D15 OPAC**, mails/records complets, exports SEDA/EAD, imports | — | — | Non portés (gabarit 3.3) |

**Suite de conformité neutre** (`contracts/conformance`) : **47/47 verts** contre
Spring Boot, sans aucune modification des tests.

**Tests d'intégration JUnit** : **47 tests** (`mvn verify`), sur la même base que
la suite neutre.

## Architecture

Organisation **par fonctionnalité** : chaque domaine (records, localisation, organisation…) regroupe
ses `controller`, `service`, `repository`, `entity`, `dto` et `mapper`. Les packages transverses
(`config`, `common`, `exception`, `security`) sont partagés à la racine du package `com.shelve`.

```
com.shelve
├── config/            configuration Spring (Jackson)
├── common/            utilitaires : pagination, filtres, validation, ApiError, GenericCrudController
├── exception/         gestion globale des erreurs (@ControllerAdvice, ApiException)
├── security/          filtre Sanctum, autorisation pilotée par la base, SecurityConfig
├── referentials/      D01 (activities, keywords, sorts, reference-lists)
│   ├── controller/    API REST
│   ├── service/       logique métier
│   ├── repository/    accès données (Spring Data JPA)
│   ├── entity/        entités JPA (tables)
│   ├── dto/           objets requête/réponse
│   └── mapper/        conversion Entity ↔ DTO
├── records/           D02 (records + statuses, supports, levels, confidentialities, types)
├── localisation/      D03 (buildings, floors, rooms, shelves, containers)
├── slips/             D04 (slips, slip-statuses, slip-records, pivots)
├── communications/    D05 (communications, reservations, pivots)
├── mails/             D06 référentiels (actions, priorities, typologies, batches)
├── retention/         D07 (retentions + pivots activité/article)
├── thesaurus/         D08 (schemes, concepts)
├── dolly/             D11 (chariots + actions)
├── workflow/          D13 (définitions, instances)
├── ai/                D14/D18 (skills, templates, prompts, conversations, routines)
├── exploitation/      D16 (backups, backup-files, backup-plannings, logs)
├── projects/          D17 (projects, objectives, kpis)
├── collaboration/     D12 (workplaces, tasks, templates, conversations)
├── search/            D10 (search/records)
└── organisation/      D09 (auth, organisations, users, roles, pivots)
```

## Décisions d'architecture (PHASE-3 §3.0)

1. **Base partagée** : Spring attaque la même base MySQL que Laravel, sans
   aucune migration de données. Flyway désactivé pendant la coexistence
   (réactivé en `validate` seul à la bascule finale, §3.6).
2. **Tokens Sanctum interopérables** : `Authorization: Bearer {id}|{plain}`,
   hash SHA-256 comparé à `personal_access_tokens.token` (§3.0.3, R21).
3. **Autorisation pilotée par la base** : permissions issues de
   `permissions` / `user_permissions` / `role_permissions`, jamais figées en
   annotations (§3.0.4, R04). `GenericCrudController` centralise le contrôle
   `{ressource}_{action}` pour tous les CRUD.
4. **Isolation multi-organisation** : scope org explicite par ressource
   (pivot `organisation_room` pour les salles, héritage pour rayonnages et
   conteneurs, double organisation pour slips/communications, `organisation_id`
   pour records/projets/workplaces). 404 hors périmètre, jamais 403 (§4, R03).
5. **Réponses conformes au contrat** : enveloppe paginée, dates ISO-8601 UTC,
   erreurs 422 au format Laravel, snake_case partout.

## Lancement

```bash
# Base de développement (shelve_db)
mvn spring-boot:run

# Base de test (shelve_test) — celle de la suite de conformité
mvn spring-boot:run -Dspring-boot.run.profiles=test

# Tests d'intégration (nécessite MySQL local + ConformanceSeeder dans shelve_test)
mvn verify

# Suite de conformité neutre rejouée contre Spring Boot
cd ../contracts/conformance
API_BASE_URL=http://localhost:8080/api/v1 \
API_TEST_EMAIL=conformance@shelve.test \
API_TEST_PASSWORD=conformance-secret \
API_TEST_FOREIGN_ORG_ID=<id de CONF-B> \
npx vitest run --no-file-parallelism
```

## Prérequis du compte de conformité

Le compte `conformance@shelve.test` et les deux organisations (CONF-A / CONF-B)
sont créés par `ConformanceSeeder` :

```bash
cd ..
DB_DATABASE=shelve_test php artisan db:seed --class=ConformanceSeeder --force
```

## Notes de parité avec Laravel

- Les messages de validation 422 restent en anglais par défaut (locale `en`),
  comme l'oracle — ils font partie du contrat (CONVENTIONS §4).
- Les liens de pagination (`first`/`last`/`next`/`prev`) reprennent la forme
  `?page=N` de Laravel ; la normalisation de la suite les réduit à `<url>`.
- Les clés composites inhabituelles (`floors` id+building_id, `shelves`
  id+room_id) sont mappées sur `id` unique (auto-incrément) ; les pivots sans
  id (`retention_activity`, `retention_law_articles`, `slip_record_container`,
  `user_organisation_role`) utilisent `@EmbeddedId`.
- Les champs posés côté serveur (créateur, organisation, codes générés,
  version/is_current_version pour les notices, statuts/priorités par défaut)
  sont reproduits à l'identique.
- Domaine D11 : `GET /dollies/action` répond 501 comme l'oracle ; les actions
  add/remove/rename/clear sont portées en points d'entrée explicites.
- Les `@SQLRestriction("deleted_at is null")` reproduisent le soft delete
  Eloquent sur les tables concernées (records, reference-lists, workplaces).
