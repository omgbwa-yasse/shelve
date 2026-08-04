# Unification des documents physiques et numériques (alignement IntelliGID)

> Plan de migration pour remplacer les trois entités actuelles (`RecordPhysical`, `RecordDigitalFolder`, `RecordDigitalDocument`) par un modèle unifié notice/support/fichier, sur le principe d'IntelliGID (`FicheDocument` / `SupportDocument` / `FichierElectronique`). Complète `analyse-intelligid.md` et `analyse-intelligid-metadonnees.md`.

---

## Contexte

Aujourd'hui shelve modélise un document sous **trois entités racines indépendantes** : `RecordPhysical` (fiche papier, ISAD(G)), `RecordDigitalFolder` (dossier numérique, arbre séparé) et `RecordDigitalDocument` (fichier numérique versionné). Chaque module a ses propres migrations, ses propres tables de types (`RecordDigitalFolderType`/`RecordDigitalDocumentType`, sans équivalent pour le physique), ses propres pivots (mots-clés, thésaurus, `Dolly`), et tout ce qui doit s'appliquer aux deux mondes (recherche, `Task.taskable`, `Communication`, `Reservation`) duplique la logique 2 à 3 fois. Un lien `transferred_to_record_id` existe déjà entre numérique et physique, mais c'est un pont manuel a posteriori, pas une unification structurelle.

