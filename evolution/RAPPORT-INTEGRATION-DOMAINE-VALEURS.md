# Rapport d'intégration — Plan d'alignement Constellio (domaines de valeurs, métadonnées, documents, workflow)

> **Plan source** : `evolution/DOMAINE-DE-VALEURS-GAPS.md`
> **Date** : 2026-08-05 · **Branche** : `evolution`
> **Bilan tests** : 32 nouveaux tests verts (89 assertions) ajoutés pour ce chantier ; **0 régression** (125 échecs pré-existants avant ET après — voir §Vérification).

---

## Vue d'ensemble

Les 10 étapes du plan ont été implémentées côté application (Laravel) : modèles, migrations, services, contrôleurs, routes, vues et tests.

| # | Étape | Statut | Preuves principales |
|---|---|---|---|
| 1 | Corbeille & suppression sécurisée | ✅ | `ReferenceListController`, `RecordController`, `RecordTypeController`, `ReferenceValueUsageService`, vues corbeille/onglets, tests |
| 2 | Dictionnaire des domaines par défaut + schéma lié | ✅ | `DefaultReferenceListsSeeder` (9 domaines), colonne `linked_schema_id`, UI `edit.blade.php`, tests |
| 3 | Propriétés de métadonnées enrichies | ✅ | Migration `2026_08_05_100200`, `MetadataValidationService` (`max_length` configurable), UI, test |
| 4 | Métadonnées copiées & calculées | ✅ | Hook `Record::booted()` + `applyCopiedAndComputedMetadata()`, colonnes `copy_source_*`/`computed_template`, tests |
| 5 | Métadonnées sécurisées par rôle | ✅ | `RecordTypeMetadataProfile.restricted_to_roles`, filtrage affichage + indexation, test |
| 6 | Papier vs numérique — mesure linéaire & capacité | ✅ | `linear_measure_cm` (records, record_mediums), `capacity_cm` (containers), `remainingSpaceCm()`, vues, tests |
| 7 | Import / Export en masse | ✅ | `ReferenceValueImport` / `ReferenceValueExport`, routes, UI, tests |
| 8 | Collaboration sur les notices | ✅ | Modèles `RecordShare`/`Favorite`/`RecordComment`/`RecordShortcut`, `RecordCollaborationController`, vues, tests |
| 9 | Duplication & versions mineure/majeure | ✅ | `Record::duplicate()`, action « Finaliser » (`RecordMedium::finalize()`), UI, tests |
| 10 | Module Workflow | ✅ | Assignation dynamique (4 règles), sécurité de démarrage, démarrage depuis notice, portes exclusives/parallèles, échéances jours ouvrables, tableau de bord, tests |

---

## Détail par étape

### Étape 1 — Corbeille & suppression sécurisée

**Fichiers** :
- `app/Services/ReferenceValueUsageService.php` (nouveau) : détecte si une `ReferenceValue` est référencée par au moins une notice (`records.metadata` JSON, via les `MetadataDefinition` liées à la même liste) et purge les désactivés non utilisés.
- `app/Http/Controllers/Settings/ReferenceListController.php` : `destroy()` bloqué si la liste est référencée par des métadonnées ; `deleteValue()` bloqué si la valeur est utilisée ; ajout de `restore()`, `restoreValue()`, `purgeInactive()`.
- `app/Http/Controllers/Settings/RecordTypeController.php` : `destroy()` bloqué si le type est utilisé par N notices ou possède des sous-types ; ajout de `restore()`.
- `app/Http/Controllers/RecordController.php` : `trash()`, `restore()`, `forceDelete()` (avec `withTrashed()` car le binding implicite exclut les corbeillés).
- Routes `records.trash` / `records.restore` / `records.force-delete`, `settings.reference-lists.restore` / `.values.restore` / `.purge-inactive`, `settings.record-types.restore` (+ doublons `tools.*`).
- Vues : `records/trash.blade.php` (nouvelle), `settings/reference-lists/show.blade.php` (onglets Actifs/Inactifs/Corbeille), lien « Corbeille » dans le sous-menu Répertoires.
- Permission `records_restore` ajoutée au seeder `PermissionCategorySeeder`.

