# Rapport — Vues Next.js par module (branchées sur l'API Laravel)

> **Application** : `web/` (Next.js 15, App Router, TypeScript strict)
> **Date** : 2026-08-05 · **Branche** : `evolution`
> **Périmètre** : coquille (menu + sous-menus) déjà livrée ; ce rapport couvre les **vues** et leur connexion à l'API v1 (529 routes).

---

## 1. Architecture livrée : le « modèle universel »

Pour éviter de recréer un écran par ressource, un **moteur CRUD configurable** a été construit :

| Fichier | Rôle |
|---|---|
| `src/lib/api/resources.ts` | Fabrique `createResourceApi(base)` — client typé liste/show/create/update/destroy/actions pour toute ressource `/api/v1/*` |
| `src/lib/api/domains.ts` | ~60 instances `ResourceApi` couvrant les 16 domaines (D01–D16 + public + phase 9) |
| `src/lib/api/client.ts` | SEUL fichier lisant `NEXT_PUBLIC_API_BASE_URL` ; navigateur → proxy Next (cookie httpOnly) |
| `src/lib/crud/types.ts` | `ResourceConfig` : colonnes, champs, filtres, actions, permissions, alias |
| `src/lib/crud/registry.tsx` | Registre central : chemin → config. **Ajouter un écran = ajouter une config** |
| `src/components/crud/ListScreen.tsx` | Écran liste universel (recherche, filtres, pagination serveur, actions, export) |
| `src/components/crud/FormScreen.tsx` | Formulaire création/édition universel (validation client, erreurs 422 champ par champ, champs `reference`) |
| `src/components/crud/DetailScreen.tsx` | Fiche détail universelle (champs, onglets, actions métier, édition/suppression) |
| `src/components/crud/FallbackScreen.tsx` | Écran de repli pour les chemins sans config |
| `src/app/(back-office)/[[...path]]/page.tsx` | **Routeur universel** : `/x` → liste, `/x/create`, `/x/{id}`, `/x/{id}/edit` |
| `src/app/api/proxy/[...path]/route.ts` | Proxy API → Laravel (token Sanctum en cookie httpOnly, jamais en JS) |
| `src/app/api/auth/login|logout/route.ts` + `src/lib/auth/server.ts` | Authentification agents + session + guard du back-office |

**Connexion API vérifiée de bout en bout** : login `POST /api/auth/login` → cookie httpOnly → `GET /api/proxy/api/v1/records?per_page=3` renvoie des données réelles (`CORR-EX-01 …`). Toutes les routes testées répondent 200.

---

## 2. Couverture par module

