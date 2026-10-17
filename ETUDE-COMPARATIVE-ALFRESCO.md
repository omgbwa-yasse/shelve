# Étude comparative — SHELVE vs Alfresco

**Objet** : analyser l'existant SHELVE (les 3 projets `laravel/`, `backend/`, `web/`) et le comparer à Alfresco (repo `Alfresco/docs-alfresco`, branches `master`).
**Date** : 10/08/2026
**Périmètre** : comparaison fonctionnelle et conceptuelle, écarts, points forts, recommandations.

---

## 1. Positionnement des deux produits

| | **SHELVE** | **Alfresco** |
|---|---|---|
| Type de produit | **SAE** (Système d'Archivage Électronique) + gestion de fonds physiques + portail public (OPAC) | **ECM** (Enterprise Content Management) + module **Records Management** (Governance Services) |
| Référentiel normatif | ISAD(G) (notices multi-niveaux), SEDA 2.1, EAD 2002/EAD3, Dublin Core, SKOS | DoD 5015.02 (baseline), MoReq-esque (File Plan/categories), pas de SEDA/EAD natif |
| Domination métier | Archives, bibliothèque, courrier administratif, thésaurus | Gestion documentaire collaborative d'entreprise, process, gouvernance |
| Cible principale | Collectivités/organisations francophones (archivistes, bibliothécaires) | Entreprises multi-secteurs, éditeurs/intégrateurs |
| Modèle de données | Base MySQL relationnelle, ~236 tables, modèle `Record` unifié | Repository Alfresco (nœuds + aspects + types, JCR-like), base relationnelle + index Solr |
| Interfaces | Back-office Laravel (Blade), front Next.js (React), portail public OPAC, API REST v1 | Alfresco Share, Digital Workspace (Angular), mobile, portail d'API (ReST v1), CMIS |
| Architecture | 3 projets : `laravel/` (oracle), `backend/` (Spring Boot, portage phase 3), `web/` (Next.js) | ACS (Java/Spring), AGS, Process Services/Automation, Search Services (Solr) |

**Lecture rapide** : SHELVE est un **SAE vertical métier** qui couvre l'intégralité du cycle de vie archivistique (notices, versements, communications, élimination, OPAC) ; Alfresco est une **plateforme ECM horizontale** sur laquelle un module de records management s'ajoute. Ils ne jouent pas exactement dans la même catégorie : la comparaison la plus pertinente est **SHELVE vs Alfresco Content Services + Governance Services**.

---

## 2. Ce qui est implémenté dans SHELVE (synthèse des 3 couches)

### 2.1 Backend Laravel (`laravel/`) — l'oracle
- **185 modèles, 306 contrôleurs, 54 services, 94 policies, 39 commandes**, 236 tables.
- **Domaines API v1 (D01→D18)** : référentiels (activities, keywords, sorts, lists), localisation (buildings→containers), records/notices (unifiées, org-scope, soft delete, versionnage), bordereaux de versement (slips), communications & réservations, courrier (mails + parapheurs/batches), cycle de vie (retentions, déclassements, réactivations), thésaurus SKOS, organisation & sécurité, recherche (D10), chariots (dolly), collaboration (workplaces, tâches), workflow BPMN (définitions/instances), IA (skills/templates/prompts), OPAC (données publiques), exploitation (backups, logs), projets/OKR/KPI, assistant IA (conversations, routines), **déclencheurs** (triggers), boîte mail IMAP/SMTP.
- **Moteurs notables** : `WorkflowEngine` (BPMN XML → transitions → affectations), `TriggerEngine` (événement + conditions If/Unless + action, async `TriggerMatcherJob`), `SedaZipBuilder`/`SedaImportService`, `EAD2002ExportService`/`EADImportService`, `RdfThesaurusImporter`, `QueryAnalyzerService`/`QueryExecutorService` (recherche NL → instructions JSON), `EmailSyncService` (IMAP), `SandboxService` (exécution Python sécurisée), `ProviderRegistry` (multi-LLM).
- **Fonctionnalités clés** : recherche libre + avancée (Scout/TNTSearch), thésaurus SKOS multilingue, IA (extraction mots-clés, résumés, classification, chat), OPAC complet côté Blade, export/import SEDA/EAD/Excel, codes-barres, versionnage + signatures, courrier + parapheurs, projets/OKR/KPI, collaboration, mises à jour système via GitHub.

### 2.2 Backend Spring Boot (`backend/`) — la cible (portage)
- **20 packages, 70 entités, 62 contrôleurs**, conformité **47/47** sur la suite neutre, 54 tests JUnit.
- **Porté** : D01, D02, D03, D04, D05, D06 (référentiels courrier), D07, D08, D09, D10 (minimal), D11, D12, D13, D14/D18 (+ sandbox), D16, D17.
- **Non porté** (gabarit 3.3) : **D15 OPAC**, **courrier complet** (mail + réception/envoi parapheur), **exports SEDA/EAD**, **imports**.
- Interopérabilité Sanctum (mêmes tokens que Laravel), autorisation 100 % pilotée par la base (`Policy.check`), org-scope strict (404 hors périmètre).

### 2.3 Frontend Next.js (`web/`) — l'UI cible
- Coquille back-office (Sidebar + Submenu + Topbar + assistant IA), auth serveur (cookie httpOnly), routeur CRUD universel (`[...path]/page.tsx`), navigation 14 domaines.
- **Implémenté (branché API)** : mails + email, workflow, projets (Kanban/Gantt), workplaces + chat, notices (le plus abouti), communications, transferts, dépôts, outils/thésaurus, contacts, chariots, admin public, IA, paramètres.
- **Squelettique / placeholder** : **portail OPAC** (`(opac)/opac/page.tsx` = « Portail public — placeholder »), configurations OPAC, demandes/réponses documents, chats publics, matrice rôles/permissions fine, code-barres, test IA, drag & drop.

---

## 3. Alfresco en quelques concepts (sources `docs-alfresco`)

### 3.1 Alfresco Content Services (ACS)
- **Sites & bibliothèques documentaires** : collaboration par site (membres, rôles, contenus), à rapprocher des *workplaces* de SHELVE.
- **Types, aspects, propriétés** : modèle de contenu extensible ; aspects ajoutant comportements/métadonnées (ex. versionnable, auditable).
- **Folder Rules** (`using/content/rules.md`) : une règle = **événement** (arrivée / modification / départ du dossier) + **conditions** (If / Unless, `all criteria met`) + **action** (Move, Copy, Check in/out, Send email, Add/Remove aspect, Add simple workflow, Set property, Transform, Start Process, …). Options : disable, appliquer aux sous-dossiers, exécution en arrière-plan, ensembles de règles ordonnés (héritées d'abord), `Run Rules` (rejeu manuel). **Pas de rétroactivité automatique.**
- **Workflows** (`admin/workflows.md`) : BPMN 2.0 (userTask, scriptTask, mailTask, gateways, listeners), 5 définitions livrées (Adhoc, Group/Parallel/Pooled Review & Approve, Review & Approve), lien règle → démarrage de process (« Start Process », uniquement sur événement *Create*).
- **Repository Actions** (`develop/repo-ext-points/repo-actions.md`) : unité de travail réutilisable, paramètres déclarés, invocable depuis règles/workflows/web scripts/jobs, exécutable en arrière-plan.
- **Search** : Search Services (Solr) / Insight Engine / Search Enterprise (Elasticsearch), facettes, filtres, full-text.
- **Permissions/groupes** (`using/permissions.md`) : modèle granularité par nœud, rôles de site (Manager, Collaborator, Contributor, Consumer), groupes, alfresco admin.
- **Smart Folders** : vues virtuelles filtrées. **Transformation** : TIKA, rendus, transform service, OCR.

### 3.2 Alfresco Governance Services / Records Management (AGS)
- **File Plan** : site dédié avec catégories/sous-catégories → l'équivalent du **plan de classement** de SHELVE (`activities`).
- **Record** : contenu « déclaré » comme record ; contenu **inaltérable** une fois classé (métadonnées modifiables, pas le binaire). Cycle : file → complete → cut off → retention → transfer/accession/destroy.
- **Retention schedules** (sur catégorie) : étapes **Cut off / Retain / Transfer / Accession / Destroy**, déclenchées par **durée** (XML duration) ou **événement**, appliquées au niveau **dossier ou record**, éligibilité automatique (job quotidien pour retain/cut off), destruction avec conservation optionnelle des métadonnées.
- **Holds** (litiges/audits) : gèle les records, bloque les opérations de rétention/destruction.
- **Classification & Security Marks** (DoD 5015.02) : marquage, compartimentage, besoin d'en connaître.
- **Audit** : rapports d'audit complets, journal immuable.
- **WORM** : compatibilité stockage Azure WORM / AWS S3 Object Lock.
- **Automate File Plan** : règles d'auto-classement/auto-déclaration à partir des sites.

---

## 4. Correspondance conceptuelle Alfresco → SHELVE

| Concept Alfresco | Équivalent SHELVE (implémenté) | Références |
|---|---|---|
| Site + bibliothèque documentaire | `Workplace` + documents partagés (`workplaces/{w}/documents`, folders, upload, share, transfer) | `laravel/app/Models/Workplace.php`, D12 |
| File Plan / catégories | Plan de classement `activities` (hiérarchie) + `record_types` | `laravel/app/Models/Activity.php`, D01 |
| Record (contenu déclaré) | Notice unifiée `Record` (ISAD(G), niveaux, versionnage, `is_current_version`) | `laravel/app/Models/Record.php`, D02 |
| Content node / fichier | `Record` avec `RecordAttachment` (PJ) ; workplace documents = records avec `is_workplace_folder` | D02, D12 |
| Retention schedule (Cut off/Retain/Transfer/Accession/Destroy) | `Retention` + sort final (`retentions`, `retention-activities`, `retention-law-articles`) + « Rapports lifecycle » (retain/keep/transfer/sort/store/eliminate) + `Accession` | D07, `laravel/app/Models/Retention.php` |
| Bordereaux de versement / transfer | `Slip` (bordereau), `slips/{slip}/receive\|approve`, `SlipRecord`, intégration, export SEDA | D04 |
| Communications (consultation) | `Communication` + `communication-records` (validate/reject/transmit/return) | D05 |
| Holds (gèle) | `Reservation`, réactivation, déclassement, prêt (RecordMedium checkout/checkin) — pas d'équivalent « hold » stricto sensu | D05, D07 |
| Folder Rules (événement+conditions+action) | **`Trigger` + `TriggerEngine`** (événement `record.created/status_changed/uploaded/…`, conditions If/Unless, action `create_task`/`start_workflow`, async, journal `trigger_runs`, `Run Rules` = `POST /triggers/{t}/run`) | D08-Triggers, `PLAN-DECLENCHEURS-TACHES.md` |
| Repository Actions | `Actions\CreateTaskAction` (+ futur `start_workflow`) ; pas d'abstraction « action réutilisable » générale | `laravel/app/Services/Triggers/` |
| Workflows BPMN (Activiti/APS) | `WorkflowDefinition` (BPMN XML) + `WorkflowInstance` + `WorkflowEngine` (transitions, affectations) | D13, `laravel/app/Services/WorkflowEngine.php` |
| Simple workflow (review/approve) | Déclencheur `create_task` + `task_assignments` (à recréer) ; workflow courrier/parapheur | D06, Triggers |
| Aspects / types / propriétés | `metadata_definitions` + `reference_lists` + `record_type` profiles + métadonnées dynamiques | D01, D02 |
| Catégories/tags | `keywords` + pivot `record_keyword` ; catégories de contenu | D01 |
| Full-text search (Solr) | Recherche D10 (Scout/TNTSearch) + recherche NL→SQL IA (`QueryAnalyzerService`) | D10, `laravel/app/Services/AI/` |
| Smart Folders (vues virtuelles) | Onglets « Mes documents » (Plan/Dossiers/Récents/Auteurs/Dates/Mots-clés) — vues, pas de nœuds virtuels | `web/src/features/records/` |
| Permissions par nœud | Permissions pilotées par la base `{ressource}_{action}` + 94 policies + org-scope (R03) | D09, `backend/security/` |
| Rôles de site | `WorkplaceMember` (rôles/permissions) ; `user_organisation_role` (postes) | D12, D09 |
| Classification / Security Marks (DoD) | **Absent** — confidentialité par record (`record_confidentialities`, communicabilité) uniquement | D02, D05 |
| Audit immuable / rapports | `logs`, `task_history`, `trigger_runs`, rapports back-office (`reports/`) ; pas de journal WORM | D16 |
| Transformation / rendus | Extraction texte (Tesseract OCR), vignettes PDF ; pas de moteur de transformation complet | `laravel/app/Services/` |
| OPAC / portail public | **OPAC SHELVE** (page publique, recherche, demandes, feedback, templates) — **pas d'équivalent chez Alfresco** | D15, `web/src/app/(opac)/` |
| SEDA / EAD | **Natif chez SHELVE** (SedaZipBuilder, EAD2002/3) — absent chez Alfresco | `laravel/app/Services/SedaZipBuilder.php` |

---

## 5. Matrice de comparaison fonctionnelle

Légende : ✅ natif/implémenté · ⚠️ partiel/squelettique · ❌ absent.

| Fonctionnalité | SHELVE | Alfresco ACS | Alfresco AGS | Commentaire |
|---|---|---|---|---|
| Notices/records multi-niveaux ISAD(G) | ✅ | ❌ (nœuds génériques) | ⚠️ (records sans ISAD(G)) | SHELVE est normé archives |
| Plan de classement hiérarchique | ✅ (`activities`) | ⚠️ (dossiers) | ✅ (File Plan/catégories) | |
| Déclaration/inaltérabilité des records | ⚠️ (versionnage, pas de verrouillage binaire) | ❌ | ✅ | Alfresco verrouille le binaire |
| Règles automatisées (IFTTT) | ✅ (triggers, phase 1) | ✅ (Folder Rules mature) | ✅ | SHELVE vient de le livrer |
| Workflows BPMN | ✅ (définitions/instances) | ✅ (Activiti/APS complet) | ⚠️ | Alfresco plus riche (5 définitions, gateways, listeners) |
| Rétention & élimination | ✅ (retentions, sort final) | ❌ | ✅ (schedules Cut off/Retain/Transfer/Accession/Destroy) | Approche équivalente |
| Élimination avec trace | ⚠️ (déclassement/réactivation) | ❌ | ✅ (destroy + keep metadata) | |
| Holds (gèle légal) | ⚠️ (réservation ≠ hold) | ❌ | ✅ | Manque chez SHELVE |
| Classification de sécurité (DoD) | ❌ (confidentialité simple) | ❌ | ✅ | Manque chez SHELVE |
| Communications/consultations | ✅ | ❌ | ⚠️ | Spécifique archives françaises |
| Courrier + parapheur | ✅ (mails, batches, transferts) | ❌ | ❌ | Spécifique SHELVE |
| Localisation physique + codes-barres | ✅ (buildings→containers, barcode, prêts) | ❌ | ❌ | Spécifique SHELVE |
| Thésaurus SKOS | ✅ | ⚠️ (categories/tags) | ❌ | SHELVE plus avancé |
| Recherche full-text | ✅ (Scout/TNTSearch) | ✅ (Solr, facettes) | ✅ | Alfresco plus scalable (Insight/Enterprise) |
| Recherche sémantique / IA | ✅ (NL→SQL, multi-LLM) | ⚠️ (Intelligence Services add-on) | ❌ | SHELVE plus avancé |
| OPAC / portail public | ✅ (OPAC complet) | ❌ | ❌ | Spécifique SHELVE |
| Export SEDA / EAD / DC | ✅ | ❌ | ❌ | Spécifique SHELVE |
| Versionnage | ✅ | ✅ | ⚠️ (records immuables) | |
| Collaboration (espaces, membres, chat) | ✅ (workplaces) | ✅ (sites, plus riche) | — | |
| Projets / OKR / KPI | ✅ | ❌ | ❌ | Spécifique SHELVE |
| Sandbox IA (exécution Python) | ✅ | ❌ | ❌ | Spécifique SHELVE |
| Intégrations externes (SAP, Salesforce, M365…) | ⚠️ (IMAP/SMTP, GitHub) | ✅ (très large écosystème) | — | |
| Multi-tenant / org-scope | ✅ (org-scope strict) | ⚠️ (sites) | — | |
| API standard (CMIS) | ❌ (API REST maison) | ✅ (ReST + CMIS) | ✅ | |
| Stockage WORM / cloud objet | ❌ (MySQL + FS) | ✅ (Azure/AWS/Glacier) | ✅ | |
| Transformation de contenu | ⚠️ (OCR texte, vignettes) | ✅ (TIKA, rendus) | — | |
| Journal d'audit immuable | ⚠️ (logs, histories) | ✅ | ✅ (rapports d'audit) | |
| Tâches / notifications | ✅ (tasks, watchers, notifications) | ⚠️ (workflow tasks) | — | SHELVE plus orienté métier |

---

## 6. Les 8 points forts de SHELVE par rapport à Alfresco

1. **Verticalité métier archives** : ISAD(G), SEDA 2.1, EAD 2002/EAD3, Dublin Core — SHELVE est directement interopérable avec les SIA et les plateformes patrimoniales (AtoM), ce qu'Alfresco n'offre pas nativement.
2. **Gestion du physique** : localisation (bâtiments → conteneurs), codes-barres, prêts/signatures électroniques (checkout/checkin, `RecordMedium`) — Alfresco est dématérialisé.
3. **OPAC public** : portail de consultation grand public avec demandes de documents, feedback, templates, statistiques — Alfresco n'a rien d'équivalent (Digital Workspace reste un back-office).
4. **IA intégrée de bout en bout** : extraction/classification/résumé, recherche NL→SQL, chat assistant, **sandbox Python sécurisé**, multi-providers (Ollama/OpenAI/Gemini/Claude…). Alfresco propose seulement un add-on (Intelligence Services) orienté classification AWS/Azure.
5. **Thésaurus SKOS** : normé, multilingue, import/export RDF/SKOS/CSV/JSON, relations hiérarchiques/associatives — outil métier complet.
6. **Déclencheurs métier** (Type « IFTTT à la Alfresco ») : déjà greffés sur les événements records/courrier, en asynchrone, avec journal — positionnés sur le **domaine archivistique** (mots-clés, statuts, workplace), là où les règles Alfresco sont génériques au contenu.
7. **Courrier administratif + parapheurs** : flux entrant/sortant, boîtes/chariots, transferts, IMAP/SMTP — domaine absent d'Alfresco.
8. **Projets/OKR/KPI et collaboration légère** : gestion de projet intégrée au SAE (jalons, livrables, risques, KPI) — hors périmètre Alfresco.

---

## 7. Les 10 écarts à combler (SHELVE vs Alfresco)

| # | Écart | Niveau | Priorité |
|---|---|---|---|
| 1 | **OPAC portail public en Next.js** : placeholder dans `web/src/app/(opac)/`, seul le Blade existe | Fonctionnel (UI) | Haute |
| 2 | **Inaltérabilité du binaire** des records : Alfresco verrouille le contenu déclaré ; SHELVE n'a que le versionnage | Fonctionnel | Haute |
| 3 | **Holds (gèle pour litige/audit)** : blocage des destructions pendant une enquête — inexistant | Fonctionnel | Haute |
| 4 | **Classification/security marks (DoD 5015.02)** : SHELVE se limite à `record_confidentialities` + communicabilité | Fonctionnel | Moyenne |
| 5 | **Élimination avec conservation des métadonnées** (« destroy + keep metadata ») : le sort final élimine tout | Fonctionnel | Moyenne |
| 6 | **Multi-assignation des tâches** : `task_assignments` supprimée, `assigned_to` mono-utilisateur — bloquant pour l'affectation par unité des déclencheurs | Données | Haute (déjà planifiée, phase 2 `PLAN-DECLENCHEURS-TACHES.md`) |
| 7 | **Portage Spring Boot incomplet** : D15 OPAC, courrier complet, exports SEDA/EAD, imports non portés | Architecture | Moyenne (gabarit 3.3) |
| 8 | **Recherche scalable** : TNTSearch/Scout vs Solr/Elasticsearch (facettes, agrégations, full-text à grande échelle) ; backend Spring n'expose que `search/records` minimal | Performance | Moyenne |
| 9 | **Transformation de contenu** : pas de moteur de rendus (PDF/A, images, métadonnées TIKA) ; OCR limité aux vignettes | Fonctionnel | Faible |
| 10 | **API standard (CMIS, OData, webhooks)** : seule une API REST maison v1 existe ; pas de modèle de contenu extensible façon aspects | Interop | Faible |

---

## 8. Ce qu'Alfresco n'a pas et que SHELVE possède (positionnement différenciant)

- Cycle de vie archivistique **normé et complet** : versement (slip) → intégration → communication → réactivation → déclassement → élimination/sort final, avec référentiels juridiques (`retention-law-articles`).
- **Exports/imports standards** SEDA 2.1, EAD 2002/3, Dublin Core, Excel.
- **OPAC** et gestion de fonds physiques (livres, périodiques, prêts, signatures).
- **Thésaurus SKOS** et recherche par concepts.
- **IA métier** (application automatique de titre/summary/thésaurus/activité/mots-clés sur les notices, chat, routines planifiées, sandbox).
- **Projets/OKR/KPI** intégrés au système de gestion d'archives.

> En résumé : Alfresco gagne sur la **généricité, la scalabilité, l'écosystème et la gouvernance réglementaire US (DoD)** ; SHELVE gagne sur la **conformité aux standards d'archives francophones/internationaux, le métier archives-bibliothèque, l'IA et le portail public**.

---

## 9. Recommandations (feuille de route courte)

1. **Terminer le portail OPAC Next.js** (phase 2/3) — c'est le seul domaine « produit fini » côté Laravel sans équivalent front.
2. **Verrouiller les records déclarés** : champ `is_declared`/`locked_at` + interdiction d'update/destroy des records déclarés (parité AGS).
3. **Introduire les Holds** : table `holds` (record/catégorie, motif, référence légale, jusqu'à) bloquant sort final et destruction.
4. **Recréer `task_assignments`** et activer l'affectation par unité/rôle pour les déclencheurs (déjà planifié, phase 2).
5. **Poursuivre le portage Spring Boot** sur OPAC, courrier complet, exports/imports (vague 6) et étendre la recherche.
6. **Optionnel** : conservatoire des métadonnées post-élimination, et journal d'audit immuable (append-only) pour la traçabilité SEDA.

---

*Document généré à partir de l'analyse du code SHELVE (Laravel, Spring Boot, Next.js) et de la documentation officielle Alfresco (`Alfresco/docs-alfresco`, master) : Folder Rules, Retention Schedules, Governance Services, Workflows, Permissions, Search.*