Chez IntelliGID, un document est **une seule notice** (`FicheDocument`) ; le support (papier/numérique/microfilm) est porté par une table de liaison (`SupportDocument`) qui ne se greffe que si nécessaire, et le fichier réel vit dans une troisième table (`FichierElectronique`). Une notice peut donc avoir un support papier **et** un support numérique (cas d'un document numérisé) sans dupliquer sa description. La distinction dossier/document, elle, est portée par le **niveau de description** (`RecordLevel`/ISAD(G): Fonds > Série > Dossier > Pièce), pas par une table séparée — ce que shelve possède déjà via `RecordLevel`, donc plus besoin de deux tables comme IntelliGID (`FicheDossier`/`FicheDocument`) : `level_id` suffit à distinguer un nœud "conteneur" d'un nœud "document".

**Objectif** : remplacer les 3 entités actuelles par une notice unique `Record` (table `records`), un type unifié (`record_types`, ancré sur `reference_lists` comme `TypeDocument` l'est sur `DomaineValeurs`), et une table de support (`record_mediums`) qui porte 0, 1 ou plusieurs supports (physique et/ou numérique) par notice — supprimant définitivement la séparation physique/numérique en tant que *structure*, tout en conservant les capacités spécifiques (versionnement, signature électronique, emplacement physique).

Vu l'ampleur (15+ tables dépendantes, 2 gros contrôleurs web + 2 contrôleurs API, 2 arborescences de vues), **ce plan est découpé en 7 phases livrables indépendamment**, chacune testable avant de passer à la suivante. Ne pas tout exécuter d'un coup.

---

## Vue d'ensemble cible

```
record_types (= TypeDocument)
   id, code, name, parent_id (auto-réf), reference_list_id → reference_lists (= id_domaineValeurs)
   is_container (bool)  -- true = joue le rôle "dossier" (pas de médium propre requis)
        │
        ▼
record_type_metadata_profiles (= MetadonneeProfilMO, fusion des 2 tables existantes)
   record_type_id, metadata_definition_id, mandatory, visible, readonly, default_value, sort_order
        │
        ▼
records (= FicheDocument, notice unique)
   id, code, name, description(s) ISAD(G), type_id, level_id, status_id, activity_id,
   parent_id (auto-réf, remplace RecordPhysical.parent_id ET RecordDigitalFolder.parent_id),
   organisation_id, creator_id, metadata (json, valeurs des champs du profil)
        │
        ▼ (1 - N)
record_mediums (= SupportDocument)
   id, record_id, support_id → record_supports (papier/numérique/microfilm, table déjà existante),
   -- colonnes "physique" : container_id/emplacement (remplace record_physical_container)
   -- colonnes "numérique" : attachment_id, version_number, is_current_version, parent_version_id,
   --                        checked_out_by/at, signature_status/signed_by/signed_at/signature_data
        │
        ▼ (si support = numérique)
attachments (= FichierElectronique, déjà existant, inchangé)
```

Une notice avec un support papier ET un support numérique (dossier numérisé) = une seule ligne `records` + deux lignes `record_mediums`. C'est exactement le comportement demandé.

---

## Exemple concret — un contrat papier, plus tard numérisé

### 1. Le type (`record_types`)

```json
{
  "id": 12,
  "code": "CONTRAT",
  "name": "Contrat",
  "parent_id": null,
  "reference_list_id": 4,      // pointe vers la liste "Types de documents"
  "is_container": false,        // c'est une pièce, pas un dossier
  "requires_versioning": true,
  "requires_signature": true,
  "default_access_level": "internal"
}
```

### 2. La notice — une seule ligne, quel que soit le support (`records`)

```json
{
  "id": 4821,
  "code": "REC-2024-4821",
  "name": "Contrat de prestation — Société Dupont",
  "type_id": 12,
  "level_id": 4,                // "Pièce" (ISAD(G))
  "status_id": 2,                // "Actif"
  "activity_id": 87,
  "parent_id": 4700,             // le dossier qui le contient
  "organisation_id": 3,
  "creator_id": 15,
  "metadata": {
    "nature_contrat": "prestation_service",
    "montant": 12500.00,
    "partie_prenante": "Société Dupont SARL"
  },
  "date_exact": "2024-03-12"
}
```

Notez : **aucune colonne ne dit "papier" ou "numérique"** ici — c'est la notice descriptive pure, exactement comme `FicheDocument` chez IntelliGID.

### 3. Les supports — 0, 1 ou plusieurs lignes (`record_mediums`)

**Au départ (juillet 2024)**, un seul support, physique :

```json
{
  "id": 9001,
  "record_id": 4821,
  "support_id": 1,               // "Papier"
  "container_id": 340,           // boîte physique où il est rangé
  "attachment_id": null,
  "version_number": 1,
  "is_current_version": true
}
```

**En janvier 2025**, quelqu'un numérise le contrat. On n'ouvre pas une nouvelle fiche : on **ajoute un support** à la même notice (`addMedium`) :

```json
{
  "id": 9002,
  "record_id": 4821,             // même notice
  "support_id": 2,               // "Numérique"
  "container_id": null,
  "attachment_id": 55310,        // → attachments.id
  "version_number": 1,
  "is_current_version": true,
  "signature_status": "signed",
  "signed_by": 15,
  "signed_at": "2025-01-14T10:22:00"
}
```

### 4. Le fichier réel (`attachments`, déjà existant, inchangé)

```json
{
  "id": 55310,
  "name": "contrat-dupont-signe.pdf",
  "mime_type": "application/pdf",
  "file_hash_md5": "a1b2c3...",
  "size": 842113
}
```

### 5. Ce que ça donne à l'écran (`records/show.blade.php`)

```
┌─────────────────────────────────────────────────────────┐
│ REC-2024-4821 — Contrat de prestation — Société Dupont   │
│ Type: Contrat · Statut: Actif · Dossier parent: DOS-4700 │
├─────────────────────────────────────────────────────────┤
│ Supports (2)                                             │
│                                                            │
│  📦 Papier — Boîte #340, Rayon B-12       [Voir plan]     │
│                                                            │
│  💾 Numérique v1 — contrat-dupont-signe.pdf (823 Ko)      │
│      ✅ Signé le 14/01/2025 par J. Martin                 │
│      [Télécharger] [Check-out] [Historique versions]      │
└─────────────────────────────────────────────────────────┘
```

**Ce que ça remplace concrètement** : aujourd'hui, ce même cas produirait *deux fiches distinctes* dans deux tables différentes (`RecordPhysical` #X et `RecordDigitalDocument` #Y), reliées seulement a posteriori par le champ `transferred_to_record_id` — sans partager ni description, ni métadonnées, ni historique commun. Avec le nouveau schéma, c'est **une seule notice, deux supports**, cherchée une seule fois, affichée une seule fois, avec un seul jeu de métadonnées (`nature_contrat`, `montant`...) qui s'applique aux deux.

---

## Phase 1 — Unifier la couche Type (`record_types`)

**But** : donner à `RecordPhysical` un type pour la première fois, et remplacer `RecordDigitalFolderType`/`RecordDigitalDocumentType` (deux catalogues séparés, non reliés à un référentiel) par un seul, ancré sur `reference_lists`/`reference_values` (déjà l'équivalent fonctionnel de `DomaineValeurs`/`ElementDomaineValeurs`, confirmé opérationnel).

**Migration** `create_record_types_table.php` :
```php
Schema::create('record_types', function (Blueprint $table) {
    $table->id();
    $table->string('code', 50)->unique();
    $table->string('name', 150);
    $table->text('description')->nullable();
    $table->foreignId('parent_id')->nullable()->constrained('record_types')->nullOnDelete();
    $table->foreignId('reference_list_id')->nullable()->constrained('reference_lists')->nullOnDelete();
    $table->boolean('is_container')->default(false); // true = ex-"dossier numérique"
    $table->string('icon')->nullable();
    $table->string('color', 20)->nullable();
    $table->string('code_pattern')->nullable();     // reprend RecordDigitalDocumentType/FolderType
    $table->json('allowed_mime_types')->nullable();
    $table->json('allowed_extensions')->nullable();
    $table->unsignedBigInteger('max_file_size')->nullable();
    $table->boolean('requires_versioning')->default(false);
    $table->boolean('requires_approval')->default(false);
    $table->boolean('requires_signature')->default(false);
    $table->string('default_access_level', 20)->default('internal');
    $table->boolean('is_active')->default(true);
    $table->integer('display_order')->default(0);
    $table->foreignId('created_by')->nullable()->constrained('users');
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('record_type_metadata_profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('record_type_id')->constrained('record_types')->cascadeOnDelete();
    $table->foreignId('metadata_definition_id')->constrained('metadata_definitions')->cascadeOnDelete();
    $table->boolean('mandatory')->default(false);
    $table->boolean('visible')->default(true);
    $table->boolean('readonly')->default(false);
    $table->string('default_value')->nullable();
    $table->json('validation_rules')->nullable();
    $table->integer('sort_order')->default(0);
    $table->timestamps();
    $table->unique(['record_type_id', 'metadata_definition_id']);
});
```

**Backfill (seeder, additif, non destructif)** :
- Copier chaque ligne de `record_digital_document_types` → `record_types` (`is_container=false`), `record_digital_folder_types` → `record_types` (`is_container=true`), en gardant une colonne temporaire `legacy_type` (`'digital_document_type:'.old_id` etc.) pour la traçabilité de la Phase 2.
- Copier `record_digital_document_metadata_profiles` + `record_digital_folder_metadata_profiles` → `record_type_metadata_profiles`.
- Créer un type générique "Dossier papier" et "Pièce/document papier" pour couvrir `RecordPhysical` (qui n'a aujourd'hui aucun type) — mappage par défaut à affiner avec le métier, mais nécessaire pour que la Phase 2 ait un `type_id` à assigner à chaque `RecordPhysical` existant (probablement dérivé de `record_levels` : niveaux hauts → `is_container=true`, niveau "Pièce"/"Document" → `is_container=false`).

**Modèle** : `app/Models/RecordType.php` (nouveau), relations `parent()/children()`, `referenceList()`, `metadataDefinitions()` (belongsToMany via `record_type_metadata_profiles`, withPivot mandatory/visible/readonly/default_value/sort_order — remplace à l'identique les méthodes déjà existantes sur `RecordDigitalFolderType`/`RecordDigitalDocumentType`), `generateCode()` (reprend la logique existante des deux modèles types).

### Précision — système vs personnalisé (arbitrage `metadata_definitions.is_system`)

Point clarifié après relecture : `code`/`name`/`description` (et les champs ISAD(G)) **restent des colonnes SQL réelles sur `records`**, exactement comme `FicheDocument.titre`/`resume` chez IntelliGID — jamais dans `ValeurMetadonnee`, donc jamais dans `metadata_definitions` non plus. Ils ne font pas partie du système de profil dynamique.

En revanche, on aligne `metadata_definitions` sur le patron `personnalisee` d'IntelliGID (`MetadonneeProfilMO.personnalisee`) : **parmi les champs dynamiques d'un profil** (ceux rattachés à un `record_type_id` via `record_type_metadata_profiles` — ex. « Nature du contrat », « Montant »), on distingue ceux livrés par défaut avec le type (« système ») de ceux ajoutés ensuite par un administrateur pour ce client (« personnalisé »). Migration additionnelle dans cette phase :

```php
// add_is_system_to_metadata_definitions_table.php
Schema::table('metadata_definitions', function (Blueprint $table) {
    $table->boolean('is_system')->default(false)->after('active');
});
```

- `is_system = true` : champ standard fourni avec l'installation (ex. les champs déjà définis par les seeders `record_digital_document_types`/`record_digital_folder_types` d'origine) — non supprimable depuis l'UI d'administration, seul `visible`/`sort_order` restent modifiables via le profil.
- `is_system = false` : champ ajouté par un administrateur métier pour un type donné — supprimable, éditable librement.

Cette distinction est purement administrative (contrôle ce qui est modifiable dans `Settings\MetadataDefinitionController`) ; elle n'a aucun impact sur le stockage de la valeur, qui reste dans `records.metadata` (JSON) pour tous les champs dynamiques, système ou non.

**Rien ne casse encore** : `record_digital_folder_types`/`record_digital_document_types` restent en place et utilisés par le code existant jusqu'à la Phase 2. Cette phase est purement additive.

**Complément — type "Fonds" (alignement `FondsDocumentaire`, db.sql)** :

`FondsDocumentaire` (db.sql) est le niveau supérieur ISAD(G) : il porte ses propres champs descriptifs (`etendueUniteArchivistique`, `precisionsCategorieDocuments` ; `histoireAdministrative`/`historiqueConservation`/`porteeContenu` réutilisent `biographical_history`/`archival_history`/`content` de la notice) et ses propres métadonnées (`MetadonneeFondsDocumentaire`, db.sql). **Décision actée** : le fonds devient une notice `records` de niveau "Fonds" (`is_container=true`) — ses champs structurels rejoignent la liste de colonnes de Phase 2, et `MetadonneeFondsDocumentaire` est migrée vers un profil `record_type_metadata_profiles` rattaché au type "Fonds" créé par le backfill. `PlanClassification`/`ProcessusActivite` sont déjà couverts par `activities` (arbre auto-référencé shelve existant) ; `ClassifDomaineObjFicheDossier` est abandonné (le classement passe par `record_types`/`activity_id`).

---

## Phase 2 — Unifier la couche Notice (`records`)

**But** : une seule table portant la description, remplaçant `record_physicals` + `record_digital_folders` + `record_digital_documents` pour tout ce qui est "notice" (hors fichier/support).

`code`/`name`/`description` et les champs ISAD(G) ci-dessous sont des **colonnes structurelles**, pas des métadonnées dynamiques (voir précision en Phase 1) — elles existent pour tout `Record` quel que soit son type, sont indexées/triables nativement, et ne transitent jamais par `metadata` (JSON). Seuls les champs propres à un `RecordType` (via `record_type_metadata_profiles`) vivent dans `metadata`.

**Migration** `create_unified_records_table.php` :
```php
Schema::create('record_confidentialities', function (Blueprint $table) {
    // Axe confidentialité (StatutConfidentialite, db.sql) — seedée en Phase 1,
    // alimente records.confidentiality_id. access_level (string) reste l'approximation rapide.
    $table->id();
    $table->string('code', 30)->unique();   // public | internal | confidential | secret
    $table->string('name', 150);
    $table->timestamps();
});

Schema::create('records', function (Blueprint $table) {
    $table->id();
    $table->string('code', 30)->unique();
    $table->string('name');                              // = title / FicheDocument.titre
    $table->text('description')->nullable();
    // Champs ISAD(G) repris tels quels de record_physicals (biographical_history, archival_history,
    // acquisition_source, content, appraisal, accrual, arrangement, access_conditions,
    // reproduction_conditions, language_material, characteristic, finding_aids,
    // location_original, location_copy, related_unit, publication_note, note,
    // archivist_note, rule_convention) — copie intégrale des colonnes, nullable.
    // Champs "Fonds" (FondsDocumentaire, db.sql) — si level_id = "Fonds" (is_container).
    // histoireAdministrative/historiqueConservation/porteeContenu → réutilisent
    // biographical_history/archival_history/content ci-dessus (pas de doublon).
    $table->text('extent')->nullable();                  // etendueUniteArchivistique
    $table->text('category_precision')->nullable();      // precisionsCategorieDocuments
    // Cycle de vie du dossier (FicheDossier, db.sql) — alimenté par le module déclassement/transfert
    $table->date('opening_date')->nullable();            // dateOuverture
    $table->date('closing_date')->nullable();            // dateFermeture
    $table->date('processing_date')->nullable();         // dateTraitement
    $table->date('transfer_approved_date')->nullable();  // dateTransfertApprouvee
    $table->date('transfer_effective_date')->nullable(); // dateTransfertReelle
    $table->date('deposit_approved_date')->nullable();   // dateVersementApprouvee
    $table->date('deposit_effective_date')->nullable();  // dateVersementReelle
    $table->date('destruction_approved_date')->nullable(); // dateDestructionApprouvee
    $table->date('destruction_effective_date')->nullable(); // dateDestructionReelle
    $table->date('last_reminder_date')->nullable();      // dateDernierRappel
    $table->string('old_record_number')->nullable();     // ancienNumeroDossier
    $table->string('archival_status_gvaa')->nullable();  // statutArchivistiqueGVAA
    $table->boolean('unavailable')->default(false);      // indisponible
    $table->boolean('annual_opening')->default(false);   // ouvertureAnnuelle
    $table->boolean('is_essential')->default(false);     // statutEssentiel
    // Prêt/emprunt de la NOTICE (dateEmprunt/id_emprunteur/datePrevueRetourEmprunt/
    // dateRetourEmpruntReelle/modifieApresEmprunt sur FicheDocument ET FicheDossier, db.sql).
    // NB : le prêt/checkout du FICHIER reste sur record_mediums (Phase 3). Alternative métier
    // possible : traiter le prêt de notice via le module Communication existant
    // (return_date/return_effective) — colonnes ajoutées par défaut pour la parité IntelliGID.
    $table->foreignId('loaned_to')->nullable()->constrained('users')->nullOnDelete();
    $table->dateTime('loaned_at')->nullable();
    $table->dateTime('loan_planned_return_at')->nullable();
    $table->dateTime('loan_actual_return_at')->nullable();
    $table->boolean('modified_after_loan')->default(false);
    // Axe confidentialité / limites d'accès (StatutConfidentialite + LimiteAcces, db.sql)
    $table->foreignId('confidentiality_id')->nullable()->constrained('record_confidentialities')->nullOnDelete();
    $table->foreignId('access_limit_id')->nullable()->constrained('reference_lists')->nullOnDelete();
    // Champs descriptifs non-ISAD (FicheDocument/FicheDossier, db.sql)
    $table->text('table_of_contents')->nullable();       // tableMatieres
    $table->string('quantity')->nullable();              // quantite
    $table->string('dimension')->nullable();             // dimension
    $table->string('publisher')->nullable();             // editeur
    $table->date('publication_date')->nullable();        // datePublication
    $table->dateTime('sent_date')->nullable();           // dateExpedition
    $table->dateTime('received_date')->nullable();       // dateReception
    $table->dateTime('signature_date')->nullable();      // dateSignature (sur la notice)
    $table->boolean('final_version_creation')->default(false); // versionFinaleCreation
    $table->string('location_before_add')->nullable();   // emplacementAvantAjout
    $table->string('sort_value')->nullable();            // valeurTri
    // Portée géographique (PorteeGeographique, db.sql) — liste de lieux libres.
    // Limitation actée : les dates multiples (PorteeTemporelle en lignes) restent une plage
    // unique (start_date/end_date/date_exact) — refus explicite de la duplication en lignes.
    $table->json('geographic_scope')->nullable();
    $table->foreignId('type_id')->nullable()->constrained('record_types')->nullOnDelete();
    $table->foreignId('level_id')->constrained('record_levels');
    $table->foreignId('status_id')->constrained('record_statuses');
    $table->foreignId('activity_id')->nullable()->constrained('activities')->nullOnDelete();
    $table->foreignId('parent_id')->nullable()->constrained('records')->nullOnDelete();
    $table->foreignId('organisation_id')->constrained('organisations'); // corrige le bug existant (colonne absente sur record_physicals)
    $table->foreignId('creator_id')->constrained('users');
    $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
    $table->string('access_level', 20)->default('internal');
    $table->boolean('requires_approval')->default(false);
    $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('approved_at')->nullable();
    $table->json('metadata')->nullable();                 // valeurs des champs du profil (Phase 1)
    $table->date('start_date')->nullable();
    $table->date('end_date')->nullable();
    $table->date('date_exact')->nullable();
    $table->string('date_format', 1)->nullable();
    // Versionnement — chaque version est une notice à part entière (aligné IntelliGID
    // FicheDocument/RelationEstVersionDeFicheDoc), voir "record_relations" ci-dessous.
    $table->integer('version_number')->default(1);
    $table->boolean('is_current_version')->default(true);
    // Traçabilité de migration (retirées en Phase 7)
    $table->string('legacy_source', 20)->nullable();      // 'physical' | 'digital_folder' | 'digital_document'
    $table->unsignedBigInteger('legacy_id')->nullable();
    $table->timestamps();
    $table->softDeletes();
    $table->index(['legacy_source', 'legacy_id']);
    $table->index(['organisation_id', 'status_id']);
    $table->index(['type_id', 'parent_id']);
});
```

**Script de bascule des données** (Artisan command dédiée `php artisan records:migrate-to-unified`, idempotente, transactionnelle par lot) :
1. Insérer toutes les lignes `record_physicals` → `records` (mapping direct colonne à colonne, `legacy_source='physical'`, `legacy_id=record_physicals.id`, `type_id` déduit du `level_id` selon le mapping défini en Phase 1).
2. Insérer `record_digital_folders` → `records` (`legacy_source='digital_folder'`, `type_id` = mapping du `type_id` numérique vers le nouveau `record_types` via la colonne `legacy_type`).
3. Insérer **chaque version** de `record_digital_documents` → **une ligne `records` par version** (`legacy_source='digital_document'`, `legacy_id`, `version_number`/`is_current_version` copiés tels quels) — une famille de versions devient une famille de notices, pas une notice unique (voir "Versionnement" ci-dessous).
4. Construire une table de correspondance temporaire (`record_migration_map`: `legacy_source, legacy_id, new_record_id`) pour la Phase 3/4.
5. Rejouer `parent_id` : pour les `record_physicals`/`record_digital_folders` dont le parent était dans la même famille, retrouver le nouvel `id` via la table de correspondance. Pour les documents versionnés, `parent_id` de **chaque version** pointe vers le même dossier parent (le classement ne change pas d'une version à l'autre).
6. Pour chaque famille de versions migrée à l'étape 3, créer les lignes `record_relations` (`type='version_of'`) chaînant `v(n)` → `v(n-1)` (voir section "Versionnement" ci-dessous, détaillée avant la Phase 3).
7. Migrer le pont existant `linked_digital_metadata` (JSON) sur `record_physicals` **en plus** de `transferred_to_record_id` (les deux mécanismes coexistent en base) : pour chaque notice physique dont le JSON référence un document numérique, appliquer la même fusion que l'étape 3 de Phase 3 (une notice, deux `record_mediums`).
8. Reporter les colonnes ajoutées en Phase 2 depuis l'existant : cycle de vie (dates de gestion), prêt, confidentialité, descriptifs, `geographic_scope` — copie colonne à colonne quand `record_physicals` les porte, sinon `NULL` (jamais de valeur par défaut inventée).

### Versionnement — une version = une notice (aligné IntelliGID)

Point tranché après relecture de `db.sql` : IntelliGID ne stocke **pas** les versions comme des lignes internes à un fichier — chaque version d'un document est une **`FicheDocument` complète**, reliée à la précédente par `RelationEstVersionDeFicheDoc`/`RelationAPourVersionFicheDoc` (deux tables symétriques, une par sens de lecture). Ce plan s'aligne dessus plutôt que de garder le versionnement à l'intérieur de `record_mediums` : `records.version_number`/`is_current_version` (ajoutés ci-dessus) portent l'état de version de la notice elle-même, et une nouvelle table générique remplace les **10 tables `Relation*FicheDoc`** d'IntelliGID (`RelationEstVersionDe`, `RelationAPourVersion`, `RelationRemplace`, `RelationEstRemplacePar`, `RelationRefereA`, `RelationEstReferPar`, `RelationRequiert`, `RelationEstRequisPar`, `RelationAPourPartie`, `RelationSeConformeA`) :

```php
// create_record_relations_table.php
Schema::create('record_relations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('source_id')->constrained('records')->cascadeOnDelete();
    $table->foreignId('target_id')->constrained('records')->cascadeOnDelete();
    $table->string('type', 30); // version_of | replaces | refers_to | requires | has_part | conforms_to
    $table->timestamps();
    $table->unique(['source_id', 'target_id', 'type']);
    $table->index(['target_id', 'type']);
});
```

Une seule table, stockée **dans un seul sens** (contrairement à IntelliGID qui duplique chaque relation dans les deux sens pour accélérer la lecture, au prix d'une cohérence à maintenir applicativement — faiblesse déjà relevée dans `analyse-intelligid.md` §7, volontairement **non reproduite** ici). Le sens inverse (« quelles sont les versions plus récentes de cette notice ? », « quels documents remplacent celui-ci ? ») s'obtient par une requête sur `target_id` — un besoin de lecture très fréquent en sens inverse justifierait un index dédié, déjà prévu ci-dessus, plutôt qu'une duplication de ligne.

`app/Models/RecordRelation.php` : `source()/target()` belongsTo `Record`, scope `ofType($type)`.

Sur `Record` : `previousVersion()` (via `record_relations` où `source_id = $this->id AND type = 'version_of'`, résout `target`), `nextVersions()` (inverse), `getAllVersions()` (remonte toute la chaîne), `createNewVersion(array $attributes = [])` — duplique la notice courante (nouvelle ligne `records`, `version_number + 1`, `is_current_version = true`), bascule l'ancienne à `is_current_version = false`, crée la ligne `record_relations` (`source = nouvelle`, `target = ancienne`, `type = 'version_of'`). Chaque version peut donc avoir son propre `metadata`/`description`, fidèle à IntelliGID — c'est le principal gain de cet alignement (impossible avec un simple champ `version_number` sur `record_mediums`).

**Conséquence pour la Phase 4** : les pivots qui pointent vers un `record_id` (Dolly, thésaurus, mots-clés, `Task.taskable`, `Communication`, `Reservation`...) se rattachent à **une version précise**, pas à la « famille ». Convention à documenter dans le code : sauf action explicite (« voir les versions »), toute liste/recherche standard filtre `is_current_version = true` — un pivot créé sur une ancienne version reste visible sur cette version-là si on navigue son historique, mais n'apparaît plus dans les listes par défaut une fois qu'une version plus récente existe. C'est le même arbitrage qu'IntelliGID (une relation `RelationRequiertFicheDocument` pointe vers une `FicheDocument` précise, pas vers une notion de « famille »).

**Modèle** : `app/Models/Record.php` — ⚠️ ce nom est aujourd'hui pris par le modèle orphelin identifié pendant l'exploration (pointe sur l'ancienne table `records` déjà renommée en `record_physicals`, code mort). **Supprimer `app/Models/Record.php` existant** avant de créer le nouveau modèle unifié au même nom.

Contenu du nouveau `Record` :
- Traits `HasFactory, SoftDeletes, Searchable, BelongsToOrganisation` (cette fois `organisation_id` existe réellement en base — le bug relevé sur `RecordPhysical` est corrigé par construction).
- Relations : `type()`, `level()`, `status()`, `activity()`, `parent()/children()`, `organisation()`, `creator()`, `assignedUser()`, `approver()`, `mediums()` (hasMany `RecordMedium`, Phase 3), `authors()`, `keywords()`, `thesaurusConcepts()` (+ `thesaurusConceptsByWeight/mainThesaurusConcepts/secondaryThesaurusConcepts`, repris tels quels de `RecordPhysical`), `declassementRecords()`, `reactivations()`, `tasks()` (via `Task.taskable`), `dollies()` (une seule relation, remplace les 3 de `Dolly`), `relationsFrom()/relationsTo()` (hasMany `RecordRelation`), `previousVersion()/nextVersions()/getAllVersions()/createNewVersion()` (voir "Versionnement" ci-dessous).
- Helpers métadonnées : `getMetadataValue/setMetadataValue/setMultipleMetadata/getRequiredMetadataFields/getVisibleMetadataFields/validateMetadata/hasCompleteMetadata` — repris de `RecordDigitalFolder`/`RecordDigitalDocument`, généralisés via `MetadataValidationService` reciblé sur `record_type_metadata_profiles` (voir Phase 1) au lieu des deux services séparés.
- Arbre : `isRoot/isLeaf/getDepth/getAncestors/getDescendants/getSiblings/getPath` (repris de `RecordDigitalFolder`).
- `isContainer()` → `$this->type->is_container` (remplace la distinction "c'est un `RecordDigitalFolder`" par une propriété du type).
- `toSearchableArray()` fusionnant les champs indexés des 3 anciens modèles.

**Rien ne casse encore** si l'on garde les anciennes tables en lecture pendant cette phase (double écriture non nécessaire — Phase 2 est un chantier "batch one-shot + bascule progressive du code lisant/écrivant", voir Phase 5/6 pour le cutover réel des contrôleurs).

---

## Phase 3 — Unifier la couche Support (`record_mediums`)

**But** : porter ce qui est spécifique au support (placement physique OU fichier numérique) sans dupliquer la notice. Le versionnement est désormais porté par `records`/`record_relations` (voir Phase 2) — `record_mediums` ne porte plus que l'état du fichier/support **pour la version courante de sa notice** (checkout, signature, statut brouillon/final).

**Migration** `create_record_mediums_table.php` :
```php
Schema::create('record_mediums', function (Blueprint $table) {
    $table->id();
    $table->foreignId('record_id')->constrained('records')->cascadeOnDelete();
    $table->foreignId('support_id')->constrained('record_supports'); // papier/numérique/microfilm, table déjà existante

    // -- Placement physique (remplace record_physical_container) --
    $table->foreignId('container_id')->nullable()->constrained('containers')->nullOnDelete();

    // -- Fichier numérique (remplace les colonnes idoines de record_digital_documents) --
    $table->foreignId('attachment_id')->nullable()->constrained('attachments')->nullOnDelete();
    $table->string('status', 20)->default('draft'); // draft | final | obsolete — aligné StatutVersion (IntelliGID), distinct du statut de la notice
    // Exemplaires (Exemplaire/TypeSupportExemplaire, db.sql) — copie principale vs secondaire.
    // Un document numérisé = 2 record_mediums ; is_principal désigne l'exemplaire de référence.
    $table->boolean('is_principal')->default(true);      // Exemplaire.principal
    $table->string('copy_code')->nullable();             // Exemplaire.numero
    $table->foreignId('checked_out_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('checked_out_at')->nullable();
    $table->string('signature_status', 20)->default('unsigned');
    $table->foreignId('signed_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('signed_at')->nullable();
    $table->text('signature_data')->nullable();

    $table->unsignedBigInteger('legacy_id')->nullable();  // record_digital_documents.id d'origine
    $table->timestamps();
    $table->index(['record_id', 'support_id']);
});
```

**Parité fichier (`FichierElectronique`, db.sql) — vérification à faire, pas de refus systématique** : shelve `attachments` couvre déjà `mime_type`/`taille` (`size`), `md5Hex` (`file_hash_md5`), aperçu (`thumbnail_path`, `page_count`), OCR (`content_text`, `ocr_language`/`ocr_confidence`), chiffrement (`crypt`/`crypt_sha512`). Extensions **non reprises par défaut** (à demander au métier avant Phase 3) : `pdfA` (conversion PDF/A), `voute` (stockage scellé), `resourceId`/`generateNewResourceId`/`updatedResourceId` (identifiant de ressource), `ProprietesFichierElectronique` (propriétés étendues clé/valeur), `SupportDocument.travailCollaboratif` (édition collaborative) et `SupportDocument.taille`. Si requis, une migration complémentaire ajoute `pdf_a` (bool) et `vault` (bool) sur `attachments`.

`status` (`draft`/`final`/`obsolete`) est ajouté par rapport à la version précédente de ce plan : IntelliGID porte cet état sur `StatutVersion` (rattaché à `FicheDocument.id_statutVersion`, propre à la version, distinct du statut global du dossier) — ici il migre naturellement sur `record_mediums` puisque chaque version est déjà sa propre notice `records` (avec son propre `status_id` global), et que `record_mediums.status` capture spécifiquement l'état d'avancement du **fichier** (brouillon de rédaction vs version finale prête à signature), un axe différent du cycle de vie archivistique (`records.status_id` : actif/archivé/etc.).

**Script de bascule** (suite de la commande Phase 2) :
1. Pour chaque `records` issu de `legacy_source='physical'` : créer une ligne `record_mediums` (`support_id` = support papier), reprendre le placement depuis `record_physical_container`.
2. Pour chaque `records` issu de `legacy_source='digital_document'` (rappel : il y en a maintenant une par version, cf. Phase 2) : créer **une ligne `record_mediums`** avec `attachment_id`, `checked_out_by/at`, `signature_*` copiés tels quels depuis la version `record_digital_documents` correspondante (`status` = `final` si `signature_status != 'unsigned'`, sinon `draft`).
3. Pour chaque ancien enregistrement ayant `transferred_to_record_id` renseigné (le pont existant physique↔numérique) : au lieu de deux notices liées par un FK externe, **fusionner en une seule notice avec deux `record_mediums`** — c'est le cas d'usage exact ("document numérisé") que ce chantier doit absorber. Traiter ces paires en priorité dans le script pour valider la logique de fusion avant de traiter le volume restant.

**Modèle** `app/Models/RecordMedium.php` : `record()` belongsTo, `support()` belongsTo `RecordSupport`, `container()` belongsTo `Container`, `attachment()` belongsTo `Attachment`, `checkedOutUser()/signer()`. Méthodes reprises de `RecordDigitalDocument`, adaptées au périmètre restant (checkout/signature — plus de version ici) : `checkout()/checkin()/cancelCheckout()/isCheckedOut()`, `sign()/rejectSignature()`, plus `verifySignature()`/`revokeSignature()` proprement implémentées **corrigeant au passage** les méthodes manquantes déjà cassées dans le code actuel (appelées par `DocumentController` mais absentes de `RecordDigitalDocument`).

À ce stade, `Record::mediums()->count() > 1` = document présent sous plusieurs supports (le besoin exprimé par la demande). `Record::isDigital()` / `Record::isPhysical()` deviennent de simples `mediums()->whereHas('support', fn($q) => $q->where('code', 'numerique'))->exists()`.

---

## Phase 4 — Repointer les dépendants (FK)

Tables/relations à migrer vers `records.id` (remplace `record_physical_id` / `folder_id` / `document_id` selon les cas) :

| Table actuelle | Colonne | Action |
|---|---|---|
| `declassement_records` | `record_physical_id` | Renommer en `record_id`, FK → `records`, backfill via table de correspondance |
| `record_reactivations` | `record_physical_id` | idem |
| `communication_record` | `record_id` (déjà nommé record_id mais → RecordPhysical) | Repointer la FK vers `records` |
| `reservation_record` | `record_id` | idem |
| `record_physical_thesaurus_concept`, `record_digital_folder_thesaurus_concept`, `record_digital_document_thesaurus_concept` | — | **Fusionner en une seule table `record_thesaurus_concept`** (`record_id`, `concept_id`, `weight`, `context`, `extraction_note`) |
| `record_physical_keyword`, `record_digital_folder_keyword`, `record_digital_document_keyword` | — | **Fusionner en `record_keyword`** (`record_id`, `keyword_id`) |
| `dolly_slip_records`, `dolly_digital_folders`, `dolly_digital_documents` | — | **Fusionner en `dolly_records`** (`dolly_id`, `record_id`) ; `dollies.category` enum simplifié |
| `record_physical_attachment` | `record_physical_id` | Conserver pour les pièces jointes secondaires (non "support principal"), repointer vers `records` |
| `workplace_folders.folder_id`, `workplace_documents.document_id` | — | Repointer vers `records.id` ; fusionner `WorkplaceFolder`/`WorkplaceDocument` en une seule pivot `WorkplaceRecord` si le temps le permet (sinon reporter, non bloquant) |
| `Task.taskable_type/taskable_id` | — | Aucun changement de schéma (déjà polymorphe) — juste s'assurer que le code qui crée des tâches sur des documents utilise désormais `Record::class` |
| `mail_*` (module courrier shelve : `mail_transactions`, `mail_archives`, ...) | `record_physical_id` ou équivalents | **Inventaire à réaliser au moment de l'implémentation** : toute FK du module Mail/Communication/`ExternalContact` pointant `record_physicals` doit être repointée vers `records` (`communication_record` est déjà listé ci-dessus) |
| `declassement_lists` + `declassement_records` | `record_physical_id` | En plus du renommage en `record_id`, ajouter `digital_support`/`analog_support`/`include_subrecords` sur `declassement_lists` (source IntelliGID `supportInformatique`/`supportAnalogique`/`dossierInclus`, `FicheDossierListeDeclassement`, db.sql) et créer `declassement_containers` (`declassement_list_id`, `container_id`, `with_pivot` `added_by`/`comment`) pour l'inclusion de contenants entiers (`ContenantListeDeclassement`) |
| (poly-hiérarchie, optionnel) | — | Si le multi-classement est requis (`IdsDossiersParentsDocument`/`idsdossiersparentsdossier`/`idsprocessusparentsdossier`/`idsunitesparentsdossier`, db.sql) : table pivot `record_parents` (`record_id`, `parent_id`) **en plus** de `records.parent_id` (parent canonique) ; sinon rester sur l'arbre strict — **décision métier à acter en Phase 2** |
| (permissions par notice, optionnel) | — | Si la parité `PermissionIFGD`/`UtilisateurAutorise`/`UniteAdministrativeAutorisee` est requise : table `record_permissions` (`record_id`, `user_id`/`organisation_id`, `permission_key`, `date_start`, `date_end`) — sinon `access_level` + `confidentiality_id` + policies globales suffisent |
| (porteurs de dossier, optionnel) | — | Si besoin de `UtilisateursPorteursDossier` : pivot `record_holders` (`record_id`, `user_id`) en complément de `assigned_to` |

Chaque repointage = 1 migration (`ALTER TABLE ... ADD record_id`, backfill via la table de correspondance de la Phase 2/3, puis `DROP` de l'ancienne colonne dans une migration ultérieure une fois Phase 7 atteinte — ne pas dropper avant d'avoir validé le cutover applicatif).

**Règle de filtrage par défaut** : puisque chaque version est désormais une notice `records` distincte (Phase 2), toute requête de listing/recherche standard (`index`, `search`, les pivots ci-dessus dans leurs écrans respectifs) doit filtrer `is_current_version = true` par défaut — sans quoi une famille de documents à 5 versions apparaîtrait 5 fois. Seuls les écrans d'historique explicite (`records/{record}/versions`) lèvent ce filtre.

---

## Phase 5 — Contrôleurs

**Nouveau contrôleur unique** `app/Http/Controllers/RecordController.php` (remplace l'existant + `Web\FolderController` + `Web\DocumentController`) :

| Méthode | Remplace | Notes |
|---|---|---|
| `index/search` | `RecordController::index/search` + `SearchRecordController::advanced/sort` | Une seule requête SQL (`Record::query()` + jointures `mediums`/`type`), fin du pattern "3 requêtes + merge PHP + pagination en mémoire" relevé dans l'exploration |
| `create/store` | `RecordController::create/store` + `FolderController::create/store` + `DocumentController::create/store` | Le formulaire s'adapte via `RecordType.is_container` et `RecordType.metadataDefinitions` (champs dynamiques déjà gérés par `MetadataValidationService`, reciblé Phase 1) ; upload de fichier optionnel → crée un `RecordMedium` numérique |
| `show/edit/update/destroy` | idem ×3 | `show` charge `mediums.attachment`, `mediums.container` |
| `move` | `FolderController::move` | Générique sur `parent_id`, plus seulement pour les dossiers numériques |
| `tree/treeView` | `FolderController::tree/treeView` | Générique, filtrée par `type.is_container` si besoin d'un arbre "dossiers seulement" |
| `addMedium/removeMedium` | (nouveau) | Ajouter/retirer un support à une notice existante — **c'est la fonctionnalité qui incarne directement la demande** ("numériser ce dossier papier" = `addMedium(record, support=numérique, attachment=...)`) |
| `checkout/checkin/cancelCheckout/sign/verifySignature/revokeSignature/approve/reject` | `DocumentController::*` | Opèrent sur un `RecordMedium` (`records/{record}/mediums/{medium}/...`), pas sur la notice |
| `createVersion/versions/downloadVersion` | `DocumentController::upload/versions/downloadVersion` | `createVersion` appelle `Record::createNewVersion()` (nouvelle notice + `record_relations` `version_of`, voir Phase 2) ; `versions` liste `record->getAllVersions()` ; `checkin` devient un raccourci qui enchaîne `createVersion()` + upload du nouveau fichier sur le `RecordMedium` de la nouvelle version |
| `export/import/exportButton/printRecords` | `RecordController::*` (existant) | Généralisés, plus besoin de dispatcher par `type_id` string (`physical_`/`folder_`/`document_`) |

**API** : fusionner `Api\RecordDigitalFolderApiController` + `Api\RecordDigitalDocumentApiController` en `Api\RecordApiController`, même logique de fusion.

**Policy** : `app/Policies/RecordPolicy.php` unique, clés `record_view/record_create/record_update/record_delete/record_force_delete/record_approve` (remplace `records_*` + `digital_folders_*` + `digital_documents_*` — prévoir un seeder de mapping permissions pour ne pas casser les rôles existants : les 3 anciens jeux de permissions peuvent temporairement pointer vers les mêmes gates via un alias en Phase 6, retirés en Phase 7). **Corriger au passage** le bug relevé : `RecordPolicy::update/delete/archive` comparaient `$record->status` (attribut inexistant) à des chaînes littérales — utiliser `$record->status->code` proprement dès la réécriture.

**Services** : fusionner `RecordDigitalFolderService`/`RecordDigitalDocumentService` en `RecordService` ; `DigitalPhysicalTransferService` devient obsolète (remplacé par `addMedium`/`removeMedium` — le "transfert" est juste l'ajout d'un support) ; `MetadataValidationService` reciblé sur `record_type_metadata_profiles`.

**Déclassement / rétention (à repointeur, non mentionné avant cette révision)** : `DeclassementList::eligibleRecordsQuery()` (derrière `LifeCycleController::recordToEliminate`) joint `record_physicals` (`date_format`/`date_end`/`date_exact`) + `activities` + `retentions` + `sorts` — cette requête et tout le module déclassement/réactivation basculent sur `records`/`record_mediums` au même titre que les pivots de Phase 4, et alimentent les colonnes de cycle de vie ajoutées en Phase 2 (`transfer_*`/`deposit_*`/`destruction_*`). Le formulaire de création gère aussi le type "Fonds" (`is_container=true`, champs structurels fonds + profil `record_type_metadata_profiles` dédié) et les axes confidentialité/cycle de vie ajoutés en Phase 2.

Autres points à corriger en passant (déjà cassés avant ce chantier, remontés par l'exploration, à ne pas recopier tels quels dans le nouveau contrôleur) : la relation `attachments()` en `morphMany` sur les anciens modèles digitaux n'a aucune colonne polymorphe en base — ne pas la reporter ; le service `App\Services\AI\QueryExecutorService` requête encore les anciennes tables `records`/`record_author` (pré-renommage) — à corriger pour pointer sur `records`/`record_keyword` unifiés. Même traitement pour toute référence à `record_physicals` restante (audit `Log`, sauvegardes de recherche, rapports) : les centraliser sur `Record::class`.

---

## Phase 6 — Vues

**Cible** : `resources/views/records/{index,create,edit,show,tree}.blade.php` + `resources/views/records/partials/{medium-card,version-history,signature-modal,checkout-badge,metadata-fields}.blade.php`.

- Remplace `resources/views/repositories/folders/*` et `resources/views/repositories/documents/*` (celles réellement utilisées par les contrôleurs actuels).
- **Supprimer** `resources/views/folders/*` et `resources/views/documents/*` — confirmées mortes (aucune route ne les rend) par l'exploration ; ne pas les migrer, juste les supprimer.
- `show.blade.php` : une notice, une liste de "supports" (`@foreach($record->mediums as $medium)`) — chaque carte affiche soit un badge "papier / [emplacement]" avec bouton "voir dans le plan", soit un badge "numérique v{{n}}" avec les actions fichier (télécharger/checkout/signer/versions). C'est la vue qui matérialise concrètement l'unification pour l'utilisateur final.
- Formulaire de création : sélection du `RecordType` en premier (détermine `is_container` → masque/affiche les champs support ; détermine les champs de métadonnées dynamiques via `record_type_metadata_profiles`).
- OPAC (`resources/views/opac/digital/*`) : fusionner avec les vues OPAC physiques existantes si elles existent séparément (à vérifier au moment de l'implémentation) — sinon adapter pour lire `Record`+`mediums` au lieu de `RecordDigitalFolder`/`RecordDigitalDocument`.
- Dolly, Task, Communication, Reservation : mettre à jour les partials qui affichaient trois types de cartes différentes (`dollies/partials/{record,digital_document,digital_folder}.blade.php`) pour n'en garder qu'une (`dollies/partials/record.blade.php`), la table pivot étant désormais unique (Phase 4).

---

## Phase 7 — Cutover et nettoyage

Une fois Phases 1-6 en production et validées (voir vérification ci-dessous) :
1. Supprimer les anciennes tables : `record_physicals` + ses pivots (`record_physical_*`), `record_digital_folders`, `record_digital_documents`, `record_digital_folder_types`, `record_digital_document_types`, `record_digital_folder_metadata_profiles`, `record_digital_document_metadata_profiles`, `dolly_slip_records`, `dolly_digital_folders`, `dolly_digital_documents`, et les 3 tables de pivot thésaurus/mots-clés dupliquées.
2. Supprimer les colonnes de traçabilité (`legacy_source`, `legacy_id` sur `records`, `legacy_id` sur `record_mediums`), les pivots de placement remplacés par `record_mediums.container_id` (`record_physical_container`, `record_containers`) et les colonnes du pont physique↔numérique consommées par la fusion (`transferred_to_record_id`, `linked_digital_metadata`).
3. Supprimer les anciens contrôleurs/policies/services listés en Phase 5, l'ancien modèle `Record.php` orphelin (déjà fait en Phase 2), et `DigitalPhysicalTransferController`.
4. Nettoyer les permissions legacy (`digital_folders_*`, `digital_documents_*`, `records_*`) une fois confirmé qu'aucun rôle ne s'appuie plus dessus.

---

## Points d'attention transverses (relevés pendant l'exploration, à ne pas réintroduire)

- `record_physical_author`/`record_physical_keyword` utilisaient la colonne `record_id` (pas `record_physical_id`, incohérence historique) — la fusion en `record_keyword`/nouvelle table auteurs règle ce point une fois pour toutes.
- Les deux mécanismes d'options parallèles pour les champs de métadonnées (`metadata_definitions.options` JSON *vs* `reference_lists`/`reference_values`) devraient converger vers `reference_lists` uniquement à l'occasion de ce chantier (le JSON `options` perd sa raison d'être une fois que `record_types` est lui-même ancré sur `reference_lists`).
- `Classification` (arbre self-référencé) n'est aujourd'hui utilisé que côté catalogue "livres" (OPAC) — ne pas le confondre avec `record_types` ; les deux peuvent coexister (l'un pour les notices archivistiques, l'autre pour le fonds bibliothèque), pas de fusion nécessaire.
- **Audit** : `ActiviteJournalisee` (db.sql) référence `idDocument`/`idDossier`/`idFichierElectronique`/`idTache`/`idContenant`. shelve a `Log` ; le plan ne crée pas de journal dédié — vérifier en Phase 5 que `Log` couvre bien `records`/`record_mediums` (création, lecture, modification, suppression, qui/quand/IP), sinon l'étendre.
- **Confidentialité** : ne pas confondre `access_level` (string, approximation) et `confidentiality_id` (axe complet). Les deux coexistent sur `records` ; la politique de lecture tranche sur `confidentiality_id` si renseigné.
- **Prêt de notice vs prêt de fichier** : deux mécanismes distincts (notice = colonnes `loan_*` ou Communication ; fichier = checkout sur `record_mediums`). Ne pas fusionner les deux dans le contrôleur.
- **Exemplaires** : `is_principal` sur `record_mediums` ; la fusion "document numérisé" crée deux mediums dont un seul `is_principal=true`.
- **Poly-hiérarchie et permissions par notice** : optionnels (décisions Phase 2/4) — les garder hors du schéma par défaut pour ne pas alourdir, les ajouter via migration si le métier les confirme.

---

## Vérification (par phase)

1. **Phase 1** : `php artisan migrate`, seeder de backfill, vérifier `RecordType::count()` = somme des deux anciennes tables types + les types physiques ajoutés ; `php artisan tinker` : charger un `record_type_metadata_profiles` et confirmer que `MetadataValidationService` (reciblé) produit les mêmes règles qu'avant.
2. **Phase 2/3** : lancer `records:migrate-to-unified` sur une copie de la base de dev ; comparer `RecordPhysical::count() + RecordDigitalFolder::count() + RecordDigitalDocument::where('is_current_version',true)->count()` à `Record::count()` ; vérifier qu'aucune ligne `record_mediums` n'a de `attachment_id` orphelin (`whereDoesntHave('attachment')`).
3. **Phase 4** : pour chaque table repointée, vérifier `whereNull('record_id')` = 0 après backfill avant de dropper l'ancienne colonne.
4. **Phase 5/6** : parcours manuel — créer une notice de type "papier", lui ajouter un support numérique (upload), vérifier qu'elle apparaît une seule fois dans la recherche unifiée avec les deux badges de support ; tester checkout/checkin/signature sur le support numérique ; tester le rattachement à une `Task`, une `Communication`, un `Dolly`.
5. **Phase 7** : `php artisan route:list` ne doit plus référencer `FolderController`/`DocumentController`/les anciennes policies ; grep du code pour `RecordDigitalFolder|RecordDigitalDocument|RecordPhysical` ne doit plus rien trouver hors migrations historiques.
6. **Post-Phases 2-5 (ajouts issus de la révision db.sql)** : vérifier que les colonnes de cycle de vie/confidentialité/prêt sont peuplées depuis l'existant (pas de `NULL` inattendu en masse) ; `DeclassementList::eligibleRecordsQuery()` renvoie les mêmes résultats sur `records` que sur `record_physicals` ; `Record::mediums()->where('is_principal', true)->count() = 1` pour toute notice multi-supports ; les notices de niveau "Fonds" apparaissent dans l'arbre avec leurs métadonnées de profil.

---

## Annexe — État des lieux `tmp/db.sql` (IntelliGID, 150 tables) vs `plan_migration.md`

> Matrice de couverture intégrée à la révision. **S** = supporté par le plan ; **E** = équivalent déjà présent côté shelve (le plan doit le repointeur seulement) ; **O** = optionnel / décision métier ; **X** = hors périmètre (accepté).

| Module IntelliGID (db.sql) | Tables | Statut | Où / décision |
|---|---|---|---|
| Notice + dossier | `FicheDocument`, `FicheDossier` | S | `records` (Phase 2), distinction par `level_id`/`is_container` |
| Support / fichier | `SupportDocument`, `FichierElectronique` | S | `record_mediums` / `attachments` (Phase 3) |
| Types | `TypeDocument`, `TypeDossier`, `TypeSupport` | S | `record_types` / `record_supports` (Phase 1) |
| Référentiels | `DomaineValeurs`, `ElementDomaineValeurs`, `ElementDVHierarchise` | E | `reference_lists`/`reference_values` (existant) |
| Métadonnées | `MetadonneeProfilMO` | S | `record_type_metadata_profiles` + `metadata_definitions.is_system` |
| Valeurs de métadonnées | `ValeurMetadonnee` + 12 tables filles | X | Remplacées par `records.metadata` (JSON) — multi-valué / typé-entité non représentable |
| Relations | 10 × `Relation*FicheDoc` | S | `record_relations` unidirectionnelle (Phase 2) |
| Thésaurus / mots-clés | `Thesaurus`, `SkosConcept*`, `MotsClesThesaurus*`, `MotCle` | E | module thésaurus shelve existant + pivots unifiés (Phase 4) |
| Version | `StatutVersion`, `FichierElectronique.numeroVersionMajeure/Mineure` | S (partiel) | `record_mediums.status` + version = notice (`records`/`record_relations`) ; versionnage sémantique fichier non repris |
| Tâches | `Tache`, `FichesDocumentsTache`, `FichesDossiersTache`, `StatutTache`, `TypeTache` | E | `Task.taskable` polymorphe (existant) |
| Déclassement / réactivation | `ListeDeclassement`, `FicheDossierListeDeclassement`, `ContenantListeDeclassement`, `DemandeValidListeDeclassement`, `ReactivationFicheDossier` | E + O | module shelve existant (Phase 4) + ajouts `support_*`/`declassement_containers` |
| Rétention / calendrier | `Delai`, `RegleConservation`, `CalendrierConservation`, `SubdivisionUniforme`, `TypeDocumentDelai`, `DetenteurPrincipalDelai`, `Exemplaire`, `TypeSupportExemplaire` | E + O | `Retention`/`Sort`/`retention_activity` existants ; exemplaires → `is_principal` sur `record_mediums` |
| Localisation physique | `Emplacement`, `Contenant`, `Boite`, `AdresseEmplacementPhysique`, `MasqueSaisieLocalisation`, `TypeEmplacement`, `TypeContenant` | E + O | hiérarchie `Building/Floor/Room/Shelf/Container` existante ; masques regex non repris |
| Cycle de vie du dossier | dates ouvert/fermé/traitement/transfert/versement/destruction, `ancienNumeroDossier`, `statutArchivistiqueGVAA`, `indisponible`, `statutEssentiel`, `ouvertureAnnuelle` | S | colonnes ajoutées Phase 2 |
| Prêt / emprunt | `dateEmprunt`/`id_emprunteur`/… sur `FicheDocument`, `FicheDossier`, `Contenant` | S | `loan_*` sur `records` (notice) + checkout `record_mediums` (fichier) ; alternative Communication |
| Confidentialité / accès | `StatutConfidentialite`, `LimiteAcces`, `StatutAutoriteEnregistrement` | S | `record_confidentialities` + `access_limit_id` (Phase 2) |
| Fonds / niveau supérieur | `FondsDocumentaire`, `MetadonneeFondsDocumentaire`, `PlanClassification`, `ClassifDomaineObjFicheDossier` | S | fonds = notice "Fonds" (`is_container`), profil dédié ; `activities` couvre ProcessusActivite |
| Correspondance / courrier | `Correspondance`, `TypeCorrespondance`, `StatutCorrespondance`, `TypeAccuseReception`, `ModeExpeditionReception`, `Rappel`, `Contact`, `TypeContact` | E + O | module Mail shelve existant ; inventaire FK à repointeur (Phase 4) ; `Rappel` ≈ `TaskReminder` |
| Portée géo / temporelle | `PorteeGeographique`, `PorteeTemporelle` | S (partiel) | `geographic_scope` JSON ; dates multiples refusées (plage unique actée) |
| Multi-parent | `IdsDossiersParentsDocument`, `idsdossiersparentsdossier`, `idsprocessusparentsdossier`, `idsunitesparentsdossier` | O | pivot `record_parents` si poly-hiérarchie confirmée |
| Permissions granulaires | `PermissionIFGD`, `UtilisateurAutorise`, `UniteAdministrativeAutorisee` | O | `record_permissions` si requis ; sinon `access_level` + policies |
| Groupes / porteurs | `Groupe`, `GroupeUniteAdministrative`, `GroupeUtilisateur`, `UtilisateursPorteursDossier`, `UtilisateurPosteClassement` | O | pivot `record_holders` si besoin |
| Audit | `ActiviteJournalisee` | E + O | `Log` shelve ; vérifier couverture `records`/`record_mediums` (Phase 5) |
| Fichier (parité) | `pdfA`, `voute`, `resourceId`, `ProprietesFichierElectronique`, `travailCollaboratif` | O | non repris par défaut ; extensions `attachments` si demandé |
| Descriptif non-ISAD | `tableMatieres`, `quantite`, `dimension`, `editeur`, `datePublication`, `dateExpedition`, `dateReception`, `dateSignature`, `versionFinaleCreation`, `emplacementAvantAjout`, `valeurTri` | S | colonnes ajoutées Phase 2 |
| Divers | `RechercheSauvegardee`, `ModeleRapport`, `ConfigurationGlobale`, `SequenceIFGD`, `I18NLabel*`, `Jeton*`, `Message`, `Groupe*` | X | hors périmètre (shelve a Setting, `generateCode()`, sa propre i18n) |
