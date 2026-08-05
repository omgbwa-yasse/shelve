# Journal d'exécution — Phase 1

> ← [README](README.md) · [Phase 1](PHASE-1-API-LARAVEL.md) · [Risques](RISQUES.md)
>
> Ce que l'exécution a réellement produit, et ce qu'elle a fait découvrir.
> Les découvertes qui invalident une hypothèse du plan sont reportées dans [RISQUES.md](RISQUES.md).

---

## Étape 1.0 — Socle et conventions

| Sous-étape | État | Livrables |
|---|:-:|---|
| 1.0.1 Inventaire des endpoints | ✅ | `contracts/inventory/endpoints.csv`, `endpoints-summary.md`, `scripts/build-endpoint-inventory.php` |
| 1.0.2 Extraction des validations | ✅ | `validation-rules.csv`, `validation-raw.txt`, `validation-gaps.md`, `scripts/extract-validation-rules.php` |
| 1.0.3 Conventions d'API | ✅ | `contracts/CONVENTIONS.md` |
| 1.0.4 Authentification API | ✅ | `Api/V1/AuthController`, 2 FormRequests, `UserResource`, 5 routes, **10 tests verts** |
| 1.0.5 Scaffolding | ✅ | `php artisan make:api-resource-set` — 5 fichiers générés par ressource |
| 1.0.6 Harnais de conformité | ✅ | `contracts/conformance/` — **13 tests verts contre l'API réelle**, 4 golden files |

**L'étape 1.0 est terminée.** Le portage des 16 domaines (étapes 1.1 → 1.16) peut démarrer.

---

## Chiffres mesurés (et non plus estimés)

L'inventaire porte sur **1 261 routes réelles** — un `Route::resource` en déploie 7, ce que le comptage
initial par `grep` (635 déclarations) ne voyait pas.

| Type | Nombre | Traitement |
|---|--:|---|
| CRUD | 641 | portage direct en API v1 |
| **Actions métier** | **352** | un endpoint `POST .../{verbe}` chacune |
| Vues `create`/`edit` | 216 | non portées — remplacées par des écrans Next |
| Exports | 24 | classe d'équivalence E2 en phase 3 |
| Uploads | 7 | |
| Framework | 21 | hors périmètre |
| **À porter** | **1 024** | |

**0 route non classée** — critère de sortie de 1.0.1 atteint.