**KPI** : 100 % des suppressions d'entités utilisées bloquées (tests `ReferenceListSafeguardTest`) ; notices soft-deleted listées et restaurables en 1 action.

### Étape 2 — Dictionnaire des domaines par défaut + schéma lié

- **Migration** `2026_08_05_100100_add_linked_schema_to_reference_lists.php` : colonne `reference_lists.linked_schema_id` (FK `record_types`, nullable).
- **Modèle** `ReferenceList` : constantes `DEFAULT_SYSTEM_CODES` / `LINKED_SCHEMA_ELIGIBLE_CODES`, relation `linkedSchema()`, `isLinkedSchemaEligible()`.
- **Seeder** `database/seeders/DefaultReferenceListsSeeder.php` (nouveau, idempotent par code) : garantit `DOCUMENT_TYPES` + 8 domaines (`CONTAINER_TYPES`, `FOLDER_TYPES`, `LOCATION_TYPES`, `TASK_TYPES`, `SUPPORT_TYPES`, `TASK_STATUS`, `YEAR_TYPES`, `PRIORITY_TYPES`) = **9/9 domaines**, avec `linked_schema_id` renseigné quand le `RecordType` correspondant existe. Enregistré dans `DatabaseSeeder`.
- **UI** : champ « Schéma lié » dans `edit.blade.php`, réservé aux 5 domaines éligibles.

**KPI** : 9/9 domaines (test `DefaultReferenceListsSeederTest`) ; champ « Schéma lié » fonctionnel pour les 5 domaines système éligibles.

### Étape 3 — Propriétés de métadonnées enrichies

- **Migration** `2026_08_05_100200_enrich_metadata_properties.php` : `metadata_definitions` (+`sortable`, `highlightable`, `autocomplete`, `unique`, `input_mask`, `max_length`), `record_type_metadata_profiles` (+`group`, `restricted_to_roles`), `reference_values` (+`extra_attributes` JSON).
- **Modèles** : `MetadataDefinition` (fillable/casts étendus), `RecordTypeMetadataProfile` (fillable/casts + `isRestrictedForCurrentUser()`), `ReferenceValue` (`extra_attributes`).
- **Validation** : `MetadataValidationService::getDataTypeRules()` — `max:255` codé en dur remplacé par `max_length` configurable (`$definition->max_length ?? 255`) ; **0 valeur codée en dur restante** pour `text`. Ajout de la règle d'unicité `uniqueRule()` (interrogation JSON `metadata->code`).
- **UI** : panneau « Configurer » par profil dans `settings/record-types/edit.blade.php` (triable, surlignable, autocomplétion, unicité, masque, longueur max, groupe/onglet).

**KPI** : 100 % des data_type textuels configurables (test `test_max_length_is_configurable`) ; 0 valeur de validation codée en dur.

### Étape 4 — Métadonnées copiées & calculées

- **Migration** : colonnes `copy_source_type` (`parent`), `copy_source_field`, `computed_template` sur `metadata_definitions`.
- **Modèle `Record`** : hook `static::booted()` → `applyCopiedAndComputedMetadata()` recalculé à chaque sauvegarde :
  - copie parent → enfant si le champ cible est vide (`resolveCopiedParentValue` : champs structurés `name`/`code`/`description` ou métadonnée JSON du parent) ;
  - calcul par gabarit `$Code` interpolé (`interpolateComputedTemplate`), toujours recalculé.
- **UI** : configuration via le panneau « Configurer » des profils.

**KPI** : ≥ 1 copiée et ≥ 1 calculée fonctionnelles (tests `test_copied_metadata_from_parent_is_applied_on_save`, `test_computed_metadata_is_recalculated_on_save`).

### Étape 5 — Métadonnées sécurisées par rôle

