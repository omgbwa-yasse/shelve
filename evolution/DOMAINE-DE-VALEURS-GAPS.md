# Plan de travail — Alignement Constellio (domaines de valeurs, métadonnées, documents, workflow)

> **Références Constellio** : [Domaine de valeurs](https://constellio.document360.io/docs/domaine-de-valeurs) · [Métadonnées](https://constellio.document360.io/docs/metadonnees) · [Schémas de métadonnées](https://constellio.document360.io/docs/schemas-de-metadonnees) · [Créer un schéma personnalisé](https://constellio.document360.io/docs/creer-un-schema-personnalise) · [Ajouter une métadonnée](https://constellio.document360.io/docs/ajouter-une-metadonnee) · [Extracteurs de métadonnée](https://constellio.document360.io/docs/extracteurs-de-metadonnee) · [Copier un domaine de valeur dans une autre collection](https://constellio.document360.io/docs/copier-un-domaine-de-valeur-dans-une-autre-collection) · [Métadonnée système](https://constellio.document360.io/docs/m%C3%A9tadonn%C3%A9e-syst%C3%A8me) · [Métadonnées copiées](https://constellio.document360.io/docs/m%C3%A9tadonn%C3%A9es-copi%C3%A9es) · [Métadonnée calculée](https://constellio.document360.io/docs/metadonnee-calculee) · [Gestion des métadonnées sécurisées](https://constellio.document360.io/docs/gestion-des-metadonnees-securisees) · [Gestion du papier vs numérique](https://constellio.document360.io/docs/gestion-du-papier-vs-numerique) · [Vue d'ensemble du dossier](https://constellio.document360.io/docs/vue-densemble-1) · [Actions (dossier)](https://constellio.document360.io/docs/actions) · [Vue d'ensemble des documents](https://constellio.document360.io/docs/vue-densemble-sur-les-documents) · [Actions (document)](https://constellio.document360.io/docs/actions-1) · [Module Workflow](https://constellio.document360.io/docs/module-workflow) · [Créer un flux de travail](https://constellio.document360.io/docs/cr%C3%A9er-un-flux-de-travail) · [Configurer les tâches d'un workflow](https://constellio.document360.io/docs/configurer-les-t%C3%A2ches-dun-workflow) · [Utilisation du flux de travail](https://constellio.document360.io/docs/utilisation-du-flux-de-travail-workflow)
>
> **Équivalent applicatif** : `App\Models\ReferenceList` / `App\Models\ReferenceValue` (domaines), `App\Models\MetadataDefinition` / `App\Models\RecordTypeMetadataProfile` (métadonnées/schémas), `App\Models\RecordType` (schéma = type de notice), `App\Models\Record` / `App\Models\RecordMedium` (papier vs numérique), `App\Models\WorkflowDefinition` / `App\Models\WorkflowInstance` (workflow)
> **Date** : 2026-08-05

## Comment lire ce plan

10 étapes, ordonnées par dépendance et effort croissant : les étapes 1-2 sécurisent l'existant (suppression, dictionnaire de base) avant d'enrichir les métadonnées (3-6), puis l'outillage en masse et la collaboration (7-9), pour finir sur le chantier le plus lourd — le module Workflow (10). Chaque étape indique son **objectif**, ses **actions** (fichiers/modèles concernés) et son **KPI** de complétion.

| # | Étape | Effort | Priorité |
|---|---|---|---|
| 1 | Corbeille & suppression sécurisée | Faible-moyen | Haute |
| 2 | Dictionnaire des domaines par défaut + schéma lié | Faible-moyen | Haute |
| 3 | Propriétés de métadonnées enrichies | Moyen | Haute |
| 4 | Métadonnées copiées & calculées | Moyen | Moyenne |
| 5 | Métadonnées sécurisées par rôle | Moyen | Haute |
| 6 | Papier vs numérique — mesure linéaire & capacité | Faible-moyen | Haute |
| 7 | Import / Export en masse | Moyen | Moyenne |
| 8 | Collaboration sur les notices (partage, favoris, raccourci, commentaire) | Moyen-élevé | Moyenne |
| 9 | Duplication de notices & versions mineure/majeure | Moyen | Moyenne |
| 10 | Module Workflow — assignation dynamique, sécurité, tableau de bord | Élevé | Haute |

---

## Étape 1 — Corbeille & suppression sécurisée

**Objectif** : ne plus jamais perdre silencieusement une donnée référencée ailleurs, et permettre de restaurer ce qui a été supprimé.

**Constat** : `ReferenceListController::deleteValue` et `RecordController::destroy` ne vérifient pas l'usage réel avant suppression (soft delete + `try/catch` générique) ; `RecordType::destroy` n'est pas bloqué malgré des notices liées (`type_id` en `nullOnDelete`) ; aucune route `restore` n'existe pour les `Record` soft-deleted, ni écran « Corbeille » pour les lister.

**Actions** :
- Ajouter une vérification bloquante avant suppression de `RecordType` (« utilisé par N notices ») et de `ReferenceValue` (recherche dans `records.metadata` / tables liées).
- Créer l'écran « Corbeille » (liste des `Record::onlyTrashed()`) + action `restore()`.
- Séparer l'affichage `ReferenceValue` en onglets Actifs/Inactifs + action « Supprimer les désactivés non utilisés ».

**KPI** :
- 0 suppression de `RecordType`/`ReferenceValue` utilisé aboutissant sans avertissement (100 % bloquées sur jeu de test).
- Écran Corbeille opérationnel : 100 % des notices soft-deleted listées et restaurables en 1 action.

---

## Étape 2 — Dictionnaire des domaines par défaut + schéma lié

**Objectif** : disposer d'un socle de domaines de valeurs équivalent à celui livré par défaut chez Constellio, et permettre d'associer un schéma à un domaine (pas seulement l'inverse).

**Constat** : un seul `ReferenceList` existe en base (`DOCUMENT_TYPES`). Manquent Types de contenants, Types de dossiers, Types d'emplacements, Types de tâches, Types de supports, Statut d'une tâche, Types d'années (+2 à confirmer). Aucun champ « Schéma lié » sur `ReferenceList` (la relation n'existe que via `MetadataDefinition.reference_list_id`, dans l'autre sens).

**Actions** :
- Créer `DefaultReferenceListsSeeder` idempotent (`firstOrCreate` par `code`) pour les domaines manquants.
- Ajouter une colonne `linked_schema_id` sur `reference_lists` (nullable, restreinte aux 5 domaines concernés) + UI de sélection dans `edit.blade.php`.

**KPI** :
- 9/9 domaines par défaut présents après exécution du seeder (vérifiable par `ReferenceList::count()`).
- 5/5 domaines système éligibles disposant du champ « Schéma lié » fonctionnel en UI.

---

## Étape 3 — Propriétés de métadonnées enrichies

**Objectif** : rattraper les réglages fins de métadonnée que Constellio propose et que l'app ne permet pas de configurer (triable, surlignage, autocomplétion, unicité, masque de saisie, groupes/onglets, champs spécifiques par domaine).

**Constat détaillé** (`MetadataDefinition`, `RecordTypeMetadataProfile`) :
- Pas de `triable`/`surlignage`/`recherche avancée`/`autocomplétion` (seul `searchable` existe).
- `max:255` codé en dur pour le type `text` (`MetadataValidationService::getDataTypeRules`, `app/Services/MetadataValidationService.php:96`), non configurable.
- Pas de contrainte d'unicité par métadonnée, ni de masque de saisie.
- Pas de groupes/onglets (`RecordTypeMetadataProfile` n'a qu'un `sort_order` global) — un `RecordType` fait à la fois office de « type de schéma » et de « schéma » (Constellio distingue les deux).
- `ReferenceValue` n'a pas de champs spécifiques par domaine (Patrons, Analogique, Types de statut, Date de fin d'année).
- Pas de libellés multilingues (`name` est un champ unique, pas `name_fr`/`name_en`).

**Actions** :
- Étendre `MetadataDefinition`/`RecordTypeMetadataProfile` : `sortable`, `highlightable`, `autocomplete`, `unique`, `input_mask`, `group`.
- Rendre `max_length` configurable (remplacer la valeur codée en dur).
- Ajouter un champ `extra_attributes` JSON sur `ReferenceValue` pour les propriétés spécifiques par domaine.

**KPI** :
- 100 % des `data_type` textuels supportent triable/surlignage/autocomplétion configurables (vérifié en configuration, pas en dur dans le code).
- 0 valeur de validation codée en dur restante dans `MetadataValidationService`.

---

## Étape 4 — Métadonnées copiées & calculées

**Objectif** : éviter la ressaisie manuelle de données déjà présentes sur une entité liée, et automatiser les champs dérivés (ex. cote complète).

**Constat** : aucun mécanisme de copie automatique parent → enfant (`Record.metadata` est indépendant par notice malgré `parent_id`/`getAncestors()` existants) ; aucune métadonnée calculée (`Record::flattenMetadataForSearch()` concatène à la volée pour Scout, jamais pour peupler un champ persistant).

**Actions** :
- Ajouter `MetadataDefinition.copy_source_type` (`parent`) + `copy_source_field`, avec hook `saving()` sur `Record` qui recopie la valeur du parent si le champ cible est vide — limité aux types compatibles (chaîne, date, booléen, référence, nombre).
- Ajouter un mode calculé simple (gabarit `$Titre $Code` interpolé côté PHP), recalculé à chaque sauvegarde d'une métadonnée source.

**KPI** :
- ≥ 1 métadonnée copiée fonctionnelle de bout en bout (parent → enfant), couverte par un test automatisé.
- ≥ 1 métadonnée calculée se recalculant automatiquement à la modification de sa source, couverte par un test automatisé.

---

## Étape 5 — Métadonnées sécurisées par rôle

**Objectif** : masquer certaines métadonnées (ex. données sensibles) selon le rôle de l'utilisateur, y compris en recherche.

**Constat** : aucun champ ne restreint la lecture d'une métadonnée précise par rôle. Les 41 policies existantes protègent des actions entières (voir/modifier/supprimer une notice), pas le grain de la métadonnée individuelle.

**Actions** :
- Ajouter `RecordTypeMetadataProfile.restricted_to_roles` (JSON).
- Filtrer dans `Record::getVisibleMetadataFields()` (`app/Models/Record.php:397`) et dans le service de recherche (exclusion de l'indexation/recherche avancée pour les rôles non autorisés).

**KPI** :
- 100 % des métadonnées marquées `restricted_to_roles` invisibles pour un rôle non autorisé, en affichage **et** en recherche avancée (0 fuite constatée sur jeu de test avec comptes de rôles différents).

---

## Étape 6 — Papier vs numérique : mesure linéaire & capacité des contenants

**Objectif** : compléter le seul écart concret identifié sur la gestion papier/numérique (déjà largement couverte par `RecordMedium` : dossier virtuel unique, emprunt, contenant, déclassement).

**Constat** : pas de champ dédié « mesure linéaire (cm) » (seul `dimension`, texte libre, existe) ; `Container` n'a aucun champ de capacité, donc aucun calcul d'espace restant.

**Actions** :
- Ajouter `linear_measure_cm` sur `RecordMedium`/`Record`.
- Ajouter `capacity_cm` sur `Container` (ou `ContainerProperty`) + calcul de l'espace restant à l'affichage du conteneur.

**KPI** :
- Capacité renseignée sur 100 % des nouveaux `Container` créés après migration.
- Espace restant affiché correctement (calcul vérifié par test) sur 100 % des fiches contenant contenant au moins un dossier.

---

## Étape 7 — Import / Export en masse

**Objectif** : permettre l'administration en volume des domaines de valeurs, et la production de rapports configurables sur les notices.

**Constat** : aucune route d'import Excel pour les `ReferenceValue` (ajout un par un uniquement) ; aucun export des valeurs d'un domaine ; `bulkExport`/`bulkPrint` sur `Record` existent mais sans gabarit configurable par l'utilisateur.

**Actions** :
- Import Excel (gabarit code/valeur/description/actif) + rapport d'erreurs ligne par ligne pour `ReferenceValue`.
- Export `.xlsx` des valeurs d'un domaine (`app/Exports/*` déjà utilisé ailleurs dans le projet, à réutiliser).
- Gabarit de rapport configurable pour `bulkExport`/`bulkPrint`.

**KPI** :
- Import de 100 valeurs de référence en moins de 10 secondes, avec 0 % d'erreur silencieuse (toute ligne invalide est rapportée nommément).
- Export `.xlsx` disponible sur 100 % des domaines de valeurs existants.

---

## Étape 8 — Collaboration sur les notices (partage, favoris, raccourci, commentaire)

**Objectif** : rattraper les actions collaboratives listées par Constellio sur dossiers et documents, actuellement absentes.

**Constat** : pas de partage ad hoc par notice à un utilisateur/groupe précis (seules des policies globales existent) ; pas de favoris (`Favorite`/`Bookmark`) ; pas de raccourci (`parent_id` unique, une notice = un seul emplacement) ; pas de commentaire générique sur `Record` (seulement `TaskComment`/`DeclassementComment`, scoped) ; pas de duplication (voir étape 9) ; pas de lien de consultation ponctuel par notice (le module Public Portal expose un catalogue entier, pas un partage à la demande).

**Actions** :
- Modèle `RecordShare` (utilisateur/groupe, permissions, expiration optionnelle).
- Modèle `Favorite` (polymorphe, personnel ou partagé).
- Modèle `RecordComment` générique (auteur seul modifie/supprime).
- Étudier la faisabilité d'un raccourci (`RecordShortcut` pointant vers un `Record` existant, sans dupliquer `parent_id`).

**KPI** :
- Partage d'une notice à un utilisateur précis opérationnel en moins de 3 clics, avec expiration testée.
- Favoris et commentaires disponibles sur 100 % des types de notices (documents et dossiers).

---

## Étape 9 — Duplication de notices & versions mineure/majeure

**Objectif** : permettre de dupliquer une notice (métadonnées seules ou fiche + arborescence) et formaliser la distinction mineure/majeure déjà esquissée par `RecordMedium.status`.

**Constat** : aucune méthode `duplicate()`/`copy()` sur `Record` (seul `createVersion()` existe, qui est du versionnement, pas de la duplication indépendante) ; `RecordMedium.status` (`draft/final/obsolete`) pourrait porter la distinction mineure/majeure mais aucune action UI « Finaliser » ne l'exploite explicitement.

**Actions** :
- `Record::duplicate(bool $withChildren = false)` : copie des métadonnées seules, ou fiche + arborescence (sans documents).
- Action « Finaliser » exposée en UI, s'appuyant sur `RecordMedium.status`.

**KPI** :
- Duplication d'une notice (méta seules ou fiche + arborescence) réalisable en 1 action, taux de succès 100 % sur jeu de test.
- Distinction mineure/majeure visible et actionnable sur 100 % des `RecordMedium` en état `draft`.

---

## Étape 10 — Module Workflow : assignation dynamique, sécurité, tableau de bord

**Objectif** : rendre le moteur BPMN déjà existant (`WorkflowDefinition`/`WorkflowInstance`/`WorkflowTransition`/`WorkflowEngine`) réellement utilisable en production, en comblant ses trois manques les plus bloquants.

**Constat** :
- Aucune règle d'assignation dynamique : `Task.assigned_to` est fixé en dur à la création (`WorkflowEngine::createTaskFromKey`) — aucune des 4 règles Constellio (créateur du flux / personne ayant terminé la tâche précédente / désigné par le responsable / titulaire d'une fonction) n'est implémentée.
- Aucune sécurité de démarrage par workflow (public/privé par unité administrative/utilisateurs/groupes) — seule la portée `organisation_id` + policy globale existent.
- Aucun démarrage possible depuis la fiche d'une notice (le titre n'est jamais auto-rempli), et aucun tableau de bord (échéances, retards par workflow/étape/utilisateur).
- Le moteur ne traite que des transitions séquentielles simples avec condition JSON plate — pas de vraie sémantique de portes BPMN (exclusive/inclusive/parallèle).

