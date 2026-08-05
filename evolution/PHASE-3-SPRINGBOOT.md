# PHASE 3 — Backend Spring Boot, équivalence prouvée, bascule

> ← [README](README.md) · [Phase 1](PHASE-1-API-LARAVEL.md) · [Phase 2](PHASE-2-NEXTJS.md) · [Risques](RISQUES.md)

> **Objectif** : réimplémenter l'API en Spring Boot, **prouver l'équivalence** avec Laravel endpoint par endpoint, puis rebrancher Next.
> **Principe** : à ce stade, Laravel n'est plus une cible mouvante — c'est un **oracle**. On compare la nouvelle implémentation à l'ancienne, automatiquement.

---

## Étape 3.0 — Décisions d'architecture (à graver avant le premier commit)

### 3.0.1 — Base de données : la même, partagée

Spring Boot attaque **la base MySQL existante**, sans reprise de données.

- Flyway en mode `baselineOnMigrate` + **`validate` uniquement** : Spring ne modifie pas le schéma pendant la coexistence. Les migrations restent pilotées par Laravel jusqu'à la bascule finale.
- **Motif** : permet de faire tourner Laravel et Spring **simultanément sur les mêmes données** → c'est ce qui rend le diff-testing (3.2) et le rollback possibles. Une migration de données en big-bang supprimerait les deux.

> Mesure du risque **R16** (coexistence sur la même base).

### 3.0.2 — Stack

| Sujet | Choix |
|---|---|
| Java | **21 LTS** |
| Spring Boot | **3.5.x** |
| Découpage | package par domaine D01–D16 (`fr.shelve.records`, …), Spring Modulith pour interdire les dépendances croisées |
| Persistance | Spring Data JPA + Hibernate ; **jOOQ ou JdbcTemplate** pour les requêtes de recherche complexes |
| Mapping DTO | MapStruct (miroir des `Resource` Laravel) |
| Validation | Bean Validation (miroir des `FormRequest`) |
| Sécurité | Spring Security + filtre de token Sanctum (3.0.3) |
| Migrations | Flyway (`validate` seul en phase de coexistence) |
| Doc | springdoc-openapi → **comparée** au contrat gelé, pas régénérée librement |
| Tests | JUnit 5 + Testcontainers (MySQL 8) + RestAssured |
| Build | Maven ou Gradle + CI |

Le découpage par domaine n'est pas cosmétique : il fait correspondre les modules Java aux unités de bascule de 3.5. Un module = un domaine = une ligne de configuration de passerelle.

### 3.0.3 — Authentification : réutiliser les tokens Sanctum

Filtre Spring qui lit `Authorization: Bearer {id}|{plain}`, hache en SHA-256, compare à `personal_access_tokens.token`, charge l'utilisateur + rôles + permissions + `current_organisation_id`.

