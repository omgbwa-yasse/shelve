# Rapport d'implémentation détaillé — Application Next.js (SHELVE back-office)

> **Application** : `web/` · Next.js 15 (App Router), React 19, TypeScript strict, TanStack Query, Tailwind
> **Backend** : API Laravel v1 (529 routes) consommée via proxy Next
> **Date** : 2026-08-05
> **Rapport de couverture** : `src/lib/crud/coverage.test.ts` (Vitest) — **100 % des entrées de navigation résolvent un écran**

---

## 1. Le modèle universel (composants & infrastructure)

> **Architecture : découpage par feature.** Chaque module est une `features/<module>/`
> autonome (types, services API, composants, configs CRUD). Le moteur CRUD générique
> reste en infrastructure partagée (`lib/`, `components/crud/`, `components/ui/`).
> `features/index.ts` est le seul agrégateur consommé par le routeur.

```
src/
├── features/                     # Logique métier découpée par module
│   ├── auth/                     # actions/ login.action.ts · components/ login-form.tsx
│   │   ├── actions/              #   hooks/ use-auth.ts · services/ auth.service.ts
│   │   ├── components/           #   types/ auth.types.ts · utils/ auth.schema.ts
│   │   ├── hooks/
│   │   ├── services/             # API connectée (login/logout/me)
│   │   ├── types/
│   │   └── utils/
│   ├── mails/                    # services/ · resources.tsx (CRUD+spécial) · components/
│   ├── workflow/                 # services/ · resources.tsx · components/ (dashboard)
│   ├── workplaces/               # services/ · resources.tsx
│   ├── chats/                    # services/ · resources.tsx
│   ├── records/                  # services/ · resources.tsx · components/ (arbre, drag&drop)
│   ├── communications/           # services/ · resources.tsx
│   ├── transferrings/            # services/ · resources.tsx · components/ (import/export)
│   ├── deposits/                 # services/ · resources.tsx
│   ├── tools/                    # services/ · resources.tsx · components/ (thésaurus, barcode)
│   ├── dollies/                  # services/ · resources.tsx
│   ├── contacts/                 # services/ · resources.tsx
│   ├── public/                   # services/ · resources.tsx · components/ (dashboard, info)
│   ├── ai/                       # services/ · resources.tsx · components/ (ressources, test)
│   ├── settings/                 # services/ · resources.tsx · components/ (compte, permissions…)
│   └── index.ts                  # SEUL agrégateur → routeur universel
├── lib/                          # infrastructure partagée
│   ├── api/                      # client.ts (URL backend) · resources.ts (factory) · types.ts
│   └── crud/                     # types.ts · helpers.tsx (configs) + coverage.test.ts
├── components/
│   ├── crud/                     # moteur générique : List/Form/Detail/RelatedList/Fallback
│   ├── ui/                       # Button, Modal, SelectionModal, page.tsx (PageHeader…)
│   └── layout/                   # Sidebar, Submenu, Topbar
└── app/
    ├── api/                      # proxy /api/proxy/* → Laravel · /api/auth/login|logout
    └── (back-office)/[[...path]] # routeur universel
```

| Couche | Fichier(s) | Rôle |
|---|---|---|
| **Services par feature** | `features/<module>/services/*.service.ts` | Instances `ResourceApi` (CRUD + actions) du module |
| **Types par feature** | `features/<module>/types/*.types.ts` | Interfaces métier du module |
| **Composants par feature** | `features/<module>/components/*.tsx` | Écrans dédiés du module |
| **Configs CRUD par feature** | `features/<module>/resources.tsx` | `ResourceConfig[]` + `SpecialRoute[]` du module |
| Agrégateur | `features/index.ts` | `featureResources` + `featureSpecialRoutes` |
| Moteur générique | `components/crud/*` | Rend liste/form/détail/onglets depuis une config |
| Client API | `lib/api/client.ts` | SEUL fichier lisant `NEXT_PUBLIC_API_BASE_URL` ; `apiFetch` via proxy |
| Proxy | `app/api/proxy/[...path]/route.ts` | Forward → Laravel avec Bearer depuis cookie httpOnly |
| Auth | `features/auth/` | Server actions + cookie httpOnly + guard |
| Routeur | `app/(back-office)/[[...path]]/page.tsx` | Spécial → CRUD → repli |

**Connexion API** : login → cookie httpOnly → proxy → données réelles (vérifié : `GET /api/proxy/api/v1/records` → `CORR-EX-01…`).

---

## 2. Détail module par module — écrans, endpoints, composants

