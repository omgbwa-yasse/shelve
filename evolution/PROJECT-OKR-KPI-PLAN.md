# Plan d'intégration — Module Projet / Tâche / OKR / KPI

> **Date** : 2026-08-05
> **Périmètre** : nouveau module rattachable à un `Workplace`, une `Organisation` (unité administrative) ou un `User` (personne), exposé en API v1, consommé par le frontal Next.js (`evolution/next/`).
> **Constat de départ** : terrain vierge — aucun modèle `Project`/`Objective`/`KeyResult`/`Kpi` n'existe. En revanche, `Task` (`app/Models/Task.php`) est **déjà polymorphe et générique** (`taskable_type`/`taskable_id`, `workflow_instance_id` nullable, `assigned_to`, `organisation_id`, `due_date`, `priority`, `status`, `parent_task_id`) — il n'a besoin d'**aucune migration** pour être rattaché à un `Project` : il suffit de lui donner `taskable_type = Project::class`.

---

## 0. Décision structurante : le rattachement polymorphe

Le besoin « rattaché à un Workplace, une unité administrative ou une personne » est un **choix parmi 3 cibles hétérogènes**, exactement le problème que `Task.taskable` résout déjà pour les tâches. Je propose de généraliser ce patron à `Project`, `Objective` et `Kpi` via un trait partagé, plutôt que 3 colonnes `workplace_id`/`organisation_id`/`user_id` nullables sur chaque table (qui obligerait une contrainte applicative « exactement une des trois remplie » répétée partout, plus fragile).

**Trait `App\Traits\HasAttachable`** (nouveau, à créer une fois, réutilisé par les 3 modèles) :
- colonnes portées : `attachable_type`, `attachable_id` (morphTo)
- relation `attachable()`
- règle de validation centralisée : `attachable_type` ∈ `{Workplace::class, Organisation::class, User::class}`
- scopes `scopeAttachedToWorkplace`, `scopeAttachedToOrganisation`, `scopeAttachedToUser`

**À valider avec toi avant de commencer** : ce choix (polymorphe unique) plutôt que 3 FK nullables. Si tu préfères 3 colonnes explicites (plus lisible en base, moins générique), dis-le — ça change les migrations du §1 mais rien d'autre dans le plan.

---

## 1. Modèle de données (Laravel)

### 1.1 `projects`
```
id, code (unique), name, description,
status (enum: draft|active|on_hold|completed|archived),
start_date, end_date,
owner_id (FK users),
attachable_type, attachable_id (polymorphe — voir §0),
organisation_id (FK organisations — scope multi-tenant, cohérent avec BelongsToOrganisation partout ailleurs),
created_by, updated_by, timestamps, softDeletes
index (attachable_type, attachable_id)
```

### 1.2 `objectives` (le "O" d'OKR)
```
id, project_id (FK projects, NULLABLE — un objectif peut vivre sans projet, directement rattaché),
title, description,
period_start, period_end (trimestre/année de l'OKR),
status (enum: on_track|at_risk|off_track|done),
owner_id (FK users),
attachable_type, attachable_id (idem §0 — permet un OKR d'équipe/personne sans projet),
organisation_id, timestamps, softDeletes
```

### 1.3 `key_results` (le "KR" d'OKR)
```
id, objective_id (FK objectives, cascadeOnDelete),
title,
metric_type (enum: number|percentage|currency|boolean),
start_value, target_value, current_value (decimal),
unit (nullable, ex. "dossiers", "%", "€"),
status (calculé ou stocké : on_track|at_risk|off_track),
due_date, sort_order, timestamps
```
Progression = `(current_value - start_value) / (target_value - start_value)`, calculée dans un accesseur `getProgressAttribute()`, jamais stockée en dur (évite la désynchronisation).

### 1.4 `kpis`
```
id, code, name, description,
unit, target_value, direction (enum: higher_is_better|lower_is_better),
frequency (enum: daily|weekly|monthly|quarterly|yearly),
attachable_type, attachable_id (idem §0),
owner_id, organisation_id, created_by, timestamps, softDeletes
```