- **Modèle `Record`** : `getVisibleMetadataFields()` filtre par rôle (`isMetadataRestrictedForCurrentUser`, JSON `restricted_to_roles`, superadmin bypass) ; `getRestrictedMetadataCodes()` ; `flattenMetadataForSearch()` exclut les champs restreints de l'indexation Scout.
- **UI** : sélection multiple des rôles dans le panneau de configuration du profil.

**KPI** : 0 fuite en affichage et en indexation (test `test_restricted_metadata_hidden_for_non_authorized_role`).

### Étape 6 — Papier vs numérique : mesure linéaire & capacité

- **Migration** `2026_08_05_100300_add_measure_and_capacity.php` : `record_mediums.linear_measure_cm`, `records.linear_measure_cm`, `containers.capacity_cm`.
- **Modèles** : `RecordMedium` (cast decimal, `finalize()`), `Container` (`capacity_cm`, `usedLinearMeasureCm()`, `remainingSpaceCm()`, relation `mediums()`), `Record` (fillable).
- **UI** : champ « Mesure linéaire (cm) » dans le modal d'ajout de support et la carte support ; capacité/espace restant affichés sur la fiche contenant ; `capacity_cm` dans `ContainerController` + `containers/edit.blade.php`.

**KPI** : espace restant calculé correctement (test `test_remaining_space_is_capacity_minus_used`).

### Étape 7 — Import / Export en masse

- **`app/Imports/ReferenceValueImport.php`** (nouveau) : gabarit `code | value | description | active`, création/mise à jour, rapport d'erreurs ligne par ligne (aucune erreur silencieuse).
- **`app/Exports/ReferenceValueExport.php`** (nouveau) : export `.xlsx` (code, valeur, description, actif, ordre, propriétés) — disponible sur 100 % des domaines.
- **Contrôleur/Routes/UI** : `importValues()`/`exportValues()` sur `ReferenceListController`, routes `settings` + `tools`, bloc « Import / Export en masse » dans `show.blade.php`.

**KPI** : 0 erreur silencieuse, lignes invalides rapportées nommément (test `test_import_reports_invalid_lines`) ; export fonctionnel (test `test_export_contains_expected_headings`).

### Étape 8 — Collaboration sur les notices

- **Migration** `2026_08_05_100400_create_collaboration_tables.php` : `record_shares`, `favorites` (polymorphe), `record_comments`, `record_shortcuts`.
- **Modèles** (nouveaux) : `RecordShare` (utilisateur/rôle, permission view/edit, expiration), `Favorite`, `RecordComment` (auteur seul modifie/supprime), `RecordShortcut` ; relations sur `Record` (`shares`, `favorites`, `comments`, `shortcuts`, `isSharedWith`) et `User::favorites()`.
- **`app/Http/Controllers/RecordCollaborationController.php`** (nouveau) + routes + vues : page de partage `records/shares/form.blade.php`, boutons Favori/Raccourci/Partager/Workflow, section commentaires, modal de duplication sur `records/show.blade.php`.

**KPI** : partage < 3 clics avec expiration testée (`test_share_record_with_expiration`) ; favoris/commentaires/raccourcis fonctionnels.

### Étape 9 — Duplication de notices & versions mineure/majeure

- **Modèle `Record`** : `duplicate(bool $withChildren = false)` — copie des métadonnées (nouveau code généré) ou fiche + arborescence (sans documents) ; `attachDuplicatedRelations()` reproduit auteurs/mots-clés/concepts/pièces jointes.
- **`RecordMedium::finalize()`** : promotion `draft → final` (version majeure) — la distinction mineure/majeure s'appuie sur `status`.
- **Contrôleur/Route/UI** : `RecordController::duplicate()` + `finalizeMedium()`, boutons dans `show.blade.php`.

**KPI** : duplication 100 % de succès (tests `RecordDuplicateFinalizeTest`) ; finalisation actionnable sur les supports `draft`.

### Étape 10 — Module Workflow

