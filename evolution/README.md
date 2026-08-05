# Migration Laravel → Spring Boot + Next.js

> **Application** : `shelve` (SAE / archives — records, courrier, thésaurus, OPAC, IA)
> **Cible** : backend Spring Boot (`evolution/springboot`) + frontend Next.js (`evolution/next`)
> **Stratégie** : migration en 3 phases avec **contrat d'API comme source de vérité unique**
> **Date** : 2026-08-04

## Documents du plan

| Document | Contenu |
|---|---|
| **README.md** (ce fichier) | Audit d'entrée, découpage en domaines, décisions structurantes, arborescence cible |
| [PHASE-1-API-LARAVEL.md](PHASE-1-API-LARAVEL.md) | Exposer 100 % du back-office en API REST — produit le **contrat gelé** |
| [PHASE-2-NEXTJS.md](PHASE-2-NEXTJS.md) | Application Next.js branchée sur l'API Laravel — **100 % des CRUD vérifiés** |
| [PHASE-3-SPRINGBOOT.md](PHASE-3-SPRINGBOOT.md) | Backend Spring Boot, **équivalence prouvée**, bascule progressive |
| [RISQUES.md](RISQUES.md) | Registre des 23 risques cotés, mesures rattachées aux étapes, plans de repli, estimations |

**Ordre de lecture** : ce README → phase 1 → phase 2 → phase 3 → risques.
Les mesures de risque ne sont pas un chapitre à part : chaque risque du registre pointe l'étape précise qui le traite.

---

## 0. Constat d'entrée (audit réalisé)

### 0.1 Volumétrie

| Élément | Volume | Conséquence sur la migration |
|---|---:|---|
| Tables MySQL | **241** | Schéma conservé tel quel (voir phase 3, §3.0.1) |
| Modèles Eloquent | **184** | ~184 entités JPA à générer |
| Contrôleurs | **155** (41 711 LOC) | Le gros du portage |
| Routes web (Blade) | **635** dont **99** `Route::resource` | ~495 endpoints CRUD + ~140 actions métier |
| Routes API existantes | ~40 | **Couverture API ≈ 8 %** |
| Vues Blade | **603** | À remplacer par ~603 écrans/composants Next |
| Policies | 41 | Autorisation à réimplémenter intégralement |
| Middlewares | 11 | Rate-limit, CORS, permission, locale, logs |
| Services métier | 25+ | Portage Java (SEDA, EAD, Dublin Core, workflow, OCR…) |
| Jobs / queue | 5 (driver `database`) | Décision d'architecture (phase 3, §3.4) |
| **FormRequests** | **1** ⚠️ | **La validation n'est pas formalisée** |
| **API Resources** | **3** ⚠️ | **La sérialisation n'est pas formalisée** |
| Tests | 36 fichiers | Filet de sécurité insuffisant |

### 0.2 Constats structurants

1. **L'API est à construire quasi intégralement.** Les seules routes `/api` couvrent le portail public (`Api/Public*`), les digital folders/documents (v1) et l'IA. Tout le back-office passe par des contrôleurs Blade retournant des `view()` et des `redirect()->with('success')`.
2. **Validation implicite.** Un seul `FormRequest` (`PromptActionRequest`) : les règles sont en ligne dans les contrôleurs (`$request->validate([...])`) ou absentes. Elles devront être **extraites et documentées** avant tout portage — sinon elles seront perdues silencieusement.
3. **Sérialisation implicite.** 3 `Resource` seulement : la forme des payloads n'est nulle part définie. Aujourd'hui c'est Blade qui décide quoi afficher.
4. **Multi-tenant implicite.** Le trait `BelongsToOrganisation` injecte `organisation_id = Auth::user()->current_organisation_id` **au `creating`**, et `HasDualOrganisation` gère un second cas. Ce comportement automatique **n'a pas d'équivalent JPA natif** → risque de fuite inter-organisation (R03, critique).
5. **Autorisation en base.** `roles` / `permissions` / `role_permissions` / `user_roles` / `user_organisation_role` + 41 Policies + `Gate::allows()` via `CheckPermissionMiddleware`. Le modèle est dynamique (permissions modifiables en base) → doit rester piloté par la donnée côté Java.
6. **Deux guards session** : `web` (agents) et `public` (usagers OPAC). Sanctum n'est utilisé que partiellement. Le passage à une API stateless impose de trancher le modèle d'authentification (phase 1, §1.0.4).
7. **Dépendances PHP sans équivalent Java direct** : `omgbwa-yasse/aibridge`, `cloudstudio/ollama-laravel`, `teamtnt/tntsearch`, `barryvdh/laravel-dompdf`, `milon/barcode`, `thiagoalessio/tesseract_ocr`, `php-ffmpeg`. Chacune est un risque identifié (R09–R11).
8. **Moteur de templates OPAC maison** (`TemplateEngineService`, `ThemeManagerService`, `OpacTemplateSecurityMiddleware`, table `public_templates`) : les usagers peuvent définir des templates. C'est le point le plus difficile à porter (R05).

### 0.3 Découpage en 16 domaines fonctionnels

Le lotissement de tout le plan suit ce découpage. Chaque domaine est l'unité de livraison, de test et de bascule, **dans les trois phases**.

