# Conventions de l'API v1 — shelve

> Phase 1, étape 1.0.3. Voir [evolution/PHASE-1-API-LARAVEL.md](../evolution/PHASE-1-API-LARAVEL.md).
>
> **Ce document est normatif.** Il gouverne l'implémentation Laravel (phase 1), le client
> Next.js (phase 2) et la réimplémentation Spring Boot (phase 3). Toute exception doit être
> justifiée dans le contrat OpenAPI de l'endpoint concerné, pas laissée à l'appréciation locale.

---

## 1. Nommage et structure des URI

```
/api/v1/{ressource}                        collection
/api/v1/{ressource}/{id}                   élément
/api/v1/{ressource}/{id}/{sous-ressource}  relation
/api/v1/{ressource}/{id}/{verbe}           action métier (POST)
/api/v1/{ressource}/options                valeurs pour les listes déroulantes
```

- **Ressources au pluriel, en kebab-case** : `/records`, `/record-types`, `/digital-folders`.
- **Identifiants numériques** tels qu'en base. Pas d'UUID exposé là où la base utilise un entier.
- **Pas de verbe dans une URI de CRUD** : `POST /records`, jamais `/records/create`.
- Les 216 routes Blade `create` / `edit` **ne sont pas portées** : un formulaire est un écran, pas une
  ressource. Leurs listes de référence sont exposées par `GET /api/v1/{ressource}/options`.

### Endpoint `/options`

Remplace le rôle des vues `create`/`edit` qui préchargeaient les `<select>`.

```http
GET /api/v1/records/options
```
```json
{
  "data": {
    "statuses":  [{ "id": 1, "label": "Brouillon" }],
    "levels":    [{ "id": 3, "label": "Dossier" }],
    "supports":  [{ "id": 2, "label": "Papier" }]
  }
}
```
Une seule requête par écran de formulaire, filtrée par les droits et l'organisation de l'appelant.

---

## 2. Enveloppe de réponse

### Élément

```json
{
  "data": { "id": 12, "code": "FR-001", "name": "Contrat de bail", "...": "..." }
}
```

### Collection paginée

```json
{
  "data": [ { "id": 12, "...": "..." } ],
  "meta": {
    "current_page": 1,
    "per_page": 25,
    "total": 1043,
    "last_page": 42
  },
  "links": {
    "first": "/api/v1/records?page[number]=1",
    "prev":  null,
    "next":  "/api/v1/records?page[number]=2",
    "last":  "/api/v1/records?page[number]=42"
  }
}
```

- `data` est **toujours** présent, **toujours** au même type pour un endpoint donné
  (un objet pour un élément, un tableau pour une collection — jamais l'un puis l'autre).
- Une collection vide renvoie `"data": []` et `200`, **jamais** `404`.
- `meta` et `links` ne sont présents que sur les collections paginées.

---

## 3. Paramètres de requête

| Paramètre | Forme | Exemple |
|---|---|---|
| Pagination | `page[number]`, `page[size]` | `?page[number]=2&page[size]=50` |
| Tri | `sort`, `-` pour descendant | `?sort=-created_at,name` |
| Filtrage | `filter[champ]` | `?filter[status_id]=3` |
| Filtrage avec opérateur | `filter[champ][op]` | `?filter[created_at][gte]=2026-01-01` |
| Relations | `include` | `?include=organisation,author` |
| Champs partiels | `fields[type]` | `?fields[record]=id,code,name` |
| Recherche libre | `q` | `?q=contrat+bail` |

**Opérateurs de filtre** : `eq` (défaut), `ne`, `gt`, `gte`, `lt`, `lte`, `like`, `in`, `between`, `null`.

**Bornes** : `page[size]` par défaut **25**, maximum **100**. Une valeur supérieure est ramenée à 100
sans erreur — le client ne doit pas pouvoir déclencher un `SELECT` illimité.

**Listes blanches obligatoires.** Chaque ressource déclare explicitement les champs filtrables, triables
et incluables. Un champ hors liste renvoie `400`, jamais un filtre silencieusement ignoré — un filtre
ignoré retourne des données que l'appelant croit filtrées, ce qui est un risque de fuite (R03).

---

## 4. Erreurs — RFC 7807

Content-Type `application/problem+json` pour toutes les réponses ≥ 400.

```json
{
  "type": "https://shelve.local/errors/validation",
  "title": "Les données envoyées sont invalides.",
  "status": 422,
  "detail": "4 champs sont en erreur.",
  "instance": "/api/v1/records",
  "errors": {
    "code": ["Le champ code est obligatoire.", "Le code est déjà utilisé."],
    "parent_id": ["La notice parente sélectionnée n'existe pas."]
  }
}
```

