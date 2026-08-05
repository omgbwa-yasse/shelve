# PHASE 1 — Exposer 100 % du back-office en API REST (Laravel)

> ← [README](README.md) · [Phase 2](PHASE-2-NEXTJS.md) · [Phase 3](PHASE-3-SPRINGBOOT.md) · [Risques](RISQUES.md)

> **Objectif** : que **tout** ce que fait aujourd'hui l'interface Blade soit accessible via une API JSON documentée, testée et stable. Laravel reste la seule implémentation.
> **Livrable pivot** : `contracts/openapi.yaml` — le contrat qui gouvernera les phases 2 et 3.
> **Règle d'or** : **aucune ligne de Next ni de Java tant que le contrat d'un domaine n'est pas figé.**

---

## Étape 1.0 — Socle et conventions (avant tout code)

### 1.0.1 — Inventaire exhaustif des endpoints

Générer l'inventaire machine, pas à la main :
```bash
php artisan route:list --json > contracts/inventory/routes.json
```
Puis produire `contracts/inventory/endpoints.csv` avec, par route : `method, uri, name, controller@action, middleware, domaine (D01–D16), type (CRUD|action|vue|export|upload), statut (à-porter|abandonné|fusionné)`.

*Critère de sortie* : chacune des 635 routes web est classée et affectée à un domaine. Les routes purement d'affichage (`create`, `edit` qui ne font que rendre un formulaire) sont marquées `vue` → **non portées en API** mais leurs données de référence (listes déroulantes) deviennent des endpoints `/options`.

> Mesure du risque **R06** (endpoints non-CRUD sous-estimés).

### 1.0.2 — Extraction des règles de validation existantes

```bash
grep -rn "validate(\|Validator::make\|rules()" app/Http/Controllers/ > contracts/inventory/validation-raw.txt
```
Chaque règle est reportée dans le contrat OpenAPI (contraintes de schéma) **et** matérialisée dans un `FormRequest`.

*Critère de sortie* : 0 endpoint d'écriture sans `FormRequest` dédié.

> Mesure du risque **R01** (règles de validation perdues) — le risque de criticité maximale du projet.

### 1.0.3 — Conventions d'API figées

Document `contracts/CONVENTIONS.md` :

| Sujet | Décision |
|---|---|
| Préfixe | `/api/v1/{ressource}` — kebab-case pluriel |
| Enveloppe | `{ "data": …, "meta": {...}, "links": {...} }` (Laravel Resource par défaut) |
| Pagination | `?page[number]=&page[size]=` — `meta.total`, `meta.per_page`, `meta.current_page` |
| Filtrage | `?filter[champ]=valeur`, `?filter[champ][op]=…` (`eq,ne,like,gt,lt,in,between,null`) |
| Tri | `?sort=-created_at,name` |
| Relations | `?include=organisation,author` (liste blanche par ressource) |
| Champs partiels | `?fields[record]=id,name,code` |
| Erreurs | **RFC 7807** `application/problem+json` : `{type,title,status,detail,instance,errors[]}` |
| Validation | HTTP 422, `errors` = `{champ: [messages]}` (format Laravel conservé) |
| Dates | **ISO-8601 UTC** systématique (`2026-08-04T18:44:00Z`) — jamais de format local |
| Décimaux | chaînes JSON pour tout montant/quantité (pas de flottant) |
| Booléens | vrais booléens JSON (pas `0`/`1`) |
| Concurrence | `ETag` en lecture + `If-Match` en écriture (basé sur `updated_at`) → 409 |
| Idempotence | header `Idempotency-Key` sur tous les `POST` d'action métier |
| Uploads | `multipart/form-data`, réponse = ressource `Attachment` |
| Téléchargements | `GET .../download` → flux binaire + `Content-Disposition` |
| Actions métier | `POST /api/v1/{ressource}/{id}/{verbe}` (ex. `/slips/12/validate`) |
| Localisation | header `Accept-Language` → `SetLocale` |
| CORS | liste blanche d'origines (Next dev + prod) |
| Stockage | arborescence et nommage des fichiers documentés — les deux backends pointeront le même volume |

> Mesures des risques **R13** (dates/fuseaux) et **R18** (fichiers et stockage).

### 1.0.4 — Authentification API

**Décision : Laravel Sanctum, tokens personnels (Bearer).**

- `POST /api/v1/auth/login` → `{token, user, organisations, permissions[]}`
- `POST /api/v1/auth/logout`, `GET /api/v1/auth/me`, `POST /api/v1/auth/refresh`
- `POST /api/v1/auth/switch-organisation` (remplace le `current_organisation_id` en session)
- Guard `public` (OPAC) → tokens Sanctum sur `PublicUser`, scope `public:*`

**Raison** : `personal_access_tokens` existe déjà, le hash est SHA-256 côté DB → **Spring Boot pourra valider les mêmes tokens en phase 3** sans réauthentifier les utilisateurs. C'est ce qui rend la bascule progressive possible.

Le back-office Blade **continue** d'utiliser la session : les deux mécanismes coexistent pendant toute la migration.

> Mesure du risque **R21** (rupture de session à la bascule).

### 1.0.5 — Squelette de génération

Créer une commande de scaffolding maison :
```
php artisan make:api-resource-set {Model} --domain=D02
```
qui génère `ApiController` + `StoreRequest`/`UpdateRequest` + `Resource` + `Collection` + `Filter` + test Feature + entrée OpenAPI.