| # | Domaine | Contrôleurs principaux | Poids |
|---|---|---|---|
| D01 | **Référentiels** | Activity, Language, Sort, Author(+Contact), Keyword, Law(+Article), Communicability, RecordStatus, RecordSupport, ContainerStatus/Property, ReferenceList, Setting(+Category) | M |
| D02 | **Records (notices)** | Record, RecordChild, RecordAttachment, RecordAuthor, RecordContainer, RecordDragDrop, RecordReactivation, RecordDigitalTransfer, Settings/RecordType + API digital folders/documents | **XL** |
| D03 | **Localisation physique** | Building, Floor, Room, Shelf, Container, Localisation, OrganisationRoom | M |
| D04 | **Versements / bordereaux** | Slip, SlipContainer, SlipRecord, SlipRecordContainer, SlipStatus, slipRecordAttachment, Accession | L |
| D05 | **Communications & réservations** | Communication, CommunicationRecord, Reservation, ReservationRecord, activityCommunicability | L |
| D06 | **Courrier (Mail)** | Mail + 19 contrôleurs Mail*/Batch* | **XL** |
| D07 | **Cycle de vie / rétention** | Retention, RetentionActivity, RetentionLawArticle, LifeCycle, DeclassementList | M |
| D08 | **Thésaurus (SKOS)** | Thesaurus, ThesaurusScheme, Search, Translation, AssociativeRelation, ExportImport (2 606 LOC à eux deux) | **XL** |
| D09 | **Organisation & sécurité** | Organisation(+Active/Activity/Contact), User, Role, RolePermission, UserRole, UserOrganisationRole, Auth/* | L |
| D10 | **Recherche** | Search, SearchRecord, SearchMail, SearchSlip, SearchCommunication, SearchReservation, Searchdolly, SearchMailFeedback | L |
| D11 | **Dolly (paniers)** | Dolly, DollyAction, DollyExport, DollyHandler | M |
| D12 | **Collaboration** | Workplace* (9), Chat, Task | L |
| D13 | **Workflow** | WorkflowDefinition, WorkflowInstance, `WorkflowEngine` | M |
| D14 | **IA** | AiSearch, AiSkill, AiTemplate, AiResource, Ollama, Prompt(+Management), Api/Ai* | L |
| D15 | **Portail public / OPAC** | Public* (17) + OPAC/* (17) + Api/Public* (16) | **XL** |
| D16 | **Exploitation** | Backup*, Log, Monitoring, SystemUpdate, RateLimit, NewFeature, Tools, Report, PDF, Barcode, SEDAExport, Dashboard, Home | L |

### 0.4 Ordre de traitement (identique dans les 3 phases)

```
Vague 1 (socle, faible risque)   : D01 Référentiels → D03 Localisation → D09 Organisation & sécurité
Vague 2 (cœur métier)            : D02 Records → D04 Versements → D07 Rétention
Vague 3 (flux)                   : D05 Communications → D11 Dolly → D06 Courrier
Vague 4 (transverse)             : D10 Recherche → D08 Thésaurus → D13 Workflow
Vague 5 (périphérie)             : D12 Collaboration → D16 Exploitation → D14 IA
Vague 6 (public)                 : D15 OPAC
```

**Justification de l'ordre** : D09 en vague 1 parce que l'auth et les permissions conditionnent les tests de toutes les autres. D15 en dernier parce qu'il porte le moteur de templates (risque maximal) et qu'il doit bénéficier de tout le retour d'expérience.

---

## 1. Les 6 décisions qui déterminent la réussite

1. **Le contrat OpenAPI est la source de vérité**, gelé à la fin de la phase 1. Next et Spring Boot en dépendent tous les deux ; ils ne dépendent jamais l'un de l'autre.
2. **La suite de conformité est écrite en technologie neutre dès la phase 1**, pour être rejouée à l'identique contre Spring Boot. Écrire ces tests en PHPUnit rendrait l'exigence d'équivalence invérifiable.
3. **Spring Boot attaque la même base MySQL** : pas de reprise de données, coexistence possible, diff-testing possible, rollback possible.
4. **Spring Boot valide les tokens Sanctum existants** : bascule progressive sans déconnecter les utilisateurs.
5. **Bascule domaine par domaine derrière une passerelle**, jamais en big-bang, rollback en une ligne de configuration.
6. **L'équivalence est prouvée par comparaison automatique avec Laravel comme oracle** (3 niveaux : conformité, diff-testing, shadow traffic) — et non par relecture humaine, impossible à cette échelle.

---

## 2. Arborescence cible des livrables

```
shelve/
├── app/ …                              # Laravel (oracle puis archive)
├── contracts/                          # ⭐ source de vérité
│   ├── openapi.yaml
│   ├── openapi.v1.0.0.yaml              # gelé
│   ├── CONVENTIONS.md
│   ├── inventory/
│   │   ├── routes.json  endpoints.csv  validation-raw.txt
│   └── conformance/                     # ⭐ suite neutre, rejouée sur les 2 backends
│       ├── D01/ … D16/
│       ├── golden/
│       └── normalize.ts
├── evolution/                          # ⭐ chantier de migration
│   ├── README.md                        # ce document
│   ├── PHASE-1-API-LARAVEL.md
│   ├── PHASE-2-NEXTJS.md
│   ├── PHASE-3-SPRINGBOOT.md
│   ├── RISQUES.md
│   ├── next/
│   │   ├── src/app/  src/lib/api/  e2e/
│   └── springboot/
│       ├── src/main/java/fr/shelve/{records,mails,thesaurus,…}/
│       └── src/test/java/…              # JUnit + Testcontainers
├── tools/
│   ├── api-diff/                       # ⭐ diff-testing Laravel vs Spring
│   └── gateway/                        # configuration de bascule par domaine
├── reports/
│   ├── coverage-api.csv  crud-matrix.csv  api-diff.html
│   ├── divergences-acceptees.md
│   └── ecarts-ui.csv
└── docs/
    ├── api/{D01…D16}.md                # analyses de domaine
    └── ui/{D01…D16}.md                 # cartographie Blade → Next
```

**Note** : `contracts/`, `tools/` et `reports/` restent à la racine — ils sont partagés entre Laravel (oracle) et les deux cibles, et ne sont pas des artefacts du seul chantier `evolution/`.