### 1.5 `kpi_measurements` (historique — indispensable pour un graphique de tendance)
```
id, kpi_id (FK kpis, cascadeOnDelete),
value (decimal), measured_at (date), recorded_by (FK users), timestamps
```

### 1.6 `tasks` — aucune migration
Réutilisation directe : `taskable_type = Project::class`, `taskable_id = $project->id`. Vérifier seulement qu'un index `(taskable_type, taskable_id)` existe déjà (probable vu l'usage existant côté `Record`) ; sinon l'ajouter dans une petite migration `add_index_to_tasks_taskable`.

**Relations à ajouter** :
- `Project::tasks()` → `morphMany(Task::class, 'taskable')`
- `Project::objectives()` → `hasMany(Objective::class)`
- `Objective::keyResults()` → `hasMany(KeyResult::class)`
- `Workplace::projects()`, `Organisation::projects()`, `User::projects()` → `morphMany` inverse via `attachable`

---

## 2. Permissions & policies

Convention observée (`PermissionCategorySeeder.php`) : `{module}_{ressource}_{action}` + un `module_{x}_access` de premier niveau (catégorie `system`, contrôle l'apparition du menu).

| Permission | Rôle |
|---|---|
| `module_projects_access` | Accès au menu "Projets" |
| `project_view` / `project_create` / `project_update` / `project_delete` | CRUD projet |
| `objective_view` / `objective_create` / `objective_update` / `objective_delete` | CRUD OKR |
| `key_result_update` | Mise à jour de la progression (plus fréquent, permission dédiée et plus permissive que `objective_update`) |
| `kpi_view` / `kpi_create` / `kpi_update` / `kpi_delete` | CRUD KPI |
| `kpi_measurement_create` | Enregistrer une mesure |

**Policies** (`app/Policies/{Project,Objective,Kpi}Policy.php`, héritent de `BasePolicy` comme `WorkflowDefinitionPolicy`) : en plus du couple permission + `organisation_id`, vérifier l'accès à la cible `attachable` :
- `attachable_type = Workplace::class` → l'utilisateur doit être membre du workplace (`WorkplaceMember`)
- `attachable_type = Organisation::class` → l'utilisateur doit appartenir à cette organisation
- `attachable_type = User::class` → l'utilisateur doit être la personne elle-même ou son responsable hiérarchique

---

## 3. API v1

Patron répliqué de `WorkflowDefinitionController` :

| Élément | Fichier |
|---|---|
| Controllers | `app/Http/Controllers/Api/V1/{Project,Objective,KeyResult,Kpi,KpiMeasurement}Controller.php` |
| FormRequests | `app/Http/Requests/Api/V1/{Project,Objective,KeyResult,Kpi}/{Store,Update}{X}Request.php` |
| Resources | `app/Http/Resources/Api/V1/{Project,Objective,KeyResult,Kpi}Resource.php` |
| Routes | nouveau fichier `routes/api/D17.php` (prochaine lettre de domaine libre après D02–D16), inclus dans `routes/api.php` |
| Query engine | trait `HandlesApiQueries` existant — `FILTERABLE` (status, attachable_type, attachable_id, organisation_id), `SORTABLE` (created_at, due_date, name), `INCLUDABLE` (owner, tasks, objectives, keyResults, measurements) |

**Endpoints clés** (au-delà du CRUD REST standard) :
- `GET /api/v1/projects/{project}/tasks` — délègue au `TaskController` existant, filtré par `taskable`
- `GET /api/v1/objectives/{objective}/key-results`
- `PATCH /api/v1/key-results/{keyResult}` — mise à jour rapide de `current_value` (écran de suivi OKR, appelé souvent)
- `POST /api/v1/kpis/{kpi}/measurements` — enregistrer un point de mesure
- `GET /api/v1/kpis/{kpi}/measurements?from=&to=` — historique pour graphique

Toutes les routes respectent le patron déjà en place : scope `byOrganisation(Auth::user()->current_organisation_id)`, `findOrFail` (404 cross-org, jamais 403 — cohérent avec le motif D03 déjà documenté ailleurs dans le projet), `$this->authorize()` par action.

---

## 4. Frontal Next.js (`evolution/next/`)

### 4.1 Nouveau module `features/projects/`
Patron minimal déjà observé sur `features/workflow/` :
```
features/projects/
├── services/
│   ├── project.service.ts
│   ├── objective.service.ts
│   └── kpi.service.ts
├── components/
│   ├── ProjectList.tsx
│   ├── ProjectForm.tsx          (inclut le sélecteur de rattachement, voir 4.3)
│   ├── ObjectiveBoard.tsx       (OKR : objectifs + barres de progression des KR)
│   ├── KeyResultProgress.tsx
│   └── KpiCard.tsx              (valeur courante, tendance, sparkline)
├── types.ts
└── pages.tsx                    (assembleur, comme les autres features)
```
Chaque service passe par `apiFetch<T>()` (`lib/api/client.ts`) → proxy `/api/proxy/*` → jamais d'appel direct au backend, cohérent avec la règle déjà en place.

### 4.2 Navigation (`lib/navigation.ts`)
Nouveau domaine inséré entre `workflow` et `workplaces` :
```
{
  key: 'projects', label: 'Projets', href: '/projects', icon: 'projects',
  items: [
    { group: 'Projets',           ... '/projects' },
    { group: 'Objectifs (OKR)',   ... '/projects/objectives' },
    { group: 'KPI',               ... '/projects/kpis' },
    { group: 'Tâches',            ... '/projects/tasks' },  // réutilise l'écran Tâches existant, filtré taskable_type=Project
  ],
}
```
Icônes à ajouter au registre (`components/icons/index.tsx`) : `Rocket`/`Flag` (projets), `Target` (objectifs) — `trendingUp` existe déjà pour les KPI.

### 4.3 Réutilisation directe de `SelectionModal`
Le sélecteur de rattachement (Workplace / Unité administrative / Personne) est **exactement** le cas d'usage déjà validé (`records/create`, démo Auteur/Contenant) : un toggle à 3 options, puis `SelectionModal` ouvre sur le jeu de données correspondant (workplaces, organisations, ou utilisateurs), avec bandeau A-Z pour les personnes/organisations (triées par nom) et pagination pour les workplaces. Aucun nouveau composant UI à créer — seulement les 3 services de recherche côté API.

---

## 5. Séquencement proposé

| Phase | Contenu | Dépend de |
|---|---|---|
| 1 | Migrations (§1) + modèles + trait `HasAttachable` | — |
| 2 | Permissions (§2) + policies | Phase 1 |
| 3 | API v1 : controllers/requests/resources/routes (§3) + tests Feature | Phase 2 |
| 4 | Seeder de démonstration (2-3 projets, OKR, KPI factices, cohérent avec le reste des seeders `database/seeders/`) | Phase 3 |
| 5 | `features/projects/` Next.js : services + types | Phase 3 (contrat API stable) |
| 6 | Composants UI (liste, formulaire + sélecteur de rattachement via `SelectionModal`, tableau OKR, cartes KPI) | Phase 5 |
| 7 | Navigation (`navigation.ts` + icônes) + pages `app/(back-office)/projects/*` | Phase 6 |
| 8 | Historique KPI + graphique de tendance (réutilise `KpiMeasurement`) | Phase 3, 6 |

---

## 6. Points à trancher avant de démarrer l'implémentation

1. **Rattachement polymorphe unique (§0) vs 3 colonnes FK explicites** — ma recommandation est le polymorphe (cohérent avec `Task.taskable` déjà en place), mais c'est ton arbitrage.
2. **Un Objective doit-il toujours appartenir à un Project, ou peut-il être autonome ?** — j'ai supposé `project_id` nullable (OKR d'équipe sans projet formel possible). À confirmer.
3. **KPI historisé (`kpi_measurements`) ou valeur courante seule ?** — j'ai supposé un historique (nécessaire pour un graphique de tendance, sinon `kpis.current_value` suffirait mais perd la dimension temporelle demandée implicitement par "KPI").
4. **La numérotation de domaine API** (`routes/api/D17.php`) — à confirmer qu'aucun autre chantier n'a déjà réservé D17.

Dis-moi comment trancher ces 4 points (ou valide les recommandations telles quelles) et je commence l'implémentation phase par phase.