Sans cet outil, 184 ressources × 6 fichiers = 1 100 fichiers à écrire à la main.

> Mesure du risque **R07** (charge sous-estimée).

### 1.0.6 — Harnais de conformité neutre

⚠️ **Point le plus important du plan.**

Créer `contracts/conformance/` : une suite de tests **indépendante du langage du backend**, qui attaque une URL de base configurable.

- Techno recommandée : **Node 22 + Vitest + `openapi-fetch`** (ou Karate DSL si l'équipe est Java).
- Elle sera exécutée : en phase 1 contre Laravel, en phase 3 contre Spring Boot, **sans une ligne modifiée**.
- Contenu par ressource : les 5 verbes CRUD, pagination, filtres, tri, 401/403/404/422/409, et un **golden file** JSON normalisé de chaque réponse.
- Un normaliseur (`normalize.ts`) neutralise `id`, timestamps, ordre des clés, avant comparaison.

> Mesure du risque **R19** (régression non détectée) — sans ce harnais, l'exigence « 100 % d'équivalence » de la phase 3 est invérifiable.

---

## Étapes 1.1 → 1.16 — Portage domaine par domaine

Pour **chaque** domaine D01→D16 (voir [ordre des vagues](README.md#04-ordre-de-traitement-identique-dans-les-3-phases)), la même séquence — le « ruban » :

| # | Tâche | Livrable | Definition of Done |
|---|---|---|---|
| a | Lire les contrôleurs Blade du domaine + les vues associées | note d'analyse `docs/api/{Dxx}.md` | Toute règle métier trouvée **dans la vue** est remontée (mesure **R02**) |
| b | Définir les schémas de ressources | `App\Http\Resources\{X}Resource` | Champs, relations, champs calculés, champs masqués |
| c | Écrire les `FormRequest` | `Store{X}Request`, `Update{X}Request` | Règles = celles extraites en 1.0.2, à l'identique (mesure **R01**) |
| d | Écrire `Api\V1\{X}Controller` | contrôleur fin (≤ 120 LOC) | Aucune logique métier : elle va dans un `Service` |
| e | Extraire la logique métier partagée | `App\Services\{Domaine}\...` | **Réutilisée par le contrôleur Blade ET l'API** → une seule source de vérité |
| f | Brancher l'autorisation | `authorize()` + Policy existante | 1 test par règle d'autorisation (mesure **R04**) |
| g | Recenser les actions non-CRUD | endpoints `POST .../{verbe}` | Le CSV de 1.0.1 ne contient plus de `à-porter` |
| h | Écrire les tests Feature Laravel | `tests/Feature/Api/{Dxx}/` | Couverture ≥ 90 % des lignes du contrôleur |
| i | Écrire la suite de conformité neutre | `contracts/conformance/{Dxx}/` | Verte contre Laravel |
| j | Publier le contrat | fragment OpenAPI | `spectral lint` sans erreur |
| k | Revue croisée | PR | 2 relecteurs, **dont 1 futur dev Java** (mesure **R23**) |

**Le point (e) est structurant** : en extrayant la logique dans un service partagé, le contrôleur Blade et le contrôleur API produisent le même comportement par construction. Sans cela, l'API dérive silencieusement de l'interface encore en production.

**Le point (k) n'est pas une formalité** : faire relire chaque PR de phase 1 par un futur développeur Java lui fait apprendre le domaine métier pendant qu'il est encore décrit en PHP. C'est le seul moment où cet apprentissage est gratuit.

---

## Étape 1.17 — Consolidation de fin de phase

1. **Contrat unique publié** : `contracts/openapi.yaml` (fusion des fragments), versionné, `spectral lint` propre, servi par `/api/documentation` (l5-swagger déjà présent en dev-dependency).
2. **Matrice de couverture** : script qui croise `routes.json` (web) et `openapi.yaml` → `contracts/coverage-api.csv`.
   **Critère de sortie de la phase 1 : 100 % des routes classées `à-porter` ont un endpoint API.**
3. **SDK TypeScript généré** :
   ```bash
   npx openapi-typescript contracts/openapi.yaml -o evolution/next/src/lib/api/schema.d.ts
   ```
   Next ne dépendra **jamais** de Laravel, seulement du contrat.
4. **Collection Postman/Bruno** générée depuis le contrat, pour la recette manuelle.
5. **Gel du contrat** : `contracts/openapi.v1.0.0.yaml`, tag git `contract-v1.0.0`. Toute évolution ultérieure = PR sur le contrat en premier (*contract-first*).

---

## Critères de sortie de la phase 1

- [ ] 100 % des routes portables exposées en API
- [ ] 0 endpoint d'écriture sans FormRequest
- [ ] Suite de conformité neutre verte à 100 % contre Laravel
- [ ] Contrat OpenAPI gelé et taggé
- [ ] Golden files enregistrés pour chaque endpoint

## Jalon de recalibrage

**Fin de la vague 1** (D01, D03, D09) : mesurer la charge réelle contre l'estimation. Si dépassement > 30 %, replanifier l'ensemble **avant** d'engager la suite. Voir [RISQUES.md §4.4](RISQUES.md#44-estimation-et-jalons-de-recalibrage).

## Règle permanente pendant les phases 1 et 2

**Pas de nouvelle route Blade sans endpoint API correspondant**, vérifiée en CI par diff de `route:list`. Sans cette règle, l'application continue d'évoluer plus vite que sa migration (risque **R08**).