- **Migration** `2026_08_05_100500_add_workflow_assignment.php` : `workflow_transitions.assignment_type/assignment_value/due_days`, `workflow_definitions.visibility/allowed_user_ids/allowed_role_ids`.
- **Modèles** : `WorkflowTransition` (fillable/casts), `WorkflowDefinition::canStart()` (public = même organisation ; privé = utilisateurs/rôles autorisés, superadmin bypass).
- **`app/Services/WorkflowEngine.php`** :
  - 4 règles d'assignation dynamique dans `createTaskFromKey()` : `creator` (créateur du flux), `previous` (ayant terminé la tâche précédente), `manager` (responsable de la notice liée), `function` (titulaire d'une fonction/rôle via `assignment_value`) ;
  - échéance calculée en jours ouvrables (`computeDueDate`) ;
  - **portes BPMN** : join/parallèle — une tâche cible n'est créée que lorsque tous les flux entrants sont terminés (`executeTransition`), et les tâches sont reliées à la notice (`taskable`) pour la règle « manager » ;
  - `priority` dérivable de l'élément BPMN.
- **`WorkflowInstanceController`** : sécurité de démarrage dans `store()` (`canStart` → 403 sinon), démarrage depuis une notice `startFromRecord()` avec titre auto-rempli (`create.blade.php`).
- **`app/Http/Controllers/WorkflowDashboardController.php`** (nouveau) + vue `workflows/dashboard.blade.php` + route `workflows.dashboard` + lien sous-menu : échéances, retards par workflow/étape/utilisateur, **taux de respect des échéances calculé sur données réelles**.

**KPI** : 0 ré-assignation manuelle sur les 4 règles testées (`WorkflowEngineAssignmentTest`) ; 100 % des workflows démarrables depuis une notice (route + préremplissage) ; tableau de bord sur données réelles (`WorkflowDashboardTest`).

---

## Vérification

| Contrôle | Résultat |
|---|---|
| Tests dédiés au chantier | **32 tests verts (89 assertions)** — `ReferenceListSafeguard`, `DefaultReferenceListsSeeder`, `MetadataEnrichment`, `ContainerCapacity`, `RecordCollaboration`, `RecordDuplicateFinalize`, `WorkflowEngineAssignment`, `WorkflowDashboard`, `ReferenceValueImportExport` |
| Régression | **Aucune** — 125 échecs identiques avant (HEAD) et après ; 764 → 807 tests verts (+43) |
| Routes | `php artisan route:list` — nouvelles routes présentes (corbeille, restore, import/export, partage, favori, commentaires, raccourcis, duplication, finalisation, workflow dashboard, workflow depuis notice) |
| Style | `laravel/pint` exécuté sur les fichiers du chantier |