| Code | Usage |
|---|---|
| `200` | Lecture, mise à jour réussie |
| `201` | Création — avec header `Location` |
| `204` | Suppression réussie, sans corps |
| `400` | Paramètre de requête invalide (filtre/tri hors liste blanche) |
| `401` | Non authentifié, ou token expiré |
| `403` | Authentifié mais non autorisé (policy, permission) |
| `404` | Ressource inexistante **ou hors de l'organisation de l'appelant** |
| `409` | Conflit : `If-Match` obsolète, ou suppression bloquée par une contrainte |
| `422` | Échec de validation — `errors` obligatoire |
| `429` | Quota dépassé — headers `X-RateLimit-*` et `Retry-After` |
| `500` | Erreur serveur — jamais de trace ni de requête SQL dans le corps |

### `404` plutôt que `403` pour l'isolation multi-organisation

Un objet appartenant à une autre organisation renvoie **`404`**, pas `403`. Un `403` confirmerait
l'existence de l'objet et permettrait d'énumérer les identifiants d'une autre organisation.
Le `403` est réservé au cas où l'objet est visible mais l'action interdite.

> Mesure du risque **R03**. Ce comportement est vérifié par le test d'isolation obligatoire (§2.2.4).

### Messages de validation

Les messages restent **ceux de Laravel, en français, à l'identique** — ils sont déjà traduits dans
`lang/` et affichés aux utilisateurs. La phase 3 devra les reproduire (mêmes clés, mêmes textes) :
ils font partie du contrat, pas de l'implémentation.

---

## 5. Types de données

| Type | Représentation | Motif |
|---|---|---|
| Date-heure | ISO-8601 **UTC** : `2026-08-04T18:44:00Z` | Élimine l'ambiguïté Carbon ↔ `java.time` (R13) |
| Date seule | `2026-08-04` | Pas de composante horaire parasite |
| Booléen | `true` / `false` | Jamais `0`/`1` : MySQL stocke `tinyint`, l'API expose un booléen |
| Entier | nombre JSON | |
| Décimal / montant | **chaîne** : `"1234.56"` | Évite la perte de précision du flottant IEEE-754 |
| Identifiant | nombre JSON | |
| Champ `json` en base | objet JSON imbriqué | `metadata`, `signature_data` (R12) |
| Absence de valeur | `null` | Jamais `""` ni `0` pour signifier « vide » |
| Énumération | chaîne, valeur métier | `"draft"`, pas `1` |

**Le fuseau est UTC de bout en bout.** La conversion vers le fuseau de l'utilisateur est la
responsabilité du client. Aucune date locale ne circule dans l'API.

---

## 6. Authentification et contexte

```http
Authorization: Bearer 42|aBcDeF...
Accept-Language: fr
X-Organisation-Id: 3        (optionnel — surcharge l'organisation courante)
```

- Tokens **Laravel Sanctum**. Voir [§1.0.4 du plan](../evolution/PHASE-1-API-LARAVEL.md#104--authentification-api).
- L'organisation courante provient de `users.current_organisation_id`. `X-Organisation-Id` permet de
  la surcharger pour une requête, **uniquement** si l'utilisateur est rattaché à cette organisation —
  sinon `403`.
- Le back-office Blade continue d'utiliser la session `web` : les deux mécanismes coexistent.

### Sécurité

- Aucun secret dans une URL (pas de token en query string) : les URLs sont journalisées.
- Réponses d'authentification **non différenciées** : identifiant inconnu et mot de passe faux
  renvoient le même message et le même délai.
- Les endpoints d'authentification restent soumis aux quotas existants (`rate.limit:auth,5,60`).

---

## 7. Concurrence

Les écritures concurrentes existent réellement ici : plusieurs archivistes travaillent sur le même
fonds. Le dernier qui enregistre ne doit pas écraser silencieusement le précédent.

```http
GET /api/v1/records/12
→ 200, ETag: "W/\"2026-08-04T18:44:00Z\""

PATCH /api/v1/records/12
   If-Match: "W/\"2026-08-04T18:44:00Z\""
→ 200 si inchangé depuis, 409 sinon
```

- `ETag` dérivé de `updated_at`, renvoyé sur tout `GET` d'élément.
- `If-Match` **obligatoire** sur `PUT`/`PATCH`/`DELETE` des ressources métier (D02, D04, D05, D06).
  Facultatif sur les référentiels (D01, D03), peu disputés.
- Absence de `If-Match` là où il est obligatoire → `428 Precondition Required`.

### Idempotence

```http
POST /api/v1/slips/12/validate
     Idempotency-Key: 8f14e45f-ea8d-4b7a-9c31-1d2e3f4a5b6c
```

Obligatoire sur toutes les actions métier (§8). La clé est conservée 24 h avec la réponse produite :
un rejeu renvoie la réponse initiale sans réexécuter l'action. Protège des doubles soumissions et des
rejeux réseau — un bordereau validé deux fois est un incident métier.

---

## 8. Actions métier

**352 actions non-CRUD** ont été recensées à l'étape 1.0.1 (`contracts/inventory/endpoints.csv`,
`type = action`). Chacune devient un endpoint explicite :

```
POST /api/v1/{ressource}/{id}/{verbe}
```