Sur la validation : **360 blocs**, **1 556 couples champ → règles**, dont **73 construites dynamiquement**
(concaténation d'un id, `implode()` sur une constante, `Rule::`) qui devront être relues à la source.
**45 méthodes d'écriture ne valident rien** (couverture 83,9 %) — chacune exige une décision explicite
au portage de son domaine.

---

## Découvertes qui modifient le plan

### 1. R06 était sous-estimé d'un facteur 2,5

Le plan annonçait « ~140 actions métier ». Il y en a **352**. La charge de la phase 1 doit être
révisée en conséquence : ce sont 352 comportements à recenser, spécifier et tester un par un, et ils
ne bénéficient d'aucun scaffolding générique (contrairement au CRUD).

### 2. ⚠️ Les migrations ne rejouent pas sur une base vierge (R24, criticité 25)

```
2023_10_21_000000_create_prompt_categories_table
  → SQLSTATE[HY000] 1824: Failed to open the referenced table 'organisations'
```
`prompt_categories` déclare une clé étrangère vers `organisations`, créée par une migration **postérieure**.
La base actuelle n'a donc pas été construite par un `migrate` intégral.

**Portée réelle** — cela retire trois fondations du plan de migration :
- pas de CI possible (aucun environnement neuf ne peut être construit) ;
- pas de jeu de recette reproductible (phase 2, §2.2.2) ;
- pas de Testcontainers en phase 3 (§3.0.2), donc pas de diff-testing sur base propre.

**Contournement mis en place** : squelette `database/schema/mysql-schema.sql` (237 tables, mécanisme
natif de Laravel) + `scripts/setup-test-db.sh`. La base de test se reconstruit en une commande.

**Non résolu pour autant** : le squelette est une photographie de l'existant, pas une réparation.
Réordonner les migrations reste à faire, et c'est un prérequis de la phase 3.

### 3. ⚠️ La suite de tests détruisait la base de développement (R25, criticité 25)

`phpunit.xml` avait ses lignes `DB_CONNECTION` / `DB_DATABASE` **commentées** : les tests tournaient sur
`shelve_db`. Or **19 fichiers utilisent `RefreshDatabase`**, qui exécute `migrate:fresh` — donc *drop de
toutes les tables*, puis échec sur le problème d'ordre ci-dessus.

Autrement dit : lancer la suite de tests détruisait la base de développement et la laissait vide.

**Endigué** : `phpunit.xml` pointe désormais `shelve_test`. Vérifié — après une exécution complète,
`shelve_db` conserve ses 237 tables et 227 utilisateurs, tandis que `shelve_test` a bien été vidée.

**Conséquence pour la migration** : la suite existante ne peut pas servir de filet de sécurité.
Cela **renforce R19** et confirme que le harnais de conformité (1.0.6) doit être construit indépendamment
des tests actuels, et non par-dessus.

---

## Défauts corrigés au passage

| Défaut | Portée | Correction |
|---|---|---|
| `routes/web.php` importait `App\Http\Controllers\floorController` et `retentionActivityController` en minuscule, alors que les classes sont `FloorController` / `RetentionActivityController`. Les alias `use` de PHP étant insensibles à la casse, `FloorController::class` (l.641) produisait la chaîne minuscule. NTFS masque le problème ; **PSR-4 sur Linux → `Class not found`, 500 sur 14 routes** | 14 routes | Casse corrigée (3 lignes) |
| Ni `User` ni `PublicUser` n'utilisaient `HasApiTokens`, alors que `auth:sanctum` protège déjà des routes et que `PublicUserApiController::login` (l.70) appelle `createToken()` → `BadMethodCallException` garantie | Toute l'API authentifiée | Trait ajouté aux deux modèles |
| `Role::$fillable` déclarait `display_name`, colonne inexistante dans la table. Combiné au `Role::create($request->all())` de `RoleController`, un champ inattendu devenait une erreur SQL 1054 | Création/édition de rôles | Champ retiré du `$fillable` |

Aucune de ces corrections ne modifie un comportement fonctionnel : ce sont des défauts qui ne se
manifestaient pas dans l'environnement de développement Windows actuel.

---

## Contraintes de schéma relevées (savoir non écrit)

À reporter dans les `FormRequest` du domaine concerné :

| Table | Contrainte | Domaine |
|---|---|---|
| `users.birthday` | `date` NOT NULL **sans valeur par défaut** — toute création d'agent doit la fournir | D09 |
| `users.birthday` | de type `date` en base mais **non casté** dans le modèle : Eloquent renvoie une chaîne. Ajouter le cast changerait le rendu des vues Blade — la normalisation ISO-8601 se fait donc dans la Resource | D09 |
| `user_organisation_role` | `role_id` **et** `creator_id` NOT NULL, alors que `User::organisations()` est un `belongsToMany` nu : un `attach($orgId)` seul échoue. Toute API de rattachement doit fournir les deux | D09 |
| `roles` | possède `description` et `guard_name`, **pas** `display_name` | D09 |

---

## Décisions prises

1. **Base de test dédiée `shelve_test`**, reconstruite par `scripts/setup-test-db.sh`.
2. **`DatabaseTransactions` plutôt que `RefreshDatabase`** dans les tests d'API : rollback par test,
   sans reconstruction de schéma — seule approche viable tant que R24 n'est pas résolu.
3. **Squelette SQL versionné** (`database/schema/mysql-schema.sql`) comme point de départ des
   environnements neufs, en attendant la remise en ordre des migrations.
4. **Tokens Sanctum** confirmés comme mécanisme d'authentification (cf. 1.0.4) : `personal_access_tokens`
   existe, le hachage SHA-256 est lisible par Spring Boot en phase 3.

---

## Décisions prises après arbitrage (2026-08-04)

### R24 — migration de baseline unique ✅ RÉSOLU

`0001_01_01_000000_baseline_schema.php` charge `database/schema/baseline-schema.sql`
(236 tables) sur une base vierge, et ne fait rien sur une base déjà en service. Les
128 migrations historiques sont conservées, non chargées, dans `database/migrations-archive/`.

Vérifié : `php artisan migrate` **et** `migrate:fresh` reconstruisent une base entièrement
vide. La CI, le jeu de recette de la phase 2 et Testcontainers en phase 3 redeviennent
possibles.

Deux pièges rencontrés, documentés dans le code :
- La table `migrations` est **exclue** du squelette : Laravel la crée lui-même, et
  l'inclure fait échouer la baseline.
- Le fichier ne doit **pas** s'appeler `mysql-schema.sql` : sous ce nom, Laravel le
  reconnaît comme son squelette natif et tente de le charger via le binaire `mysql`,
  absent du PATH sous WAMP. Notre baseline le charge par PDO.

### R25 — résolu mécaniquement par R24 ✅

Aucun des 19 fichiers n'a eu besoin d'être modifié : `RefreshDatabase` fonctionne dès lors
que `migrate:fresh` fonctionne. Restait une dette distincte — `database/factories/` était
**vide** alors que les tests attendent 22 factories. `UserFactory` et `OrganisationFactory`
(les deux socles) sont créées ; les 20 autres seront produites au fil du portage de leur
domaine, certaines visant des modèles qui n'existent plus (`Document`, `Folder`, `Type`).

### Défaut supplémentaire trouvé — compteurs de quota persistants en test

`config/cache.php` définit `'limiter' => env('CACHE_LIMITER_STORE', 'redis')` : le
RateLimiter **n'utilise pas** le store de cache par défaut. `phpunit.xml` surchargeait
`CACHE_STORE` mais pas `CACHE_LIMITER_STORE`, laissé sur `database` — les compteurs
survivaient d'une exécution à l'autre et finissaient par renvoyer 429 aux tests
d'authentification, sans rapport avec le code testé.

Corrigé dans `phpunit.xml`. Tests stables sur trois exécutions consécutives.

---

## Points en attente d'arbitrage

| Sujet | Enjeu | Options |
|---|---|---|
| **Charge révisée de la phase 1** | 352 actions métier au lieu de ~140 | Recalibrage au jalon de fin de vague 1, comme prévu au plan |
| **20 factories manquantes** | Bloquent les tests des domaines concernés | Les produire au fil du portage (retenu) · ou en amont, en un lot |
| **Réponse 429 non conforme RFC 7807** | `CONVENTIONS.md` §4 impose `application/problem+json` ; le corps actuel expose `message`, consommé par le portail public | Traiter au portage de D15/D16 (retenu) · ou plus tôt avec adaptation du portail |

---

## Étape 1.0 — clôture

Le socle est complet et vérifié :

| Vérification | Résultat |
|---|---|
| Reconstruction d'une base vierge (`migrate`) | ✅ 237 tables |
| `migrate:fresh` | ✅ |
| Tests Feature de l'authentification | ✅ 10/10, stables sur 3 exécutions |
| Suite de conformité contre l'API réelle | ✅ 13/13, golden files stables |
| Scaffolding sur 2 modèles de structures différentes | ✅ code syntaxiquement valide |
| Base de développement `shelve_db` | ✅ intacte (237 tables, 227 utilisateurs) |

**Prochaine étape : 1.1 — portage du domaine D01 (Référentiels), première vague.**

### Comment reprendre

```bash
# Base de test
scripts/setup-test-db.sh
DB_DATABASE=shelve_test php artisan db:seed --class=ConformanceSeeder

# Tests Laravel
php artisan test tests/Feature/Api/V1/

# Suite de conformité (serveur sur un port libre, quotas désactivés)
DB_DATABASE=shelve_test RATE_LIMIT_ENABLED=false php artisan serve --port=8124
cd contracts/conformance && npm install
API_BASE_URL=http://127.0.0.1:8124/api/v1 \
  API_TEST_EMAIL=conformance@shelve.test \
  API_TEST_PASSWORD=conformance-secret \
  API_TEST_FOREIGN_ORG_ID=42 \
  npx vitest run

# Générer une ressource
php artisan make:api-resource-set Activity --domain=D01
```

---

## Étape 1.1 — D01 Référentiels ✅ TERMINÉE (2026-08-04)

> Détail complet : [docs/api/D01.md](../docs/api/D01.md).

Les **96 routes « à porter »** du domaine sont exposées en API v1. Les 29 routes de
vue `create`/`edit` sont abandonnées (écrans Next en phase 2) ; les 2 exports
(`activities/export/*`) restent en classe E2 (phase 3).

### Livrables

| Ressource | CRUD | Actions |
|---|:-:|---|
| activities, languages, sorts, keywords, laws, law-articles, communicabilities | ✅ | `list`, `hierarchy/{id?}`, `search`, `process`, `{locale}/activate` |
| authors (+ `author-types`), author-contacts, external-contacts, external-organizations | ✅ | `author-types` |
| settings, setting-categories, reference-lists (+ `/values`) | ✅ | `set-value`, `reset-value`, `tree`, valeurs imbriquées |

**Mesures** : 14 contrôleurs API, 28 FormRequests, 15 Resources, 5 Policies créées,
**157 tests Feature verts** (386 assertions), **15 tests de conformité verts** contre
l'API réelle, note d'analyse `docs/api/D01.md`.

### Découvertes majeures

#### 1. `access-in-organisation` verrouillait les référentiels globaux (R03)

Le Gate renvoyait `false` pour tout modèle sans `organisation_id` : les référentiels
D01 (pas de colonne org) répondaient **404 sur `show`/`update`/`delete`** pour tout
agent, même habilité. La relation `organisations()` en many-to-many était traitée
comme une propriété alors qu'elle est une **affectation**. Corrigé : seuls les modèles
portant `organisation_id` (ou `involvesOrganisation`) sont restreints ; une ligne sans
organisation (`NULL`) reste globale. Les référentiels sont donc **partagés**, et un
test de « partage entre organisations » le prouve.

#### 2. `hasPermission` par Gate dynamique ne voyait pas les permissions tardives (R04)

Les Gates dynamiques de `PolicyService` ne sont enregistrés qu'au boot, à partir des
permissions présentes en base. `BasePolicy::hasPermission` interroge désormais
`User::hasPermission()` (tables natives) — même comportement pour l'existant,
correct pour des permissions créées après le boot.

**Effet net sur la suite complète : 227 → 125 échecs** (dont 0 régression D01), les
échecs restants étant préexistants et documentés (WorkplaceTest, UnifiedRecordsModuleTest,
opac, digital*… — la suite historique ne redevient pas un filet de sécurité avant
d'être réparée).

#### 3. ⚠️ `Setting::setValue` (Blade) viole `unique:settings,name`

Le contrôleur Blade **clone** le paramètre pour porter une valeur personnalisée —
mais `settings.name` est unique : le clone échoue en SQL 1062. `SettingMergedStructureTest`
(test préexistant) échoue exactement pour cette raison : la sémantique « instance
personnalisée » est incompatible avec le schéma actuel.

**Décision API** : `setValue` écrit sur la ligne du paramètre (conversion + validation
de type/contraintes conservées), `resetValue` efface `value`. À arbitrer avec le
propriétaire du domaine — le Blade reste tel quel.

#### 4. Autorisation inexistante côté Blade pour 8 ressources (R04)

`KeywordController`, `LawArticleController`, `SortController`,
`CommunicabilityController`, `AuthorContactController`, `External*Controller`,
`ReferenceListController` n'appliquaient **aucune** Policy. Policies créées et
branchées ; permissions ajoutées au `PermissionSeeder` (aucun rôle ne les reçoit :
la politique RBAC est une décision métier, pas un choix de migration).

#### 5. `settings` est la seule table org-scopée du domaine

L'index applique `scopeForUserAndOrganisation`. Un paramètre d'une autre organisation
n'est pas visible — testé.

### État des points en attente

| Sujet | État |
|---|---|
| Charge révisée (352 actions) | D01 : 8 actions métier portées sur 8 recensées — le jalon de recalibrage reste à fin de vague 1 (D01+D03+D09) |
| 20 factories manquantes | Toujours 2 (User, Organisation) — non bloquant pour D01 (création directe dans les tests) |
| 429 non conforme RFC 7807 | Toujours en attente (D15/D16) |

**Prochaine étape : 1.2 — portage du domaine D03 (Localisation physique).**

---

## Étape 1.2 — D03 Localisation physique ✅ TERMINÉE (2026-08-04)

> Détail complet : [docs/api/D03.md](../docs/api/D03.md).

Les **46 routes « à porter »** du domaine sont exposées (0 action métier, tout CRUD).
Fusions : `trolleys` → `buildings`, `access` → `container-statuses` (alias du même
contrôleur). 7 ressources : `buildings`, `floors`, `rooms`, `shelves`, `containers`,
`container-properties`, `container-statuses`.

**C'est le premier domaine avec une isolation réelle (R03)** : `rooms`, `shelves`,
`containers` sont org-scopés (pivot `organisation_room` et héritage), contrairement à
D01 qui était global. Deux Policies créées, 7 portées `inOrganisation()` sur les
modèles, **66 tests Feature** (dont les tests bloquants d'isolation : ressource d'une
autre organisation → **404** sur show/update/destroy), **8 tests de conformité**.
Suite D01+D03 : **223 tests Feature** et **36/36 conformité** verts.

### Découverte structurante

L'isolation org ne passe **pas** par `PolicyService::access-in-organisation` pour ces
modèles : `Room`/`Shelf`/`Container` n'ont pas de colonne `organisation_id`, le Gate
les laissait passer. La garantie est portée par des **portées Eloquent**
(`inOrganisation()`) appliquées à l'index ET à la résolution des routes (404) — c'est
l'équivalent des filtres Hibernate que Spring Boot devra poser en phase 3.

**Prochaine étape : 1.3 — portage du domaine D09 (Organisation & sécurité), qui
clôt la vague 1 et déclenche le jalon de recalibrage (RISQUES.md §4.4).**

---

## Étapes 1.2 → 1.15 — Portage des domaines restants ✅ (2026-08-05)

> Portage massif conduit par sous-agents en parallèle (un fichier de routes par
> domaine, intégration et vérification centralisées). L'API v1 passe de **150 à
> 498 routes**, de **20 à 89 contrôleurs** API, de **36 à 79 fichiers de test**.
> Résultat final : **611 tests Feature verts** et **36/36 tests de conformité**.

### Ce qui a été porté

| Domaine | Ressources principales | Notes |
|---|---|---|
| **D02** Records | records (+ children/authors/containers/attachments), record-reactivations, record-statuses, record-supports, record-types, metadata-definitions, profils de métadonnées | XL. Org-scopé (motif D03). Réactivations approuvées/rejetées. Digital-transfer & drag-drop : TODO |
| **D04** Versements | slips (+ receive/approve), slip-records, slip-record-containers, slip-record-attachments, slip-statuses | Clés composites sans `id` → Query Builder |
| **D05** Communications | communications (+ validate/reject/transmit/return), communication-records, reservations (+ mark-returned), reservation-records | `activityCommunicability` fusionné dans D01 (aucun modèle dédié) |
| **D06** Courrier | mails (org-scopé, count-unread, **store création sans upload**), mail-actions/priorities/typologies, mail-containers, mail-archives, mail-attachments, batches, batch-transactions | XL. Workflows parapheur (envoi) : TODO — `mail_transactions` absente du schéma |
| **D07** Cycle de vie | retentions, retention-activities, retention-law-articles, declassement-lists (+ workflow approbation), life-cycle (6 rapports) | Pivots sans timestamps corrigés |
| **D08** Thésaurus | thesaurus-schemes, thesaurus-concepts (+ search/autocomplete), **import SKOS-RDF/CSV/JSON + statut** | Export SKOS, hiérarchies : TODO — export classe E2 (phase 3) |
| **D10** Recherche | 23 endpoints d'action (search/records, mails, slips, communications, réservations, dolly, localisation) | Tous org-scopés ; enveloppe paginée standard |
| **D11** Dolly | dollies (+ 11 actions add/remove), **add-slip/remove-slip/clear/rename**, dolly-handler (JSON) | DollyAction : mass-edit en masse incohérent avec le schéma — 501 documenté |
| **D12** Collaboration | workplaces (+ archive/settings, membres, contenus), workplace-conversations, workplace-templates, tasks | Org-scopé (workplace), conversations globales+workplace |
| **D13** Workflow | workflow-definitions (+ configuration BPMN, start/pause/resume/cancel via WorkflowEngine) | |
| **D14** IA | ai-skills (+ toggle), ai-templates, prompts (visibilité org/système/personnel) | Appels LLM, install ZIP, download : TODO |
| **D16** Exploitation | backups, backup-files, backup-plannings, logs | Exports binaires (PDF/SEDA/barcode) : classe E2 phase 3 |

### Découvertes / écarts

| Découverte | Traitement |
|---|---|
| **`tasks.organisation_id` NOT NULL** mais `Task` ne portait pas `BelongsToOrganisation` | Posé depuis `Auth::user()->current_organisation_id` au store |
| **Pivots sans clé `id`** (`record_physical_attachment`, `slip_record_container`, pivots de rétention) | `update`/`delete`/`fresh` Eloquent impossibles → Query Builder ; tri par défaut adapté (colonne existante) |
| **`belongsToMany()->getQuery()` éclipse l'id de la cible** (record_author) | Join explicite + `select('authors.*')` |
| **5 implémentations divergentes de création de courrier** dans le Blade | Store de courrier porté (2026-08-05) sans téléversement : code séquentiel, statuts entrant/sortant, relations external_* ; upload déporté sur `mail-attachments` (TODO E2) |
| **`mail_transactions` absente du schéma** alors que `MailTransaction` existe | Workflow parapheur bloqué, documenté 501 |
| **Enums de statut vs chaînes** (`Communication::isPending()` comparait une enum à une chaîne) | Comparaison contre `CommunicationStatus::{...}` |
| **`PolicyService` par Gate dynamique** corrigé (D01) → les Policies auto-découvertes par convention fonctionnent pour tous les domaines |

### Reste à faire (phase 1)

- **D15 OPAC** : volontairement **non porté** dans ce lot — c'est la vague 6 du plan
  (README §0.4), le portail public avec guard `public`, moteur de templates en base
  (risque **R05**, criticité 20) et option de repli « OPAC conservé sur Laravel ».
  L'API `/api/public/*` préexistante couvre déjà une partie (login usager, records,
  news, événements, feedback, demandes de documents, templates).
- **D02 Digital-transfer / drag-drop**, **D06 workflow d'envoi du parapheur**
  (`mail_transactions` absente du schéma), **D11 DollyAction mass-edit en masse**
  (colonnes incohérentes : `mails.date_exact`/`type_id`/`is_achived`), **D08 export
  SKOS**, **D14 actions IA** : documentés en TODO 501 dans les contrôleurs — à porter
  en phase 1 fin de vague ou phase 2.
- **Exports binaires** (PDF, Excel, SEDA, barcode) : classe E2, phase 3.
- **Couverture de test** : les ressources portées ont les tests de base (auth,
  permission, index, validation) + happy-path/isolation sur les ressources
  principales ; la couverture ≥ 90 % ligne-par-ligne reste à compléter sur les
  actions métier.
- **Jalon de recalibrage** (fin de vague 1 : D01+D03+D09) : D09 reste à porter.

### Sauts de charge mesurés

- 498 routes API v1 (dont ~350 nouvelles dans ce lot) contre **1 024 endpoints
  « à porter »** recensés en 1.0.1 — l'écart restant est fait d'actions métier
  documentées TODO, d'exports E2 et de D15 (vague 6).
- R06 (352 actions métier) : le gros des actions les plus simples est porté ;
  les actions complexes restent documentées — voir le détail par domaine ci-dessus.

---

## Vérification de santé post-portage (2026-08-05)

> Contrôle indépendant de l'ensemble du travail livré ci-dessus (D01 → D16 hors D15),
> exécuté après la finalisation. Deux passages complets de la suite de tests, **en
> série** — un premier essai avec deux suites lancées en parallèle sur la même base
> a produit une erreur `Table 'workplaces' doesn't exist` : un `RefreshDatabase`
> concurrent avait dropé le schéma pendant qu'une autre suite tournait. Résultat
> invalidé, à ne pas reproduire — toujours lancer un seul processus de test à la fois
> contre une base MySQL partagée.

**Suite complète, seule** : 889 tests, **762 passants, 127 échecs**.

**Répartition des 127 échecs** : **125 dans la suite historique déjà documentée**
(RecordPolicyTest, MetadataSystemTest, Periodicals/Document/DigitalDocuments/FolderApiTest
— l'ancienne suite `Api/*` non-V1 —, WorkplaceTest, PublicUserControllerTest,
AttachmentExtensionTest, PublicDocumentRequestTest, BatchTransferTest,
UnifiedRecordsModuleTest, SettingMergedStructureTest, OpacTemplateSystemTest,
ExampleTest, CommunicationStatusTest), **2 dans la nouvelle suite `Api/V1`**.

### Deux bugs réels trouvés et corrigés

1. **`UserOrganisationRoleController::index()`** — 500 sur tout appel sans `?sort=`
   explicite. `applySorting()` trie par `id` par défaut ; `user_organisation_role`
   a une clé composite (`user_id`+`organisation_id`) et pas de colonne `id`.
   Corrigé avec un défaut explicite (`created_at`).

2. **`SlipRecordContainerController::index()`** — même défaut, trouvé par audit
   proactif des autres contrôleurs sur pivots sans `id` (`slip_record_container`
   n'a pas de colonne `id` non plus). Corrigé de la même façon. Les deux
   contrôleurs de rétention (`RetentionActivityController`,
   `RetentionLawArticleController`) avaient déjà le bon réflexe — seuls ces deux-là
   manquaient.

3. **`EventController::cancelRegistration()`** (portail public) — `TypeError` : la
   signature déclarait `: JsonResponse` mais la méthode renvoie
   `response()->noContent()`, un `Response` simple. Corrigé (signature + import).

**Après correction : 24/24 tests verts sur les 3 fichiers concernés, 0 échec dans
`tests/Feature/Api/` en dehors des 58 pré-existants de l'ancienne suite non-V1**
(14+13+11+10+10 = 58, confirmé par recomptage — aucune régression introduite).

### Verdict

**La phase 1 est saine.** 94/94 fichiers de la suite `Api/V1` passent. Les échecs
restants dans la suite globale sont tous pré-existants, déjà expliqués dans ce
journal, et hors du périmètre que la phase 1 s'était fixé de réparer (la suite
historique « ne redevient pas un filet de sécurité avant d'être réparée », comme
noté plus haut) — sauf les 3 bugs ci-dessus, qui appartenaient bien au nouveau
périmètre et ont été corrigés.

## Nettoyage du code mort de la famille RecordPhysical/RecordDigitalFolder/RecordDigitalDocument (2026-08-05)

L'utilisateur a demandé l'analyse et la suppression des anciens modèles/contrôleurs
`RecordPhysical`, `RecordDigitalFolder`, `RecordDigitalDocument` (remplacés « en
théorie » par le modèle unifié `Record`). Un agent Explore a cartographié
exhaustivement les ~230 fichiers potentiellement concernés (routes, vues,
relations, policies, tests, comptages de lignes en base `shelve_db`).

**Découverte majeure : la migration n'est pas terminée.** `RecordController`
(Blade) est bien passé au modèle unifié, mais plusieurs fonctionnalités
périphériques restent branchées en direct sur les anciens modèles et sont
**vivantes et routées** :
- `RecordChildController` (`records.child`) — CRUD des notices-filles sur `RecordPhysical`.
- `SEDAExportController::exportRecord` (`records.export.seda`) — route model binding
  `RecordPhysical $record` **potentiellement cassé** pour la plupart des notices
  actuelles (les IDs `record_physicals` ne coïncident plus avec `records` après
  migration) — incohérence à traiter, pas du code mort.
- Imports EAD/SEDA (`SlipController::import`) — créent encore des `RecordPhysical`.
- API Phase 9 (`/api/v1/digital-folders*`, `/api/v1/digital-documents*`), OPAC
  (`digital.folders.*`, `digital.documents.*`), Settings (types), Dolly,
  `RecordDigitalTransferController` (racine) — tous vivants sur
  `RecordDigitalFolder`/`RecordDigitalDocument`.
- `record_digital_folder_metadata_profiles` / `record_digital_document_metadata_profiles`
  sont vides (0 ligne) mais exposées par une API D02 fraîchement portée
  (`Api/V1/RecordDigital{Folder,Document}MetadataProfileController`) — probablement
  dupliquées par `record_type_metadata_profiles` (le futur système unifié), à
  trancher avant de figer l'API D02.

Comptage `shelve_db` : `record_physicals`=23, `record_digital_folders`=45,
`record_digital_documents`=46 lignes, toutes déjà répliquées dans `records`
(46+45+23=114, cohérent avec `records.legacy_source`). La commande
`MigrateToUnifiedRecords` a donc bien tourné, mais le nettoyage du schéma legacy
est explicitement reporté à une « Phase 7 » non atteinte.

**Suppressions effectuées (seuls éléments confirmés MORT sans aucune référence
vivante, vérifié par grep exhaustif avant suppression) :**
- `app/Http/Resources/RecordDigitalFolderResource.php` — jamais utilisée (le
  contrôleur retourne les données brutes du service).
- `app/Http/Resources/RecordDigitalDocumentResource.php` — idem.
- `app/Observers/RecordObserver.php` — jamais enregistré via `::observe()` dans
  aucun ServiceProvider ; ses hooks ne se sont jamais déclenchés.
- `app/Http/Controllers/Api/V1/RecordDigitalTransferController.php` — stub vide
  (classe sans méthode), explicitement documenté dans son propre docblock comme
  « NON PORTÉ, abandon documenté » ; les routes `record-digital-transfer/*`
  pointent vers le contrôleur racine (`App\Http\Controllers\RecordDigitalTransferController`),
  pas vers ce stub V1.
- `resources/views/records/digital-folders/partials/{transfer-button,transfer-modal}.blade.php`
  et `resources/views/records/digital-documents/partials/{transfer-button,transfer-modal}.blade.php`
  — aucun `@include` nulle part dans `resources/views` ; le JS `initTransferModal()`
  qu'ils définissaient n'était donc jamais chargé.

**Conséquence :** la route API `record-digital-transfer/*` (contrôleur racine,
`DigitalPhysicalTransferService`) reste vivante et testée mais **sans point
d'entrée UI** — orpheline côté interface. Non supprimée (route + tests actifs),
signalée pour décision future.

**Explicitement NON supprimé** — tout le reste de la famille (modèles
`RecordPhysical`/`RecordDigitalFolder`/`RecordDigitalDocument` + Types +
MetadataProfiles, leurs contrôleurs API/Settings/OPAC, services, policies,
`RecordDigitalFolderMetadataProfileResource`/`RecordDigitalDocumentMetadataProfileResource`
V1) reste **vivant et routé** — le supprimer casserait des fonctionnalités
actives (imports EAD/SEDA, API Phase 9, OPAC, Settings, Dolly).

**Décisions à prendre par l'utilisateur (hors périmètre de cette suppression,
car elles nécessitent un arbitrage fonctionnel, pas juste une analyse de code
mort) :**
1. Porter `RecordChildController` et `SEDAExportController` sur le modèle
   unifié `Record` (le binding SEDA est probablement cassé aujourd'hui).
2. Rebrancher (nouveau bouton UI) ou supprimer formellement la route
   `record-digital-transfer/*` et son contrôleur, désormais orpheline côté UI.
3. Trancher le sort de `record_digital_folder_metadata_profiles` /
   `record_digital_document_metadata_profiles` (tables vides, doublon probable
   de `record_type_metadata_profiles`) avant de finaliser l'API D02 qui vient
   de les exposer.
4. Les tests `DigitalFoldersApiTest`, `DigitalDocumentsApiTest`, `FolderApiTest`,
   `DocumentApiTest` (pré-existants, en échec depuis la vérification de santé du
   2026-08-05) testent du code **toujours vivant** — ce ne sont donc pas des
   tests de code mort à supprimer, mais de vraies régressions/legacy à traiter
   séparément.
5. Planifier la « Phase 7 » de nettoyage du schéma (suppression effective des
   tables `record_physicals`/`record_digital_folders`/`record_digital_documents`
   et satellites) une fois les points 1 à 3 résolus.

## Finalisation de la migration vers le modèle unifié Record (2026-08-05)

Suite au nettoyage du code mort (section précédente), l'utilisateur a demandé de
« finaliser la migration vers Record unifié ». Le diagnostic avait montré que la
migration n'était pas terminée : plusieurs chemins **actifs** créaient encore des
lignes dans les tables legacy (`record_physicals`) ou étaient cassés depuis
l'unification. Ce tour de travail ferme tous les chemins qui **créaient de la
nouvelle dette legacy** et corrige les liaisons de route cassées.

### Portés vers `Record` (avec tests)

1. **`RecordChildController`** (web, `records.child` / `record-child.*`) — entièrement
   réécrit sur `Record`. Corrige au passage 3 bugs préexistants et indépendants de
   l'unification :
   - `create(INT $id)` : `INT` n'est pas un type PHP valide → erreur fatale à chaque
     appel. La route `record-child.create` était donc cassée à 100 % avant ce correctif.
   - `record::findOrFail($id)` : classe `record` (minuscule) inexistante.
   - **Découverte plus profonde** : le paramètre de route généré par
     `Route::resource('records.child', ...)` s'appelle `{record}`, pas `{parent}`.
     Le contrôleur (legacy et donc aussi ma première version) type-hintait
     `$parent` : Laravel ne pouvait pas faire le binding par nom et résolvait un
     `Record`/`RecordPhysical` **vide** via le conteneur. Résultat mesuré en test :
     un `store()` qui insérait `level_id = NULL` en base (violation de contrainte)
     et un rattachement au parent silencieusement perdu (`$parent->id` = null).
     Corrigé en renommant le paramètre en `$record` partout (index/create/store).
   - Le formulaire `records/child/create.blade.php` poste déjà vers `records.store`
     (le endpoint unifié) — `RecordChildController::store()` n'est donc pas le chemin
     réellement emprunté par l'UI actuelle, mais reste corrigé et fonctionnel si
     appelé directement (garantit qu'aucun appelant ne recrée du `RecordPhysical`).
   - Tests : `tests/Feature/RecordChildAndSedaExportTest.php` (5 tests, verts).

2. **`SEDAExportController::exportRecord`** (`records.export.seda`) — le binding de
   route `RecordPhysical $record` était cassé pour la quasi-totalité des notices
   actuelles : les ids de `record_physicals` (23 lignes) ne coïncident plus avec ceux
   de `records` (132 lignes) depuis la migration. Reporté à `Record $record`, propagé
   à `SedaZipBuilder::buildForRecord()`. **Bug silencieux corrigé au passage** :
   `SEDAExport::addDatesAndIdentifiersToContent()` lisait `$record->date_start`/
   `date_end` (nommage `RecordPhysical`) alors que `Record` expose `start_date`/
   `end_date` — l'export SEDA d'une notice unifiée omettait donc silencieusement
   `<StartDate>`/`<EndDate>` avant ce correctif.

3. **`EADImportService`** et **`SedaImportService`** (imports EAD3/SEDA 2.1, appelés
   par `SlipController::import`, route `slips.import`, active) — remplacent
   `RecordPhysical::create()` par `Record::create()`. `records.code` est
   `UNIQUE NOT NULL` (contrairement à `record_physicals.code`, nullable) : ajout
   d'un repli `{PREFIX}-{horodatage}-{aléatoire}` quand l'EAD/SEDA source n'a pas
   d'identifiant. `level_id`/`status_id` (NOT NULL sur `records`) repliés sur la
   première valeur de référence disponible, comme le fait déjà `RecordController`
   côté web. Vérifié par un smoke-test EAD bout-en-bout (import réel dans une
   transaction annulée) : notice créée avec `level_id`/`status_id`/`organisation_id`
   valides.

### Sciemment reporté (non traité ce tour, avec justification)

Deux composants touchent encore les modèles legacy et ont été **volontairement
laissés en l'état** plutôt que portés dans la foulée :

- **`RecordDigitalTransferController` + `DigitalPhysicalTransferService`**
  (`record-digital-transfer/*`, déjà signalé orphelin côté UI). Un vrai portage
  n'est plus un simple renommage : dans le modèle unifié, « transférer » un
  numérique vers un physique devient une **fusion de deux `Record` déjà
  existants** (déplacement des `RecordMedium`, ré-attachement des enfants,
  soft-delete + `RecordRelation` — mécanisme déjà éprouvé par
  `MigrateToUnifiedRecords::mergeTransferredPairs()`), ce qui change le contrat de
  l'API (`type=document|folder` n'a plus de sens, `cancel` devient une opération
  d'« annulation de fusion » différente de l'ancienne). Ce contrat est
  actuellement figé par **26 tests existants et verts**
  (`tests/Feature/DigitalPhysicalTransferTest.php`,
  `tests/Unit/DigitalPhysicalTransferServiceTest.php`) écrits contre l'ancienne API.
  Le redesign nécessite de réécrire ces 26 tests en même temps — jugé trop risqué
  à faire en fin de tour sans dédier une passe propre.
- **OPAC `DigitalFolderController`/`DigitalDocumentController`** (lecture publique).
  Porter la navigation « dossiers/documents numériques » vers `Record`+
  `RecordMedium` pose une vraie question de conception (le nouveau modèle ne porte
  plus le numérique au niveau du conteneur mais au niveau du support ; naviguer
  par « dossier numérique » devient soit un aplatissement des résultats soit une
  requête récursive sur `parent_id`, arbitrage produit et non un renommage
  mécanique) sur une surface **publique et sensible côté contrôle d'accès**, sans
  aucun test existant pour verrouiller le comportement attendu. Reporté plutôt que
  deviné.

Ces deux points, plus les éléments déjà listés lors du nettoyage précédent
(API Phase 9 `RecordDigitalFolderApiController`/`RecordDigitalDocumentApiController`
à déprécier au profit de l'API D02 `records`/`records/{record}/children` déjà
complète, consolidation `record_digital_*_metadata_profiles` →
`record_type_metadata_profiles`, migration des écrans Settings Types), forment le
reste du travail avant que les tables `record_physicals`/`record_digital_folders`/
`record_digital_documents` puissent être réellement supprimées (Phase 7).

### Bug de sécurité pré-existant noté, hors périmètre

`RecordChildController` n'a jamais eu de vérification d'autorisation
(`Gate::authorize`/`$this->authorize`), contrairement à `RecordController`. Ce
n'est pas une régression introduite ici (le code legacy ne l'avait pas non plus) :
signalé pour une correction séparée, pas traité dans ce tour pour rester sur le
périmètre « finalisation du modèle unifié ».