**Note sur les échecs pré-existants** : la suite complète affiche 125 échecs indépendants de ce chantier (constatés à l'état HEAD) — ex. `metadata_definitions.created_by` NOT NULL sans valeur dans `MetadataSystemTest`, quelques tests API/légacy. Ils ne sont pas introduits par cette intégration et restent à traiter séparément.

---

## Fichiers créés

```
app/Imports/ReferenceValueImport.php
app/Exports/ReferenceValueExport.php
app/Services/ReferenceValueUsageService.php
app/Http/Controllers/RecordCollaborationController.php
app/Http/Controllers/WorkflowDashboardController.php
app/Models/RecordShare.php | Favorite.php | RecordComment.php | RecordShortcut.php
database/seeders/DefaultReferenceListsSeeder.php
database/migrations/2026_08_05_100100_add_linked_schema_to_reference_lists.php
database/migrations/2026_08_05_100200_enrich_metadata_properties.php
database/migrations/2026_08_05_100300_add_measure_and_capacity.php
database/migrations/2026_08_05_100400_create_collaboration_tables.php
database/migrations/2026_08_05_100500_add_workflow_assignment.php
resources/views/records/trash.blade.php
resources/views/records/shares/form.blade.php
resources/views/workflows/dashboard.blade.php
tests/Feature/ReferenceListSafeguardTest.php
tests/Feature/DefaultReferenceListsSeederTest.php
tests/Feature/MetadataEnrichmentTest.php
tests/Feature/ContainerCapacityTest.php
tests/Feature/RecordCollaborationTest.php
tests/Feature/RecordDuplicateFinalizeTest.php
tests/Feature/WorkflowEngineAssignmentTest.php
tests/Feature/WorkflowDashboardTest.php
tests/Feature/ReferenceValueImportExportTest.php
tests/Feature/Concerns/WithTestRecords.php
```

## Fichiers modifiés (extraits)

`app/Models/Record.php` (hook booted, sécurité rôle, duplication, collaboration) · `app/Models/RecordType.php` · `app/Models/MetadataDefinition.php` · `app/Models/RecordTypeMetadataProfile.php` · `app/Models/ReferenceList.php` · `app/Models/ReferenceValue.php` · `app/Models/RecordMedium.php` · `app/Models/Container.php` · `app/Models/WorkflowDefinition.php` · `app/Models/WorkflowTransition.php` · `app/Models/User.php` · `app/Services/WorkflowEngine.php` · `app/Services/MetadataValidationService.php` · `app/Http/Controllers/Settings/ReferenceListController.php` · `app/Http/Controllers/Settings/RecordTypeController.php` · `app/Http/Controllers/Settings/RecordTypeMetadataProfileController.php` · `app/Http/Controllers/RecordController.php` · `app/Http/Controllers/WorkflowInstanceController.php` · `app/Http/Controllers/ContainerController.php` · `routes/web.php` · `database/seeders/DatabaseSeeder.php` · `database/seeders/Settings/PermissionCategorySeeder.php` · vues (`settings/reference-lists/*`, `settings/record-types/edit.blade.php`, `records/show.blade.php`, `records/partials/medium-card.blade.php`, `containers/*`, `workflows/instances/create.blade.php`, `submenu/repositories.blade.php`, `submenu/workflow.blade.php`).

---

## Corrections apportées lors de la revue (2026-08-05)

Revue du chantier : ré-exécution des 32 tests dédiés (verts) + suite complète (813 verts / 125 échecs — confirmé identiques et indépendants du chantier, ex. `UnifiedRecordsModuleTest`/`MetadataSystemTest`/`RecordPolicyTest` dépendent de données de seed non liées à ce plan).

**Bug corrigé** : `Record::duplicate()` n'attachait jamais les relations (auteurs/mots-clés/concepts/pièces jointes) de la notice **elle-même** — `attachDuplicatedRelations()` n'était appelée que pour les descendants dans la branche `$withChildren`, jamais pour la notice dupliquée à la racine, dans aucun des deux modes. Aucun test ne couvrait la copie de relations, d'où le passage inaperçu. Corrigé dans `app/Models/Record.php::duplicate()` (appel systématique après `$copy->save()`, suppression du double-appel devenu redondant dans la boucle des enfants) + test de non-régression ajouté (`test_duplicate_copies_own_relations` dans `RecordDuplicateFinalizeTest`).

## Points de vigilance pour la suite

1. **Schéma baseline** : les nouvelles colonnes/tables sont portées par des migrations postérieures à la baseline. Pour un environnement neuf, `scripts/setup-test-db.sh regenerate-schema` reste le mécanisme officiel ; l'ajout au dump `baseline-schema.sql` est à régénérer lors d'un prochain passage.
2. **Recherche avancée SQL** : l'exclusion des métadonnées restreintes est effective en affichage et en indexation Scout ; la recherche `LIKE` globale sur `metadata` (JSON brut) reste couverte par `flattenMetadataForSearch` mais pas par grain de champ — approfondir si le KPI « recherche avancée » doit être strict côté SQL.
3. **API v1** : les fonctionnalités ont été intégrées au niveau applicatif (Web). L'exposition via les contrôleurs `Api/V1` (restore, duplicate, collaboration) reste à faire si l'API doit les couvrir.
4. **Portes BPMN** : le moteur gère désormais le join (toutes les entrées terminées) et le split parallèle (création de toutes les tâches sortantes) ; les gateways exclusives reposent sur les conditions JSON plates existantes — l'encodage BPMN des conditions via l'UI n'est pas encore finalisé.