| Règle | Détail |
|---|---|
| Verbe à l'infinitif, en kebab-case | `/validate`, `/transfer`, `/mark-as-read` |
| Toujours `POST` | même si l'action semble idempotente |
| Corps = paramètres de l'action | `{ "container_id": 44, "comment": "…" }` |
| Réponse = **la ressource modifiée** | permet au client de rafraîchir sans second appel |
| `409` si l'état interdit l'action | un bordereau déjà validé ne se revalide pas |
| `Idempotency-Key` obligatoire | §7 |

Les actions en lot prennent la collection pour cible :
```http
POST /api/v1/records/bulk-transfer
{ "ids": [12, 15, 33], "container_id": 44 }
```
Réponse `207 Multi-Status` avec le détail par élément — un échec partiel ne doit pas être présenté
comme un succès global.

---

## 9. Fichiers

### Upload

```http
POST /api/v1/records/12/attachments
Content-Type: multipart/form-data
```
Réponse `201` avec la ressource `Attachment` (jamais le binaire).

- Taille maximale et types MIME déclarés **par endpoint** dans le contrat OpenAPI.
- Le type MIME est vérifié **par le contenu**, pas par l'extension ni par l'en-tête client.
- Le nom d'origine est conservé en métadonnée ; le nom de stockage est généré (jamais celui fourni).

### Téléchargement

```http
GET /api/v1/attachments/88/download
→ 200, Content-Type: application/pdf
       Content-Disposition: attachment; filename="contrat.pdf"
       Content-Length: 284116
```
Réponse **streamée**, jamais chargée intégralement en mémoire : certains versements dépassent 100 Mo.
`Accept-Ranges: bytes` sur les fichiers volumineux.

### Contrat de stockage

Les deux backends (Laravel puis Spring Boot) pointent le **même volume**, avec la même arborescence.
Le chemin physique n'est jamais exposé dans une réponse d'API — seul l'identifiant de la ressource.

> Mesure du risque **R18**.

---

## 10. Exports

Les 24 exports recensés (PDF, Excel, SEDA, EAD, Dublin Core, codes-barres) suivent :

```http
GET /api/v1/records/12/export?format=pdf
GET /api/v1/slips/44/export?format=seda
```

- `format` en paramètre, valeurs énumérées dans le contrat.
- Génération longue → `202 Accepted` + ressource de suivi, puis téléchargement quand prêt.
- Ces endpoints relèvent de la **classe d'équivalence E2** en phase 3 : la comparaison portera sur
  les données extraites (texte du PDF, cellules du XLSX, XML canonicalisé), pas sur les octets.

---

## 11. En-têtes de réponse

| Header | Sur | Rôle |
|---|---|---|
| `ETag` | `GET` d'élément | Concurrence optimiste (§7) |
| `Location` | `201` | URI de la ressource créée |
| `X-RateLimit-Limit` / `-Remaining` / `-Reset` | toutes | Quotas |
| `Retry-After` | `429`, `503` | Délai avant nouvelle tentative |
| `Content-Disposition` | téléchargements | Nom de fichier |
| `X-Request-Id` | toutes | Corrélation des journaux entre backends — indispensable au diff-testing (phase 3) |

---

## 12. Versionnement

- Le préfixe `/api/v1` est figé pour toute la durée de la migration : **les phases 2 et 3 consomment
  la même version**. Introduire `/v2` pendant la migration rendrait la comparaison Laravel ↔ Spring
  Boot ininterprétable.
- Évolutions **additives uniquement** : ajouter un champ optionnel, ajouter un endpoint. Autorisé.
- Retirer un champ, renommer, changer un type, restreindre une valeur : **rupture** → interdit sur v1.
- Toute évolution passe par une PR sur `contracts/openapi.yaml` **avant** le code (*contract-first*).

---

## 13. Ce que l'API ne fait pas

Points de vigilance issus de l'audit, à ne pas reproduire par automatisme depuis le code Blade :

- **Pas de redirection.** Un contrôleur d'API ne renvoie jamais `302`. Les `redirect()->with('success')`
  du code actuel deviennent un code de statut et un corps.
- **Pas de message flash.** Le libellé de succès est produit par le client à partir du code de statut.
- **Pas d'état de session.** Aucun endpoint ne dépend d'une valeur posée en session par un appel
  précédent. Tout contexte nécessaire est dans la requête (token, `X-Organisation-Id`, corps).
- **Pas de HTML.** Aucune réponse ne contient de balisage, y compris dans un message d'erreur.
- **Pas de logique d'affichage.** Le tri par défaut, les libellés traduits et les formats appartiennent
  au client — sauf quand ils sont métier, auquel cas ils sont dans les données.

---

## 14. Références

| Sujet | Document |
|---|---|
| Plan de la phase 1 | [evolution/PHASE-1-API-LARAVEL.md](../evolution/PHASE-1-API-LARAVEL.md) |
| Inventaire des endpoints | `contracts/inventory/endpoints.csv` · [synthèse](inventory/endpoints-summary.md) |
| Règles de validation existantes | `contracts/inventory/validation-rules.csv` |
| Écritures sans validation | [inventory/validation-gaps.md](inventory/validation-gaps.md) |
| Registre des risques | [evolution/RISQUES.md](../evolution/RISQUES.md) |