**Actions** (par ordre de dépendance) :
1. Règles d'assignation dynamique sur `Task` (créateur / précédent / responsable / fonction via `ReferenceList`).
2. Sécurité de démarrage sur `WorkflowDefinition` (public/privé/utilisateurs/groupes).
3. Démarrage d'un workflow depuis `Record::show`, titre auto-rempli depuis la notice.
4. Sémantique réelle des portes BPMN dans `WorkflowEngine` (au minimum exclusive + parallèle).
5. Échéance calculée automatiquement (jours ouvrables depuis assignation) + rappels.
6. Tableau de bord (taux de respect des échéances, retards par workflow/étape/utilisateur).

**KPI** :
- 0 tâche de workflow nécessitant une ré-assignation manuelle après déploiement des règles d'assignation dynamique (mesuré sur les 20 premières instances en production).
- 100 % des workflows démarrables directement depuis la fiche d'une notice, titre auto-rempli.
- Tableau de bord affichant un taux de respect des échéances calculé sur données réelles (non simulées).

---

## Suivi global

| # | Étape | KPI de complétion |
|---|---|---|
| 1 | Corbeille & suppression sécurisée | 0 suppression silencieuse d'entité utilisée ; 100 % des notices supprimées restaurables |
| 2 | Domaines par défaut + schéma lié | 9/9 domaines créés ; 5/5 domaines système avec schéma lié |
| 3 | Propriétés de métadonnées enrichies | 100 % des data_type textuels avec triable/surlignage/autocomplétion configurables |
| 4 | Métadonnées copiées & calculées | ≥ 1 copiée + ≥ 1 calculée fonctionnelles, testées |
| 5 | Métadonnées sécurisées par rôle | 0 fuite en affichage et en recherche sur métadonnées restreintes |
| 6 | Mesure linéaire & capacité contenants | 100 % des contenants avec capacité renseignée et espace restant calculé |
| 7 | Import / Export en masse | Import 100 valeurs < 10 s, 0 % erreur silencieuse ; export sur 100 % des domaines |
| 8 | Collaboration sur les notices | Partage < 3 clics ; favoris/commentaires sur 100 % des types de notices |
| 9 | Duplication & versions mineure/majeure | Duplication 100 % de succès ; distinction mineure/majeure actionnable |
| 10 | Module Workflow | 0 ré-assignation manuelle ; 100 % démarrage depuis une notice ; tableau de bord sur données réelles |