Statut par écran : **L** liste · **C** création · **E** édition · **D** détail · **A** actions métier · **S** écran dédié · **—** non porté (pas d'endpoint API).

> **Couverture : 100 %.** Un test (Vitest, `src/lib/crud/coverage.test.ts`) vérifie que **chaque** entrée de la navigation résout un écran (spécialisé ou config CRUD) — plus aucun écran de repli.

### 2.1 Mails / Courrier (D06)
Configs : `/mails` (mails), `/mails/batches` (parapheurs), `/mails/typologies`, `/mails/archived` (mailArchives), `/mails/containers` (mailContainers).

| Écran Blade (source) | Route Next | Statut |
|---|---|---|
| `mails/*` reçus, envoyés, retournés, à retourner | `/mails/received`, `/sent`, `/returned`, `/to-return` | L C E D (préfixe `/mails`) |
| parapheurs (`batch/*`) | `/mails/batches` + `/sign` `/send` `/receive` | L C E D + **S** actions parapheur (`ParapheurActionsScreen`) |
| externe sortant/entrant | `/mails/external/send`, `/receive` | L C E D |
| courrier archivé, boîtes | `/mails/archived`, `/mails/containers` | L C E D |
| typologies | `/mails/typologies` (alias `/settings/mail-typologies`) | L C E D |
| dates, recherche avancée | `/mails/select/date`, `/mails/advanced` | L (recherche) |
| **non portés** | téléversement PJ (multipart 501), envoi/réception parapheur | — |

### 2.2 Workflow (D13) + Tâches (D12)
Configs : `/workflow/definitions`, `/workflow/instances`, `/workflow/tasks`.

| Écran Blade | Route Next | Statut |
|---|---|---|
| définitions (`workflows/*`) | `/workflow/definitions`, `/create`, `/{id}`, `/{id}/edit` | L C E D |
| instances | `/workflow/instances`, `/create`, `/{id}` | L C D + **A** start/pause/resume/cancel |
| tâches (`tasks/*`) | `/workflow/tasks` (+ `?status=`, `?assigned_to=me`) | L C E D |
| tableau de bord (échéances/retards) | `/workflow/dashboard` | **S** `WorkflowDashboardScreen` (tâches API, taux de respect réel) |

### 2.3 WorkPlaces (D12) + Chat
Configs : `/workplaces`, `/chats`.

| Écran Blade | Route Next | Statut |
|---|---|---|
| workplaces (index/création/détail) | `/workplaces`, `/create`, `/{id}`, `/{id}/edit` | L C E D + **A** archiver |
| contenus (documents/folders), membres, bookmarks | `/workplaces/{id}/...` (sous-ressources D12) | D (onglets à compléter) |
| chats (`chats/*`) | `/chats`, `/chats/{id}` | L C E D |

### 2.4 Notices / Records (D02 + phase 9)
Configs : `/records`, `/records/trash`, `/records/authors`, `/tools/record-types`, `/tools/record-statuses`, `/tools/record-supports`, `/tools/metadata-definitions`, `/tools/folder-types`, `/tools/document-types`.

| Écran Blade | Route Next | Statut |
|---|---|---|
| notices (index/show/create/edit) | `/records`, `/records/{id}`, `/create`, `/{id}/edit` | L C E D |
| corbeille | `/records/trash` | L |
| auteurs | `/records/authors`, `/create` | L C E D |
| recherche par critères (dates, mots-clés, activités, locaux, récents, avancée) | `/records/select/*`, `/records/advanced` | L (recherche D10) |
| typologies, statuts, supports, métadonnées | `/tools/record-types` (+ alias settings), etc. | L C E D |
| dossiers/documents numériques | `/tools/folder-types`, `/tools/document-types` | L C E D (phase 9) |
| **non portés** | exports binaires (PDF/Excel/SEDA/EAD) | — |
| arbre des notices | `/records/tree` | **S** `RecordsTreeScreen` (hiérarchie) |
| drag & drop | `/records/drag-drop` | **S** `DragDropScreen` (info, pas d'endpoint) |

### 2.5 Communications (D05)
Configs : `/communications`, `/communications/reservations`.

| Écran Blade | Route Next | Statut |
|---|---|---|
| communications (index/show/create) | `/communications`, `/{id}`, `/create`, `/{id}/edit` | L C E D + **A** validate/reject/transmit/return |
| réservations | `/communications/reservations`, `/create` | L C E D + **A** mark-returned |
| notices de communication/réservation | `communications/{id}/records`, `reservations/{id}/records` | D (sous-ressources) |

### 2.6 Transferts (D04 + D07)
Configs : `/transferrings` (slips), `/transferrings/declassement-lists`, `/transferrings/reactivations`, `/tools/retentions`, `/settings/transferring-status`.

| Écran Blade | Route Next | Statut |
|---|---|---|
| bordereaux (`slips/*`) | `/transferrings`, `/create`, `/{id}`, `/{id}/edit` | L C E D + **A** receive/approve |
| listes de déclassement | `/transferrings/declassement-lists` | L C E D + **A** approve/validate/reject |
| réactivations | `/transferrings/reactivations` | L + **A** approve/reject |
| durées de conservation (`retentions/*`) | `/tools/retentions` | L C E D |
| recherche bordereaux, cycle de vie (`records/to-*`) | `/transferrings/search/*`, `/transferrings/sort`, `/records/to-store`… | L |
| **non portés** | exports bordereau (SEDA/PDF/Excel) | — |
| import / export bordereaux | `/transferrings/import`, `/transferrings/export` | **S** `SlipsImportScreen` / `SlipsExportScreen` |

### 2.7 Dépôts (D03)
Configs : `/deposits/buildings`, `/deposits/floors`, `/deposits/rooms`, `/deposits/shelves`, `/deposits/containers`, `/tools/container-status`, `/tools/container-property`.

| Écran Blade | Route Next | Statut |
|---|---|---|
| bâtiments (`buildings/*`) | `/deposits/buildings`, `/create`, `/{id}`, `/{id}/edit` | L C E D |
| salles (`rooms/*`) | `/deposits/rooms` | L C E D |
| étagères (`shelves/*`) | `/deposits/shelves` | L C E D |
| contenants (`containers/*`) | `/deposits/containers` | L C E D |
| propriétés/statuts de contenants | `/tools/container-property`, `/tools/container-status` (+ alias settings) | L C E D |

### 2.8 Outils (D01)
Configs : `/tools/activities`, `/tools/communicabilities`, `/tools/organisations`, `/tools/reference-lists`, `/tools/thesaurus`, `/tools/thesaurus/concepts`, `/tools/languages`, `/tools/sorts`, `/tools/laws`, `/tools/keywords`.

| Écran Blade | Route Next | Statut |
|---|---|---|
| plan de classement (`activities/*`) | `/tools/activities` | L C E D |
| communicabilité (`communicabilities/*`) | `/tools/communicabilities` | L C E D |
| organigramme (`organisations/*`) | `/tools/organisations` | L C E D |
| domaines de valeurs (`settings/reference-lists`) | `/tools/reference-lists` (+ alias settings) | L C E D |
| thésaurus (`thesaurus/*`) | `/tools/thesaurus`, `/tools/thesaurus/concepts` | L C E D |
| hiérarchie / recherche / import-export thésaurus | `/tools/thesaurus/hierarchy`, `/search`, `/export-import` | **S** `ThesaurusScreen` |
| langues, sorts, lois, mots-clés | `/tools/languages`, `/tools/sorts`, `/tools/laws`, `/tools/keywords` | L C E D |
| code-barres | `/tools/barcode/create` | **S** `BarcodeScreen` |
| **non portés** | exports thésaurus (SKOS-RDF/CSV/JSON), hiérarchies profondes | — |

### 2.9 Chariots (D11)
Config : `/dollies`.

| Écran Blade | Route Next | Statut |
|---|---|---|
| chariots (`dollies/*`) | `/dollies`, `/create`, `/{id}`, `/{id}/edit` | L C E D + **A** add-*/remove-*/clear/rename (10 types) |
| filtres par catégorie | `/dollies/sort?categ=…` | L |

### 2.10 Contacts (D01)
Configs : `/contacts`, `/contacts/organisations`.

| Écran Blade | Route Next | Statut |
|---|---|---|
| contacts externes (`external/*`) | `/contacts`, `/create` | L C E D |
| organisations externes | `/contacts/organisations`, `/create` | L C E D |
| auteurs | `/records/authors` | L C E D |

### 2.11 Public / Portail (D15)
Configs : `/public/news`, `/public/events`, `/public/pages`, `/public/templates`, `/public/users`, `/public/records`, `/public/feedback`, `/public/search-logs`.

| Écran Blade | Route Next | Statut |
|---|---|---|
| actualités, événements, pages, templates | `/public/news`, `/public/events`, `/public/pages`, `/public/templates` | L C E D |
| utilisateurs publics | `/public/users` | L C E D |
| notices publiques | `/public/records` | L C E D |
| retours, journaux de recherche | `/public/feedback`, `/public/search-logs` | L |
| tableau de bord & statistiques | `/public/dashboard`, `/public/statistics` | **S** `PublicDashboardScreen` (compteurs publics) |
| config OPAC, templates OPAC | `/public/configurations`, `/public/opac-templates` | **S** info (pas d'endpoint) |
| demandes/réponses/PJ | `/public/document-requests`, `/responses`, `/response-attachments` | **S** info (API partielle) |
| chats publics / participants / inscriptions | `/public/chats`, `/chat-participants`, `/event-registrations` | **S** info (contrôleurs non routés) |

### 2.12 IA (D14)
Configs : `/ai-search/resources` (skills, alias prompts settings), `/ai-search/prompts`.

| Écran Blade | Route Next | Statut |
|---|---|---|
| skills, prompts, templates | `/ai-search/resources` (+ `?tab=`), `/ai-search/prompts` | L C E D + **A** toggle · **S** `AiResourcesScreen` (onglets) |
| test du système IA | `/ai-search/test` | **S** `AiTestScreen` (info, exécution non exposée) |
| **non portés** | exécution/chat IA (routes web-session, non Bearer) | — |

### 2.13 Paramètres (D01, D09, D16)
Configs : `/settings/definitions`, `/settings/categories`, `/settings/users`, `/settings/roles`, `/settings/user-organisation-role`, `/settings/backups`, `/settings/backup-files`, `/settings/backup-plannings`, `/settings/transferring-status`, `/settings/mail-actions`, `/settings/mail-priorities`, + alias settings des configs outils/répertoire.

| Écran Blade | Route Next | Statut |
|---|---|---|
| paramètres + catégories (`settings/*`) | `/settings/definitions`, `/settings/categories` | L C E D |
| utilisateurs, rôles, postes (`users/*`, `roles/*`, `role_permissions/*`) | `/settings/users`, `/settings/roles`, `/settings/user-organisation-role` | L C E D |
| courrier (typologies/actions/priorités) | `/settings/mail-typologies`, `/mail-actions`, `/mail-priorities` | L C E D |
| répertoire / not. numériques | `/settings/record-supports`, `/record-statuses`, `/folder-types`, `/document-types`, `/record-types`, `/reference-lists`, `/metadata-definitions` | L C E D (alias) |
| dépôt, transfert, sort | `/settings/container-status`, `/container-property`, `/transferring-status`, `/sorts` | L C E D |
| sauvegardes (`backups/*`) | `/settings/backups`, `/backup-files`, `/backup-plannings` | L C E D |
| mon compte | `/settings/account` | **S** `AccountScreen` |
| rôles & permissions | `/settings/roles`, `/settings/role-permissions` | L C E D + **S** `RolePermissionsScreen` (matrice) |
| mises à jour système, LDAP | `/settings/system-updates`, `/settings/ldap` | **S** info |
| **non portés** | matrice permissions fines (pas d'API), exports binaires (codes-barres, rate-limit) | — |

---

## 3. Vérifications

| Contrôle | Résultat |
|---|---|
| `npx tsc --noEmit` | ✅ 0 erreur |
| `npm run build` (Next 15) | ✅ compilé, lint + types OK |
| Vitest — couverture navigation | ✅ **100 % des entrées de menu résolvent un écran** (`src/lib/crud/coverage.test.ts`) |
| Guard back-office | ✅ `/records` sans session → 307 `/login` ; `/login` rend le formulaire |
| Connexion end-to-end | ✅ login → cookie httpOnly → proxy → données réelles (`/api/proxy/api/v1/records`) |
| Smoke test routes (avec session) | ✅ 200 sur listes, formulaires, détails ET écrans dédiés : `/workflow/dashboard`, `/mails/batches/sign`, `/records/tree`, `/tools/barcode/create`, `/tools/thesaurus/{hierarchy,search,export-import}`, `/transferrings/{import,export}`, `/public/{dashboard,statistics,configurations,opac-templates,chats}`, `/ai-search/resources?tab=*`, `/ai-search/test`, `/settings/{account,role-permissions,system-updates,ldap,languages}`, `/ai-search/prompts` |

## 4. Points d'attention

1. **Rendu des données** : les colonnes/champs utilisent les clés brutes de l'API (snake_case) ; un affinage par ressource (libellés de références, formatage dates) se fait dans la config.
2. **`schema.d.ts`** : placeholder — régénérer depuis `openapi.yaml` (`npx openapi-typescript`) pour durcir le typage.
3. **Sous-ressources** (records/{id}/children, slips/{id}/records, workplaces/{id}/members…) : endpoints API existants, à brancher en onglets de détail (`config.tabs`).
4. **Écrans d'action non exposés par l'API** (exports binaires, exécution IA, matrices de permissions fines, chat public) : écrans d'information livrés ; à activer quand le contrat API sera étendu.
5. **E2E Playwright** : suite à écrire domaine par domaine (voir PHASE-2-NEXTJS.md, 2.2.3).
