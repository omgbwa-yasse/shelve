# Analyse des risques et mesures intégrées

> ← [README](README.md) · [Phase 1](PHASE-1-API-LARAVEL.md) · [Phase 2](PHASE-2-NEXTJS.md) · [Phase 3](PHASE-3-SPRINGBOOT.md)

Cotation : **P** probabilité (1–5), **I** impact (1–5), **C** criticité = P × I.

Les mesures ne sont pas un chapitre séparé : chaque risque pointe **l'étape précise** du plan qui le traite. Un risque sans étape de rattachement est un risque non traité.

---

## 4.1 Registre des risques

| ID | Risque | P | I | C | Mesure | Étape |
|---|---|:-:|:-:|:-:|---|---|
| **R01** | **Règles de validation perdues** : 1 seul FormRequest, tout est en ligne ou absent → l'API accepte des données que Blade refusait | 5 | 4 | **20** | Extraction systématique par grep + FormRequest obligatoire + test par règle | [1.0.2](PHASE-1-API-LARAVEL.md#102--extraction-des-règles-de-validation-existantes), 1.1.c |
| **R02** | **Logique métier dans les vues Blade** (603 fichiers) : calculs, agrégats, conditions d'affichage jamais portés | 5 | 4 | **20** | Lecture des vues **avant** d'écrire l'API ; note d'analyse par domaine ; champs calculés dans les Resources | 1.1.a, 2.1.a |
| **R03** | **Fuite de données inter-organisation** : `BelongsToOrganisation` est implicite en PHP, sans équivalent JPA | 4 | 5 | **20** | Filtre Hibernate + EntityListener + **test d'isolation obligatoire par ressource**, bloquant en CI | [3.0.5](PHASE-3-SPRINGBOOT.md#305--isolation-multi-organisation), 2.2.4, 3.5.1 |
> **D01 (2026-08-04)** : décision structurante — les référentiels sont **globaux** (pas d'`organisation_id`), la relation `organisations()` en many-to-many est une affectation, pas une propriété. `PolicyService::access-in-organisation` corrigé (ne restreint que les modèles org-scopés ; ligne sans org = globale). Tests « partage entre organisations » ajoutés. |
| **R04** | **Régression d'autorisation** : 41 Policies + permissions dynamiques en base à réécrire en Java | 4 | 5 | **20** | Portage Policy par Policy **avec son test** ; `PermissionEvaluator` piloté par la base ; tests croisés 4 profils | [3.0.4](PHASE-3-SPRINGBOOT.md#304--autorisation--pilotée-par-la-base-pas-par-annotations-figées), 1.1.f, 2.2.4 |
> **D01 (2026-08-04)** : 8 ressources n'avaient aucune Policy côté Blade ; 5 Policies créées, toutes branchées, 1 test d'autorisation chacune. `BasePolicy::hasPermission` réparé (interroge les tables natives, pas les Gates dynamiques du boot). ⚠️ La politique RBAC (quels rôles reçoivent les permissions D01) reste une décision métier. |
| **R05** | **Moteur de templates OPAC** : templates utilisateur en base, moteur maison, middleware de sécurité — pas d'équivalent Java | 4 | 5 | **20** | D15 traité **en dernier** dans les 3 phases ; POC Thymeleaf sandboxé dès la fin de phase 1 ; **option de repli : garder l'OPAC sur Laravel** comme service autonome | 1.16, 3.3 (D15), [§4.3](#43-plans-de-repli-à-décider-en-amont-pas-en-crise) |
| **R19** | **Régression fonctionnelle non détectée** : 36 fichiers de test pour 41 k LOC | 5 | 4 | **20** | **Raison d'être du harnais à 3 niveaux** : conformité neutre, diff-testing, shadow traffic — l'oracle est l'application actuelle, pas la documentation | [1.0.6](PHASE-1-API-LARAVEL.md#106--harnais-de-conformité-neutre), [3.2](PHASE-3-SPRINGBOOT.md#étape-32--le-harnais-déquivalence-cœur-de-la-phase) |
| **R24** | ~~**Environnement non reproductible**~~ : les migrations ne rejouaient pas sur une base vierge (`prompt_categories` référence `organisations`, créée plus tard → MySQL 1824). Sans reconstruction possible : pas de CI, pas de jeu de recette (phase 2), pas de Testcontainers (phase 3) | 5 | 5 | ~~25~~ | ✅ **RÉSOLU le 2026-08-04.** Migration de baseline unique (`0001_01_01_000000_baseline_schema.php`) chargeant `database/schema/baseline-schema.sql` (236 tables) ; 128 migrations historiques conservées dans `database/migrations-archive/`. `migrate` et `migrate:fresh` reconstruisent désormais une base vierge — vérifié | [1.0.4](PHASE-1-API-LARAVEL.md) ✅ |
| **R25** | **Suite de tests destructrice** : 19 fichiers utilisent `RefreshDatabase` alors que `phpunit.xml` ne définissait aucune base de test → chaque exécution détruisait la base de développement | 5 | 5 | **25 → 6** | ✅ **Endigué** : `phpunit.xml` pointe `shelve_test` (vérifié : `shelve_db` intacte après une exécution complète). ✅ **Débloqué** par R24 : `RefreshDatabase` fonctionne. ⚠️ **Reste** : `database/factories/` était vide — `UserFactory` et `OrganisationFactory` créées, **20 factories manquent encore**, à produire au fil du portage de chaque domaine | [1.0.4](PHASE-1-API-LARAVEL.md) — résiduel |
| **R06** | **Endpoints non-CRUD sous-estimés** : **352** actions métier (et non ~140 comme estimé) noyées dans 1 261 routes | 5 | 4 | **20** | **Mesuré le 2026-08-04** : inventaire CSV exhaustif, 0 route non classée. L'estimation initiale était 2,5× trop basse — à répercuter sur la charge de la phase 1 | [1.0.1](PHASE-1-API-LARAVEL.md#101--inventaire-exhaustif-des-endpoints) ✅, 1.17.2 |
> **Jauge 2026-08-05** : sur 1 024 endpoints « à porter », l'API v1 en expose 498 (crud + actions simples portées sur D01–D14 et D16). Le solde est fait d'actions métier complexes documentées TODO 501 (création de courrier/parapheur, DollyAction, drag-drop, imports/exports), d'exports binaires E2 (phase 3) et du domaine D15 (vague 6, R05). La mesure d'effort réel se fera au jalon de recalibrage (fin de vague 1 : D01+D03+D09). |
| **R07** | **Charge sous-estimée** : 41 711 LOC de contrôleurs, 603 vues, 184 modèles | 4 | 4 | **16** | Scaffolding automatisé ; découpage en 16 lots livrables ; **jalon de recalibrage après la vague 1** — si dépassement > 30 %, replanifier | [1.0.5](PHASE-1-API-LARAVEL.md#105--squelette-de-génération), [§4.4](#44-estimation-et-jalons-de-recalibrage) |
| **R08** | **Dérive fonctionnelle pendant la migration** : Laravel continue d'évoluer (cf. commits IA récents) → cible mouvante | 4 | 4 | **16** | **Gel fonctionnel dès la phase 3** ; en phases 1–2, toute évolution Laravel = obligation d'endpoint API + test de conformité dans la même PR (règle CI) | [§4.2](#42-mesures-transverses-permanentes) |
| **R09** | **Recherche full-text** : TNTSearch sans équivalent Java, scoring différent | 4 | 3 | **12** | Classée E3 dès le départ ; MySQL FULLTEXT en v1, OpenSearch en cible ; corpus de requêtes de référence avec **recouvrement ≥ 90 % du top-10** | [3.4](PHASE-3-SPRINGBOOT.md#étape-34--les-briques-non-triviales), 3.2 |
| **R11** | **IA / Ollama / aibridge** : paquet PHP propriétaire, `ProviderRegistry`, prompts en base | 3 | 4 | **12** | Domaine D14 en avant-dernière vague ; réécriture du client ; classe E3 ; tests sur prompts figés avec `temperature=0` et modèle épinglé | 1.15, 3.3 (D14) |
| **R12** | **Sémantique Eloquent ↔ JPA** : soft deletes, observers, relations polymorphes, casts JSON, `$appends` | 4 | 3 | **12** | Checklist de revue de mapping entité par entité + test de parité de sérialisation sur données réelles | [3.1](PHASE-3-SPRINGBOOT.md#étape-31--génération-et-revue-des-entités) |
| **R13** | **Dates, fuseaux, formats** : Carbon vs `java.time`, timezone applicative, dates nulles / `0000-00-00` | 4 | 3 | **12** | ISO-8601 UTC imposé dans le contrat ; `serverTimezone=UTC` sur la datasource ; jeu de tests dédié (DST, nulls, dates limites) | [1.0.3](PHASE-1-API-LARAVEL.md#103--conventions-dapi-figées), 3.1 |
| **R14** | **Tri et collation** : `utf8mb4_unicode_ci` MySQL vs `Collator` Java ; accents, casse | 4 | 3 | **12** | Le tri est **toujours délégué à MySQL** (`ORDER BY`), jamais fait en mémoire côté Java ; test de tri sur jeu accentué | 3.1, 3.2 |
| **R15** | **Performance JPA** : N+1, `LazyInitializationException`, requêtes Eloquent complexes mal traduites | 4 | 3 | **12** | `@EntityGraph` / fetch joins ; jOOQ pour les requêtes lourdes ; **budget p95 ≤ Laravel × 1,2** comme critère bloquant de bascule | [3.5.1](PHASE-3-SPRINGBOOT.md#351--critères-de-bascule-dun-domaine-tous-obligatoires), 2.2.5 |
| **R16** | **Coexistence sur la même base** : deux applications écrivant simultanément, migrations concurrentes | 3 | 4 | **12** | Flyway en `validate` seul pendant la coexistence ; **Laravel seul propriétaire du schéma** jusqu'à 3.6 ; fenêtre de migration annoncée, les deux apps redéployées ensemble | [3.0.1](PHASE-3-SPRINGBOOT.md#301--base-de-données--la-même-partagée), 3.6 |
| **R17** | **Sécurité web du nouveau frontal** : passage de session+CSRF à token+CORS, XSS via templates OPAC | 3 | 4 | **12** | Token en cookie `httpOnly` via Route Handlers Next ; CORS en liste blanche ; CSP stricte ; **audit de sécurité obligatoire en fin de phase 2 et de phase 3** | [2.0](PHASE-2-NEXTJS.md#étape-20--socle-next), [§4.5](#45-points-de-contrôle-qualité-obligatoires) |
| **R18** | **Fichiers et stockage** : 130 contrôleurs manipulent `Storage::` ; chemins, disques, gros fichiers | 3 | 4 | **12** | Contrat de stockage documenté (arborescence, nommage) ; les deux backends pointent le **même volume** ; tests d'upload/download > 100 Mo, streaming | 1.0.3, 3.3 |
| **R23** | **Perte de compétence / effet tunnel** : équipe PHP devant du Java + React | 3 | 4 | **12** | Un futur dev Java relecteur obligatoire sur **chaque PR de phase 1** → il apprend le domaine avant d'écrire du Java ; formation en amont ; pair programming sur la vague 1 | [1.1.k](PHASE-1-API-LARAVEL.md#étapes-11--116--portage-domaine-par-domaine), [§4.4](#44-estimation-et-jalons-de-recalibrage) |
| **R10** | **Sorties binaires non identiques** (PDF, Excel, SEDA, EAD) | 5 | 2 | **10** | Classe d'équivalence E2 : comparaison du **contenu extrait**, pas des octets ; Gotenberg partagé pour le PDF ; validation XSD pour SEDA/EAD | [3.2](PHASE-3-SPRINGBOOT.md#définition-opérationnelle-de-léquivalence), 3.4 |
| **R20** | **SEO du portail public** : changement d'URL ou de rendu → perte de référencement | 3 | 3 | **9** | URLs OPAC **inchangées** ; rendu serveur (RSC) ; sitemap et données structurées conservés ; contrôle Lighthouse avant/après | [2.3](PHASE-2-NEXTJS.md#étape-23--bascule-du-frontal) |
| **R21** | **Rupture de session utilisateur à la bascule** | 3 | 3 | **9** | Spring valide les **mêmes tokens Sanctum** → continuité de session ; bascule par domaine derrière une passerelle | [3.0.3](PHASE-3-SPRINGBOOT.md#303--authentification--réutiliser-les-tokens-sanctum), 3.5 |
| **R22** | **i18n dupliquée** : `lang/` Laravel + dictionnaires Next + messages Java | 3 | 2 | **6** | `lang/` reste la source ; script d'export vers JSON pour Next et `.properties` pour Spring ; test de complétude des clés | 2.0, 3.3 |

### Lecture du registre

Les **six risques de criticité 20** (R01, R02, R03, R04, R05, R19) partagent une caractéristique : ce sont tous des **savoirs non écrits**. Règles de validation dans le corps des contrôleurs, logique métier dans les templates, isolation multi-tenant dans un trait, autorisation dans 41 policies, moteur de rendu maison, comportement attendu connu seulement de l'application en production.

Aucun d'eux n'est un problème technique de traduction PHP → Java. Ce sont des problèmes d'**extraction de connaissance**, et c'est pourquoi la phase 1 — qui ne produit aucune ligne de Java ni de React — est la plus déterminante des trois.

---

## 4.2 Mesures transverses permanentes

1. **Contract-first, sans exception.** Toute évolution passe par une PR sur `contracts/openapi.yaml` **avant** le code. Un job CI casse le build si l'implémentation diverge du contrat (validation de schéma sur les réponses de test).

2. **Règle du « pas de nouvelle route Blade sans endpoint API »** pendant les phases 1 et 2 — vérifiée en CI par diff de `route:list`. Sans cela, R08 se réalise mécaniquement : l'application avance plus vite que sa migration.

3. **Gel fonctionnel à partir de la phase 3.** Seuls les correctifs de sécurité et bugs bloquants sont acceptés, et ils doivent être appliqués **dans les deux backends dans la même PR**.

4. **Golden files versionnés.** Toute modification d'un golden file exige une justification en PR — c'est le garde-fou contre les régressions silencieuses. Un golden file modifié sans explication est une régression acceptée sans le savoir.

5. **Environnement de préproduction iso-production** avec copie anonymisée des données réelles. Le diff-testing sur données synthétiques ne détecte pas les cas tordus, qui sont précisément ceux qui cassent.

---

## 4.3 Plans de repli (à décider en amont, pas en crise)

| Situation | Repli |
|---|---|
| **Le moteur de templates OPAC (D15) résiste au portage** | **Conserver l'OPAC sur Laravel** en service autonome derrière la passerelle. Le périmètre est isolable : guard `public`, tables `public_*`, contrôleurs `OPAC/*` et `Public*`. Décision à prendre au plus tard à la fin de la vague 5. |
| **Un domaine dépasse largement son estimation** | Le laisser sur Laravel derrière la passerelle et poursuivre les autres. L'architecture de bascule progressive rend cela sans coût technique. |
| **Divergence de recherche full-text inacceptable pour le métier** | Introduire OpenSearch et l'utiliser depuis **les deux** backends → équivalence par construction plutôt que par vérification. |
| **La phase 3 dérape en durée** | La phase 2 est déjà livrée en production sur Laravel : **l'application est modernisée même si Spring Boot n'aboutit pas**. C'est la raison de cet ordonnancement. |

Le dernier point est le plus important : l'ordre des phases n'est pas dicté par la logique technique (on pourrait écrire Spring Boot avant Next), mais par la **protection de la valeur livrée**. Chaque phase produit un bénéfice autonome.

---

## 4.4 Estimation et jalons de recalibrage

Ordres de grandeur **à affiner après la vague 1**, pour une équipe de 4 développeurs :

| Phase | Charge estimée | Jalon de contrôle |
|---|---:|---|
| Phase 1 — API Laravel | 5 – 7 mois | **Fin de vague 1** : si dépassement > 30 %, replanifier l'ensemble avant d'engager la suite |
| Phase 2 — Next.js | 6 – 9 mois (recouvrement partiel avec la phase 1, domaine par domaine) | **Fin de vague 2** : matrice CRUD ≥ 40 % |
| Phase 3 — Spring Boot | 8 – 12 mois | **Fin de vague 1 Spring** : mesurer la vélocité réelle de portage et extrapoler |

**Recouvrements** :
- Phase 1 / phase 2 : possible **par domaine** (un domaine dont le contrat est gelé peut partir en Next pendant que le suivant est mis en API). Réduit le calendrier global d'environ 30 %.
- Phase 2 / phase 3 : possible **mais déconseillé** avant la vague 2. Le contrat doit être éprouvé par un vrai frontal avant d'être réimplémenté — sinon on réimplémente des erreurs de conception d'API.

Ces chiffres sont des ordres de grandeur, pas un engagement. Ce qui compte est le **dispositif de recalibrage** : trois points de mesure où la trajectoire réelle est comparée à l'estimation, avec une décision explicite à prendre.

---

## 4.5 Points de contrôle qualité obligatoires

| Moment | Contrôle | Bloquant |
|---|---|:-:|
| Fin de chaque domaine, phase 1 | Revue de contrat + conformité verte | ✅ |
| Fin phase 1 | Gel du contrat, couverture 100 % | ✅ |
| Fin de chaque domaine, phase 2 | E2E + tests d'isolation | ✅ |
| Fin phase 2 | **Audit de sécurité applicatif** (auth, CORS, CSP, XSS, IDOR) | ✅ |
| Fin de chaque domaine, phase 3 | Diff-testing 0 divergence bloquante | ✅ |
| Avant chaque bascule | Critères [3.5.1](PHASE-3-SPRINGBOOT.md#351--critères-de-bascule-dun-domaine-tous-obligatoires) (6 points) | ✅ |
| Fin phase 3 | **Audit de sécurité + test de charge** | ✅ |

Tous ces points sont bloquants. Un point de contrôle qu'on peut contourner sous pression de calendrier n'est pas un point de contrôle — c'est une recommandation, et les recommandations ne survivent pas au dernier trimestre d'un projet de trois ans.