### 2.1 Mails / Courrier
| Écran | Route | Type | API (D06) |
|---|---|---|---|
| Courriers (liste/détail/créer/éditer) | `/mails` + `/mails/{id}` · `/create` · `/edit` | CRUD | `mails` |
| Parapheurs | `/mails/batches` + `/mails/batches/{id}` | CRUD | `batches` |
| Parapher / Envoyer / Recevoir parapheur | `/mails/batches/sign` · `/send` · `/receive` | **S** `ParapheurActionsScreen` | `batches` |
| Typologies | `/mails/typologies` (alias `/settings/mail-typologies`) | CRUD | `mail-typologies` |
| Courrier archivé | `/mails/archived` | CRUD | `mail-archives` |
| Boîtes de courrier | `/mails/containers` + `/create` | CRUD | `mail-containers` |
| Pièces jointes | `/mails/attachments` | CRUD | `mail-archives` |
| Transactions de lot | `/settings/batch-transactions` | CRUD | `batch-transactions` |
| Reçus / Envoyés / Retournés / externes | `/mails/received`·`/sent`·`/returned`·`/external/*` | liste (préfixe `/mails`) | `mails` |
| Recherche (dates, avancée) | `/mails/select/date` · `/mails/advanced` | liste + recherche | `search/mails` |

### 2.2 Workflow
| Écran | Route | Type | API (D13/D12) |
|---|---|---|---|
| Définitions (liste/détail/créer/éditer) | `/workflow/definitions` (+ `/create` `/edit`) | CRUD | `workflow-definitions` |
| Instances + actions | `/workflow/instances` · `/create` · `/{id}` | CRUD + **A** start/pause/resume/cancel | `workflow-instances` |
| Tâches (toutes/attente/cours/miennes) | `/workflow/tasks` (+ `?status=` `?assigned_to=me`) | CRUD | `tasks` |
| Tableau de bord | `/workflow/dashboard` | **S** `WorkflowDashboardScreen` (taux de respect réel) | `tasks` |

### 2.3 WorkPlaces + 2.4 Chat
| Écran | Route | Type | API (D12) |
|---|---|---|---|
| Espaces de travail (liste/détail/créer/éditer) | `/workplaces` (+ `/create` `/edit`) | CRUD | `workplaces` |
| Onglets fiche workplace | `…/{id}` : **Membres**, **Documents partagés**, **Dossiers partagés**, **Favoris** | **S** `RelatedList` | `workplaces/{id}/members`, `content/documents`, `content/folders`, `bookmarks` |
| Archiver un workplace | `…/{id}` → bouton | **A** | `workplaces/{id}/archive` |
| Conversations (chats) | `/chats` + `/{id}` | CRUD | `workplace-conversations` |