### Vérification

- Nouveau fichier `tests/Feature/RecordChildAndSedaExportTest.php` : 5/5 verts.
- Smoke-test manuel `EADImportService::importRecordsFromString()` : notice créée
  avec succès sur le modèle unifié (transaction annulée après vérification).
- Aucun test existant ne référence `RecordChildController`, `SEDAExportController`,
  `SedaZipBuilder`, `EADImportService` ou `SedaImportService` : zéro régression
  possible sur une suite préexistante pour ces fichiers.
- `resources/views/submenu/repositories.blade.php` et `records/child/index.blade.php` :
  Gates `RecordPhysical::class` → `Record::class` (comportement identique — même
  `RecordPolicy` résolu dans les deux cas — mais cohérent avec le modèle réellement
  utilisé par les contrôleurs cibles).

## Déplacement des champs descriptifs de Record vers le système de métadonnées dynamique (2026-08-05)

Chantier planifié (voir plan approuvé), exécuté en 6 phases. Objectif : les ~27
colonnes descriptives figées sur `records` (19 ISAD(G) narratifs + 8 non-ISAD :
content, biographical_history, archival_history, acquisition_source, appraisal,
accrual, arrangement, access_conditions, reproduction_conditions,
language_material, characteristic, finding_aids, location_original,
location_copy, related_unit, publication_note, note, archivist_note,
rule_convention, extent, category_precision, table_of_contents, quantity,
dimension, publisher, sort_value, geographic_scope) deviennent des
`MetadataDefinition` système (`is_system=true`), rattachées à chaque `RecordType`
via `RecordTypeMetadataProfile` (mandatory/visible/ordre configurables par type),
stockées dans `records.metadata` (JSON, déjà existant). Permet de créer de
nouveaux `RecordType` (dossier patient, acte de naissance, offre de service) avec
leurs propres profils de métadonnées système + personnalisées, sans toucher au
schéma. `code`/`name`/`type_id`/`level_id`/`status_id`/`organisation_id`/
`parent_id`/dates de cycle de vie restent des colonnes réelles (listes, tri,
recherche indexée, contrainte d'unicité).

**Vérifié avant de commencer** : 0/132 notices existantes n'avaient de valeur
dans l'une de ces 27 colonnes — suppression de colonnes sans script de migration
de données.

### Réalisé
- Migration `2026_08_05_100000_drop_descriptive_columns_from_records_table.php`
  (idempotente, `down()` restaure les colonnes en `text nullable`).
- Seeder `SystemMetadataDefinitionsSeeder` : 27 `MetadataDefinition` (is_system),
  attachées à tous les `RecordType` actifs (51 au moment du seed → 1378 profils),
  `mandatory=false`/`visible=true` par défaut (comportement identique à avant).
- `MetadataDefinition::$fillable`/`$casts` : ajout de `is_system` (manquant),
  scope `system()`.
- `Record::$fillable`/`toSearchableArray()` : colonnes retirées ; l'index Scout
  aplatit désormais `metadata` en texte (`flattenMetadataForSearch()`) plutôt que
  du JSON brut.
- **Nouvel écran d'admin** `settings.record-types.metadata.*`
  (`RecordTypeMetadataProfileController` + bloc ajouté à
  `settings/record-types/edit.blade.php`) : attacher/détacher une définition à un
  type, régler mandatory/visible/ordre. Sans cet écran la fonctionnalité aurait
  été inutilisable sans passer par la base.
- `RecordController::validateRecord()`, `RecordChildController::store()`,
  `Api/V1/RecordController`, `Api/V1/RecordChildController` : validation des 27
  champs déplacée vers `MetadataValidationService::validateRecordMetadata()`
  (mandatory par type), au lieu de règles codées en dur. **Bug corrigé en cours
  de route** : la validation ne se déclenchait que si le client envoyait un
  `metadata` non vide — un client omettant complètement `metadata` contournait
  les champs obligatoires. Corrigé (toujours valider dès qu'un type est résolu,
  avec `$metadata ?? []`), capturé par
  `test_store_requires_mandatory_metadata_for_the_selected_type`.
- FormRequests API (`Store/UpdateRecordRequest`, `Store/UpdateRecordChildRequest`)
  et `RecordResource` : règles/champs codés en dur retirés, exposés uniquement
  via `metadata`.
- Vues : `form-fields.blade.php` (5 inputs codés en dur retirés, couverts par la
  boucle dynamique déjà existante `metadata-fields.blade.php`), `show.blade.php`
  (7 champs codés en dur → boucle générique `getVisibleMetadataFields()`, couvre
  maintenant les 27 au lieu de 7), `records/child/create.blade.php` (463 → ~250
  lignes : le formulaire ISAD à onglets, dont le `<form action>` pointait déjà
  par erreur vers `records.store` au lieu de `record-child.store` — silencieux
  depuis l'origine —, remplacé par le même bloc dynamique, POST corrigé vers la
  bonne route).
- Exports/imports repointés vers `getMetadataValue()`/`setMultipleMetadata()` :
  `SEDAExport`, `EADExport`, `RecordsExport`, `UnifiedRecordsExport`,
  `MigrateToUnifiedRecords::mapPhysical()`, `PublicRecord` (4 accesseurs +
  `scopeSearchContent()`), `QueryExecutorService::mapFieldNames()`.
  **Bugs de nommage de dates corrigés au passage** (même classe que le bug
  `date_start`/`date_end` déjà trouvé dans `SEDAExport` plus tôt cette session,
  jamais vraiment fonctionnel pour `Record`) : `EADExport::addRecordComponent()`,
  `PublicRecord::getDateStartAttribute()`/`getDateEndAttribute()`.
- **Recherches trouvées en trop lors de la vérification finale** (absentes de
  l'inventaire initial, découvertes par l'exécution de la suite Api/V1 complète
  — 2 tests réellement cassés en `SQLSTATE 42S22 Unknown column 'content'`) :
  `Api/V1/SearchController::records()` (D10) et
  `Api/V1/SearchRecordController::applyCriteria()` (D10, même fichier avait aussi
  le bug `date_start`/`date_end`) — les deux interrogeaient `content` en colonne
  directe ; repointés vers `CAST(metadata AS CHAR) LIKE`. Egalement
  `PublicRecordApiController::transformRecordForApi()` : accès directs
  `$record->record->archival_history` etc. (contournant les accesseurs déjà
  corrigés de `PublicRecord`) — repointés vers `getMetadataValue()`.

### Explicitement non touché (confirmé hors périmètre)
`AiRecordContextBuilder`, `SearchActionService`, `Api/RecordSearchController`
(public), `RecordsImport.php`, `Api/AiRecordApplyController` — tous encore basés
sur le modèle legacy `RecordPhysical` (colonnes intactes, non affectées).
`EAD2002ExportService`/`DublinCoreExportService` — confirmés sans appelant
(code mort), déjà candidats à une suppression séparée.

### Vérification
- `tests/Feature/RecordControllerTest.php` (nouveau, 6 tests — ce contrôleur
  central n'avait aucun test avant ce chantier), `RecordChildAndSedaExportTest`
  (5), `RecordTypeMetadataProfileTest` (5, écran d'admin) : tous verts.
- Suite complète `tests/Feature/Api/V1/` (94 fichiers) rejouée intégralement en
  série : **692/692 verts**, 0 régression (les 2 échecs trouvés au premier passage
  ont été corrigés puis reconfirmés verts au second).
- Smoke-test manuel `EADExport::exportRecords()` sur une notice avec métadonnées
  peuplées (content/biographical_history/dates) : valeurs correctement présentes
  dans le XML produit, transaction annulée après vérification.
- `DB::table('records')->count()` inchangé (132), 0 colonne des 27 encore
  présente, 27 `MetadataDefinition` système, 1378 profils attachés.