- **Bénéfice décisif** : un utilisateur connecté sur Laravel reste connecté sur Spring → bascule domaine par domaine sans déconnexion globale.
- Les mots de passe sont en **bcrypt** : `BCryptPasswordEncoder` de Spring Security les valide sans reprise (vérifier que le préfixe `$2y$` est accepté — il l'est).
- Après bascule complète, migration optionnelle vers JWT (hors périmètre de ce plan).

> Mesure du risque **R21** (rupture de session).

### 3.0.4 — Autorisation : pilotée par la base, pas par annotations figées

Les 41 Policies deviennent des `PermissionEvaluator` Spring lisant `roles` / `permissions` / `user_organisation_role`. Un `@PreAuthorize("hasPermission(#id,'Record','update')")` par endpoint.

**Chaque Policy Laravel est portée avec son test d'origine.** Le modèle de permissions est modifiable en base par les administrateurs : une réimplémentation qui figerait les règles en annotations Java casserait cette capacité.

> Mesure du risque **R04** (régression d'autorisation), criticité maximale.

### 3.0.5 — Isolation multi-organisation

Réimplémentation explicite du trait `BelongsToOrganisation` :

- Filtre Hibernate `@FilterDef`/`@Filter` activé par un intercepteur sur `organisation_id` (lecture)
- `@EntityListeners` avec `@PrePersist` qui injecte l'organisation courante (écriture)

**Recommandation : les deux**, plus le test d'isolation systématique par ressource écrit en phase 2 (§2.2.4), rejoué ici et **bloquant en CI**.

C'est le point où un comportement implicite de 30 lignes de PHP devient du code explicite à écrire, tester et maintenir. Le traiter comme un détail de mapping est la meilleure façon de créer une fuite de données inter-organisation.

> Mesure du risque **R03**, criticité maximale.

---

## Étape 3.1 — Génération et revue des entités

1. Reverse-engineering du schéma (241 tables) via Hibernate tools / IntelliJ / jOOQ codegen → `evolution/springboot/src/main/java/**/entity/`.

2. **Revue manuelle obligatoire** entité par entité, contre le modèle Eloquent correspondant. Checklist :

   | Point | Piège | Traitement JPA |
   |---|---|---|
   | `$casts` | casts JSON (`metadata`, `signature_data`) | `@JdbcTypeCode(SqlTypes.JSON)` |
   | `$appends` / accesseurs | champs calculés absents du schéma | `@Transient` ou champ du DTO |
   | Soft deletes | `deleted_at` filtré automatiquement par Eloquent | `@SQLRestriction("deleted_at is null")` |
   | Observers | `RecordObserver`, `SlipObserver`, `TaskObserver` | `@EntityListeners` |
   | Relations polymorphes | `Task.taskable`, `Dolly*` | **Pas de support JPA natif** — à traiter à la main |
   | Pivots sans modèle | `record_keyword`, `communication_record`, … | `@ManyToMany` ou entité de liaison |
   | Enums | `enum` MySQL vs `App\Enums\*` PHP | `@Enumerated(STRING)` + valeurs identiques |
   | Booléens | `tinyint(1)` | `Boolean`, pas `Integer` |

3. **Test de parité de mapping** : pour chaque entité, lire une ligne réelle via Eloquent et via JPA, comparer le JSON sérialisé. C'est un test généré, pas écrit à la main.

> Mesure du risque **R12** (sémantique Eloquent ↔ JPA).

---

## Étape 3.2 — Le harnais d'équivalence (cœur de la phase)

Trois niveaux, du moins au plus fort. **C'est ce dispositif qui matérialise l'exigence « 100 % d'équivalence ».**

### Niveau 1 — Conformité au contrat

La suite neutre de la phase 1 (`contracts/conformance/`) est rejouée contre Spring Boot en changeant `BASE_URL`.

**Zéro modification autorisée** : toute adaptation du test est un *aveu de divergence* et doit être traitée comme un défaut, pas comme un ajustement de test.

### Niveau 2 — Diff-testing (comparaison automatique des deux backends)

Développer `tools/api-diff/` :

```
requête → ┬→ Laravel  (:8000) ─┐
          └→ Spring   (:8080) ─┴→ normalisation → comparaison → rapport
```

- Rejoue **chaque cas** de la suite de conformité + un corpus de requêtes réelles anonymisées (extrait des logs de production).
- **Normalisation** : ordre des clés, ordre des collections quand le contrat ne le spécifie pas, IDs auto-générés, timestamps.
- **Compare** : code HTTP, headers significatifs, structure JSON, types, valeurs, **et l'état de la base après écriture**.
- Sortie : `reports/api-diff.html` — vert / divergence tolérée (justification signée) / divergence bloquante.

**Critère de sortie : 0 divergence bloquante sur 100 % des endpoints.**

### Niveau 3 — Shadow traffic (miroir de production)

Sur l'environnement de préproduction, un proxy duplique le trafic réel Next→Laravel vers Spring Boot **en lecture seule** (les écritures shadow s'exécutent dans une base clone rafraîchie chaque nuit), et compare les réponses en continu pendant **2 semaines**.

C'est ce qui attrape les cas que personne n'a pensé à tester — et ils existent, dans une application de 41 000 lignes de contrôleurs couverte par 36 fichiers de tests.

### Définition opérationnelle de l'équivalence

Nécessaire, car « 100 % au bit près » n'est pas atteignable sur les sorties binaires. Trois classes, contractualisées :

| Classe | Périmètre | Critère |
|---|---|---|
| **E1 — Stricte** | Tous les endpoints JSON | Égalité exacte après normalisation |
| **E2 — Sémantique** | Exports PDF / Excel / SEDA / EAD / Dublin Core / codes-barres | Mêmes **données** extraites (texte PDF, cellules XLSX, XML canonicalisé C14N) — le rendu binaire peut différer |
| **E3 — Comportementale** | IA, recherche full-text, tri sur collation | Mêmes **ensembles** de résultats, tolérance documentée sur l'ordre et le scoring |

Toute divergence E2/E3 est **inscrite au registre `reports/divergences-acceptees.md`**, avec justification, impact utilisateur et validation métier. **Aucune divergence non inscrite n'est acceptable.**

> Mesures des risques **R10** (sorties binaires) et **R19** (régression non détectée).

---

## Étape 3.3 — Portage domaine par domaine

Même ordre de vagues qu'en phase 1. Pour chaque domaine :

| # | Tâche | DoD |
|---|---|---|
| a | Entités + repositories du domaine | revue de mapping signée (checklist 3.1.2) |
| b | DTO + MapStruct (miroir des `Resource`) | golden file identique |
| c | Bean Validation (miroir des `FormRequest`) | mêmes messages, mêmes codes 422 |
| d | Services (portage de `app/Services/…`) | tests unitaires portés |
| e | Contrôleurs REST | signature identique au contrat gelé |
| f | Sécurité (`@PreAuthorize` + isolation org) | tests d'autorisation **et** d'isolation verts |
| g | Conformité niveau 1 | 100 % vert |
| h | Diff-testing niveau 2 | 0 divergence bloquante |
| i | Tests d'intégration Testcontainers | ≥ 80 % de couverture |

**Jalon de recalibrage** : à la fin de la première vague Spring, mesurer la vélocité réelle de portage et extrapoler sur les 13 domaines restants.

---

## Étape 3.4 — Les briques non triviales

À traiter explicitement, chacune avec sa décision prise en amont :

| Brique Laravel | Cible Spring Boot | Difficulté | Décision |
|---|---|---|---|
| Queue `database` + 5 Jobs | Spring `@Async` + table `jobs` partagée, ou **Quartz** | M | Conserver le **worker Laravel** pendant la coexistence, porter en dernier |
| `laravel/scout` + TNTSearch | **MySQL FULLTEXT** en v1, **OpenSearch** en cible | **H** | Aucun équivalent Java de TNTSearch → classe E3, scoring différent assumé (**R09**) |
| `dompdf` (PDFController, ReportController) | OpenPDF / Flying Saucer / **Gotenberg** | **H** | Recommandation : **microservice Gotenberg** (HTML→PDF) piloté par les deux backends → équivalence E2 garantie par construction (**R10**) |
| `maatwebsite/excel` | **Apache POI** | M | Comparaison cellule à cellule |
| `milon/barcode` | **ZXing** | F | E2 |
| `tesseract_ocr` + `php-ffmpeg` + `smalot/pdfparser` | **Apache Tika** + Tesseract (process) + ffmpeg (process) | M | Extraction de texte : tolérance E3 documentée |
| `phpoffice/phpword` | Apache POI XWPF | M | |
| SEDA / EAD2002 / Dublin Core (`SedaZipBuilder`, `EAD2002ExportService`, `DublinCoreExportService`, `EADImportService`) | JAXB + XSD officiels | **H** | **Validation XSD des deux sorties + comparaison C14N** — le plus rigoureux du lot, et le plus vérifiable |
| `omgbwa-yasse/aibridge` + Ollama | client HTTP Java vers Ollama, `ProviderRegistry` réécrit | **H** | Pas d'équivalent → réécriture ; classe E3 ; tests sur prompts figés, `temperature=0`, modèle épinglé (**R11**) |
| `WorkflowEngine` | portage direct, ou Flowable si complexité avérée | M | Comparer les transitions sur un corpus d'instances réelles |
| **Moteur de templates OPAC** (`TemplateEngineService`, templates en base) | Thymeleaf sandboxé ou moteur maison | **XL** | **Le risque le plus élevé** — POC dès la fin de phase 1, plan de repli documenté (**R05**) |
| `RateLimitMiddleware` + `RateLimitService` | Bucket4j + Redis | F | Mêmes quotas, mêmes headers |
| `LogUserAction` → table `logs` | filtre Spring + même table | F | |
| Notifications / Mail | Spring Mail + mêmes templates | M | |
| `barcodes`, `backups`, `SystemUpdate`, `GitHubApiService` | portage direct | M | Domaine D16, en fin de parcours |

Les recommandations de type « microservice partagé » (Gotenberg, OpenSearch) méritent d'être remarquées : quand une brique est difficile à porter à l'identique, la **sortir des deux backends** et la faire appeler par les deux garantit l'équivalence au lieu de la vérifier.

---

## Étape 3.5 — Bascule progressive

**Ne jamais basculer en big-bang.** Mécanisme retenu :

1. Une **passerelle** (Nginx / Traefik / API Gateway) devant `/api/v1` route par **préfixe de ressource** vers Laravel ou Spring, pilotée par un fichier de configuration.
2. Bascule d'**un domaine** → observation **72 h** (erreurs 5xx, latence p95, écarts shadow) → domaine suivant.
3. **Rollback = une ligne de configuration**, < 2 minutes, sans redéploiement.
4. Ordre de bascule : **inverse du risque** — D01 Référentiels d'abord, D02 Records et D15 OPAC en dernier.
5. Next ne change pas : `NEXT_PUBLIC_API_BASE_URL` pointe sur la passerelle **depuis le début de la phase 2**.

### 3.5.1 — Critères de bascule d'un domaine (tous obligatoires)

- [ ] Conformité niveau 1 : 100 %
- [ ] Diff-testing niveau 2 : 0 divergence bloquante
- [ ] Shadow ≥ 7 jours sans divergence non expliquée
- [ ] Tests d'isolation multi-organisation verts
- [ ] **p95 Spring ≤ p95 Laravel × 1,2** (référence mesurée en phase 2, §2.2.5)
- [ ] Runbook de rollback **testé en préproduction**

Le critère de performance n'est pas une formalité : la traduction naïve de requêtes Eloquent complexes en JPA produit des N+1 et des `LazyInitializationException`. Le mesurer par domaine, avant bascule, évite de le découvrir en production.

> Mesure du risque **R15** (performance JPA).

---

## Étape 3.6 — Décommissionnement

Uniquement après **30 jours** de fonctionnement de tous les domaines sur Spring Boot :

1. Reprise des migrations Flyway comme source de vérité du schéma (fin du mode `validate`).
2. Portage du worker de queue.
3. Gel puis archivage du code Laravel (tag git, image Docker conservée 12 mois).
4. Suppression des routes Blade et de la passerelle de bascule.

---

## Critères de sortie de la phase 3

- [ ] 16/16 domaines basculés
- [ ] `reports/api-diff.html` : 0 divergence bloquante
- [ ] Registre des divergences acceptées signé par le métier
- [ ] **Audit de sécurité + test de charge** — bloquants
- [ ] 30 jours de production stable
- [ ] Laravel archivé

## Gel fonctionnel

**À partir du début de cette phase**, seuls les correctifs de sécurité et les bugs bloquants sont acceptés — et ils doivent être appliqués **dans les deux backends dans la même PR**. Sans ce gel, l'oracle bouge pendant qu'on le compare, et le diff-testing perd tout son sens (risque **R08**).
