# PHASE 2 — Application Next.js branchée sur l'API Laravel

> ← [README](README.md) · [Phase 1](PHASE-1-API-LARAVEL.md) · [Phase 3](PHASE-3-SPRINGBOOT.md) · [Risques](RISQUES.md)

> **Objectif** : remplacer les 603 vues Blade par une application Next, **validée à 100 % sur les CRUD** contre l'API Laravel.
> **Contrainte structurante** : Next ne connaît que le contrat. Le changement de backend en phase 3 doit être **une variable d'environnement**.

---

## Étape 2.0 — Socle Next

### Stack retenue

| Sujet | Choix | Motif |
|---|---|---|
| Framework | **Next.js 15+, App Router, TypeScript strict** | RSC pour l'OPAC (SEO), client pour le back-office |
| Client HTTP | `openapi-fetch` + types générés | Toute dérive du contrat = **erreur de compilation** |
| État serveur | **TanStack Query v5** | Cache, invalidation, optimistic updates |
| Formulaires | `react-hook-form` + `zod` (schémas générés depuis OpenAPI) | Validation client alignée sur le serveur |
| UI | Tailwind + shadcn/ui (ou conservation de Bootstrap si l'existant le justifie) | Cohérence avec `tailwind.config.js` déjà présent |
| Tables | TanStack Table | 99 écrans de liste avec tri/filtre/pagination |
| i18n | `next-intl`, dictionnaires importés depuis `lang/` | Mesure **R22** |
| Tests | Vitest (unit) + **Playwright** (E2E) + MSW (mocks) | |
| Auth | **Route Handlers Next** proxy → cookie `httpOnly` `SameSite=Lax` | Le token Sanctum ne touche jamais le JS client (mesure **R17**) |

### Arborescence

```
evolution/next/
├── src/
│   ├── app/
│   │   ├── (auth)/login/
│   │   ├── (back-office)/            # guard: session agent
│   │   │   ├── records/  slips/  mails/  thesaurus/  …   # 1 dossier par domaine
│   │   └── (opac)/                   # guard: usager public, RSC + SEO
│   ├── lib/
│   │   ├── api/
│   │   │   ├── client.ts             # ⚠️ SEUL point qui connaît l'URL du backend
│   │   │   ├── schema.d.ts           # généré depuis openapi.yaml
│   │   │   └── endpoints/{domaine}.ts
│   │   ├── auth/                     # session, permissions, org courante
│   │   └── permissions.ts            # miroir des Gates Laravel
│   ├── components/  hooks/  types/
├── e2e/                              # Playwright, 1 spec par domaine
└── .env.local                        # NEXT_PUBLIC_API_BASE_URL  ← la clé de la phase 3
```

### Règle d'architecture non négociable

`lib/api/client.ts` est le **seul** fichier qui lit `NEXT_PUBLIC_API_BASE_URL`. Un test de lint CI vérifie qu'aucun autre fichier ne contient d'URL absolue.

Sans cela, la bascule de phase 3 devient un chantier de refonte au lieu d'un changement de configuration.

**Dès le début de la phase 2**, `NEXT_PUBLIC_API_BASE_URL` pointe sur la **passerelle** (voir phase 3, §3.5), pas directement sur Laravel — même si la passerelle ne fait que router 100 % du trafic vers Laravel au départ. Cela évite d'avoir à reconfigurer Next au moment de la première bascule.

---

## Étape 2.1 — Traduction des écrans Blade

Pour chaque domaine, dans le **même ordre de vagues** que la phase 1 :

| # | Tâche | DoD |
|---|---|---|
| a | Cartographier les vues Blade → écrans Next | `docs/ui/{Dxx}.md` : 1 ligne par vue, cible ou « supprimée » (mesure **R02**) |
| b | Écran **liste** | pagination serveur, tri, filtres, recherche, export |
| c | Écran **détail** | onglets, relations, pièces jointes |
| d | **Création / édition** | validation zod = validation serveur, gestion 422 champ par champ |
| e | **Suppression** | confirmation, gestion 409 (contraintes FK) |
| f | **Actions métier** | 1 bouton par endpoint `POST .../{verbe}` |
| g | **Autorisation UI** | masquage/désactivation selon `permissions[]` — **jamais** comme seule protection |
| h | E2E Playwright | scénario CRUD complet, en base de test isolée |

Le point (g) mérite d'être explicite : l'UI qui masque un bouton n'est pas une mesure de sécurité, c'est une mesure d'ergonomie. La protection réelle reste côté serveur, et elle est testée en 2.2.4.

**Recouvrement avec la phase 1** : un domaine dont le contrat est gelé peut partir en Next pendant que le domaine suivant est mis en API. C'est ce recouvrement qui réduit le calendrier global d'environ 30 %.

---

## Étape 2.2 — Vérification « 100 % des CRUD passent »

C'est l'exigence explicite du projet ; elle doit être **mesurée, pas constatée**.

### 2.2.1 — Matrice de couverture CRUD générée automatiquement

Script `scripts/crud-matrix.ts` : lit `openapi.yaml`, produit `reports/crud-matrix.csv` :

| ressource | GET list | GET one | POST | PUT/PATCH | DELETE | actions | écran Next | E2E |
|---|---|---|---|---|---|---|---|---|
| records | ✅ | ✅ | ✅ | ✅ | ✅ | 7/7 | ✅ | ✅ |
| slips | ✅ | ✅ | ✅ | ✅ | ✅ | 4/4 | ✅ | ✅ |
| … | | | | | | | | |

**Critère de sortie : aucune cellule vide, taux = 100 %.** Le rapport est publié en CI à chaque merge — un taux qui régresse casse le build.

### 2.2.2 — Jeu de données de recette reproductible

`database/seeders/E2ESeeder.php` : jeu figé couvrant chaque domaine, **2 organisations**, **4 profils** (superadmin, archiviste, lecteur, usager OPAC). Rejoué avant chaque campagne (`migrate:fresh --seed=E2ESeeder`).

Les 2 organisations ne sont pas décoratives : elles sont la condition du test 2.2.4.

### 2.2.3 — Campagne E2E complète

Playwright, 4 profils × 16 domaines. Chaque test : créer → lire → lister → filtrer → modifier → supprimer → vérifier l'absence.

### 2.2.4 — Tests d'autorisation croisés ⚠️

Pour chaque ressource, un utilisateur de l'organisation A tente d'accéder à un objet de l'organisation B → **403/404 attendu**.

**Ce test est obligatoire et bloquant.** Il est écrit ici, en phase 2, parce qu'il devra être rejoué tel quel contre Spring Boot en phase 3 — où l'isolation multi-organisation n'est plus assurée par un trait Eloquent mais par du code écrit à la main.

> Mesures des risques **R03** (fuite inter-organisation) et **R04** (régression d'autorisation), tous deux de criticité maximale.

### 2.2.5 — Tests non-fonctionnels

- Budget de performance par écran de liste : **p95 < 800 ms** sur le jeu de recette
- Accessibilité : axe-core, sans violation bloquante
- Responsive : jeux de tests mobile / tablette / desktop

Les mesures de performance relevées ici deviennent la **référence** contre laquelle Spring Boot sera comparé en phase 3 (critère p95 ≤ Laravel × 1,2).

### 2.2.6 — Recette utilisateur

2 semaines en parallèle du Blade existant — **les deux frontaux tapent la même base**, ce qui rend la comparaison directe. Registre d'écarts `reports/ecarts-ui.csv`, chaque écart tracé et arbitré (corrigé / accepté / abandonné).

---

## Étape 2.3 — Bascule du frontal

- Next devient le frontal de référence ; **Blade est conservé en lecture seule** pendant 1 cycle (rollback immédiat possible).
- Redirections des anciennes URLs Blade → nouvelles URLs Next (301) pour ne pas casser les favoris des agents.
- **URLs publiques OPAC : conservées à l'identique.** Rendu serveur (RSC), sitemap et données structurées préservés, contrôle Lighthouse avant/après.

> Mesure du risque **R20** (SEO du portail public).

---

## Critères de sortie de la phase 2

- [ ] `reports/crud-matrix.csv` = 100 %
- [ ] Suite E2E verte, 4 profils
- [ ] Tests d'isolation multi-organisation verts
- [ ] Recette utilisateur close, 0 écart bloquant
- [ ] Aucune URL de backend en dur hors `lib/api/client.ts` (lint CI)
- [ ] **Audit de sécurité applicatif** (auth, CORS, CSP, XSS, IDOR) — bloquant
- [ ] Mesures de performance de référence enregistrées

## Point de valeur

À la fin de cette phase, **l'application est modernisée et en production, même si la phase 3 n'aboutit jamais.** C'est la raison de cet ordonnancement : le risque de la phase 3 (le plus long, le plus technique) ne met pas en péril le bénéfice déjà livré.