### 2.5 Notices / Records
| Écran | Route | Type | API (D02 + phase 9) |
|---|---|---|---|
| Notices (liste/détail/créer/éditer) | `/records` (+ `/create` `/edit`) | CRUD | `records` |
| Onglets fiche notice | **Sous-notices**, **Contenants**, **Pièces jointes**, **Auteurs** | **S** `RelatedList` | `records/{id}/children`, `containers`, `attachments`, `authors` |
| Corbeille | `/records/trash` | liste | `records` (deleted) |
| Auteurs / Contacts d'auteurs | `/records/authors` · `/records/author-contacts` | CRUD | `authors`, `author-contacts` |
| Dossiers / Documents numériques | `/records/digital-folders` · `/records/digital-documents` | CRUD | `digital-folders`, `digital-documents` |
| Arbre des notices | `/records/tree` | **S** `RecordsTreeScreen` | `records` (hiérarchie) |
| Drag & Drop | `/records/drag-drop` | **S** info | — (pas d'endpoint) |
| Typologies / Statuts / Supports | `/tools/record-types`·`/record-statuses`·`/record-supports` (+ alias settings) | CRUD | `record-types`, `record-statuses`, `record-supports` |
| Définitions de métadonnées | `/tools/metadata-definitions` | CRUD | `metadata-definitions` |
| Recherche par critères | `/records/select/*` · `/records/advanced` | liste + recherche | `search/records/*` |

### 2.6 Communications
| Écran | Route | Type | API (D05) |
|---|---|---|---|
| Communications + actions | `/communications` (+ `/create` `/edit`) | CRUD + **A** validate/reject/transmit | `communications` |
| Onglet Notices communiquées | `…/{id}` | **S** `RelatedList` | `communications/{id}/records` |
| Réservations + retour | `/communications/reservations` (+ `/create`) | CRUD + **A** mark-returned | `reservations` |
| Onglet Notices réservées | `…/{id}` | **S** `RelatedList` | `reservations/{id}/records` |

### 2.7 Transferts
| Écran | Route | Type | API (D04/D07) |
|---|---|---|---|
| Bordereaux + actions | `/transferrings` (+ `/create` `/edit`) | CRUD + **A** receive/approve | `slips` |
| Onglets : Notices du bordereau, Contenants | `…/{id}` | **S** `RelatedList` | `slips/{id}/records`, `slips/{id}/records/{r}/containers` |
| Listes de déclassement + actions | `/transferrings/declassement-lists` | CRUD + **A** approve/validate/reject | `declassement-lists` |
| Réactivations | `/transferrings/reactivations` | liste | `record-reactivations` |
| Durées de conservation | `/tools/retentions` | CRUD | `retentions` |
| Statuts de transfert | `/settings/transferring-status` | CRUD | `slip-statuses` |
| Import / Export | `/transferrings/import` · `/export` | **S** `SlipsImportScreen` / `SlipsExportScreen` | `slips` |
| Cycle de vie (`records/to-*`) | `/records/to-store` … `/to-sort` | liste | `transferrings/lifecycle/*` |

### 2.8 Dépôts
| Écran | Route | Type | API (D03) |
|---|---|---|---|
| Bâtiments | `/deposits/buildings` (+ `/create`) | CRUD | `buildings` |
| Salles | `/deposits/rooms` (+ `/create`) | CRUD | `rooms` |
| Étagères | `/deposits/shelves` (+ `/create`) | CRUD | `shelves` |
| Contenants (+ capacité cm) | `/deposits/containers` (+ `/create`) | CRUD | `containers` |
| Statuts / Propriétés de contenants | `/tools/container-status` · `/tools/container-property` | CRUD | `container-statuses`, `container-properties` |

### 2.9 Outils
| Écran | Route | Type | API (D01/D08) |
|---|---|---|---|
| Plan de classement | `/tools/activities` | CRUD | `activities` |
| Communicabilité | `/tools/communicabilities` | CRUD | `communicabilities` |
| Organigramme | `/tools/organisations` | CRUD | `organisations` |
| Domaines de valeurs | `/tools/reference-lists` | CRUD | `reference-lists` |
| Thésaurus : schémas, termes | `/tools/thesaurus` · `/tools/thesaurus/concepts` | CRUD | `thesaurus-schemes`, `thesaurus-concepts` |
| Thésaurus : hiérarchie/recherche/import-export | `/tools/thesaurus/{hierarchy,search,export-import}` | **S** `ThesaurusScreen` | `thesaurus-concepts`, `thesaurus/import` |
| Langues / Sorts / Lois / Articles / Mots-clés | `/tools/languages`·`/sorts`·`/laws`·`/law-articles`·`/keywords` | CRUD | `languages`, `sorts`, `laws`, `law-articles`, `keywords` |
| Code-barres | `/tools/barcode/create` | **S** `BarcodeScreen` | — |

### 2.10 Chariots
| Écran | Route | Type | API (D11) |
|---|---|---|---|
| Chariots + actions | `/dollies` (+ `/create` `/edit`) | CRUD + **A** add/remove/rename/clear | `dollies`, `dollies/{dolly}/add-*`, `remove-*` |
| Filtres par catégorie | `/dollies/sort?categ=…` | liste | `dollies` |

### 2.11 Contacts
| Écran | Route | Type | API (D01) |
|---|---|---|---|
| Contacts externes | `/contacts` (+ `/create`) | CRUD | `external-contacts` |
| Organisations externes | `/contacts/organisations` (+ `/create`) | CRUD | `external-organizations` |
| Auteurs | `/records/authors` | CRUD | `authors` |

### 2.12 Public / Portail
| Écran | Route | Type | API (D15) |
|---|---|---|---|
| Actualités / Événements / Pages / Templates | `/public/{news,events,pages,templates}` | CRUD | `news`, `events`, `pages`, `templates` |
| Utilisateurs publics | `/public/users` | CRUD | `users` (public) |
| Notices publiques | `/public/records` | CRUD | `records` (public) |
| Retours / Journaux de recherche | `/public/feedback` · `/public/search-logs` | liste | `feedbacks`, `search-logs` |
| Tableau de bord / Statistiques | `/public/dashboard` · `/statistics` | **S** `PublicDashboardScreen` | `news`, `events`, `users` |
| Config OPAC / Templates OPAC | `/public/configurations` · `/opac-templates` | **S** info | — (non exposé) |
| Demandes / Réponses / PJ | `/public/document-requests` · `/responses` · `/response-attachments` | **S** info | `document-requests` (store), `responses` (store) |
| Chats / Participants / Inscriptions | `/public/chats` · `/chat-participants` · `/event-registrations` | **S** info | — (contrôleurs non routés) |

### 2.13 IA
| Écran | Route | Type | API (D14) |
|---|---|---|---|
| Skills / Prompts / Templates | `/ai-search/resources` (+ `?tab=`) · `/ai-search/prompts` | CRUD + **A** toggle · **S** `AiResourcesScreen` | `ai-skills`, `prompts`, `ai-templates` |
| Test du système | `/ai-search/test` | **S** `AiTestScreen` | — (exécution web-session) |

### 2.14 Paramètres
| Écran | Route | Type | API (D01/D09/D16) |
|---|---|---|---|
| Paramètres / Catégories | `/settings/definitions` · `/settings/categories` | CRUD | `settings`, `setting-categories` |
| Utilisateurs / Rôles / Postes / Rôles-utilisateurs | `/settings/users`·`/roles`·`/user-organisation-role`·`/user-roles` | CRUD | `users`, `roles`, `user-organisation-roles`, `user-roles` |
| Courrier (typologies/actions/priorités) | `/settings/mail-{typologies,actions,priorities}` | CRUD | `mail-*` |
| Répertoire / Notices numériques | `/settings/{record-supports,record-statuses,folder-types,document-types,record-types,reference-lists,metadata-definitions}` | CRUD (alias tools) | `record-*`, `metadata-definitions`, `reference-lists` |
| Dépôt / Transfert / Sort | `/settings/{container-status,container-property,transferring-status,sorts}` | CRUD | `container-*`, `slip-statuses`, `sorts` |
| Sauvegardes / Fichiers / Planifications | `/settings/backups`·`/backup-files`·`/backup-plannings` | CRUD | `backups`, `backup-files`, `backup-plannings` |
| Mon compte | `/settings/account` | **S** `AccountScreen` | `auth/me` |
| Rôles & permissions (matrice) | `/settings/role-permissions` | **S** `RolePermissionsScreen` | `roles`, `users` |
| Mises à jour / LDAP | `/settings/{system-updates,ldap}` | **S** info | — |

---

## 3. Écrans dédiés (par feature, dans `features/<module>/components/`)

- **workflow** : `workflow-dashboard.tsx` (`WorkflowDashboard`)
- **mails** : `parapheur-actions.tsx` (`ParapheurActions`)
- **records** : `records-views.tsx` (`RecordsTree`, `DragDrop`)
- **tools** : `tool-views.tsx` (`ThesaurusViews`, `Barcode`)
- **transferrings** : `slips-import-export.tsx` (`SlipsImport`, `SlipsExport`)
- **public** : `public-views.tsx` (`PublicDashboard`, `PublicInfo`)
- **ai** : `ai-views.tsx` (`AiResources`, `AiTest`)
- **settings** : `setting-views.tsx` (`Account`, `RolePermissions`, `SystemUpdates`, `Ldap`)
- Primitives partagées : `components/ui/page.tsx` (`PageHeader`, `InfoPanel`, `StatCard`, `InfoScreen`)

---

## 4. Vérifications

| Contrôle | Résultat |
|---|---|
| `npx tsc --noEmit` | ✅ 0 erreur (strict + `noUncheckedIndexedAccess`) |
| `npm run build` (Next 15.5) | ✅ compilé, lint + types OK |
| Vitest (`coverage.test.ts`) | ✅ 100 % des entrées de navigation → écran |
| Guard back-office | ✅ `/records` sans session → 307 `/login` |
| Connexion end-to-end | ✅ login → cookie httpOnly → proxy → données réelles |
| Smoke test (avec session) | ✅ 200 : listes, formulaires, détails (avec onglets), écrans dédiés |

## 5. Limites connues

1. **`schema.d.ts`** placeholder — régénérer depuis `openapi.yaml` (`npx openapi-typescript`) pour durcir le typage des payloads.
2. **Exports binaires** (SEDA/EAD/PDF/Excel, code-barres), **exécution IA**, **chat public**, **matrice de permissions fines** : pas d'endpoint API v1 → écrans d'information livrés en attendant l'extension du contrat.
3. **Sous-ressources d'onglets** : `RelatedList` gère listage/suppression/ajout simples ; les verbes spécifiques (ex. return-effective sur les notices de communication) restent à brancher par config.
4. **E2E Playwright** : suite par domaine à écrire (PHASE-2-NEXTJS.md §2.2.3) ; le jeu de recette `E2ESeeder` (2 organisations, 4 profils) et les tests d'isolation multi-organisation restent à produire.
