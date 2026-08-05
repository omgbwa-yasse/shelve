# Suite de conformité de l'API

> Phase 1, étape 1.0.6. Voir [evolution/PHASE-1-API-LARAVEL.md](../../evolution/PHASE-1-API-LARAVEL.md).

Cette suite vérifie que l'API respecte [CONVENTIONS.md](../CONVENTIONS.md). Elle est écrite
en **technologie neutre** (Node + Vitest) et ne contient aucune référence à Laravel, PHP,
Spring ou Java.

## Pourquoi elle n'est pas écrite en PHPUnit

En phase 3, l'API sera réimplémentée en Spring Boot et devra être prouvée **équivalente**.
Cette suite sera alors relancée **sans une ligne de modification**, en changeant seulement
`API_BASE_URL`. Une suite écrite en PHPUnit serait inutilisable à ce moment-là — et
l'exigence « 100 % d'équivalence » deviendrait invérifiable.

C'est la raison d'être de ce dossier, et le seul motif de sa contrainte d'écriture :

> **Aucun test ne doit contenir d'URL absolue ni de référence à une technologie de backend.**
> Seul `lib/client.js` lit `API_BASE_URL`.

En phase 3, **adapter un test pour qu'il passe contre Spring Boot est l'aveu d'une
divergence**, pas un ajustement. La seule modification légitime est celle qui corrige une
erreur du test lui-même — et elle doit être rejouée contre Laravel pour le prouver.

## Installation

```bash
cd contracts/conformance
npm install
```

## Configuration

| Variable | Rôle | Défaut |
|---|---|---|
| `API_BASE_URL` | Racine de l'API visée | `http://localhost:8000/api/v1` |
| `API_TEST_EMAIL` | Compte de test | — (obligatoire) |
| `API_TEST_PASSWORD` | Mot de passe | — (obligatoire) |
| `API_TEST_FOREIGN_ORG_ID` | Organisation à laquelle le compte **n'est pas** rattaché | — (obligatoire) |
| `UPDATE_GOLDEN` | `1` pour réécrire les golden files | — |

`API_TEST_FOREIGN_ORG_ID` n'est pas optionnel : sans lui, le contrôle d'isolation
multi-organisation (risque **R03**) ne peut pas être vérifié. La suite échoue alors
explicitement plutôt que de sauter le test — une garantie de sécurité non vérifiée doit
se voir dans le rapport, pas disparaître dans un `skip`.

## Exécution

```bash
# Contre Laravel (phase 1 et 2)
php artisan serve                      # dans une autre console
npm run test:laravel

# Contre Spring Boot (phase 3) - MÊME suite, aucune modification
npm run test:springboot
```

**⚠️ Depuis le portage des 16 domaines (498 routes)**, le serveur `artisan serve`
est mono-thread : exécuter les fichiers de test en parallèle le sature et fait
échouer des tests par dépassement de délai. Utiliser le mode séquentiel :

```bash
npx vitest run --no-file-parallelism
```

## Golden files

`golden/` conserve la forme normalisée de chaque réponse. Un fichier absent est créé à la
première exécution ; ensuite, toute différence fait échouer le test.

La normalisation (`lib/normalize.js`) neutralise ce qui varie sans porter de sens —
identifiants auto-générés, horodatages, ordre des clés d'objet. Elle **ne neutralise pas**
l'ordre des collections : il est significatif (tri demandé, hiérarchie), et le masquer
laisserait passer un `ORDER BY` divergent — précisément ce que la collation MySQL face à
un tri fait en mémoire côté Java peut produire (risque **R14**).

Réécrire un golden file :

```bash
npm run golden:update
```

**Toute modification d'un golden file se justifie en revue.** Sans cette règle, « le test
passe » ne signifie plus que « le test a été ajusté ».

## Organisation

```
contracts/conformance/
├── lib/
│   ├── client.js      ⭐ seul point qui connaît API_BASE_URL
│   ├── normalize.js   normalisation avant comparaison
│   └── golden.js      comparaison aux golden files
├── D01/ … D16/        une suite par domaine
├── golden/            réponses de référence
└── README.md
```

## État

| Domaine | Couverture |
|---|---|
| D01 — Référentiels | référentiel type (activités : CRUD complet, filtres, pagination, 401/404/422), action non-CRUD (keywords/search), sous-ressources (reference-lists/values), partage inter-organisation (R03) |
| D03 — Localisation | référentiel global (bâtiments), chaîne org-scopée salles → rayonnages → conteneurs (R03), champs calculés d'occupation |
| D09 — Organisation & sécurité | authentification (login, me, logout, switch-organisation) |
| D02 … D16 | à compléter au fil du portage — les tests Feature (`tests/Feature/Api/V1/`, 611 verts) couvrent l'essentiel ; la suite de conformité neutre est étendue domaine par domaine (étape 1.1.i) |
