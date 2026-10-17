# Déclencheurs & Tâches — Plan d'exécution détaillé

**Contexte** : SHELVE, système de gestion d'archives (Laravel 12 / MySQL / Next.js, Spring Boot en portage parallèle).
**But** : permettre à une organisation de créer des **déclencheurs** qui créent des **tâches** automatiquement quand certaines conditions sont réunies (mot-clé dans un dossier, fichier créé dans un classeur, changement de statut d'une notice, etc.), et de les affecter à des **unités** ou des **personnes**.

Ce document transpose la philosophie d'**Alfresco Content Services** (Folder Rules / Repository Actions / Workflows) au cas SHELVE, puis déroule un plan d'exécution concret en phases.

---

## 1. Sources Alfresco parcourues

Documentation officielle (dépôt `Alfresco/docs-alfresco`, branche `master`) :

| Fichier | Contenu |
|---|---|
| `content-services/latest/using/content/rules.md` | **Folder Rules** : événement + conditions + actions, options (sous-dossiers, arrière-plan, désactivation), ensembles de règles, héritage, ordre, exécution manuelle. |
| `content-services/latest/develop/repo-ext-points/repo-actions.md` | **Repository Actions** : action = unité de travail réutilisable, invocable depuis règles, workflows, web scripts, jobs planifiés. |
| `content-services/latest/admin/workflows.md` | **Workflows** : BPMN 2.0, userTask/scriptTask/mailTask, gateways (parallel/exclusive), listeners, variables, 5 définitions livrées (Adhoc, Group/Parallel/Pooled Review & Approve). |
| `content-services/latest/config/action.md` | **APS Action** : règle de dossier qui **démarre un processus** (Start Process), contraintes d'usage. |

---

## 2. Philosophie Alfresco — le modèle mental à reproduire

### 2.1 Une règle = Événement + Conditions + Action

> « There are three parts to a content rule: **the event that triggers the rule**, **the conditions the content has to meet**, **the action performed on the content**. »
> — Alfresco, *Folder rules*

Alfresco présente les règles comme un outil **« If This Then That »** pour la gestion de contenu.

**Événements déclencheurs** (3 seulement chez Alfresco) :
1. Un contenu **arrive** dans le dossier (créé, copié, téléversé).
2. Un contenu **quitte** le dossier (déplacé ou supprimé).
3. Un contenu **dans** le dossier est **modifié**.

**Conditions** — deux modes composables :
- **If all criteria are met** : la règle s'applique si toutes les conditions sont vraies.
- **Unless all criteria are met** : la règle s'applique si les conditions sont fausses.
- On peut combiner un bloc **If** et un bloc **Unless** sur la même règle.
- Critères typiques : propriété du contenu (titre contient « urgent », type = X, créé avant une date, taille, aspect…).

**Actions** — ce qui se passe quand les conditions sont remplies. Chez Alfresco : `Execute script`, `Copy`, `Move`, `Check in/out`, `Link to category`, `Add/Remove aspect`, `Add simple workflow`, `Send email`, `Transform and copy`, `Import`, `Specialise type`, `Set property value`, `Start Process`.

### 2.2 Une Action = unité de travail réutilisable

> « An action is a discrete unit of work that can be invoked repeatedly… You can then call these actions from within the repository for any number of content objects. »
> — Alfresco, *Repository Actions*

Points clés :
- Une action a des **paramètres déclarés** (`addParameterDefinitions`) et une méthode d'exécution (`executeImpl`).
- Elle est **invocable depuis plusieurs sources** : Folder Rules, Workflows, Web Scripts, Scheduled Jobs, UI.
- Elle peut être exécutée **en arrière-plan** (asynchrone) — critique pour ne pas bloquer le téléversement.

### 2.3 Un Workflow = séquence de tâches liées à un contenu

> « A workflow is a sequence of connected tasks applied to a document… Each task can be performed by a person, a group, or automatically. »
> — Alfresco, *Workflows*

- BPMN 2.0 : `userTask` (humain), `scriptTask` (automatique), `mailTask` (email), gateways (parallel fork/join, exclusive XOR), events (start/end), listeners.
- 5 définitions livrées : Adhoc (1 utilisateur), Group Review & Approve (1 groupe), Parallel Review & Approve (N utilisateurs), Pooled Review & Approve (N utilisateurs, un seul prend possession), Review & Approve (1 utilisateur).
- **Une règle peut démarrer un workflow** : « Start Process » — mais **uniquement sur événement de type Create** (pas Update) chez Alfresco.

### 2.4 Options de gestion d'un ensemble de règles

- **Disable rule** : désactiver sans supprimer.
- **Rule applies to subfolders** : héritage vers les sous-dossiers.
- **Run rule in background** : exécution asynchrone + action en cas d'erreur.
- **Ensemble de règles** : ordre d'exécution (drag & drop), règles **héritées** toujours exécutées en premier, règles liées (link to rule set), désactivation de l'héritage.
- **Run Rules** : appliquer manuellement les règles aux contenus déjà présents (backfill).
- Les règles s'appliquent aux nouveaux contenus ; **pas** rétroactivement (sauf Run Rules).

---

## 3. Cartographie avec l'existant SHELVE (points d'ancrage)

Analyse de l'existant (`laravel/`, `web/`) :

| Concept Alfresco | Équivalent SHELVE existant |
|---|---|
| Événement « contenu arrive » | `RecordController::store()` (notice), `WorkplaceDocumentController::upload()` (fichier), `storeFolder()` (dossier) |
| Événement « contenu modifié » | `RecordController::status()` (PATCH `/records/{record}/status`), `update()` |
| Événement « contenu quitte » | `WorkplaceDocumentController::transfer()` (workplace → activité), `destroy()`, `share()` |
| Mots-clés | Pivot `record_keyword` + `keywords` (sync dans `AiRecordApplyController`, `SlipRecordController`, `RecordDragDropController`) |
| Classeurs / plan de classement | Tables `activities` (hiérarchie), `record_types`, `containers`, `workplaces` |
| Tâche | Table `tasks` : `organisation_id`, `title`, `description`, `assigned_to`, `taskable_type/taskable_id` (polymorphe → Record/Project), `due_date`, `priority`, `status`, `form_data`, `parent_task_id` |
| Assignation | `tasks.assigned_to` (mono-utilisateur) + `task_watchers` ; **pas** de multi-assignation (table `task_assignments` supprimée) |
| Workflow | `workflow_definitions` (BPMN XML), `workflow_instances`, `workflow_transitions` (conditions JSON `{field, operator, value}`) + `WorkflowEngine` (start/executeTransition/createTaskFromKey/resolveAssignee/computeDueDate/evaluateCondition) |
| Scheduler | `ai_routines` + `ai:routines:run-due` (toutes les minutes) = patron de processeur par poll ; `QUEUE_CONNECTION=database` |
| Journal | `task_history`, `logs`, `workplace_activities`, `notifications` (custom) |
| Hooks existants | Observers `TaskObserver`, `SlipObserver`, `Attachment::booted()` ; **aucun Event/Listener** (EventServiceProvider découvre `false`) |

**Constat** : aucun système de déclencheurs n'existe. Il n'y a ni table `triggers`, ni mécanisme d'événements métier. La brique la plus proche est `WorkflowEngine::evaluateCondition()` (format de condition JSON) et `AiRoutine` (processeur périodique).

---

## 4. Cible fonctionnelle (IFTTT à la Alfresco, adapté à SHELVE)

### 4.1 Schéma général

```
ÉVÉNEMENT (quand ?)            →   CONDITIONS (si ?)           →   ACTION (donc ?)
─────────────────────────────────────────────────────────────────────────────────
record.created                  If  { field, operator, value }      create_task
record.status_changed           Unless { field, operator, value }   start_workflow
record.keyword_added            portée : workplace / activité /      (phase 2)
record.uploaded                    record_type / keyword / statut
record.transferred
task.created (chaînage)
```

### 4.2 Événements supportés (phase 1 → 3)

| `event_type` | Déclenchement | Hook technique proposé |
|---|---|---|
| `record.created` | Nouvelle notice / dossier / fichier créé | Observer `Record::created` |
| `record.status_changed` | Statut de notice modifié | `Record::updated` (détection champ `status_id`) |
| `record.uploaded` | Fichier téléversé dans un workplace | `Attachment::created` / `Record::created` (`is_workplace_folder=false`) |
| `record.keyword_added` | Un mot-clé est attaché à une notice | Listener sur sync `record_keyword` |
| `record.transferred` | Fichier quitté un workplace vers le Records | `WorkplaceDocumentController::transfer()` |
| `task.created` | Chaînage de tâches | Observer `Task::created` (existant) |

### 4.3 Conditions (calquées sur les critères Alfresco + format existant)

- **Modes** : `If` / `Unless` / les deux (comme Alfresco). Combinaison interne `all` / `any`.
- **Opérateurs** (réutiliser `WorkflowEngine::evaluateCondition`) : `equals`, `not_equals`, `contains`, `greater_than`, `less_than`, `is_null`, `not_null`, `starts_with`, `regex`.
- **Champs évaluables** sur le record déclencheur : `name`, `title`, `code`, `status_id`, `activity_id`, `record_type_id`, `workplace_id`, `parent_id`, `metadata.*` (JSON), présence d'un keyword (via `record_keyword`), `created_at`, `is_workplace_folder`, `is_workplace_shared`.
- **Portée (scope)** : restriction du champ d'application — `workplace_id`, `activity_id` (classeur), `record_type_id`, liste de `keyword_ids`, et combinaisons. Equivalent du « rule applies to subfolders » : portée récursive sur `activities.parent_id` / `records.parent_id`.

### 4.4 Actions

| `action_type` | Effet | Champs `action_config` |
|---|---|---|
| `create_task` | Crée une `Task` liée au record déclencheur | `title_template`, `description_template`, `priority`, `due_days` (jours ouvrables), `assignment_type`, `assignment_value`, `taskable` (Record lié), `create_notification` |
| `start_workflow` (phase 2) | Démarre une instance de workflow sur le record (réutilise `WorkflowEngine::startWorkflow`) | `workflow_definition_id`, `name` |

### 4.5 Assignation (personnes ou unités)

Inspirée de `WorkflowEngine::resolveAssignee` + des 5 définitions de workflows Alfresco :

| `assignment_type` | Cible | Résolution |
|---|---|---|
| `user` | Une personne | `users.id` direct |
| `role` / `function` | Un rôle de l'organisation | titulaires du rôle (via `roles` / `user_organisation_role`) |
| `unit` | Une unité / organisation | membres de l'unité (via `workplace_members` ou `user_organisation_role`) |
| `creator` | L'auteur de l'événement | `record.creator_id` / auteur de l'action |
| `manager` | Le responsable de la notice | `records.assigned_to` |
| `previous` | L'auteur de la tâche précédente | `task.updated_by` |

> **Décision structurante** : la table `tasks.assigned_to` est **mono-utilisateur**. Pour affecter à une **unité** (plusieurs personnes), il faut **recréer la table `task_assignments`** (`task_id`, `user_id`, `organisation_id`, rôle/type) et garder `tasks.assigned_to` comme assigné « principal » (premier de la file). Les permissions `task_assignment_*` existent toujours en base (mortes aujourd'hui) → les réactiver.

---

## 5. Modèle de données proposé

### 5.1 Table `triggers`

| Colonne | Type | Rôle |
|---|---|---|
| `id` | bigint PK | |
| `organisation_id` | bigint FK | portée organisation (org-scope, R03) |
| `name` | string(190) | libellé |
| `description` | text nullable | |
| `event_type` | string(40) | `record.created` etc. |
| `event_mode` | string(10) | `create` (synchrone différé) / `update` / `delete` — inspiré des 3 événements Alfresco |
| `condition_mode` | string(10) | `if` / `unless` / `both` |
| `conditions` | json | liste `{field, operator, value}` (bloc If) |
| `unless_conditions` | json | liste (bloc Unless) |
| `scope` | json | `{workplace_ids[], activity_ids[], record_type_ids[], keyword_ids[], recursive}` |
| `action_type` | string(20) | `create_task` / `start_workflow` |
| `action_config` | json | dépend de l'action |
| `run_in_background` | bool | asynchrone (défaut true) |
| `is_enabled` | bool | activé/désactivé (Alfresco « Disable rule ») |
| `sort_order` | int | ordre dans l'ensemble de règles |
| `run_count` | int | compteur d'exécutions |
| `last_run_at` | datetime nullable | |
| `created_by / created_at / updated_by / updated_at` | | audit |

### 5.2 Table `trigger_runs` (journal d'exécution)

`id, trigger_id, triggerable_type, triggerable_id, event_type, matched (bool), status (success|failed|skipped), output (json), executed_by, executed_at` → équivalent de l'audit Alfresco + `task_history`.

### 5.3 Table `task_assignments` (recréée)

`id, task_id FK, user_id FK, organisation_id FK nullable, assignment_type (user|unit|role|creator|manager|previous), added_by, added_at` + unique(`task_id`, `user_id`).

> Toutes les migrations sont à écrire côté Laravel (schéma maître). Spring Boot suit avec `ddl-auto: none` (refléter les colonnes dans l'entité `Organisation`, ajouter l'entité `Trigger`).

---

## 6. Architecture d'exécution

### 6.1 Moteur : `TriggerEngine` (service Laravel)

```
Événement métier
   │
   ▼
Observer / hook (Record::created, ...)
   │   dispatch (QUEUE_CONNECTION=database, async)
   ▼
TriggerMatcherJob (par événement)
   │   charge les triggers is_enabled + event_type + org du record
   │   pour chaque trigger (dans l'ordre sort_order) :
   │     1. Vérifie le SCOPE (workplace/activité/type/keywords, récursif)
   │     2. Évalue If (all/any)  et  Unless
   │     3. matched → exécute l'ACTION via ActionRunner
   │     4. écrit trigger_runs + notifications
   │
   ▼
ActionRunner (create_task / start_workflow)
```

- **Asynchrone par défaut** (comme « Run rule in background ») : ne bloque jamais l'upload.
- **Exécution manuelle** (« Run Rules » d'Alfresco) : endpoint `POST /api/v1/triggers/{trigger}/run` sur un record cible, ou relecture d'un dossier.

### 6.2 Journalisation et notifications

- Chaque exécution → `trigger_runs` (match, statut, output) → l'UI affiche l'historique.
- Tâche créée → `task_history` (action `created` via l'observer existant) + `notifications` (module `Workflows`) aux assignés.
- Erreur → `trigger_runs.status=failed` + log + (option) `action_config.error_notification_user_id`.

---

## 7. Plan d'exécution en phases

### Phase 1 — Socle (modèle + CRUD + moteur + action `create_task` mono) — ✅ LIVRÉE

**Objectif** : un déclencheur configurable qui crée une tâche assignée à une personne sur événement `record.created` / `record.status_changed`, en asynchrone, avec journal.

**Livré le 2026-08-10** :
- Migration `2026_08_10_100000_create_triggers_tables.php` (tables `triggers` + `trigger_runs`).
- Modèles `Trigger`, `TriggerRun` (org-scope R03, casts JSON).
- Requests `StoreTriggerRequest` / `UpdateTriggerRequest` (validation événement + conditions If/Unless + action).
- `TriggerController` (CRUD org-scopé + `run` manuel + `runs` journal) + routes `routes/api/Triggers.php`.
- Permissions `trigger_*` (seeder `PermissionCategorySeeder`) + `TriggerPolicy`.
- `TriggerEngine` (dispatch/process/matchesScope/evaluate/createTask/resolveAssignee/computeDueDate/renderTemplate) — opérateurs `contains`/`starts_with` insensibles à la casse (aligné Alfresco).
- `TriggerMatcherJob` (async, queue database) + `RecordObserver` (record.created/uploaded/status_changed).
- Tests `TriggerApiTest` : **13 tests verts** (CRUD, isolation R03, déclenchement, If/Unless, run manuel, journal).

| # | Tâche | Fichiers cibles |
|---|---|---|
| 1.1 | Migration `triggers` + `trigger_runs` | `laravel/database/migrations/2026_08_10_100000_create_triggers_tables.php` ✅ |
| 1.2 | Modèles `Trigger`, `TriggerRun` (+ relations, scopes `enabled()`, `forEvent()`) | `laravel/app/Models/Trigger.php`, `TriggerRun.php` ✅ |
| 1.3 | Requests `StoreTriggerRequest` / `UpdateTriggerRequest` | `laravel/app/Http/Requests/Api/V1/Trigger/` ✅ |
| 1.4 | Controller CRUD `TriggerController` + routes `routes/api/Triggers.php` | `laravel/app/Http/Controllers/Api/V1/TriggerController.php` ✅ |
| 1.5 | Permissions : seeder (`trigger_viewAny/view/create/update/delete`, `trigger_run`) + Policy | `database/seeders/…`, `app/Policies/TriggerPolicy.php` ✅ |
| 1.6 | **`TriggerEngine`** : `matchEvent()`, `evaluateConditions()`, `checkScope()`, `execute()`, `resolveAssignee()`, `computeDueDate()` | `laravel/app/Services/Triggers/TriggerEngine.php` ✅ |
| 1.7 | **ActionRunner** : `create_task` | intégré dans `TriggerEngine::createTask()` ✅ |
| 1.8 | **`TriggerMatcherJob`** (async, `queue=database`) | `laravel/app/Jobs/TriggerMatcherJob.php` ✅ |
| 1.9 | Observers émetteurs : `RecordObserver` (created/status_changed) | `laravel/app/Observers/RecordObserver.php` ✅ |
| 1.10 | Tests : `TriggerApiTest` | `laravel/tests/Feature/Api/V1/TriggerApiTest.php` ✅ |

**Critère de sortie atteint** : `POST /api/v1/triggers` (événement `record.status_changed`, action `create_task` assignée à un user) → PATCH statut d'un record → une tâche apparaît, journal alimenté, test vert (vérifié de bout en bout via l'API + worker queue).

### Phase 2 — Assignation unités, mots-clés, workflow

| # | Tâche | Détails |
|---|---|---|
| 2.1 | Recréer `task_assignments` + CRUD + réactivation permissions `task_assignment_*` | multi-affectation ; `assigned_to` = principal |
| 2.2 | Assignation **unit**/**role** : résolution des membres | `workplace_members`, `roles`, `user_organisation_role` |
| 2.3 | Événement `record.keyword_added` : listener sur sync `record_keyword` | hooks dans `AiRecordApplyController`, `SlipRecordController`, `RecordDragDropController` |
| 2.4 | Événements `record.uploaded` et `record.transferred` | `Attachment::created`, `WorkplaceDocumentController::transfer()` |
| 2.5 | Action `start_workflow` : démarre une instance via `WorkflowEngine` sur le record | équivalent « Start Process » Alfresco |
| 2.6 | Notifications aux assignés (table `notifications`, module Workflows) + email optionnel | |
| 2.7 | Tests d'intégration (assignation unité, keywords, workflow) | |

### Phase 3 — Frontend Next.js + ergonomie — ✅ LIVRÉE (CRUD + test + journal)

**Livré le 2026-08-10** :
- Feature `triggers` : `web/src/features/triggers/` (`services/trigger.service.ts` + `pages.tsx`).
- Écrans : **Liste** (nom, événement, action, actif, exécutions, dernier déclenchement) · **Détail** (configuration + conditions If/Unless + exécution manuelle « Tester » + journal) · **Formulaire** (création/édition : événement, conditions, portée, action, assignation).
- Enregistrée dans `features/index.ts` + navigation sous **Workflow → Déclencheurs** (`web/src/lib/navigation.ts`).
- Typecheck vert ; pages rendues (200) vérifiées.

| # | Tâche | Détails |
|---|---|---|
| 3.1 | Feature `triggers` : pages Liste / Création / Édition / Détail (CRUD) | `web/src/features/triggers/pages.tsx`, `services/trigger.service.ts` ✅ |
| 3.2 | Éditeur de conditions (champs + opérateurs + valeurs, blocs If/Unless) | composant `ConditionEditor` ✅ |
| 3.3 | Éditeur de portée (workplaces, activités/classeurs, récursif) | section Portée ✅ |
| 3.4 | Config d'action `create_task` (titre/description/priorité/échéance/assignation) | formulaire + validation ✅ |
| 3.5 | Bouton **« Tester »** (`POST /triggers/{id}/run` sur un record) | dans le Détail ✅ |
| 3.6 | Onglet **Journal** (trigger_runs : match, statut, tâches créées, erreurs) | dans le Détail ✅ |
| 3.7 | Navigation : sous-menu « Déclencheurs » (Workflow) | `web/src/lib/navigation.ts` ✅ |
| 3.8 | (reste) Affichage des tâches créées automatiquement (badge/source trigger sur la tâche) | `workflow/tasks` |

### Phase 4 — (Optionnel) Exécution périodique & Spring Boot

| # | Tâche | Détails |
|---|---|---|
| 4.1 | Déclencheur temporel (`schedule`) : `triggers:run-due` calqué sur `ai:routines:run-due` (scope `next_run_at`) | conditions « aucune activité depuis N jours », échéances |
| 4.2 | Portage Spring : entité `Trigger`, endpoint de lecture, refléter colonnes | `backend/…` (ddl-auto none, base partagée) |

---

## 8. Règles produit à respecter (calquées Alfresco)

1. **Un déclencheur = événement + conditions + action**, une seule action par défaut (extensible à plusieurs, comme Alfresco, en phase 3).
2. **If / Unless** composables ; combinaison `all` / `any` pour chaque bloc.
3. **Asynchrone par défaut** (`run_in_background=true`) : ne jamais bloquer l'upload ni la sauvegarde d'un record.
4. **Nouveaux contenus seulement** : un déclencheur ne s'applique pas rétroactivement, sauf action « Exécuter maintenant » (équivalent `Run Rules`).
5. **Désactivation** plutôt que suppression (« Disable rule »).
6. **Ordre d'exécution** respecté (`sort_order`) ; les déclencheurs hérités (portée récursive) avant les propres.
7. **Org-scope strict (R03)** : un déclencheur ne voit que les records de son organisation ; 404 hors périmètre.
8. **Journal complet** : `trigger_runs` + `task_history` + `notifications`.
9. **Contrainte « Start Process » d'Alfresco** : l'action `start_workflow` n'est disponible que sur événement `create` (pas `update`).
10. **Superadmin bypass** : le créateur/éditeur de déclencheurs doit avoir `trigger_*` ; un superadmin teste sans restriction.

---

## 9. Risques & décisions à trancher

| Sujet | Décision | Impact |
|---|---|---|
| Multi-assignation | Recréer `task_assignments` (oui/non ?) | Affichage des tâches par unité |
| Rétroactivité | « Exécuter maintenant » uniquement (pas de backfill auto) | Alignement Alfresco |
| Syntaxe des conditions | Réutiliser `{field, operator, value}` de `WorkflowEngine::evaluateCondition()` | Réutilise le moteur existant |
| Portée récursive | sur `activities`/`records` (parent_id) | Équivalent « subfolders » |
| Déclencheur temporel | Phase 4 optionnelle | Scope de la v1 |
| Spring Boot | Phase 4 : lecture seule d'abord | Cohérence base partagée |

---

*Document généré à partir de l'analyse du code SHELVE et de la documentation officielle Alfresco (`Alfresco/docs-alfresco`, master).*
