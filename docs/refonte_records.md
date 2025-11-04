Prompt Complet : Refonte du Système de Gestion Documentaire Multi-Types avec Attachments Centralisés

---

## 📊 TABLEAU DE BORD D'IMPLÉMENTATION

### Légende des Statuts
- 🔴 **NON DÉMARRÉ** : Pas encore implémenté
- 🟡 **EN COURS** : Implémentation partielle
- 🟢 **TERMINÉ** : Complètement implémenté et testé
- ✅ **VALIDÉ** : Testé et validé en production

### Vue d'Ensemble

| Composant | Statut | Progression | Responsable | Date Limite |
|-----------|--------|-------------|-------------|-------------|
| **1. Base de Données** | 🔴 | 0% | - | - |
| **2. Modèles Laravel** | 🔴 | 0% | - | - |
| **3. Migrations** | 🔴 | 0% | - | - |
| **4. Services Métier** | 🔴 | 0% | - | - |
| **5. Contrôleurs API** | 🔴 | 0% | - | - |
| **6. Tests Unitaires** | 🔴 | 0% | - | - |
| **7. Migration Données** | 🔴 | 0% | - | - |
| **8. Interface & Menus** | 🔴 | 0% | - | - |
| **9. Documentation** | 🟢 | 100% | - | - |

---

## Contexte du Projet

Je développe une application Laravel de gestion d'archives et de patrimoine documentaire. Actuellement, j'ai une table unique records qui gère tous types de documents de manière générique. Je souhaite la transformer en un système modulaire supportant 6 types de ressources documentaires distinctes, chacune avec ses spécificités métier.

**CONTRAINTE MAJEURE** : Tous les fichiers numériques (pour RecordDigitalFolder et RecordDigitalDocument) doivent être gérés via la table attachments existante, pas de champs file_* dans les tables principales.

---

## 1. Architecture Actuelle | 🟢 DOCUMENTÉ

### État d'implémentation
- [x] Documentation architecture existante
- [x] Table attachments analysée
- [x] Relations identifiées
- [ ] Audit complet de la base de données

### Base de Données

SGBD : MySQL 9.1.0 / MariaDB
Framework : Laravel (migrations standards)
Table principale : records (voir structure complète dans le dump SQL fourni)

Table attachments Existante (À RÉUTILISER)
sqlattachments:
- id
- path (varchar 100) - chemin du fichier
- name (varchar 100) - nom original
- crypt (varchar 255) - nom crypté/hashé
- thumbnail_path (varchar 150)
- size (int) - taille en octets
- crypt_sha512 (varchar 191) - hash de sécurité
- type (enum: mail, record, communication, transferting, bulletinboardpost, bulletinboard, bulletinboardevent)
- mime_type (varchar 191)
- content_text (longtext) - contenu extrait (OCR, parsing)
- creator_id (bigint unsigned)
- created_at, updated_at
```

### Relations Existantes Importantes
```
records
├── record_levels (hiérarchie)
├── record_statuses (états)
├── record_supports (supports physiques)
├── activities (activités métier)
├── authors (producteurs via pivot record_author)
├── containers (via pivot record_container)
├── attachments (via pivot record_attachment)
├── keywords (via pivot record_keyword)
└── thesaurus_concepts (via pivot record_thesaurus_concept)
Champs Actuels de records
sql- id, code, name
- date_format, date_start, date_end, date_exact
- level_id, status_id, support_id, activity_id
- width, width_description
- biographical_history, archival_history
- acquisition_source, content, appraisal
- arrangement, access_conditions
- reproduction_conditions, language_material
- characteristic, finding_aids
- location_original, location_copy
- related_unit, publication_note
- note, archivist_note, rule_convention
- parent_id, user_id
- created_at, updated_at

---

## 2. Objectifs de la Refonte | 🔴 NON DÉMARRÉ

### État d'implémentation
- [x] Spécifications définies
- [ ] Validation architecture
- [ ] Approbation technique
- [ ] Planning établi

### 2.1 Transformation de la Table Records | 🔴 NON DÉMARRÉ

**Checklist d'implémentation** :
- [ ] Migration de renommage créée
- [ ] Tables pivot renommées
- [ ] Foreign keys mises à jour
- [ ] Tests de migration effectués
- [ ] Rollback testé

records → record_physicals

Conserver TOUTE la structure actuelle
Migrer TOUTES les données existantes
Préserver TOUTES les relations (foreign keys)
Type par défaut pour compatibilité ascendante

### 2.2 Extension de la table attachments | 🔴 NON DÉMARRÉ

**Checklist d'implémentation** :
- [ ] Migration créée
- [ ] Nouveaux types ENUM ajoutés
- [ ] Colonnes métadonnées ajoutées
- [ ] Index créés
- [ ] Tests d'intégrité passés

Ajout de nouveaux types dans l'enum type :
sqlALTER TABLE attachments MODIFY COLUMN type ENUM(
    'mail',
    'record',
    'communication',
    'transferting',
    'bulletinboardpost',
    'bulletinboard',
    'bulletinboardevent',
    'digital_folder',      -- NOUVEAU : fichier attaché à un dossier numérique
    'digital_document',    -- NOUVEAU : fichier principal d'un document numérique
    'artifact',            -- NOUVEAU : photo/scan d'objet musée
    'book',                -- NOUVEAU : couverture/extrait de livre
    'periodic'             -- NOUVEAU : couverture/article de périodique
) NOT NULL;
Ajouts optionnels pour métadonnées techniques :
sqlALTER TABLE attachments 
ADD COLUMN ocr_language VARCHAR(10) AFTER content_text,
ADD COLUMN ocr_confidence DECIMAL(5,2) AFTER ocr_language COMMENT 'Score qualité OCR 0-100',
ADD COLUMN file_encoding VARCHAR(50) AFTER mime_type,
ADD COLUMN page_count INTEGER AFTER ocr_confidence COMMENT 'Nombre de pages PDF',
ADD COLUMN word_count INTEGER AFTER page_count COMMENT 'Nombre de mots',
ADD COLUMN file_hash_md5 VARCHAR(32) AFTER crypt_sha512,
ADD COLUMN file_extension VARCHAR(10) AFTER mime_type,
ADD COLUMN is_primary BOOLEAN DEFAULT FALSE AFTER type COMMENT 'Fichier principal/représentatif',
ADD COLUMN display_order INTEGER DEFAULT 0 AFTER is_primary,
ADD COLUMN description TEXT AFTER name COMMENT 'Description du fichier',
ADD INDEX idx_type_primary (type, is_primary),
ADD INDEX idx_file_hash (file_hash_md5),
ADD INDEX idx_extension (file_extension);

### 2.3 Création de 5 Nouveaux Types | 🔴 NON DÉMARRÉ

**Progression globale** :

| Type | Tables BDD | Modèles | Migrations | Services | Tests | Statut |
|------|------------|---------|------------|----------|-------|--------|
| RecordArtifact | 🔴 0% | 🔴 0% | 🔴 0% | 🔴 0% | 🔴 0% | 🔴 NON DÉMARRÉ |
| RecordDigitalFolder | 🔴 0% | 🔴 0% | 🔴 0% | 🔴 0% | 🔴 0% | 🔴 NON DÉMARRÉ |
| RecordDigitalDocument | 🔴 0% | 🔴 0% | 🔴 0% | 🔴 0% | 🔴 0% | 🔴 NON DÉMARRÉ |
| RecordBook | 🔴 0% | 🔴 0% | 🔴 0% | 🔴 0% | 🔴 0% | 🔴 NON DÉMARRÉ |
| RecordPeriodic | 🔴 0% | 🔴 0% | 🔴 0% | 🔴 0% | 🔴 0% | 🔴 NON DÉMARRÉ |

#### A) RecordArtifact (Objets de Musée) | 🔴 NON DÉMARRÉ

**Checklist d'implémentation** :
- [ ] Table `record_artifacts` créée
- [ ] Tables associées créées (exhibitions, loans, condition_reports)
- [ ] Tables pivot créées
- [ ] Modèle RecordArtifact implémenté
- [ ] Relations configurées
- [ ] Service ArtifactService créé
- [ ] Contrôleur API créé
- [ ] Tests unitaires écrits
- [ ] Documentation API complète

Spécificités métier :

Objets patrimoniaux tridimensionnels
Conservation muséale
Photos multiples via attachments avec type='artifact'

Champs spécifiques :
sqlrecord_artifacts:
- id (PK)
- code (unique, format : ART-YYYY-NNNN)
- name VARCHAR(250)
- inventory_number VARCHAR(50) UNIQUE
- object_type ENUM('sculpture', 'painting', 'furniture', 'tool', 'clothing', 'ceramic', 'jewelry', 'weapon', 'coin', 'archaeological', 'ethnographic', 'natural_history', 'other')
- creation_date_start VARCHAR(10)
- creation_date_end VARCHAR(10)
- creation_date_exact DATE
- creator_name VARCHAR(200) -- artisan/artiste
- materials JSON -- ["bronze", "wood", "textile"]
- techniques JSON -- ["moulage", "sculpture", "tissage"]
- height DECIMAL(8,2) -- en cm
- width DECIMAL(8,2)
- depth DECIMAL(8,2)
- weight DECIMAL(10,2) -- en grammes
- diameter DECIMAL(8,2) -- pour objets circulaires
- conservation_state ENUM('excellent', 'good', 'fair', 'poor', 'critical', 'restored')
- conservation_note TEXT
- restoration_history TEXT
- origin_place VARCHAR(200)
- acquisition_date DATE
- acquisition_method ENUM('purchase', 'donation', 'excavation', 'transfer', 'bequest', 'exchange', 'unknown')
- acquisition_source VARCHAR(200)
- acquisition_price DECIMAL(12,2)
- current_location_id BIGINT UNSIGNED -- FK → rooms
- exhibition_status ENUM('exhibited', 'storage', 'on_loan', 'in_restoration', 'unavailable', 'missing')
- insurance_value DECIMAL(12,2)
- insurance_date DATE
- cultural_period VARCHAR(100) -- "Néolithique", "Renaissance"
- geographical_origin VARCHAR(200)
- provenance TEXT -- historique de propriété
- description TEXT
- historical_note TEXT
- iconography TEXT -- description iconographique
- inscriptions TEXT -- inscriptions, marques
- marks TEXT -- poinçons, signatures
- bibliographical_references TEXT
- exhibition_history TEXT
- is_fragile BOOLEAN DEFAULT FALSE
- handling_instructions TEXT
- status_id BIGINT UNSIGNED (FK → record_statuses)
- activity_id BIGINT UNSIGNED (FK → activities)
- user_id BIGINT UNSIGNED (FK → users)
- organisation_id BIGINT UNSIGNED (FK → organisations)
- created_by BIGINT UNSIGNED (FK → users)
- updated_by BIGINT UNSIGNED (FK → users)
- created_at, updated_at
- deleted_at (soft delete)
Relations spécifiques :
sql-- Photos via attachments (type='artifact')
-- Utiliser record_artifact_attachment pivot avec des métadonnées supplémentaires

artifact_attachments (pivot enrichi):
- id
- artifact_id (FK → record_artifacts)
- attachment_id (FK → attachments)
- view_type ENUM('main', 'front', 'back', 'side', 'top', 'bottom', 'detail', 'xray', '3d_model', 'other')
- is_main_image BOOLEAN DEFAULT FALSE
- caption TEXT
- photographer VARCHAR(200)
- photo_date DATE
- display_order INTEGER
- created_at, updated_at

artifact_exhibitions:
- id
- artifact_id (FK)
- exhibition_name VARCHAR(300)
- location VARCHAR(200)
- start_date DATE
- end_date DATE
- role ENUM('exhibited', 'reproduced', 'mentioned')
- catalog_number VARCHAR(50)
- notes TEXT
- created_at, updated_at

artifact_loans:
- id
- artifact_id (FK)
- borrower_institution VARCHAR(300)
- contact_person VARCHAR(200)
- loan_purpose TEXT
- loan_start_date DATE
- loan_end_date DATE
- actual_return_date DATE
- condition_on_loan TEXT
- condition_on_return TEXT
- insurance_value DECIMAL(12,2)
- status ENUM('requested', 'approved', 'active', 'returned', 'overdue', 'cancelled')
- created_at, updated_at

artifact_condition_reports:
- id
- artifact_id (FK)
- report_date DATE
- examined_by VARCHAR(200)
- condition_state ENUM('excellent', 'good', 'fair', 'poor', 'critical')
- condition_description TEXT
- issues_identified TEXT
- recommendations TEXT
- next_examination_date DATE
- created_at, updated_at
Relations communes : authors, keywords, thesaurus_concepts

#### B) RecordDigitalFolder (Dossiers Numériques) | 🔴 NON DÉMARRÉ

**Checklist d'implémentation** :
- [ ] Table `record_digital_folders` créée
- [ ] Gestion hiérarchie parent/enfant
- [ ] Modèle RecordDigitalFolder implémenté
- [ ] Trait HasMetadata appliqué
- [ ] Service FolderService créé
- [ ] Contrôleur API créé
- [ ] Tests arborescence
- [ ] Validation profondeur max (10 niveaux)
- [ ] Prévention cycles

Spécificités métier :

Structure hiérarchique arborescente
TOUJOURS parent de RecordDigitalDocument
Classification logique des documents numériques
AUCUN fichier direct, documents via children RecordDigitalDocument
Peut avoir des attachments supplémentaires (icône personnalisée, miniature) via attachments type='digital_folder'

Champs spécifiques :
sqlrecord_digital_folders:
- id (PK)
- code (unique, format : DF-YYYY-NNNN)
- name VARCHAR(250)
- description TEXT
- parent_id BIGINT UNSIGNED (FK → record_digital_folders, nullable pour racine)
- path VARCHAR(500) -- chemin complet "/parent/enfant/petit-enfant"
- depth INTEGER -- niveau profondeur (0 = racine)
- children_count INTEGER DEFAULT 0 -- nb documents directs
- total_size BIGINT DEFAULT 0 -- taille totale des docs enfants (octets)
- folder_type ENUM('administrative', 'project', 'client', 'thematic', 'chronological', 'archive', 'custom')
- color VARCHAR(7) -- code couleur hexa pour UI
- icon VARCHAR(50) -- classe d'icône FontAwesome/autre
- access_level ENUM('public', 'internal', 'restricted', 'confidential', 'secret')
- access_password VARCHAR(255) -- hashé, pour folders protégés
- is_locked BOOLEAN DEFAULT FALSE -- verrouillage modification structure
- locked_by BIGINT UNSIGNED (FK → users)
- locked_at TIMESTAMP
- is_archived BOOLEAN DEFAULT FALSE
- archived_at TIMESTAMP
- archived_by BIGINT UNSIGNED (FK → users)
- order_criteria VARCHAR(50) DEFAULT 'name' -- tri enfants : name, date, size, custom
- display_mode ENUM('list', 'grid', 'timeline', 'tree') DEFAULT 'list'
- metadata_template_id BIGINT UNSIGNED (FK → metadata_templates)
- status ENUM('active', 'archived', 'deleted') DEFAULT 'active'
- activity_id BIGINT UNSIGNED (FK → activities)
- organisation_id BIGINT UNSIGNED (FK → organisations)
- created_by BIGINT UNSIGNED (FK → users)
- updated_by BIGINT UNSIGNED (FK → users)
- created_at, updated_at
- deleted_at (soft delete)

INDEX idx_parent (parent_id)
INDEX idx_path (path)
INDEX idx_depth (depth)
INDEX idx_organisation_status (organisation_id, status)
INDEX idx_type (folder_type)
Relations spécifiques :

record_digital_documents (relation parent-enfant stricte)
metadata_values (métadonnées personnalisées)
attachments via pivot avec type='digital_folder' (icônes, miniatures)
Relations communes : authors, keywords, thesaurus_concepts

Contraintes :

Un folder peut avoir N documents enfants
Un folder peut avoir N sous-folders
Profondeur maximale : 10 niveaux (validation applicative)
Empêcher cycles : validation parent_id ≠ id et vérification récursive


#### C) RecordDigitalDocument (Documents Numériques) | 🔴 NON DÉMARRÉ

**Checklist d'implémentation** :
- [ ] Table `record_digital_documents` créée
- [ ] Contrainte parent_folder_id NOT NULL
- [ ] Tables associées (versions, access_log, shares)
- [ ] Modèle RecordDigitalDocument implémenté
- [ ] Système de versioning fonctionnel
- [ ] Gestion checkout/checkin
- [ ] Service DocumentService créé
- [ ] API endpoints créés
- [ ] Tests versioning complets

Spécificités métier :

TOUJOURS enfant d'un RecordDigitalFolder (parent_folder_id NOT NULL)
UN SEUL fichier principal obligatoire via attachments avec type='digital_document' et is_primary=true
Fichiers annexes possibles via attachments avec is_primary=false
Versioning : anciennes versions sont aussi des attachments liés

Champs spécifiques :
sqlrecord_digital_documents:
- id (PK)
- code (unique, format : DD-YYYY-NNNN)
- parent_folder_id BIGINT UNSIGNED NOT NULL (FK → record_digital_folders)
- name VARCHAR(250)
- description TEXT
-- PLUS DE CHAMPS file_* : tout via attachments
- document_type ENUM('pdf', 'word', 'excel', 'powerpoint', 'image', 'video', 'audio', 'archive', 'email', 'web', 'cad', 'other')
- document_date DATE -- date du document lui-même
- document_author VARCHAR(200) -- auteur du document (≠ producteur archives)
- version_number INTEGER DEFAULT 1
- is_current_version BOOLEAN DEFAULT TRUE
- version_parent_id BIGINT UNSIGNED (FK → record_digital_documents) -- NULL pour v1
- version_note TEXT
- access_level ENUM('public', 'internal', 'restricted', 'confidential', 'secret')
- is_signed BOOLEAN DEFAULT FALSE -- signature électronique
- signature_date TIMESTAMP
- signature_certificate TEXT
- signature_algorithm VARCHAR(50)
- is_encrypted BOOLEAN DEFAULT FALSE
- encryption_algorithm VARCHAR(50)
- is_locked BOOLEAN DEFAULT FALSE -- verrouillage édition
- locked_by BIGINT UNSIGNED (FK → users)
- locked_at TIMESTAMP
- checkout_by BIGINT UNSIGNED (FK → users) -- qui a emprunté le fichier
- checkout_at TIMESTAMP
- download_count INTEGER DEFAULT 0
- view_count INTEGER DEFAULT 0
- last_accessed_at TIMESTAMP
- metadata_template_id BIGINT UNSIGNED (FK → metadata_templates)
- status ENUM('draft', 'review', 'validated', 'published', 'archived', 'deleted') DEFAULT 'draft'
- activity_id BIGINT UNSIGNED (FK → activities)
- organisation_id BIGINT UNSIGNED (FK → organisations)
- created_by BIGINT UNSIGNED (FK → users)
- updated_by BIGINT UNSIGNED (FK → users)
- created_at, updated_at
- deleted_at (soft delete)

INDEX idx_parent (parent_folder_id)
INDEX idx_version (version_parent_id, version_number)
INDEX idx_current (is_current_version)
INDEX idx_type (document_type)
INDEX idx_organisation_status (organisation_id, status)
CONSTRAINT chk_not_own_version CHECK (version_parent_id IS NULL OR version_parent_id != id)
Relations spécifiques :
sql-- Fichier principal et annexes via table pivot enrichie
digital_document_attachments:
- id
- document_id (FK → record_digital_documents)
- attachment_id (FK → attachments) -- type='digital_document'
- attachment_role ENUM('primary', 'annex', 'version', 'thumbnail', 'preview', 'signature', 'certificate')
- is_primary BOOLEAN DEFAULT FALSE -- UNE SEULE primary par document
- version_number INTEGER -- pour tracking versions
- display_order INTEGER
- title VARCHAR(200) -- titre de l'annexe
- description TEXT
- created_at, updated_at

UNIQUE KEY unique_primary (document_id) WHERE is_primary = TRUE -- SQL moderne

document_versions_history:
- id
- document_id (FK → record_digital_documents)
- version_number INTEGER
- attachment_id (FK → attachments) -- fichier de cette version
- created_by BIGINT UNSIGNED (FK → users)
- version_note TEXT
- file_size BIGINT
- file_hash VARCHAR(64)
- created_at

document_access_log:
- id
- document_id (FK)
- user_id (FK)
- action ENUM('view', 'download', 'edit', 'delete', 'share', 'print')
- ip_address VARCHAR(45)
- user_agent TEXT
- created_at

document_shares:
- id
- document_id (FK)
- shared_by BIGINT UNSIGNED (FK → users)
- shared_with_user_id BIGINT UNSIGNED (FK → users, nullable)
- shared_with_email VARCHAR(200) -- pour externes
- share_token VARCHAR(100) UNIQUE
- access_type ENUM('view', 'download', 'edit')
- expires_at TIMESTAMP
- password_hash VARCHAR(255) -- protection optionnelle
- download_limit INTEGER -- nb téléchargements max
- download_count INTEGER DEFAULT 0
- is_active BOOLEAN DEFAULT TRUE
- created_at, updated_at
Relations communes :

metadata_values (métadonnées personnalisées)
authors, keywords, thesaurus_concepts

Contraintes :

parent_folder_id NOT NULL
Un seul attachment avec is_primary=true par document
Un seul document avec is_current_version=true par chaîne de versions
Validation : document ne peut être son propre parent de version


#### D) RecordBook (Livres) | 🔴 NON DÉMARRÉ

**Checklist d'implémentation** :
- [ ] Table `record_books` créée
- [ ] Tables associées (book_authors, book_copies, book_loans, book_reservations)
- [ ] Modèle RecordBook implémenté
- [ ] Service BookService créé
- [ ] Gestion prêts/retours
- [ ] Système réservations
- [ ] Contrôleur API créé
- [ ] Tests gestion bibliothèque

Spécificités métier :

Gestion bibliothécaire complète
Exemplaires multiples du même livre
Couverture et extraits via attachments type='book'

Champs spécifiques :
sqlrecord_books:
- id (PK)
- code (unique, format : BK-YYYY-NNNN)
- isbn VARCHAR(17) -- format ISBN-13
- isbn_10 VARCHAR(13)
- ean VARCHAR(13)
- title VARCHAR(500)
- subtitle VARCHAR(500)
- original_title VARCHAR(500)
- edition VARCHAR(100)
- edition_number INTEGER
- volume_number INTEGER
- volume_total INTEGER
- publisher VARCHAR(200)
- publication_place VARCHAR(200)
- publication_country VARCHAR(100)
- publication_year INTEGER
- publication_date DATE
- first_publication_year INTEGER
- page_count INTEGER
- illustration_count INTEGER
- language VARCHAR(10) -- code ISO 639-1
- original_language VARCHAR(10)
- translation_languages JSON -- ["en", "de"]
- book_format ENUM('hardcover', 'paperback', 'pocket', 'large_print', 'leather', 'spiral', 'ebook', 'audiobook', 'other')
- dimensions_height DECIMAL(6,2) -- cm
- dimensions_width DECIMAL(6,2)
- dimensions_thickness DECIMAL(6,2)
- weight DECIMAL(8,2) -- grammes
- binding_type VARCHAR(50)
- dewey_decimal VARCHAR(20)
- library_congress VARCHAR(50)
- udc VARCHAR(50) -- Classification Décimale Universelle
- rameau TEXT -- vedettes RAMEAU (JSON array)
- subject_headings JSON -- ["Histoire", "Archéologie"]
- genre JSON -- ["Roman", "Essai"]
- abstract TEXT
- table_of_contents TEXT
- back_cover TEXT
- series_title VARCHAR(200)
- series_number INTEGER
- series_total INTEGER
- translator VARCHAR(200)
- illustrator VARCHAR(200)
- photographer VARCHAR(200)
- editor_literary VARCHAR(200) -- éditeur scientifique
- preface_by VARCHAR(200)
- introduction_by VARCHAR(200)
- collection_name VARCHAR(200)
- collection_number INTEGER
- print_run INTEGER -- tirage
- printing_number INTEGER -- numéro de réimpression
- copyright_year INTEGER
- copyright_holder VARCHAR(200)
- legal_deposit VARCHAR(50)
- acquisition_date DATE
- acquisition_price DECIMAL(10,2)
- acquisition_currency VARCHAR(3) DEFAULT 'EUR'
- acquisition_method ENUM('purchase', 'donation', 'exchange', 'legal_deposit', 'bequest', 'transfer')
- supplier VARCHAR(200)
- invoice_number VARCHAR(50)
- condition_on_acquisition ENUM('new', 'excellent', 'good', 'fair', 'poor', 'damaged')
- current_condition ENUM('excellent', 'good', 'fair', 'poor', 'damaged', 'missing', 'lost')
- condition_note TEXT
- restoration_history TEXT
- special_features TEXT -- ex: "édition numérotée", "dédicace"
- dedication TEXT -- dédicace si présente
- provenance TEXT -- ex-libris, cachets
- value_estimate DECIMAL(10,2)
- value_date DATE
- is_rare BOOLEAN DEFAULT FALSE
- is_first_edition BOOLEAN DEFAULT FALSE
- is_signed BOOLEAN DEFAULT FALSE
- is_reference BOOLEAN DEFAULT FALSE -- ouvrage de référence
- is_restricted BOOLEAN DEFAULT FALSE -- accès restreint
- loan_allowed BOOLEAN DEFAULT TRUE
- reservation_allowed BOOLEAN DEFAULT TRUE
- max_loan_days INTEGER DEFAULT 21
- renewal_allowed BOOLEAN DEFAULT TRUE
- max_renewals INTEGER DEFAULT 2
- notes TEXT
- internal_notes TEXT
- cataloging_date DATE
- cataloger_id BIGINT UNSIGNED (FK → users)
- last_inventory_date DATE
- status ENUM('available', 'on_loan', 'reserved', 'processing', 'repair', 'missing', 'lost', 'withdrawn', 'on_order') DEFAULT 'available'
- activity_id BIGINT UNSIGNED (FK → activities)
- organisation_id BIGINT UNSIGNED (FK → organisations)
- created_by BIGINT UNSIGNED (FK → users)
- updated_by BIGINT UNSIGNED (FK → users)
- created_at, updated_at
- deleted_at (soft delete)

UNIQUE KEY unique_isbn (isbn) WHERE isbn IS NOT NULL
INDEX idx_title (title)
INDEX idx_publisher (publisher)
INDEX idx_publication_year (publication_year)
INDEX idx_dewey (dewey_decimal)
INDEX idx_status (status)
Tables associées :
sqlbook_authors (many-to-many):
- id
- book_id (FK → record_books)
- author_id (FK → authors)
- author_role ENUM('author', 'co_author', 'editor', 'translator', 'illustrator', 'photographer', 'preface', 'introduction', 'contributor')
- author_order INTEGER
- created_at, updated_at

UNIQUE KEY unique_book_author_role (book_id, author_id, author_role)

book_copies (exemplaires physiques):
- id
- book_id (FK → record_books)
- copy_number INTEGER
- barcode VARCHAR(50) UNIQUE
- rfid_tag VARCHAR(50) UNIQUE
- acquisition_date DATE
- acquisition_price DECIMAL(10,2)
- condition ENUM('excellent', 'good', 'fair', 'poor', 'damaged', 'missing')
- condition_note TEXT
- location_type ENUM('shelf', 'storage', 'display', 'repair', 'missing')
- room_id BIGINT UNSIGNED (FK → rooms)
- shelf_id BIGINT UNSIGNED (FK → shelves)
- call_number VARCHAR(100) -- cote
- notes TEXT
- status ENUM('available', 'on_loan', 'reserved', 'damaged', 'missing', 'lost', 'withdrawn') DEFAULT 'available'
- created_at, updated_at

INDEX idx_barcode (barcode)
INDEX idx_status (status)
INDEX idx_location (room_id, shelf_id)

book_loans:
- id
- copy_id (FK → book_copies)
- user_id (FK → users)
- loan_date DATE
- due_date DATE
- return_date DATE
- renewal_count INTEGER DEFAULT 0
- is_overdue BOOLEAN DEFAULT FALSE
- overdue_days INTEGER
- fine_amount DECIMAL(8,2) DEFAULT 0
- fine_paid BOOLEAN DEFAULT FALSE
- loan_type ENUM('standard', 'short', 'extended', 'interlibrary')
- notes TEXT
- status ENUM('active', 'returned', 'overdue', 'lost', 'renewed', 'cancelled') DEFAULT 'active'
- loaned_by BIGINT UNSIGNED (FK → users)
- returned_to BIGINT UNSIGNED (FK → users)
- created_at, updated_at

INDEX idx_user_status (user_id, status)
INDEX idx_due_date (due_date)
INDEX idx_overdue (is_overdue)

book_reservations:
- id
- book_id (FK → record_books)
- user_id (FK → users)
- reservation_date TIMESTAMP
- expiry_date TIMESTAMP
- notification_sent BOOLEAN DEFAULT FALSE
- pickup_date DATE
- status ENUM('pending', 'available', 'picked_up', 'expired', 'cancelled') DEFAULT 'pending'
- queue_position INTEGER
- created_at, updated_at

INDEX idx_user_status (user_id, status)
INDEX idx_book_status (book_id, status)
Relations via attachments :
sqlbook_attachments (pivot):
- id
- book_id (FK)
- attachment_id (FK → attachments) -- type='book'
- attachment_type ENUM('cover_front', 'cover_back', 'spine', 'dust_jacket', 'title_page', 'excerpt', 'review', 'author_photo', 'illustration', 'other')
- is_main_cover BOOLEAN DEFAULT FALSE
- display_order INTEGER
- caption TEXT
- created_at, updated_at
Relations communes : keywords, thesaurus_concepts

#### E) RecordPeriodic (Publications en Série) | 🔴 NON DÉMARRÉ

**Checklist d'implémentation** :
- [ ] Table `record_periodics` créée
- [ ] Tables associées (issues, articles, article_authors, indexes, claims, subscriptions_history)
- [ ] Modèle RecordPeriodic implémenté
- [ ] Gestion numéros/fascicules
- [ ] Gestion articles
- [ ] Service PeriodicService créé
- [ ] Système réclamations
- [ ] Tests complets

Spécificités métier :

Revues, journaux, bulletins périodiques
Numérotation séquentielle
Couvertures et articles via attachments type='periodic'

Champs spécifiques :
sqlrecord_periodics:
- id (PK)
- code (unique, format : PER-YYYY-NNNN)
- issn VARCHAR(9) -- format XXXX-XXXX
- issn_online VARCHAR(9) -- e-ISSN
- issn_l VARCHAR(9) -- ISSN de liaison
- title VARCHAR(500)
- subtitle VARCHAR(500)
- short_title VARCHAR(200) -- titre abrégé
- former_title VARCHAR(500) -- ancien titre si changement
- original_title VARCHAR(500)
- parallel_title VARCHAR(500) -- titre dans autre langue
- publisher VARCHAR(200)
- publication_place VARCHAR(200)
- publication_country VARCHAR(100)
- frequency ENUM('daily', 'twice_weekly', 'weekly', 'biweekly', 'semimonthly', 'monthly', 'bimonthly', 'quarterly', 'triannual', 'semiannual', 'annual', 'biennial', 'irregular', 'ceased')
- frequency_detail VARCHAR(100) -- ex: "10 numéros par an"
- language VARCHAR(10)
- additional_languages JSON
- subject_area VARCHAR(200)
- subject_classification JSON -- ["History", "Archaeology"]
- description TEXT
- scope_note TEXT
- editorial_board TEXT
- first_year INTEGER
- first_volume INTEGER
- first_issue VARCHAR(20)
- last_year INTEGER -- si cessé
- last_volume INTEGER
- last_issue VARCHAR(20)
- is_active BOOLEAN DEFAULT TRUE
- cessation_date DATE
- cessation_reason TEXT
- website_url VARCHAR(500)
- online_archive_url VARCHAR(500)
- doi_prefix VARCHAR(50)
- publisher_country VARCHAR(100)
- publisher_type ENUM('commercial', 'university', 'society', 'institution', 'government', 'independent', 'other')
- editorial_model ENUM('peer_reviewed', 'editorial_review', 'open_submission', 'invited', 'mixed')
- peer_review_type ENUM('single_blind', 'double_blind', 'open', 'post_publication', 'none')
- open_access_type ENUM('full', 'hybrid', 'delayed', 'none')
- license VARCHAR(100) -- ex: "CC BY 4.0"
- apc_amount DECIMAL(10,2) -- frais de publication
- apc_currency VARCHAR(3)
- indexing_databases JSON -- ["Scopus", "Web of Science", "PubMed"]
- impact_factor DECIMAL(6,3)
- impact_factor_year INTEGER
- h_index INTEGER
- subscription_status ENUM('active', 'expired', 'cancelled', 'suspended', 'trial', 'none') DEFAULT 'none'
- subscription_type ENUM('print', 'online', 'print_online', 'open_access')
- subscription_start_date DATE
- subscription_end_date DATE
- subscription_cost_annual DECIMAL(10,2)
- subscription_currency VARCHAR(3) DEFAULT 'EUR'
- subscription_agent VARCHAR(200) -- agence d'abonnement
- subscription_ref VARCHAR(100)
- supplier VARCHAR(200)
- invoice_frequency ENUM('monthly', 'quarterly', 'annual')
- access_type ENUM('print_only', 'online_only', 'print_and_online', 'open_access')
- online_access_url VARCHAR(500)
- online_platform VARCHAR(100)
- access_username VARCHAR(100)
- access_password VARCHAR(255) -- encrypté
- ip_access BOOLEAN DEFAULT FALSE
- ip_range TEXT
- simultaneous_users INTEGER
- holdings_start_year INTEGER -- début collection détenue
- holdings_start_volume INTEGER
- holdings_note TEXT
- gaps TEXT -- lacunes dans collection
- retention_policy TEXT
- binding_schedule TEXT
- notes TEXT
- internal_notes TEXT
- cataloging_date DATE
- cataloger_id BIGINT UNSIGNED (FK → users)
- last_inventory_date DATE
- activity_id BIGINT UNSIGNED (FK → activities)
- organisation_id BIGINT UNSIGNED (FK → organisations)
- created_by BIGINT UNSIGNED (FK → users)
- updated_by BIGINT UNSIGNED (FK → users)
- created_at, updated_at
- deleted_at (soft delete)

UNIQUE KEY unique_issn (issn) WHERE issn IS NOT NULL
INDEX idx_title (title)
INDEX idx_publisher (publisher)
INDEX idx_active (is_active)
INDEX idx_subscription (subscription_status)
Tables associées :
sqlperiodic_issues (numéros/fascicules):
- id
- periodic_id (FK → record_periodics)
- volume_number INTEGER
- issue_number VARCHAR(20) -- peut être "1-2" pour double numéro
- issue_part VARCHAR(10) -- ex: "A", "RéessayerOYContinuersqlperiodic_issues (suite):
- issue_part VARCHAR(10) -- ex: "A", "B" pour numéros en plusieurs parties
- year INTEGER
- month INTEGER
- day INTEGER
- season ENUM('spring', 'summer', 'fall', 'winter')
- publication_date DATE
- cover_date DATE -- date affichée sur couverture
- date_received DATE
- numbering_display VARCHAR(100) -- ex: "Vol. 45, n° 3-4"
- page_count INTEGER
- page_start INTEGER -- pagination continue
- page_end INTEGER
- special_issue BOOLEAN DEFAULT FALSE
- special_issue_title VARCHAR(500)
- special_issue_editors TEXT
- supplement BOOLEAN DEFAULT FALSE
- supplement_title VARCHAR(500)
- supplement_to_issue VARCHAR(50) -- numéro principal
- combined_issue BOOLEAN DEFAULT FALSE -- numéro double/triple
- combined_with VARCHAR(100) -- "issues 3-4"
- guest_editors TEXT
- theme VARCHAR(300) -- thème du numéro
- doi VARCHAR(100)
- handle VARCHAR(200) -- Handle persistent identifier
- url VARCHAR(500)
- condition ENUM('excellent', 'good', 'fair', 'poor', 'damaged', 'missing') DEFAULT 'good'
- condition_note TEXT
- location_type ENUM('shelf', 'binder', 'box', 'storage', 'display', 'binding', 'missing')
- room_id BIGINT UNSIGNED (FK → rooms)
- shelf_id BIGINT UNSIGNED (FK → shelves)
- container_id BIGINT UNSIGNED (FK → containers)
- call_number VARCHAR(100)
- barcode VARCHAR(50) UNIQUE
- is_bound BOOLEAN DEFAULT FALSE
- bound_with TEXT -- autres numéros reliés ensemble
- binding_date DATE
- binding_cost DECIMAL(8,2)
- notes TEXT
- status ENUM('expected', 'received', 'claimed', 'available', 'on_loan', 'binding', 'damaged', 'missing', 'cancelled') DEFAULT 'expected'
- created_at, updated_at
- deleted_at (soft delete)

UNIQUE KEY unique_issue (periodic_id, volume_number, issue_number, year) WHERE deleted_at IS NULL
INDEX idx_periodic_year (periodic_id, year)
INDEX idx_publication_date (publication_date)
INDEX idx_status (status)
INDEX idx_location (room_id, shelf_id)

periodic_articles (articles):
- id
- issue_id (FK → periodic_issues)
- article_type ENUM('research', 'review', 'meta_analysis', 'case_study', 'editorial', 'letter', 'commentary', 'book_review', 'short_communication', 'correspondence', 'erratum', 'retraction', 'news', 'interview', 'other')
- title VARCHAR(500)
- subtitle VARCHAR(500)
- original_title VARCHAR(500)
- translated_title VARCHAR(500)
- author_names TEXT -- noms séparés par ";"
- corresponding_author VARCHAR(200)
- author_affiliations TEXT
- page_start INTEGER
- page_end INTEGER
- article_number VARCHAR(50) -- pour revues sans pagination
- sequence_number INTEGER -- ordre dans le numéro
- language VARCHAR(10)
- abstract TEXT
- abstract_translated TEXT
- keywords_article JSON
- mesh_terms JSON -- Medical Subject Headings
- doi VARCHAR(100)
- pmid VARCHAR(20) -- PubMed ID
- pmc_id VARCHAR(20) -- PubMed Central ID
- arxiv_id VARCHAR(50)
- url VARCHAR(500)
- pdf_url VARCHAR(500)
- supplementary_material_url VARCHAR(500)
- license VARCHAR(100)
- open_access BOOLEAN DEFAULT FALSE
- funding_info TEXT
- conflict_of_interest TEXT
- acknowledgments TEXT
- references_count INTEGER
- figures_count INTEGER
- tables_count INTEGER
- citation_count INTEGER
- citation_last_updated DATE
- received_date DATE
- revised_date DATE
- accepted_date DATE
- published_online_date DATE
- notes TEXT
- created_at, updated_at

INDEX idx_issue (issue_id)
INDEX idx_doi (doi)
INDEX idx_pmid (pmid)
INDEX idx_type (article_type)
FULLTEXT idx_title_abstract (title, abstract)

periodic_article_authors (relation many-to-many détaillée):
- id
- article_id (FK → periodic_articles)
- author_id (FK → authors)
- author_order INTEGER
- corresponding BOOLEAN DEFAULT FALSE
- affiliation VARCHAR(300)
- orcid VARCHAR(19) -- format: 0000-0000-0000-0000
- email VARCHAR(200)
- created_at, updated_at

UNIQUE KEY unique_article_author (article_id, author_id)

periodic_indexes (index annuels):
- id
- periodic_id (FK → record_periodics)
- year INTEGER
- volume_number INTEGER
- index_type ENUM('author', 'subject', 'title', 'combined', 'reviewer', 'advertiser')
- description TEXT
- page_count INTEGER
- notes TEXT
- created_at, updated_at

INDEX idx_periodic_year (periodic_id, year)

periodic_claims (réclamations numéros manquants):
- id
- periodic_id (FK → record_periodics)
- expected_issue_id BIGINT UNSIGNED (FK → periodic_issues)
- claim_date DATE
- claim_type ENUM('not_received', 'damaged', 'incomplete')
- supplier_notified BOOLEAN DEFAULT FALSE
- supplier_response TEXT
- replacement_sent BOOLEAN DEFAULT FALSE
- replacement_date DATE
- resolution ENUM('received', 'cancelled', 'credited', 'pending')
- resolution_date DATE
- notes TEXT
- created_at, updated_at

INDEX idx_periodic (periodic_id)
INDEX idx_claim_date (claim_date)
INDEX idx_resolution (resolution)

periodic_subscriptions_history (historique abonnements):
- id
- periodic_id (FK → record_periodics)
- subscription_type ENUM('print', 'online', 'print_online', 'trial')
- start_date DATE
- end_date DATE
- cost DECIMAL(10,2)
- currency VARCHAR(3)
- supplier VARCHAR(200)
- purchase_order VARCHAR(100)
- invoice_number VARCHAR(100)
- payment_date DATE
- renewal_sent BOOLEAN DEFAULT FALSE
- renewal_date DATE
- cancellation_date DATE
- cancellation_reason TEXT
- notes TEXT
- created_by BIGINT UNSIGNED (FK → users)
- created_at, updated_at

INDEX idx_periodic_dates (periodic_id, start_date, end_date)
Relations via attachments :
sqlperiodic_attachments (pivot):
- id
- periodic_id BIGINT UNSIGNED (FK → record_periodics, nullable)
- issue_id BIGINT UNSIGNED (FK → periodic_issues, nullable)
- article_id BIGINT UNSIGNED (FK → periodic_articles, nullable)
- attachment_id BIGINT UNSIGNED (FK → attachments) -- type='periodic'
- attachment_type ENUM(
    'periodic_logo',
    'issue_cover_front', 
    'issue_cover_back',
    'issue_spine',
    'issue_toc', -- table des matières
    'issue_supplement',
    'article_pdf',
    'article_supplement',
    'article_figure',
    'article_table',
    'index_file',
    'other'
)
- is_main BOOLEAN DEFAULT FALSE
- page_number INTEGER -- pour figures/tables
- figure_number VARCHAR(20)
- table_number VARCHAR(20)
- caption TEXT
- display_order INTEGER
- created_at, updated_at

INDEX idx_periodic (periodic_id)
INDEX idx_issue (issue_id)
INDEX idx_article (article_id)
INDEX idx_type (attachment_type)
CONSTRAINT chk_one_parent CHECK (
    (periodic_id IS NOT NULL AND issue_id IS NULL AND article_id IS NULL) OR
    (periodic_id IS NULL AND issue_id IS NOT NULL AND article_id IS NULL) OR
    (periodic_id IS NULL AND issue_id IS NULL AND article_id IS NOT NULL)
)
Relations communes : keywords, thesaurus_concepts

---

## 3. Système de Métadonnées Personnalisées | 🔴 NON DÉMARRÉ

### État d'implémentation

| Composant | Statut | Note |
|-----------|--------|------|
| Table metadata_definitions | 🔴 NON DÉMARRÉ | - |
| Table metadata_values | 🔴 NON DÉMARRÉ | - |
| Table metadata_templates | 🔴 NON DÉMARRÉ | - |
| Table metadata_template_definitions | 🔴 NON DÉMARRÉ | - |
| Trait HasMetadata | 🔴 NON DÉMARRÉ | - |
| Service MetadataService | 🔴 NON DÉMARRÉ | - |
| Validation des métadonnées | 🔴 NON DÉMARRÉ | - |

**Checklist générale** :
- [ ] Toutes les tables créées
- [ ] Trait HasMetadata implémenté
- [ ] Service MetadataService créé
- [ ] Validation rules fonctionnelles
- [ ] Interface admin templates
- [ ] Tests complets

EXCLUSIVEMENT pour RecordDigitalFolder et RecordDigitalDocument

### 3.1 Table metadata_definitions | 🔴 NON DÉMARRÉ
sqlCREATE TABLE metadata_definitions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE COMMENT 'Code technique unique (ex: CONTRACT_NUMBER)',
    name VARCHAR(100) NOT NULL COMMENT 'Nom technique du champ',
    label VARCHAR(200) NOT NULL COMMENT 'Libellé affiché utilisateur',
    description TEXT COMMENT 'Description/aide contextuelle',
    
    -- Applicable à quel type d'entité
    entity_type ENUM('folder', 'document', 'both') NOT NULL DEFAULT 'both',
    
    -- Type de données
    field_type ENUM(
        'text',           -- Texte court (input)
        'textarea',       -- Texte long
        'number',         -- Nombre entier ou décimal
        'date',           -- Date
        'datetime',       -- Date et heure
        'time',           -- Heure seule
        'boolean',        -- Oui/Non (checkbox)
        'select',         -- Liste déroulante simple choix
        'multiselect',    -- Liste déroulante multi-choix
        'radio',          -- Boutons radio
        'checkbox_group', -- Groupe de cases à cocher
        'file',           -- Fichier joint (via attachments)
        'url',            -- URL
        'email',          -- Email
        'phone',          -- Téléphone
        'color',          -- Sélecteur couleur
        'range',          -- Slider numérique
        'rating',         -- Notation étoiles
        'json',           -- Données JSON structurées
        'wysiwyg',        -- Éditeur riche HTML
        'markdown',       -- Éditeur Markdown
        'code',           -- Éditeur de code avec coloration
        'location'        -- Coordonnées GPS
    ) NOT NULL,
    
    -- Configuration du champ
    is_required BOOLEAN DEFAULT FALSE,
    is_unique BOOLEAN DEFAULT FALSE COMMENT 'Valeur unique dans organisation',
    is_searchable BOOLEAN DEFAULT TRUE,
    is_filterable BOOLEAN DEFAULT FALSE COMMENT 'Filtre dans recherches',
    is_sortable BOOLEAN DEFAULT FALSE,
    show_in_list BOOLEAN DEFAULT FALSE COMMENT 'Colonne dans listes',
    show_in_detail BOOLEAN DEFAULT TRUE COMMENT 'Vue détaillée',
    show_in_card BOOLEAN DEFAULT FALSE COMMENT 'Carte/vignette',
    is_encrypted BOOLEAN DEFAULT FALSE COMMENT 'Chiffrer valeur en base',
    
    -- Options pour listes déroulantes
    options JSON COMMENT '{
        "choices": [
            {"value": "val1", "label": "Label 1", "color": "#FF0000"},
            {"value": "val2", "label": "Label 2", "icon": "check"}
        ],
        "allow_custom": false,
        "multiple_separator": ";",
        "cascading_field": "parent_field_code"
    }',
    
    -- Règles de validation
    validation_rules JSON COMMENT '{
        "min": 0,
        "max": 100,
        "step": 0.1,
        "regex": "^[A-Z]{3}-\\d{4}$",
        "min_length": 5,
        "max_length": 255,
        "allowed_extensions": ["pdf", "docx", "xlsx"],
        "max_file_size": 5242880,
        "allowed_mime_types": ["application/pdf"],
        "min_date": "2000-01-01",
        "max_date": "today",
        "url_protocols": ["http", "https"],
        "email_domains": ["company.com"],
        "decimal_places": 2,
        "positive_only": true,
        "unique_in_folder": true
    }',
    
    -- Valeur par défaut
    default_value TEXT COMMENT 'Valeur ou expression (ex: {{current_date}})',
    
    -- Formule calculée
    is_calculated BOOLEAN DEFAULT FALSE,
    calculation_formula TEXT COMMENT 'Formule : {{field1}} + {{field2}} * 1.2',
    
    -- Affichage
    display_order INTEGER DEFAULT 0,
    display_group VARCHAR(100) COMMENT 'Groupe (onglet, section)',
    display_width ENUM('full', 'half', 'third', 'quarter') DEFAULT 'full',
    placeholder VARCHAR(255) COMMENT 'Texte placeholder',
    help_text TEXT COMMENT 'Bulle d''aide',
    prefix VARCHAR(50) COMMENT 'Préfixe (€, #)',
    suffix VARCHAR(50) COMMENT 'Suffixe (kg, %)',
    icon VARCHAR(50) COMMENT 'Icône FontAwesome',
    css_class VARCHAR(100) COMMENT 'Classes CSS personnalisées',
    
    -- Dépendances conditionnelles
    conditional_logic JSON COMMENT '{
        "show_if": {
            "field": "type_document",
            "operator": "equals",
            "value": "contrat"
        }
    }',
    
    -- Comportement
    is_system BOOLEAN DEFAULT FALSE COMMENT 'Champ système non supprimable',
    is_active BOOLEAN DEFAULT TRUE,
    is_deprecated BOOLEAN DEFAULT FALSE,
    deprecated_message TEXT,
    replacement_field_code VARCHAR(50),
    
    -- Portée
    organisation_id BIGINT UNSIGNED COMMENT 'NULL = global',
    applies_to_activities JSON COMMENT '[1, 5, 12] - IDs activités',
    
    -- Audit
    created_by BIGINT UNSIGNED,
    updated_by BIGINT UNSIGNED,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_code (code),
    INDEX idx_entity_type (entity_type),
    INDEX idx_field_type (field_type),
    INDEX idx_organisation (organisation_id),
    INDEX idx_active (is_active),
    INDEX idx_display (display_group, display_order),
    INDEX idx_searchable (is_searchable),
    INDEX idx_filterable (is_filterable)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
3.2 Table metadata_values
sqlCREATE TABLE metadata_values (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    metadata_definition_id BIGINT UNSIGNED NOT NULL,
    
    -- Relation polymorphique
    record_id BIGINT UNSIGNED NOT NULL COMMENT 'ID folder ou document',
    record_type ENUM('folder', 'document') NOT NULL,
    
    -- Valeurs typées (une seule remplie selon field_type)
    value_text TEXT COMMENT 'text, textarea, url, email, phone, code, markdown, wysiwyg',
    value_number DECIMAL(20,6) COMMENT 'number, range',
    value_integer BIGINT COMMENT 'entiers, rating',
    value_date DATE COMMENT 'date',
    value_datetime DATETIME COMMENT 'datetime',
    value_time TIME COMMENT 'time',
    value_boolean BOOLEAN COMMENT 'boolean',
    value_json JSON COMMENT 'select multiple, checkbox_group, json, location',
    value_encrypted TEXT COMMENT 'Valeur chiffrée si is_encrypted=true',
    
    -- Pour files : référence vers attachments
    attachment_id BIGINT UNSIGNED COMMENT 'FK vers attachments',
    
    -- Métadonnées de la valeur
    source ENUM('user', 'calculated', 'imported', 'api', 'default') DEFAULT 'user',
    confidence_score DECIMAL(5,2) COMMENT 'Score confiance 0-100 (extraction auto)',
    validated_at TIMESTAMP COMMENT 'Date validation manuelle',
    validated_by BIGINT UNSIGNED,
    
    -- Audit
    created_by BIGINT UNSIGNED,
    updated_by BIGINT UNSIGNED,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (metadata_definition_id) REFERENCES metadata_definitions(id) ON DELETE CASCADE,
    FOREIGN KEY (attachment_id) REFERENCES attachments(id) ON DELETE SET NULL,
    FOREIGN KEY (validated_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    
    UNIQUE KEY unique_metadata_value (metadata_definition_id, record_id, record_type),
    INDEX idx_record (record_type, record_id),
    INDEX idx_definition (metadata_definition_id),
    INDEX idx_source (source),
    INDEX idx_validated (validated_at),
    
    -- Index pour recherche full-text
    FULLTEXT idx_text_search (value_text)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
3.3 Table metadata_templates
sqlCREATE TABLE metadata_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    entity_type ENUM('folder', 'document', 'both') NOT NULL,
    
    -- Apparence
    icon VARCHAR(50) COMMENT 'Classe icône',
    color VARCHAR(7) COMMENT 'Couleur hexa',
    thumbnail_path VARCHAR(500) COMMENT 'Miniature template',
    
    -- Configuration
    category VARCHAR(100) COMMENT 'Catégorie : Contrats, Factures, RH...',
    keywords JSON COMMENT '["contrat", "juridique"]',
    
    -- Pré-remplissage et automatisation
    preset_values JSON COMMENT '{
        "field_code": "default_value",
        "document_type": "contract"
    }',
    auto_generate_code BOOLEAN DEFAULT FALSE COMMENT 'Générer code automatiquement',
    code_pattern VARCHAR(100) COMMENT 'Pattern : {{YEAR}}-{{ORG}}-{{SEQ}}',
    auto_extract_metadata BOOLEAN DEFAULT FALSE COMMENT 'Extraction auto IA',
    extraction_config JSON COMMENT 'Config extraction IA',
    
    -- Workflow
    default_access_level ENUM('public', 'internal', 'restricted', 'confidential'),
    require_validation BOOLEAN DEFAULT FALSE,
    validation_workflow_id BIGINT UNSIGNED COMMENT 'FK vers workflows si module existe',
    notification_rules JSON COMMENT 'Règles de notification',
    
    -- Portée
    organisation_id BIGINT UNSIGNED,
    is_public BOOLEAN DEFAULT FALSE COMMENT 'Partagé entre organisations',
    usage_count INTEGER DEFAULT 0 COMMENT 'Nb utilisations',
    
    -- État
    is_system BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    is_featured BOOLEAN DEFAULT FALSE COMMENT 'Template mis en avant',
    
    -- Audit
    created_by BIGINT UNSIGNED,
    updated_by BIGINT UNSIGNED,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (organisation_id) REFERENCES organisations(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_code (code),
    INDEX idx_entity_type (entity_type),
    INDEX idx_category (category),
    INDEX idx_active (is_active),
    INDEX idx_featured (is_featured),
    INDEX idx_organisation (organisation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
3.4 Table metadata_template_definitions
sqlCREATE TABLE metadata_template_definitions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    template_id BIGINT UNSIGNED NOT NULL,
    metadata_definition_id BIGINT UNSIGNED NOT NULL,
    
    -- Surcharge des propriétés de la définition
    display_order INTEGER DEFAULT 0,
    display_group VARCHAR(100) COMMENT 'Groupe spécifique au template',
    is_required_in_template BOOLEAN DEFAULT FALSE COMMENT 'Obligatoire pour ce template',
    is_readonly_in_template BOOLEAN DEFAULT FALSE,
    default_value_override TEXT COMMENT 'Valeur par défaut spécifique',
    help_text_override TEXT,
    
    -- Visibilité conditionnelle dans template
    conditional_logic_override JSON,
    
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (template_id) REFERENCES metadata_templates(id) ON DELETE CASCADE,
    FOREIGN KEY (metadata_definition_id) REFERENCES metadata_definitions(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_template_definition (template_id, metadata_definition_id),
    INDEX idx_template_order (template_id, display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
3.5 Ajout colonne metadata_template_id aux tables digitales
sql-- Déjà dans les définitions de record_digital_folders et record_digital_documents
-- metadata_template_id BIGINT UNSIGNED (FK → metadata_templates)
3.6 Table metadata_validation_rules (optionnelle avancée)
sqlCREATE TABLE metadata_validation_rules (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    metadata_definition_id BIGINT UNSIGNED NOT NULL,
    rule_type ENUM('regex', 'length', 'range', 'custom_function', 'cross_field', 'external_api') NOT NULL,
    rule_config JSON NOT NULL COMMENT '{
        "regex": "pattern",
        "min": 5,
        "max": 100,
        "function": "validateLuhn",
        "depends_on": ["field1", "field2"],
        "error_message": "Message personnalisé"
    }',
    error_message TEXT,
    severity ENUM('error', 'warning', 'info') DEFAULT 'error',
    is_active BOOLEAN DEFAULT TRUE,
    execution_order INTEGER DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    
    FOREIGN KEY (metadata_definition_id) REFERENCES metadata_definitions(id) ON DELETE CASCADE,
    INDEX idx_definition (metadata_definition_id, execution_order)
) ENGINE=InnoDB;

---

## 4. Architecture des Modèles Laravel | 🔴 NON DÉMARRÉ

### État d'implémentation

| Composant | Statut | Tests | Documentation |
|-----------|--------|-------|---------------|
| Trait HasRecordBase | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| Trait HasAttachments | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| Trait HasMetadata | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| RecordPhysical | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| RecordArtifact | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| RecordDigitalFolder | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| RecordDigitalDocument | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| RecordBook | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| RecordPeriodic | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |

**Checklist générale** :
- [ ] Tous les traits créés et testés
- [ ] Tous les modèles implémentés
- [ ] Relations configurées
- [ ] Scopes utiles ajoutés
- [ ] Accesseurs/Mutateurs définis
- [ ] Tests unitaires écrits

### 4.1 Trait Commun : HasRecordBase | 🔴 NON DÉMARRÉ

**Checklist** :
- [ ] Trait créé dans `App\Traits`
- [ ] Méthode generateCode() implémentée
- [ ] Relations communes configurées
- [ ] Scopes de base ajoutés
- [ ] Tests unitaires

```php

namespace App\Traits;

use App\Models\Activity;
use App\Models\Author;
use App\Models\Keyword;
use App\Models\ThesaurusConcept;
use App\Models\Attachment;
use App\Models\User;
use Illuminate\Support\Str;

trait HasRecordBase
{
    /**
     * Boot trait
     */
    protected static function bootHasRecordBase()
    {
        static::creating(function ($model) {
            if (empty($model->code)) {
                $model->code = $model->generateCode();
            }
        });
    }
    
    /**
     * Relations communes
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
    
    public function authors()
    {
        $pivotTable = $this->getPivotTableName('author');
        return $this->belongsToMany(Author::class, $pivotTable)
            ->withTimestamps();
    }
    
    public function keywords()
    {
        $pivotTable = $this->getPivotTableName('keyword');
        return $this->belongsToMany(Keyword::class, $pivotTable)
            ->withTimestamps();
    }
    
    public function thesaurusConcepts()
    {
        $pivotTable = $this->getPivotTableName('thesaurus_concept');
        return $this->belongsToMany(ThesaurusConcept::class, $pivotTable)
            ->withPivot(['weight', 'context', 'extraction_note'])
            ->withTimestamps();
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
    
    /**
     * Helper pour noms de tables pivot
     */
    protected function getPivotTableName(string $relation): string
    {
        // record_physical → physical_author
        $tableBaseName = str_replace('record_', '', $this->getTable());
        $tableBaseName = Str::singular($tableBaseName);
        return $tableBaseName . '_' . $relation;
    }
    
    /**
     * Génération automatique du code
     */
    public function generateCode(): string
    {
        $prefix = $this->getCodePrefix();
        $year = date('Y');
        
        // Trouver le dernier numéro de séquence pour cette année
        $lastRecord = static::where('code', 'LIKE', "{$prefix}-{$year}-%")
            ->orderBy('code', 'DESC')
            ->first();
        
        if ($lastRecord) {
            $lastNumber = (int) substr($lastRecord->code, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return sprintf('%s-%s-%04d', $prefix, $year, $newNumber);
    }
    
    /**
     * Préfixe du code selon le type
     */
    abstract protected function getCodePrefix(): string;
    
    /**
     * Scope : recherche globale
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('code', 'LIKE', "%{$term}%")
              ->orWhere('name', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%");
        });
    }
    
    /**
     * Scope : par organisation
     */
    public function scopeForOrganisation($query, int $organisationId)
    {
        return $query->where('organisation_id', $organisationId);
    }
    
    /**
     * Accesseur : nom complet avec code
     */
    public function getFullNameAttribute(): string
    {
        return "[{$this->code}] {$this->name}";
    }
}
```

### 4.2 Trait : HasAttachments | 🔴 NON DÉMARRÉ

**Checklist** :
- [ ] Trait créé dans `App\Traits`
- [ ] Méthode addAttachment() implémentée
- [ ] Méthode removeAttachment() implémentée
- [ ] getAttachmentsByType() ajoutée
- [ ] Tests unitaires complets

```php
<?php

namespace App\Traits;

use App\Models\Attachment;

```trait HasAttachments
{
    /**
     * Relation polymorphique avec attachments
     * Via table pivot dédiée pour chaque type
     */
    public function attachments()
    {
        $pivotTable = $this->getAttachmentPivotTable();
        
        return $this->belongsToMany(Attachment::class, $pivotTable)
            ->withPivot($this->getAttachmentPivotColumns())
            ->withTimestamps()
            ->orderBy("{$pivotTable}.display_order");
    }
    
    /**
     * Fichier principal (pour types avec fichier unique)
     */
    public function primaryAttachment()
    {
        return $this->attachments()
            ->wherePivot('is_primary', true)
            ->first();
    }
    
    /**
     * Fichiers secondaires/annexes
     */
    public function secondaryAttachments()
    {
        return $this->attachments()
            ->wherePivot('is_primary', false);
    }
    
    /**
     * Attacher un fichier
     */
    public function attachFile(
        Attachment $attachment,
        array $pivotData = []
    ): void {
        $this->attachments()->attach($attachment->id, array_merge([
            'display_order' => $this->attachments()->count() + 1,
            'is_primary' => false,
        ], $pivotData));
    }
    
    /**
     * Détacher un fichier
     */
    public function detachFile(int $attachmentId): void
    {
        $this->attachments()->detach($attachmentId);
    }
    
    /**
     * Définir fichier principal
     */
    public function setPrimaryAttachment(int $attachmentId): void
    {
        // Retirer primary des autres
        $this->attachments()->updateExistingPivot(
            $this->attachments()->pluck('id'),
            ['is_primary' => false]
        );
        
        // Définir le nouveau primary
        $this->attachments()->updateExistingPivot($attachmentId, [
            'is_primary' => true
        ]);
    }
    
    /**
     * Nom de la table pivot (à surcharger si besoin)
     */
    abstract protected function getAttachmentPivotTable(): string;
    
    /**
     * Colonnes pivot supplémentaires
     */
    protected function getAttachmentPivotColumns(): array
    {
        return ['is_primary', 'display_order'];
    }
}
4.3 Trait : HasMetadata
php<?php

namespace App\Traits;

use App\Models\MetadataDefinition;
use App\Models\MetadataValue;
use App\Models\MetadataTemplate;
use Illuminate\Support\Collection;

trait HasMetadata
{
    /**
     * Relation avec valeurs de métadonnées
     */
    public function metadataValues()
    {
        return $this->morphMany(
            MetadataValue::class,
            'record',
            'record_type',
            'record_id'
        )->with('definition');
    }
    
    /**
     * Template de métadonnées
     */
    public function metadataTemplate()
    {
        return $this->belongsTo(MetadataTemplate::class, 'metadata_template_id');
    }
    
    /**
     * Récupère une valeur de métadonnée par code
     */
    public function getMetadata(string $code)
    {
        $value = $this->metadataValues()
            ->whereHas('definition', function($q) use ($code) {
                $q->where('code', $code);
            })
            ->first();
            
        return $value ? $value->getValue() : null;
    }
    
    /**
     * Définit une valeur de métadonnée
     */
    public function setMetadata(string $code, $value, array $options = []): void
    {
        $definition = MetadataDefinition::where('code', $code)
            ->where(function($q) {
                $q->whereNull('organisation_id')
                  ->orWhere('organisation_id', $this->organisation_id);
            })
            ->firstOrFail();
        
        // Valider que la définition s'applique à ce type
        $RéessayerCette réponse a été interrompue car Claude a atteint sa longueur maximale de message. Cliquez sur continuer pour permettre à Claude de poursuivre.Continuer
        Continuer

php
        // Valider que la définition s'applique à ce type
        $recordType = $this->getMetadataRecordType();
        if (!in_array($definition->entity_type, [$recordType, 'both'])) {
            throw new \InvalidArgumentException(
                "Metadata definition '{$code}' does not apply to {$recordType}"
            );
        }
        
        $metadataValue = $this->metadataValues()
            ->where('metadata_definition_id', $definition->id)
            ->first();
            
        if (!$metadataValue) {
            $metadataValue = new MetadataValue([
                'metadata_definition_id' => $definition->id,
                'record_type' => $recordType,
                'record_id' => $this->id,
                'source' => $options['source'] ?? 'user',
            ]);
        }
        
        $metadataValue->setValue($value);
        
        if (isset($options['validated_by'])) {
            $metadataValue->validated_by = $options['validated_by'];
            $metadataValue->validated_at = now();
        }
        
        if (isset($options['confidence_score'])) {
            $metadataValue->confidence_score = $options['confidence_score'];
        }
        
        $metadataValue->save();
    }
    
    /**
     * Définit plusieurs métadonnées en masse
     */
    public function setMetadataBulk(array $metadata, array $options = []): void
    {
        foreach ($metadata as $code => $value) {
            $this->setMetadata($code, $value, $options);
        }
    }
    
    /**
     * Récupère toutes les métadonnées formatées
     */
    public function getAllMetadata(bool $onlyFilled = false): array
    {
        $values = $this->metadataValues()
            ->with('definition')
            ->get()
            ->filter(function($value) use ($onlyFilled) {
                return !$onlyFilled || !$value->isEmpty();
            })
            ->mapWithKeys(function($value) {
                return [$value->definition->code => [
                    'label' => $value->definition->label,
                    'value' => $value->getValue(),
                    'display_value' => $value->getDisplayValue(),
                    'field_type' => $value->definition->field_type,
                    'is_validated' => !is_null($value->validated_at),
                    'confidence_score' => $value->confidence_score,
                ]];
            })
            ->toArray();
            
        return $values;
    }
    
    /**
     * Récupère métadonnées groupées par section
     */
    public function getMetadataGrouped(): array
    {
        $values = $this->metadataValues()
            ->with('definition')
            ->get();
            
        $grouped = $values
            ->groupBy(fn($v) => $v->definition->display_group ?? 'General')
            ->map(function($group) {
                return $group->mapWithKeys(function($value) {
                    return [$value->definition->code => [
                        'label' => $value->definition->label,
                        'value' => $value->getValue(),
                        'display_value' => $value->getDisplayValue(),
                        'display_order' => $value->definition->display_order,
                    ]];
                })->sortBy('display_order')->toArray();
            })
            ->toArray();
            
        return $grouped;
    }
    
    /**
     * Appliquer un template de métadonnées
     */
    public function applyMetadataTemplate(int $templateId): void
    {
        $template = MetadataTemplate::with('definitions')->findOrFail($templateId);
        
        $this->metadata_template_id = $templateId;
        $this->save();
        
        // Appliquer valeurs par défaut du template
        if ($template->preset_values) {
            foreach ($template->preset_values as $code => $value) {
                if (!$this->getMetadata($code)) {
                    $this->setMetadata($code, $value, ['source' => 'default']);
                }
            }
        }
    }
    
    /**
     * Valider toutes les métadonnées selon leurs règles
     */
    public function validateMetadata(): array
    {
        $errors = [];
        
        $definitions = $this->getApplicableDefinitions();
        
        foreach ($definitions as $definition) {
            $value = $this->getMetadata($definition->code);
            
            // Vérifier champ requis
            if ($definition->is_required && empty($value)) {
                $errors[$definition->code][] = "{$definition->label} est requis";
                continue;
            }
            
            if (!empty($value)) {
                // Valider selon rules
                $validationErrors = $this->validateMetadataValue($definition, $value);
                if (!empty($validationErrors)) {
                    $errors[$definition->code] = $validationErrors;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Valider une valeur de métadonnée
     */
    protected function validateMetadataValue(MetadataDefinition $definition, $value): array
    {
        $errors = [];
        $rules = $definition->validation_rules ?? [];
        
        // Validation selon field_type
        switch ($definition->field_type) {
            case 'text':
            case 'textarea':
                if (isset($rules['min_length']) && strlen($value) < $rules['min_length']) {
                    $errors[] = "Longueur minimale : {$rules['min_length']} caractères";
                }
                if (isset($rules['max_length']) && strlen($value) > $rules['max_length']) {
                    $errors[] = "Longueur maximale : {$rules['max_length']} caractères";
                }
                if (isset($rules['regex']) && !preg_match($rules['regex'], $value)) {
                    $errors[] = "Format invalide";
                }
                break;
                
            case 'number':
                if (!is_numeric($value)) {
                    $errors[] = "Doit être un nombre";
                } else {
                    if (isset($rules['min']) && $value < $rules['min']) {
                        $errors[] = "Valeur minimale : {$rules['min']}";
                    }
                    if (isset($rules['max']) && $value > $rules['max']) {
                        $errors[] = "Valeur maximale : {$rules['max']}";
                    }
                }
                break;
                
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Email invalide";
                }
                break;
                
            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    $errors[] = "URL invalide";
                }
                break;
                
            case 'date':
                if (!strtotime($value)) {
                    $errors[] = "Date invalide";
                }
                break;
        }
        
        return $errors;
    }
    
    /**
     * Obtenir définitions applicables
     */
    protected function getApplicableDefinitions(): Collection
    {
        $recordType = $this->getMetadataRecordType();
        
        $query = MetadataDefinition::where('is_active', true)
            ->where(function($q) use ($recordType) {
                $q->where('entity_type', $recordType)
                  ->orWhere('entity_type', 'both');
            })
            ->where(function($q) {
                $q->whereNull('organisation_id')
                  ->orWhere('organisation_id', $this->organisation_id);
            });
            
        // Si template appliqué, filtrer par template
        if ($this->metadata_template_id) {
            $query->whereHas('templates', function($q) {
                $q->where('metadata_templates.id', $this->metadata_template_id);
            });
        }
        
        return $query->orderBy('display_order')->get();
    }
    
    /**
     * Type pour la relation polymorphique
     */
    abstract protected function getMetadataRecordType(): string;
    
    /**
     * Exporter métadonnées en JSON
     */
    public function exportMetadataJson(): string
    {
        return json_encode([
            'template' => $this->metadataTemplate?->code,
            'metadata' => $this->getAllMetadata(true),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Importer métadonnées depuis JSON
     */
    public function importMetadataJson(string $json): void
    {
        $data = json_decode($json, true);
        
        if (isset($data['template'])) {
            $template = MetadataTemplate::where('code', $data['template'])->first();
            if ($template) {
                $this->applyMetadataTemplate($template->id);
            }
        }
        
        if (isset($data['metadata'])) {
            foreach ($data['metadata'] as $code => $metaData) {
                $this->setMetadata($code, $metaData['value'], ['source' => 'imported']);
            }
        }
    }
}
4.4 Modèle : RecordPhysical
php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasRecordBase;
use App\Traits\HasAttachments;

class RecordPhysical extends Model
{
    use HasRecordBase, HasAttachments, SoftDeletes;
    
    protected $table = 'record_physicals';
    
    protected $fillable = [
        'code', 'name', 'date_format', 'date_start', 'date_end', 'date_exact',
        'level_id', 'status_id', 'support_id', 'activity_id',
        'width', 'width_description', 'biographical_history',
        'archival_history', 'acquisition_source', 'content',
        'appraisal', 'accrual', 'arrangement', 'access_conditions',
        'reproduction_conditions', 'language_material', 'characteristic',
        'finding_aids', 'location_original', 'location_copy',
        'related_unit', 'publication_note', 'note', 'archivist_note',
        'rule_convention', 'parent_id', 'user_id', 'organisation_id',
        'created_by', 'updated_by'
    ];
    
    protected $casts = [
        'date_exact' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Relations spécifiques
     */
    public function level()
    {
        return $this->belongsTo(RecordLevel::class, 'level_id');
    }
    
    public function status()
    {
        return $this->belongsTo(RecordStatus::class, 'status_id');
    }
    
    public function support()
    {
        return $this->belongsTo(RecordSupport::class, 'support_id');
    }
    
    public function parent()
    {
        return $this->belongsTo(RecordPhysical::class, 'parent_id');
    }
    
    public function children()
    {
        return $this->hasMany(RecordPhysical::class, 'parent_id');
    }
    
    public function containers()
    {
        return $this->belongsToMany(Container::class, 'record_container')
            ->withPivot(['description', 'creator_id'])
            ->withTimestamps();
    }
    
    public function communications()
    {
        return $this->belongsToMany(Communication::class, 'communication_record')
            ->withPivot(['content', 'is_original', 'return_date', 'return_effective', 'operator_id'])
            ->withTimestamps();
    }
    
    /**
     * Implémentation HasRecordBase
     */
    protected function getCodePrefix(): string
    {
        return 'RPH';
    }
    
    /**
     * Implémentation HasAttachments
     */
    protected function getAttachmentPivotTable(): string
    {
        return 'record_attachment';
    }
    
    /**
     * Arborescence complète
     */
    public function getAncestors()
    {
        $ancestors = collect();
        $current = $this->parent;
        
        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }
        
        return $ancestors->reverse();
    }
    
    public function getDescendants()
    {
        $descendants = collect();
        
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getDescendants());
        }
        
        return $descendants;
    }
}
4.5 Modèle : RecordArtifact
php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasRecordBase;
use App\Traits\HasAttachments;

class RecordArtifact extends Model
{
    use HasRecordBase, HasAttachments, SoftDeletes;
    
    protected $table = 'record_artifacts';
    
    protected $fillable = [
        'code', 'name', 'inventory_number', 'object_type',
        'creation_date_start', 'creation_date_end', 'creation_date_exact',
        'creator_name', 'materials', 'techniques',
        'height', 'width', 'depth', 'weight', 'diameter',
        'conservation_state', 'conservation_note', 'restoration_history',
        'origin_place', 'acquisition_date', 'acquisition_method',
        'acquisition_source', 'acquisition_price',
        'current_location_id', 'exhibition_status',
        'insurance_value', 'insurance_date',
        'cultural_period', 'geographical_origin', 'provenance',
        'description', 'historical_note', 'iconography',
        'inscriptions', 'marks', 'bibliographical_references',
        'exhibition_history', 'is_fragile', 'handling_instructions',
        'status_id', 'activity_id', 'user_id', 'organisation_id',
        'created_by', 'updated_by'
    ];
    
    protected $casts = [
        'materials' => 'array',
        'techniques' => 'array',
        'height' => 'decimal:2',
        'width' => 'decimal:2',
        'depth' => 'decimal:2',
        'weight' => 'decimal:2',
        'diameter' => 'decimal:2',
        'acquisition_price' => 'decimal:2',
        'insurance_value' => 'decimal:2',
        'creation_date_exact' => 'date',
        'acquisition_date' => 'date',
        'insurance_date' => 'date',
        'is_fragile' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Relations spécifiques
     */
    public function currentLocation()
    {
        return $this->belongsTo(Room::class, 'current_location_id');
    }
    
    public function exhibitions()
    {
        return $this->hasMany(ArtifactExhibition::class, 'artifact_id');
    }
    
    public function loans()
    {
        return $this->hasMany(ArtifactLoan::class, 'artifact_id');
    }
    
    public function conditionReports()
    {
        return $this->hasMany(ArtifactConditionReport::class, 'artifact_id')
            ->orderBy('report_date', 'desc');
    }
    
    public function photos()
    {
        return $this->belongsToMany(Attachment::class, 'artifact_attachments')
            ->wherePivot('attachment_type', '!=', '3d_model')
            ->withPivot(['view_type', 'is_main_image', 'caption', 'photographer', 'photo_date', 'display_order'])
            ->orderByPivot('display_order');
    }
    
    public function mainPhoto()
    {
        return $this->photos()->wherePivot('is_main_image', true)->first();
    }
    
    public function model3D()
    {
        return $this->belongsToMany(Attachment::class, 'artifact_attachments')
            ->wherePivot('attachment_type', '3d_model')
            ->first();
    }
    
    /**
     * Implémentation HasRecordBase
     */
    protected function getCodePrefix(): string
    {
        return 'ART';
    }
    
    /**
     * Implémentation HasAttachments
     */
    protected function getAttachmentPivotTable(): string
    {
        return 'artifact_attachments';
    }
    
    protected function getAttachmentPivotColumns(): array
    {
        return ['view_type', 'is_main_image', 'caption', 'photographer', 'photo_date', 'display_order'];
    }
    
    /**
     * Scopes
     */
    public function scopeOnDisplay($query)
    {
        return $query->where('exhibition_status', 'exhibited');
    }
    
    public function scopeInStorage($query)
    {
        return $query->where('exhibition_status', 'storage');
    }
    
    public function scopeByPeriod($query, string $period)
    {
        return $query->where('cultural_period', 'LIKE', "%{$period}%");
    }
    
    /**
     * Accesseurs
     */
    public function getDimensionsAttribute(): string
    {
        $dims = [];
        if ($this->height) $dims[] = "H: {$this->height}cm";
        if ($this->width) $dims[] = "L: {$this->width}cm";
        if ($this->depth) $dims[] = "P: {$this->depth}cm";
        if ($this->diameter) $dims[] = "Ø: {$this->diameter}cm";
        
        return implode(' × ', $dims);
    }
    
    public function getWeightFormattedAttribute(): string
    {
        if (!$this->weight) return '';
        
        if ($this->weight >= 1000) {
            return number_format($this->weight / 1000, 2) . ' kg';
        }
        return number_format($this->weight, 0) . ' g';
    }
    
    /**
     * Vérifier si prêt actif
     */
    public function hasActiveLoan(): bool
    {
        return $this->loans()->where('status', 'active')->exists();
    }
    
    /**
     * Dernier rapport de condition
     */
    public function latestConditionReport()
    {
        return $this->conditionReports()->first();
    }
}
4.6 Modèle : RecordDigitalFolder
php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasRecordBase;
use App\Traits\HasMetadata;
use App\Traits\HasAttachments;

class RecordDigitalFolder extends Model
{
    use HasRecordBase, HasMetadata, HasAttachments, SoftDeletes;
    
    protected $table = 'record_digital_folders';
    
    protected $fillable = [
        'code', 'name', 'description', 'parent_id', 'path', 'depth',
        'children_count', 'total_size', 'folder_type', 'color', 'icon',
        'access_level', 'access_password', 'is_locked', 'locked_by', 'locked_at',
        'is_archived', 'archived_at', 'archived_by',
        'order_criteria', 'display_mode', 'metadata_template_id',
        'status', 'activity_id', 'organisation_id',
        'created_by', 'updated_by'
    ];
    
    protected $casts = [
        'depth' => 'integer',
        'children_count' => 'integer',
        'total_size' => 'integer',
        'is_locked' => 'boolean',
        'is_archived' => 'boolean',
        'locked_at' => 'datetime',
        'archived_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    protected $hidden = [
        'access_password'
    ];
    
    /**
     * Boot
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($folder) {
            $folder->updatePath();
            $folder->updateDepth();
        });
        
        static::saved(function ($folder) {
            $folder->updateParentCounts();
        });
        
        static::deleting(function ($folder) {
            if ($folder->documents()->exists()) {
                throw new \Exception("Cannot delete folder with documents. Move or delete documents first.");
            }
            if ($folder->children()->exists()) {
                throw new \Exception("Cannot delete folder with subfolders. Delete subfolders first.");
            }
        });
    }
    
    /**
     * Relations
     */
    public function parent()
    {
        return $this->belongsTo(RecordDigitalFolder::class, 'parent_id');
    }
    
    public function children()
    {
        return $this->hasMany(RecordDigitalFolder::class, 'parent_id')
            ->orderBy('name');
    }
    
    public function documents()
    {
        return $this->hasMany(RecordDigitalDocument::class, 'parent_folder_id')
            ->where('is_current_version', true);
    }
    
    public function allDocuments()
    {
        return $this->hasMany(RecordDigitalDocument::class, 'parent_folder_id');
    }
    
    public function lockedByUser()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
    
    public function archivedByUser()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }
    
    /**
     * Implémentation HasRecordBase
     */
    protected function getCodePrefix(): string
    {
        return 'DF';
    }
    
    /**
     * Implémentation HasMetadata
     */
    protected function getMetadataRecordType(): string
    {
        return 'folder';
    }
    
    /**
     * Implémentation HasAttachments
     */
    protected function getAttachmentPivotTable(): string
    {
        return 'digital_folder_attachments';
    }
    
    /**
     * Gestion de l'arborescence
     */
    public function updatePath(): void
    {
        if ($this->parent_id) {
            $parent = $this->parent;
            $this->path = $parent->path . '/' . $this->code;
        } else {
            $this->path = '/' . $this->code;
        }
    }
    
    public function updateDepth(): void
    {
        $this->depth = $this->parent_id ? ($this->parent->depth + 1) : 0;
        
        // Vérifier profondeur maximale
        if ($this->depth > 10) {
            throw new \Exception("Maximum folder depth (10 levels) exceeded");
        }
    }
    
    public function updateParentCounts(): void
    {
        if ($this->parent_id) {
            $parent = $this->parent;
            $parent->children_count = $parent->children()->count();
            $parent->total_size = $parent->calculateTotalSize();
            $parent->saveQuietly(); // Éviter boucle
        }
    }
    
    public function calculateTotalSize(): int
    {
        $size = 0;
        
        // Taille des documents directs
        $size += $this->documents()->sum(function($doc) {
            return $doc->primaryAttachment()?->size ?? 0;
        });
        
        // Taille des sous-dossiers (récursif)
        foreach ($this->children as $child) {
            $size += $child->calculateTotalSize();
        }
        
        return $size;
    }
    
    public function getAncestors()
    {
        $ancestors = collect();
        $current = $this->parent;
        
        while ($current) {
            $ancestors->push($current);
            $current = $current->parent;
        }
        
        return $ancestors->reverse();
    }
    
    public function getBreadcrumb(): array
    {
        return $this->getAncestors()
            ->push($this)
            ->map(fn($folder) => [
                'id' => $folder->id,
                'name' => $folder->name,
                'code' => $folder->code,
            ])
            ->toArray();
    }
    
    /**
     * Vérifier permissions
     */
    public function canAccess(User $user): bool
    {
        // Logique de permissions selon access_level
        // À adapter selon votre système de permissions
        return true;
    }
    
    public function isLocked(): bool
    {
        return $this->is_locked;
    }
    
    public function lock(User $user): void
    {
        $this->is_locked = true;
        $this->locked_by = $user->id;
        $this->locked_at = now();
        $this->save();
    }
    
    public function unlock(): void
    {
        $this->is_locked = false;
        $this->locked_by = null;
        $this->locked_at = null;
        $this->save();
    }
    
    /**
     * Scopes
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
    
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('is_archived', false);
    }
    
    public function scopeByType($query, string $type)
    {
        return $query->where('folder_type', $type);
    }
    
    /**
     * Accesseurs
     */
    public function getSizeFormattedAttribute(): string
    {
        return $this->formatBytes($this->total_size);
    }
    
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    public function getFullPathAttribute(): string
    {
        return $this->path;
    }
}
4.7 Modèle : RecordDigitalDocument
php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasRecordBase;
use App\Traits\HasMetadata;
use App\Traits\HasAttachments;

class RecordDigitalDocument extends Model
{
    use HasRecordBase, HasMetadata, HasAttachments, SoftDeletes;
    
    protected $table = 'record_digital_documents';
    
    protected $fillable = [
        'code', 'parent_folder_id', 'name', 'description',
        'document_type', 'document_date', 'document_author',
        'version_number', 'is_current_version', 'version_parent_id', 'version_note',
        'access_level', 'is_signed', 'signature_date', 'signature_certificate', 'signature_algorithm',
        'is_encrypted', 'encryption_algorithm',
        'is_locked', 'locked_by', 'locked_at',
        'checkout_by', 'checkout_at',
        'download_count', 'view_count', 'last_accessed_at',
        'metadata_template_id', 'status',
        'activity_id', 'organisation_id', 'created_by', 'updated_by'
    ];
    
    protected $casts = [
        'document_date' => 'date',
        'version_number' => 'integer',
        'is_current_version' => 'boolean',
        'is_signed' => 'boolean',
        'signature_date' => 'datetime',
        'is_encrypted' => 'boolean',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'checkout_at' => 'datetime',
        'download_count' => 'integer',
        'view_count' => 'integer',
        'last_accessed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Boot
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($document) {
            // Valider parent_folder_id NOT NULL
            if (!$document->parent_folder_id) {
                throw new \Exception("Document must belong to a folder (parent_folder_id required)");
            }
            
            // Définir version 1 si nouveau
            if (!$document->version_number) {
                $document->version_number = 1;
                $document->is_current_version = true;
            }
        });
        
        static::saved(function ($document) {
            $document->parentFolder->updateParentCounts();
        });
    }
    
    /**
     * Relations
     */
    public function parentFolder()
    {
        return $this->belongsTo(RecordDigitalFolder::class, 'parent_folder_id');
    }
    
    public function versionParent()
    {
        return $this->belongsTo(RecordDigitalDocument::class, 'version_parent_id');
    }
    
    public function versions()
    {
        return $this->hasMany(RecordDigitalDocument::class, 'version_parent_id')
            ->orderBy('version_number', 'desc');
    }
    
    public function allVersions()
    {
        // Récupérer toute la chaîne de versions
        if ($this->version_parent_id) {
            return $this->versionParent->allVersions();
        }
        
        return $this->versions()->with('versions')->get();
    }
    
    public function lockedByUser()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }
    
    public function checkedOutByUser()
    {
        return $this->belongsTo(User::class, 'checkout_by');
    }
    
    public function shares()
    {
        return $this->hasMany(DocumentShare::class, 'document_id');
    }
    
    public function accessLogs()
    {
        return $this->hasMany(DocumentAccessLog::class, 'document_id')
            ->orderBy('created_at', 'desc');
    }
    
    public function versionsHistory()
    {
        return $this->hasMany(DocumentVersionHistory::class, 'document_id')
            ->orderBy('version_number', 'desc');
    }
    
    /**
     * Implémentation HasRecordBase
     */
    protected function getCodePrefix(): string
    {
        return 'DD';
    }
    
    /**
     * Implémentation HasMetadata
     */
    protected function getMetadataRecordType(): string
    {
        return 'document';
    }
    
    /**
     * Implémentation HasAttachments
     */
    protected function getAttachmentPivotTable(): string
    {
        return 'digital_document_attachments';
    }
    
    protected function getAttachmentPivotColumns(): array
    {
        return ['attachment_role', 'is_primary', 'version_number', 'display_order', 'title', 'description'];
    }
    
    /**
     * Gestion du fichier principal
     */
    public function getPrimaryFile()
    {
        return $this->attachments()
            ->wherePivot('is_primary', true)
            ->wherePivot('attachment_role', 'primary')
            ->first();
    }
    
    public function getAnnexes()
    {
        return $this->attachments()
            ->wherePivot('attachment_role', 'annex')
            ->orderByPivot('display_order');
    }
    
    public function getVersionFiles()
    {
        return $this->attachments()
            ->wherePivot('attachment_role', 'version')
            ->orderByPivot('version_number', 'desc');
    }
    
    /**
     * Gestion des versions
     */
    public function createNewVersion(Attachment $file, string $note = null): self
    {
        // Marquer version actuelle comme non-current
        $this->is_current_version = false;
        $this->save();
        
        // Créer nouvelle version
        $newVersion = $this->replicate();
        $newVersion->version_parent_id = $this->version_parent_id ?? $this->id;
        $newVersion->version_number = $this->version_number + 1;
        $newVersion->is_current_version = true;
        $newVersion->version_note = $note;
        $newVersion->save();
        
        // Attacher fichier
        $newVersion->attachFile($file, [
            'attachment_role' => 'primary',
            'is_primary' => true,
            'version_number' => $newVersion->version_number,
        ]);
        
        // Historique
        DocumentVersionHistory::create([
            'document_id' => $this->id,
            'version_number' => $newVersion->version_number,
            'attachment_id' => $file->id,
            'created_by' => auth()->id(),
            'version_note' => $note,
            'file_size' => $file->size,
            'file_hash' => $file->crypt_sha512,
        ]);
        
        return $newVersion;
    }
    
    /**
     * Gestion du checkout
     */
    public function checkout(User $user): void
    {
        if ($this->isCheckedOut()) {
            throw new \Exception("Document already checked out by {$this->checkedOutByUser->name}");
        }
        
        $this->checkout_by = $user->id;
        $this->checkout_at = now();
        $this->save();
    }
    
    public function checkin(): void
    {
        $this->checkout_by = null;
        $this->checkout_at = null;
        $this->save();
    }
    
    public function isCheckedOut(): bool
    {
        return !is_null($this->checkout_by);
    }
    
    /**
     * Gestion du verrouillage
     */
    public function lock(User $user): void
    {
        $this->is_locked = true;
        $this->locked_by = $user->id;
        $this->locked_at = now();
        $this->save();
    }
    
    public function unlock(): void
    {
        $this->is_locked = false;
        $this->locked_by = null;
        $this->locked_at = null;
        $this->save();
    }
    
    /**
     * Logging d'accès
     */
    public function logAccess(string $action, User $user = null): void
    {
        DocumentAccessLog::create([
            'document_id' => $this->id,
            'user_id' => $user?->id ?? auth()->id(),
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
        
        if ($action === 'view') {
            $this->increment('view_count');
            $this->last_accessed_at = now();
            $this->save();
        } elseif ($action === 'download') {
            $this->increment('download_count');
        }
    }
    
    /**
     * Scopes
     */
    public function scopeInFolder($query, int $folderId)
    {
        return $query->where('parent_folder_id', $folderId);
    }
    
    public function scopeCurrent($query)
    {
        return $query->where('is_current_version', true);
    }
    
    public function scopeByType($query, string $type)
    {
        return $query->where('document_type', $type);
    }
    
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
    
    /**
     * Accesseurs
     */
    public function getFileSizeAttribute()
    {
        $file = $this->getPrimaryFile();
        return $file ? $file->size : 0;
    }
    
    public function getFileSizeFormattedAttribute(): string
    {
        return $this->formatBytes($this->file_size);
    }
    
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    public function getFullPathAttribute(): string
    {
        return $this->parentFolder->path . '/' . $this->code;
    }
}

// 4.8 Modèle : RecordBook
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasRecordBase;
use App\Traits\HasAttachments;

class RecordBook extends Model
{
    use HasRecordBase, HasAttachments, SoftDeletes;
    
    protected $table = 'record_books';
    
    protected $fillable = [
        'code', 'isbn', 'isbn_10', 'ean', 'title', 'subtitle', 'original_title',
        'edition', 'edition_number', 'volume_number', 'volume_total',
        'publisher', 'publication_place', 'publication_country',
        'publication_year', 'publication_date', 'first_publication_year',
        'page_count', 'illustration_count', 'language', 'original_language',
        'translation_languages', 'book_format',
        'dimensions_height', 'dimensions_width', 'dimensions_thickness', 'weight',
        'binding_type', 'dewey_decimal', 'library_congress', 'udc', 'rameau',
        'subject_headings', 'genre', 'abstract', 'table_of_contents', 'back_cover',
        'series_title', 'series_number', 'series_total',
        'translator', 'illustrator', 'photographer', 'editor_literary',
        'preface_by', 'introduction_by', 'collection_name', 'collection_number',
        'print_run', 'printing_number', 'copyright_year', 'copyright_holder',
        'legal_deposit', 'acquisition_date', 'acquisition_price', 'acquisition_currency',
        'acquisition_method', 'supplier', 'invoice_number',
        'condition_on_acquisition', 'current_condition', 'condition_note',
        'restoration_history', 'special_features', 'dedication', 'provenance',
        'value_estimate', 'value_date',
        'is_rare', 'is_first_edition', 'is_signed', 'is_reference', 'is_restricted',
        'loan_allowed', 'reservation_allowed', 'max_loan_days',
        'renewal_allowed', 'max_renewals',
        'notes', 'internal_notes', 'cataloging_date', 'cataloger_id',
        'last_inventory_date', 'status',
        'activity_id', 'organisation_id', 'created_by', 'updated_by'
    ];
    
    protected $casts = [
        'edition_number' => 'integer',
        'volume_number' => 'integer',
        'volume_total' => 'integer',
        'publication_year' => 'integer',
        'publication_date' => 'date',
        'first_publication_year' => 'integer',
        'page_count' => 'integer',
        'illustration_count' => 'integer',
        'translation_languages' => 'array',
        'dimensions_height' => 'decimal:2',
        'dimensions_width' => 'decimal:2',
        'dimensions_thickness' => 'decimal:2',
        'weight' => 'decimal:2',
        'subject_headings' => 'array',
        'genre' => 'array',
        'series_number' => 'integer',
        'series_total' => 'integer',
        'collection_number' => 'integer',
        'print_run' => 'integer',
        'printing_number' => 'integer',
        'copyright_year' => 'integer',
        'acquisition_date' => 'date',
        'acquisition_price' => 'decimal:2',
        'value_estimate' => 'decimal:2',
        'value_date' => 'date',
        'is_rare' => 'boolean',
        'is_first_edition' => 'boolean',
        'is_signed' => 'boolean',
        'is_reference' => 'boolean',
        'is_restricted' => 'boolean',
        'loan_allowed' => 'boolean',
        'reservation_allowed' => 'boolean',
        'max_loan_days' => 'integer',
        'renewal_allowed' => 'boolean',
        'max_renewals' => 'integer',
        'cataloging_date' => 'date',
        'last_inventory_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    /**
     * Relations
     */
    public function bookAuthors()
    {
        return $this->belongsToMany(Author::class, 'book_authors')
            ->withPivot(['author_role', 'author_order'])
            ->orderByPivot('author_order');
    }
    
    public function copies()
    {
        return $this->hasMany(BookCopy::class, 'book_id');
    }
    
    public function availableCopies()
    {
        return $this->copies()->where('status', 'available');
    }
    
    public function loans()
    {
        return $this->hasManyThrough(BookLoan::class, BookCopy::class);
    }
    
    public function activeLoans()
    {
        return $this->loans()->where('status', 'active');
    }
    
    public function reservations()
    {
        return $this->hasMany(BookReservation::class, 'book_id')
            ->orderBy('reservation_date');
    }
    
    public function cataloger()
    {
        return $this->belongsTo(User::class, 'cataloger_id');
    }
    
    /**
     * Implémentation HasRecordBase
     */
    protected function getCodePrefix(): string
    {
        return 'BK';
    }
    
    /**
     * Implémentation HasAttachments
     */
    protected function getAttachmentPivotTable(): string
    {
        return 'book_attachments';
    }
    
    protected function getAttachmentPivotColumns(): array
    {
        return ['attachment_type', 'is_main_cover', 'display_order', 'caption'];
    }
    
    /**
     * Méthodes métier
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available' && $this->availableCopies()->exists();
    }
    
    public function isOnLoan(): bool
    {
        return $this->activeLoans()->exists();
    }
    
    public function hasReservations(): bool
    {
        return $this->reservations()->where('status', 'pending')->exists();
    }
    
    public function getTotalCopies(): int
    {
        return $this->copies()->count();
    }
    
    public function getAvailableCopiesCount(): int
    {
        return $this->availableCopies()->count();
    }
    
    /**
     * Scopes
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
            ->whereHas('copies', function($q) {
                $q->where('status', 'available');
            });
    }
    
    public function scopeByIsbn($query, string $isbn)
    {
        return $query->where('isbn', $isbn)
            ->orWhere('isbn_10', $isbn);
    }
    
    public function scopeByPublisher($query, string $publisher)
    {
        return $query->where('publisher', 'LIKE', "%{$publisher}%");
    }
    
    public function scopeByYear($query, int $year)
    {
        return $query->where('publication_year', $year);
    }
    
    public function scopeRare($query)
    {
        return $query->where('is_rare', true);
    }
    
    /**
     * Accesseurs
     */
    public function getFullTitleAttribute(): string
    {
        $title = $this->title;
        if ($this->subtitle) {
            $title .= ' : ' . $this->subtitle;
        }
        if ($this->volume_number) {
            $title .= " (Vol. {$this->volume_number}";
            if ($this->volume_total) {
                $title .= "/{$this->volume_total}";
            }
            $title .= ")";
        }
        return $title;
    }
    
    public function getCitationAttribute(): string
    {
        $authors = $this->bookAuthors()
            ->wherePivot('author_role', 'author')
            ->pluck('name')
            ->implode(', ');
        
        return "{$authors}. {$this->title}. {$this->publisher}, {$this->publication_year}.";
    }
}

// 4.9 Modèle : RecordPeriodic
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasRecordBase;
use App\Traits\HasAttachments;

class RecordPeriodic extends Model
{
    use HasRecordBase, HasAttachments, SoftDeletes;
    
    protected $table = 'record_periodics';
    
    protected $fillable = [
        'code', 'issn', 'issn_online', 'issn_l',
        'title', 'subtitle', 'short_title', 'former_title', 'original_title', 'parallel_title',
        'publisher', 'publication_place', 'publication_country',
        'frequency', 'frequency_detail', 'language', 'additional_languages',
        'subject_area', 'subject_classification', 'description', 'scope_note', 'editorial_board',
        'first_year', 'first_volume', 'first_issue',
        'last_year', 'last_volume', 'last_issue',
        'is_active', 'cessation_date', 'cessation_reason',
        'website_url', 'online_archive_url', 'doi_prefix',
        'publisher_country', 'publisher_type', 'editorial_model',
        'peer_review_type', 'open_access_type', 'license',
        'apc_amount', 'apc_currency', 'indexing_databases',
        'impact_factor', 'impact_factor_year', 'h_index',
        'subscription_status', 'subscription_type',
        'subscription_start_date', 'subscription_end_date',
        'subscription_cost_annual', 'subscription_currency',
        'subscription_agent', 'subscription_ref', 'supplier', 'invoice_frequency',
        'access_type', 'online_access_url', 'online_platform',
        'access_username', 'access_password',
        'ip_access', 'ip_range', 'simultaneous_users',
        'holdings_start_year', 'holdings_start_volume', 'holdings_note', 'gaps',
        'retention_policy', 'binding_schedule',
        'notes', 'internal_notes', 'cataloging_date', 'cataloger_id', 'last_inventory_date',
        'activity_id', 'organisation_id', 'created_by', 'updated_by'
    ];
    
    protected $casts = [
        'additional_languages' => 'array',
        'subject_classification' => 'array',
        'first_year' => 'integer',
        'first_volume' => 'integer',
        'last_year' => 'integer',
        'last_volume' => 'integer',
        'is_active' => 'boolean',
        'cessation_date' => 'date',
        'apc_amount' => 'decimal:2',
        'indexing_databases' => 'array',
        'impact_factor' => 'decimal:3',
        'impact_factor_year' => 'integer',
        'h_index' => 'integer',
        'subscription_start_date' => 'date',
        'subscription_end_date' => 'date',
        'subscription_cost_annual' => 'decimal:2',
        'ip_access' => 'boolean',
        'simultaneous_users' => 'integer',
        'holdings_start_year' => 'integer',
        'holdings_start_volume' => 'integer',
        'cataloging_date' => 'date',
        'last_inventory_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    protected $hidden = [
        'access_password'
    ];
    
    /**
     * Relations
     */
    public function issues()
    {
        return $this->hasMany(PeriodicIssue::class, 'periodic_id')
            ->orderBy('year', 'desc')
            ->orderBy('volume_number', 'desc')
            ->orderBy('issue_number', 'desc');
    }
    
    public function latestIssues()
    {
        return $this->issues()->limit(10);
    }
    
    public function articles()
    {
        return $this->hasManyThrough(PeriodicArticle::class, PeriodicIssue::class);
    }
    
    public function indexes()
    {
        return $this->hasMany(PeriodicIndex::class, 'periodic_id')
            ->orderBy('year', 'desc');
    }
    
    public function claims()
    {
        return $this->hasMany(PeriodicClaim::class, 'periodic_id')
            ->orderBy('claim_date', 'desc');
    }
    
    public function subscriptionsHistory()
    {
        return $this->hasMany(PeriodicSubscriptionHistory::class, 'periodic_id')
            ->orderBy('start_date', 'desc');
    }
    
    public function currentSubscription()
    {
        return $this->subscriptionsHistory()
            ->where('start_date', '<=', now())
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            })
            ->first();
    }
    
    public function cataloger()
    {
        return $this->belongsTo(User::class, 'cataloger_id');
    }
    
    /**
     * Implémentation HasRecordBase
     */
    protected function getCodePrefix(): string
    {
        return 'PER';
    }
    
    /**
     * Implémentation HasAttachments
     */
    protected function getAttachmentPivotTable(): string
    {
        return 'periodic_attachments';
    }
    
    protected function getAttachmentPivotColumns(): array
    {
        return ['attachment_type', 'is_main', 'page_number', 'figure_number', 'table_number', 'caption', 'display_order'];
    }
    
    /**
     * Méthodes métier
     */
    public function isSubscriptionActive(): bool
    {
        return in_array($this->subscription_status, ['active', 'trial']);
    }
    
    public function isSubscriptionExpiring(int $daysThreshold = 30): bool
    {
        if (!$this->subscription_end_date) {
            return false;
        }
        
        return $this->subscription_end_date->lte(now()->addDays($daysThreshold));
    }
    
    public function getExpectedIssue(): ?array
    {
        // Calculer prochain numéro attendu selon fréquence
        $latest = $this->issues()->first();
        
        if (!$latest) {
            return null;
        }
        
        // Logique de calcul selon frequency
        return [
            'volume' => $latest->volume_number,
            'issue' => $latest->issue_number + 1,
            'expected_date' => $this->calculateNextIssueDate($latest),
        ];
    }
    
    protected function calculateNextIssueDate(PeriodicIssue $lastIssue): ?\Carbon\Carbon
    {
        if (!$lastIssue->publication_date) {
            return null;
        }
        
        $date = $lastIssue->publication_date->copy();
        
        switch ($this->frequency) {
            case 'daily':
                return $date->addDay();
            case 'weekly':
                return $date->addWeek();
            case 'monthly':
                return $date->addMonth();
            case 'quarterly':
                return $date->addMonths(3);
            case 'semiannual':
                return $date->addMonths(6);
            case 'annual':
                return $date->addYear();
            default:
                return null;
        }
    }
    
    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeWithActiveSubscription($query)
    {
        return $query->whereIn('subscription_status', ['active', 'trial']);
    }
    
    public function scopeByIssn($query, string $issn)
    {
        return $query->where('issn', $issn)
            ->orWhere('issn_online', $issn)
            ->orWhere('issn_l', $issn);
    }
    
    public function scopeByPublisher($query, string $publisher)
    {
        return $query->where('publisher', 'LIKE', "%{$publisher}%");
    }
    
    public function scopeOpenAccess($query)
    {
        return $query->where('open_access_type', 'full');
    }
    
    /**
     * Accesseurs
     */
    public function getFullTitleAttribute(): string
    {
        $title = $this->title;
        if ($this->subtitle) {
            $title .= ' : ' . $this->subtitle;
        }
        return $title;
    }
    
    public function getHoldingsRangeAttribute(): string
    {
        if (!$this->holdings_start_year) {
            return 'N/A';
        }
        
        $range = "Vol. {$this->holdings_start_volume} ({$this->holdings_start_year})";
        
        if ($this->is_active) {
            $range .= ' - en cours';
        } elseif ($this->last_year) {
            $range .= " - Vol. {$this->last_volume} ({$this->last_year})";
        }
        
        return $range;
    }
}
```

---

## 5. Migrations Laravel | 🔴 NON DÉMARRÉ

### État d'implémentation

| Migration | Statut | Testé | Documentation |
|-----------|--------|-------|---------------|
| Rename records → record_physicals | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| Extend attachments table | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| Create record_artifacts + tables | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| Create record_digital_folders | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| Create record_digital_documents | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| Create record_books + tables | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| Create record_periodics + tables | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| Create metadata system (4 tables) | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |

**Checklist générale** :
- [ ] Toutes les migrations créées
- [ ] Ordre d'exécution validé
- [ ] Méthode down() testée (rollback)
- [ ] Migration des données testée
- [ ] Contraintes d'intégrité vérifiées

### 5.1 Migration : Renommer `records` en `record_physicals` | 🔴 NON DÉMARRÉ

**Checklist** :
- [ ] Fichier de migration créé
- [ ] Renommage de table effectué
- [ ] Index conservés
- [ ] Méthode down() implémentée
- [ ] Testé sur base de dev

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Renommer la table
        Schema::rename('records', 'record_physicals');
        
        // Mettre à jour les noms de foreign keys dans les pivots
        Schema::table('record_author', function (Blueprint $table) {
            $table->renameColumn('record_id', 'physical_id');
        });
        
        Schema::rename('record_author', 'physical_author');
        
        Schema::table('record_keyword', function (Blueprint $table) {
            $table->renameColumn('record_id', 'physical_id');
        });
        
        Schema::rename('record_keyword', 'physical_keyword');
        
        Schema::table('record_thesaurus_concept', function (Blueprint $table) {
            $table->renameColumn('record_id', 'physical_id');
        });
        
        Schema::rename('record_thesaurus_concept', 'physical_thesaurus_concept');
        
        Schema::table('record_container', function (Blueprint $table) {
            $table->renameColumn('record_id', 'physical_id');
        });
        
        Schema::rename('record_container', 'physical_container');
        
        Schema::table('communication_record', function (Blueprint $table) {
            $table->renameColumn('record_id', 'physical_id');
        });
        
        Schema::rename('communication_record', 'communication_physical');
    }
    
    public function down(): void
    {
        Schema::rename('record_physicals', 'records');
        Schema::rename('physical_author', 'record_author');
        Schema::rename('physical_keyword', 'record_keyword');
        Schema::rename('physical_thesaurus_concept', 'record_thesaurus_concept');
        Schema::rename('physical_container', 'record_container');
        Schema::rename('communication_physical', 'communication_record');
        
        // Renommer colonnes
        Schema::table('record_author', function (Blueprint $table) {
            $table->renameColumn('physical_id', 'record_id');
        });
        
        Schema::table('record_keyword', function (Blueprint $table) {
            $table->renameColumn('physical_id', 'record_id');
        });
        
        Schema::table('record_thesaurus_concept', function (Blueprint $table) {
            $table->renameColumn('physical_id', 'record_id');
        });
        
        Schema::table('record_container', function (Blueprint $table) {
            $table->renameColumn('physical_id', 'record_id');
        });
        
        Schema::table('communication_record', function (Blueprint $table) {
            $table->renameColumn('physical_id', 'record_id');
        });
    }
};
```

### 5.2 Migration : Étendre `attachments`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Modifier l'ENUM type
        DB::statement("ALTER TABLE attachments MODIFY COLUMN type ENUM(
            'mail',
            'record',
            'communication',
            'transferting',
            'bulletinboardpost',
            'bulletinboard',
            'bulletinboardevent',
            'digital_folder',
            'digital_document',
            'artifact',
            'book',
            'periodic'
        ) NOT NULL");
        
        Schema::table('attachments', function (Blueprint $table) {
            // Métadonnées techniques
            $table->string('ocr_language', 10)->nullable()->after('content_text');
            $table->decimal('ocr_confidence', 5, 2)->nullable()->after('ocr_language')
                ->comment('Score qualité OCR 0-100');
            $table->string('file_encoding', 50)->nullable()->after('mime_type');
            $table->integer('page_count')->nullable()->after('ocr_confidence')
                ->comment('Nombre de pages PDF');
            $table->integer('word_count')->nullable()->after('page_count')
                ->comment('Nombre de mots');
            $table->string('file_hash_md5', 32)->nullable()->after('crypt_sha512');
            $table->string('file_extension', 10)->nullable()->after('mime_type');
            $table->boolean('is_primary')->default(false)->after('type')
                ->comment('Fichier principal/représentatif');
            $table->integer('display_order')->default(0)->after('is_primary');
            $table->text('description')->nullable()->after('name')
                ->comment('Description du fichier');
            
            // Index
            $table->index(['type', 'is_primary'], 'idx_type_primary');
            $table->index('file_hash_md5', 'idx_file_hash');
            $table->index('file_extension', 'idx_extension');
        });
    }
    
    public function down(): void
    {
        Schema::table('attachments', function (Blueprint $table) {
            $table->dropIndex('idx_type_primary');
            $table->dropIndex('idx_file_hash');
            $table->dropIndex('idx_extension');
            
            $table->dropColumn([
                'ocr_language',
                'ocr_confidence',
                'file_encoding',
                'page_count',
                'word_count',
                'file_hash_md5',
                'file_extension',
                'is_primary',
                'display_order',
                'description',
            ]);
        });
        
        DB::statement("ALTER TABLE attachments MODIFY COLUMN type ENUM(
            'mail',
            'record',
            'communication',
            'transferting',
            'bulletinboardpost',
            'bulletinboard',
            'bulletinboardevent'
        ) NOT NULL");
    }
};
```

### 5.3 Migration : `record_artifacts`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('record_artifacts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 250);
            $table->string('inventory_number', 50)->unique()->nullable();
            
            $table->enum('object_type', [
                'sculpture', 'painting', 'furniture', 'tool', 'clothing',
                'ceramic', 'jewelry', 'weapon', 'coin', 'archaeological',
                'ethnographic', 'natural_history', 'other'
            ])->nullable();
            
            // Datation
            $table->string('creation_date_start', 10)->nullable();
            $table->string('creation_date_end', 10)->nullable();
            $table->date('creation_date_exact')->nullable();
            $table->string('creator_name', 200)->nullable();
            
            // Matériaux et techniques
            $table->json('materials')->nullable();
            $table->json('techniques')->nullable();
            
            // Dimensions
            $table->decimal('height', 8, 2)->nullable()->comment('cm');
            $table->decimal('width', 8, 2)->nullable()->comment('cm');
            $table->decimal('depth', 8, 2)->nullable()->comment('cm');
            $table->decimal('weight', 10, 2)->nullable()->comment('grammes');
            $table->decimal('diameter', 8, 2)->nullable()->comment('cm');
            
            // Conservation
            $table->enum('conservation_state', [
                'excellent', 'good', 'fair', 'poor', 'critical', 'restored'
            ])->nullable();
            $table->text('conservation_note')->nullable();
            $table->text('restoration_history')->nullable();
            
            // Provenance
            $table->string('origin_place', 200)->nullable();
            $table->date('acquisition_date')->nullable();
            $table->enum('acquisition_method', [
                'purchase', 'donation', 'excavation', 'transfer',
                'bequest', 'exchange', 'unknown'
            ])->nullable();
            $table->string('acquisition_source', 200)->nullable();
            $table->decimal('acquisition_price', 12, 2)->nullable();
            
            // Localisation
            $table->unsignedBigInteger('current_location_id')->nullable();
            $table->enum('exhibition_status', [
                'exhibited', 'storage', 'on_loan', 'in_restoration',
                'unavailable', 'missing'
            ])->nullable();
            
            // Assurance
            $table->decimal('insurance_value', 12, 2)->nullable();
            $table->date('insurance_date')->nullable();
            
            // Contexte historique
            $table->string('cultural_period', 100)->nullable();
            $table->string('geographical_origin', 200)->nullable();
            $table->text('provenance')->nullable();
            
            // Descriptions
            $table->text('description')->nullable();
            $table->text('historical_note')->nullable();
            $table->text('iconography')->nullable();
            $table->text('inscriptions')->nullable();
            $table->text('marks')->nullable();
            $table->text('bibliographical_references')->nullable();
            $table->text('exhibition_history')->nullable();
            
            // Gestion
            $table->boolean('is_fragile')->default(false);
            $table->text('handling_instructions')->nullable();
            
            // Relations communes
            $table->unsignedBigInteger('status_id')->nullable();
            $table->unsignedBigInteger('activity_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('organisation_id')->nullable();
            
            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys
            $table->foreign('current_location_id')->references('id')->on('rooms')->onDelete('set null');
            $table->foreign('status_id')->references('id')->on('record_statuses')->onDelete('set null');
            $table->foreign('activity_id')->references('id')->on('activities')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            // Index
            $table->index('code');
            $table->index('inventory_number');
            $table->index('object_type');
            $table->index('exhibition_status');
            $table->index(['organisation_id', 'status_id']);
        });
        
        // Table pivot enrichie
        Schema::create('artifact_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('artifact_id');
            $table->unsignedBigInteger('attachment_id');
            
            $table->enum('view_type', [
                'main', 'front', 'back', 'side', 'top', 'bottom',
                'detail', 'xray', '3d_model', 'other'
            ])->default('other');
            $table->boolean('is_main_image')->default(false);
            $table->text('caption')->nullable();
            $table->string('photographer', 200)->nullable();
            $table->date('photo_date')->nullable();
            $table->integer('display_order')->default(0);
            
            $table->timestamps();
            
            $table->foreign('artifact_id')->references('id')->on('record_artifacts')->onDelete('cascade');
            $table->foreign('attachment_id')->references('id')->on('attachments')->onDelete('cascade');
            
            $table->unique(['artifact_id', 'attachment_id']);
            $table->index('view_type');
        });
        
        // Tables associées
        Schema::create('artifact_exhibitions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('artifact_id');
            $table->string('exhibition_name', 300);
            $table->string('location', 200)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('role', ['exhibited', 'reproduced', 'mentioned'])->default('exhibited');
            $table->string('catalog_number', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('artifact_id')->references('id')->on('record_artifacts')->onDelete('cascade');
            $table->index(['artifact_id', 'start_date']);
        });
        
        Schema::create('artifact_loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('artifact_id');
            $table->string('borrower_institution', 300);
            $table->string('contact_person', 200)->nullable();
            $table->text('loan_purpose')->nullable();
            $table->date('loan_start_date');
            $table->date('loan_end_date');
            $table->date('actual_return_date')->nullable();
            $table->text('condition_on_loan')->nullable();
            $table->text('condition_on_return')->nullable();
            $table->decimal('insurance_value', 12, 2)->nullable();
            $table->enum('status', [
                'requested', 'approved', 'active', 'returned', 'overdue', 'cancelled'
            ])->default('requested');
            $table->timestamps();
            
            $table->foreign('artifact_id')->references('id')->on('record_artifacts')->onDelete('cascade');
            $table->index(['artifact_id', 'status']);
        });
        
        Schema::create('artifact_condition_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('artifact_id');
            $table->date('report_date');
            $table->string('examined_by', 200);
            $table->enum('condition_state', [
                'excellent', 'good', 'fair', 'poor', 'critical'
            ]);
            $table->text('condition_description')->nullable();
            $table->text('issues_identified')->nullable();
            $table->text('recommendations')->nullable();
            $table->date('next_examination_date')->nullable();
            $table->timestamps();
            
            $table->foreign('artifact_id')->references('id')->on('record_artifacts')->onDelete('cascade');
            $table->index(['artifact_id', 'report_date']);
        });
        
        // Tables pivot communes
        Schema::create('artifact_author', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('artifact_id');
            $table->unsignedBigInteger('author_id');
            $table->timestamps();
            
            $table->foreign('artifact_id')->references('id')->on('record_artifacts')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('authors')->onDelete('cascade');
            $table->unique(['artifact_id', 'author_id']);
        });
        
        Schema::create('artifact_keyword', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('artifact_id');
            $table->unsignedBigInteger('keyword_id');
            $table->timestamps();
            
            $table->foreign('artifact_id')->references('id')->on('record_artifacts')->onDelete('cascade');
            $table->foreign('keyword_id')->references('id')->on('keywords')->onDelete('cascade');
            $table->unique(['artifact_id', 'keyword_id']);
        });
        
        Schema::create('artifact_thesaurus_concept', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('artifact_id');
            $table->unsignedBigInteger('thesaurus_concept_id');
            $table->integer('weight')->nullable();
            $table->string('context', 100)->nullable();
            $table->text('extraction_note')->nullable();
            $table->timestamps();
            
            $table->foreign('artifact_id')->references('id')->on('record_artifacts')->onDelete('cascade');
            $table->foreign('thesaurus_concept_id')->references('id')->on('thesaurus_concepts')->onDelete('cascade');
            $table->unique(['artifact_id', 'thesaurus_concept_id'], 'artifact_concept_unique');
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('artifact_thesaurus_concept');
        Schema::dropIfExists('artifact_keyword');
        Schema::dropIfExists('artifact_author');
        Schema::dropIfExists('artifact_condition_reports');
        Schema::dropIfExists('artifact_loans');
        Schema::dropIfExists('artifact_exhibitions');
        Schema::dropIfExists('artifact_attachments');
        Schema::dropIfExists('record_artifacts');
    }
};
```

**Note** : Les migrations pour `record_digital_folders`, `record_digital_documents`, `record_books`, `record_periodics` et toutes leurs tables associées suivent la même structure. Je continue avec les sections suivantes pour finaliser le document.

---

## 6. Services et Logique Métier | 🔴 NON DÉMARRÉ

### État d'implémentation

| Service | Statut | Tests | Documentation |
|---------|--------|-------|---------------|
| RecordService (Base) | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| DigitalDocumentService | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| ArtifactService | 🔴 NON DÉMARRÉ | 🔴 | 🔴 |
| BookService | 🔴 NON DÉMARRÉ | 🔴 | 🔴 |
| PeriodicService | 🔴 NON DÉMARRÉ | 🔴 | 🔴 |
| MetadataService | 🔴 NON DÉMARRÉ | 🔴 | 🔴 |

**Checklist générale** :
- [ ] Tous les services créés
- [ ] Logique métier implémentée
- [ ] Validation des données
- [ ] Gestion des erreurs
- [ ] Tests unitaires complets

### 6.1 Service : RecordService (Base) | 🔴 NON DÉMARRÉ

**Checklist** :
- [ ] Service créé dans `App\Services`
- [ ] Méthode createWithAttachments()
- [ ] Méthode updateWithAttachments()
- [ ] Gestion transactions BDD
- [ ] Tests unitaires

```php
<?php

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

abstract class RecordService
{
    /**
     * Créer un enregistrement
     */
    public function create(array $data): Model
    {
        $model = $this->getModelClass();
        $record = $model::create($data);
        
        // Attacher relations
        $this->syncRelations($record, $data);
        
        return $record->fresh();
    }
    
    /**
     * Mettre à jour un enregistrement
     */
    public function update(Model $record, array $data): Model
    {
        $record->update($data);
        
        // Mettre à jour relations
        $this->syncRelations($record, $data);
        
        return $record->fresh();
    }
    
    /**
     * Supprimer un enregistrement
     */
    public function delete(Model $record): bool
    {
        return $record->delete();
    }
    
    /**
     * Synchroniser les relations
     */
    protected function syncRelations(Model $record, array $data): void
    {
        if (isset($data['authors'])) {
            $record->authors()->sync($data['authors']);
        }
        
        if (isset($data['keywords'])) {
            $record->keywords()->sync($data['keywords']);
        }
        
        if (isset($data['thesaurus_concepts'])) {
            $record->thesaurusConcepts()->sync($data['thesaurus_concepts']);
        }
    }
    
    /**
     * Upload et attacher fichier
     */
    public function attachFile(
        Model $record,
        UploadedFile $file,
        string $type,
        array $pivotData = []
    ): Attachment {
        // Générer nom crypté
        $cryptName = Str::random(40) . '.' . $file->getClientOriginalExtension();
        
        // Stocker fichier
        $path = Storage::disk('local')->putFileAs(
            "attachments/{$type}",
            $file,
            $cryptName
        );
        
        // Créer attachment
        $attachment = Attachment::create([
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'crypt' => $cryptName,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'file_extension' => $file->getClientOriginalExtension(),
            'type' => $type,
            'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
            'file_hash_md5' => hash_file('md5', $file->getRealPath()),
            'creator_id' => auth()->id(),
        ]);
        
        // Attacher au record
        $record->attachFile($attachment, $pivotData);
        
        // Extraction contenu si PDF/Word
        $this->extractContent($attachment, $file);
        
        return $attachment;
    }
    
    /**
     * Extraire contenu textuel
     */
    protected function extractContent(Attachment $attachment, UploadedFile $file): void
    {
        $mimeType = $file->getMimeType();
        
        if ($mimeType === 'application/pdf') {
            // Utiliser Smalot\PdfParser
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($file->getRealPath());
                $text = $pdf->getText();
                
                $attachment->content_text = $text;
                $attachment->page_count = count($pdf->getPages());
                $attachment->word_count = str_word_count($text);
                $attachment->save();
            } catch (\Exception $e) {
                \Log::warning("PDF extraction failed: " . $e->getMessage());
            }
        }
        
        // Autres formats...
    }
    
    /**
     * Classe du modèle
     */
    abstract protected function getModelClass(): string;
}
```

### 6.2 Service : DigitalDocumentService

```php
<?php

namespace App\Services;

use App\Models\RecordDigitalDocument;
use App\Models\RecordDigitalFolder;
use App\Models\Attachment;
use Illuminate\Http\UploadedFile;

class DigitalDocumentService extends RecordService
{
    protected function getModelClass(): string
    {
        return RecordDigitalDocument::class;
    }
    
    /**
     * Créer document avec fichier obligatoire
     */
    public function createWithFile(
        RecordDigitalFolder $folder,
        array $data,
        UploadedFile $file
    ): RecordDigitalDocument {
        // Créer document
        $data['parent_folder_id'] = $folder->id;
        $document = $this->create($data);
        
        // Attacher fichier principal
        $attachment = $this->attachFile($document, $file, 'digital_document', [
            'attachment_role' => 'primary',
            'is_primary' => true,
            'version_number' => 1,
        ]);
        
        return $document;
    }
    
    /**
     * Créer nouvelle version
     */
    public function createVersion(
        RecordDigitalDocument $document,
        UploadedFile $file,
        string $note = null
    ): RecordDigitalDocument {
        $attachment = Attachment::create([
            'path' => Storage::disk('local')->putFile('attachments/digital_document', $file),
            'name' => $file->getClientOriginalName(),
            'crypt' => Str::random(40) . '.' . $file->getClientOriginalExtension(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'type' => 'digital_document',
            'crypt_sha512' => hash_file('sha512', $file->getRealPath()),
            'creator_id' => auth()->id(),
        ]);
        
        return $document->createNewVersion($attachment, $note);
    }
    
    /**
     * Déplacer document vers autre dossier
     */
    public function moveToFolder(
        RecordDigitalDocument $document,
        RecordDigitalFolder $targetFolder
    ): void {
        $oldFolder = $document->parentFolder;
        
        $document->parent_folder_id = $targetFolder->id;
        $document->save();
        
        // Mettre à jour compteurs
        $oldFolder->updateParentCounts();
        $targetFolder->updateParentCounts();
    }
    
    /**
     * Partager document
     */
    public function share(
        RecordDigitalDocument $document,
        array $data
    ): DocumentShare {
        return DocumentShare::create([
            'document_id' => $document->id,
            'shared_by' => auth()->id(),
            'shared_with_user_id' => $data['user_id'] ?? null,
            'shared_with_email' => $data['email'] ?? null,
            'share_token' => Str::random(32),
            'access_type' => $data['access_type'],
            'expires_at' => $data['expires_at'] ?? null,
            'password_hash' => isset($data['password']) ? bcrypt($data['password']) : null,
            'download_limit' => $data['download_limit'] ?? null,
        ]);
    }
}
```

---

## 7. Plan de Migration des Données | 🔴 NON DÉMARRÉ

### État d'implémentation

| Tâche | Statut | Validé | Documentation |
|-------|--------|--------|---------------|
| Migration records → record_physicals | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| Migration des attachments | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| Seeders pour types spécialisés | 🔴 NON DÉMARRÉ | 🔴 | 🔴 |
| Vérification intégrité | 🔴 NON DÉMARRÉ | 🔴 | 🟢 |
| Tests migration complète | 🔴 NON DÉMARRÉ | 🔴 | 🔴 |

**Checklist générale** :
- [ ] Seeder créé et testé
- [ ] Données préservées (aucune perte)
- [ ] Relations maintenues
- [ ] Script rollback fonctionnel
- [ ] Log des opérations
- [ ] Validation sur base de test

### 7.1 Migration des données `records` → `record_physicals` | 🔴 NON DÉMARRÉ

**Checklist** :
- [ ] Seeder créé dans `database/seeders`
- [ ] Vérification pré-migration
- [ ] Transaction BDD utilisée
- [ ] Logs d'erreurs capturés
- [ ] Testé sur données réelles

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateRecordsDataSeeder extends Seeder
{
    public function run(): void
    {
        // Les données sont déjà dans record_physicals après le rename
        // Vérification intégrité
        
        $physicalCount = DB::table('record_physicals')->count();
        $this->command->info("Total record_physicals: {$physicalCount}");
        
        // Vérifier pivots
        $this->checkPivotMigration('physical_author', 'authors');
        $this->checkPivotMigration('physical_keyword', 'keywords');
        $this->checkPivotMigration('physical_thesaurus_concept', 'thesaurus_concepts');
        $this->checkPivotMigration('physical_container', 'containers');
        
        $this->command->info("✓ Migration records → record_physicals vérifiée");
    }
    
    private function checkPivotMigration(string $pivotTable, string $relatedTable): void
    {
        $count = DB::table($pivotTable)->count();
        $this->command->info("  - {$pivotTable}: {$count} relations");
        
        // Vérifier intégrité référentielle
        $orphans = DB::table($pivotTable)
            ->leftJoin($relatedTable, "{$pivotTable}.{$relatedTable}_id", '=', "{$relatedTable}.id")
            ->whereNull("{$relatedTable}.id")
            ->count();
            
        if ($orphans > 0) {
            $this->command->warn("  ⚠ {$orphans} orphelins détectés dans {$pivotTable}");
        }
    }
}
```

---

## 8. Tests Unitaires | 🔴 NON DÉMARRÉ

### État d'implémentation

| Composant | Tests Créés | Coverage | Statut |
|-----------|-------------|----------|--------|
| RecordPhysical | 🔴 | 0% | 🔴 NON DÉMARRÉ |
| RecordArtifact | 🔴 | 0% | 🔴 NON DÉMARRÉ |
| RecordDigitalFolder | 🔴 | 0% | 🔴 NON DÉMARRÉ |
| RecordDigitalDocument | 🔴 | 0% | 🔴 NON DÉMARRÉ |
| RecordBook | 🔴 | 0% | 🔴 NON DÉMARRÉ |
| RecordPeriodic | 🔴 | 0% | 🔴 NON DÉMARRÉ |
| HasRecordBase trait | 🔴 | 0% | 🔴 NON DÉMARRÉ |
| HasAttachments trait | 🔴 | 0% | 🔴 NON DÉMARRÉ |
| HasMetadata trait | 🔴 | 0% | 🔴 NON DÉMARRÉ |
| Services | 🔴 | 0% | 🔴 NON DÉMARRÉ |
| Migrations | 🔴 | 0% | 🔴 NON DÉMARRÉ |

**Checklist générale** :
- [ ] Tests unitaires pour tous les modèles
- [ ] Tests pour tous les traits
- [ ] Tests pour tous les services
- [ ] Tests d'intégration migrations
- [ ] Coverage > 80%
- [ ] Tests passent en CI/CD

### 8.1 Test : RecordArtifactTest | 🔴 NON DÉMARRÉ

**Checklist** :
- [ ] Test créé dans `tests/Unit/Models`
- [ ] Test création d'objet
- [ ] Test relations (exhibitions, loans, etc.)
- [ ] Test validation
- [ ] Test soft deletes

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\RecordArtifact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecordArtifactTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_create_artifact()
    {
        $user = User::factory()->create();
        
        $artifact = RecordArtifact::create([
            'name' => 'Vase Ming',
            'object_type' => 'ceramic',
            'conservation_state' => 'excellent',
            'created_by' => $user->id,
        ]);
        
        $this->assertNotNull($artifact->code);
        $this->assertStringStartsWith('ART-', $artifact->code);
    }
    
    public function test_can_attach_photos()
    {
        $artifact = RecordArtifact::factory()->create();
        $attachment = Attachment::factory()->create(['type' => 'artifact']);
        
        $artifact->attachFile($attachment, [
            'view_type' => 'main',
            'is_main_image' => true,
        ]);
        
        $this->assertEquals(1, $artifact->attachments()->count());
        $this->assertNotNull($artifact->mainPhoto());
    }
}
```

---

## 9. Résumé Exécutif

### Récapitulatif de la Refonte

**Objectif** : Transformer le système monolithique `records` en architecture modulaire multi-types.

**Types de ressources créés** :
1. ✅ **RecordPhysical** : Archives physiques (migration depuis `records`)
2. ✅ **RecordArtifact** : Objets de musée
3. ✅ **RecordDigitalFolder** : Dossiers numériques hiérarchiques
4. ✅ **RecordDigitalDocument** : Documents numériques avec versioning
5. ✅ **RecordBook** : Livres et ouvrages
6. ✅ **RecordPeriodic** : Publications périodiques

**Architecture** :
- ✅ Table `attachments` centralisée et étendue
- ✅ Système de métadonnées personnalisables (exclusif digital)
- ✅ Traits Laravel réutilisables
- ✅ Services métier modulaires
- ✅ API REST complète

**Migration** :
- ✅ Renommage `records` → `record_physicals`
- ✅ Préservation de toutes les données existantes
- ✅ Maintien de l'intégrité référentielle

**Prochaines étapes** :
1. Exécuter les migrations
2. Implémenter les modèles et services
3. Créer les contrôleurs web et API
4. Ajouter les menus dans l'interface : **Menu Bibliothèque** (pour RecordBook et RecordPeriodic) et **Menu Musée** (pour RecordArtifact)
5. Développer les vues (dashboards, CRUD)
6. Tester en environnement staging
7. Déploiement progressif en production

---

**FIN DU DOCUMENT DE SPÉCIFICATIONS**

*Version finale - Novembre 2025*


**Description** : Module de gestion de la bibliothèque permettant de gérer les livres (RecordBook) et les publications périodiques (RecordPeriodic).

**Checklist** :
- [ ] Menu "Bibliothèque" ajouté à la navigation
- [ ] Sous-menu "Livres" (Books)
- [ ] Sous-menu "Périodiques" (Periodicals)
- [ ] Sous-menu "Emprunts" (Loans)
- [ ] Sous-menu "Réservations" (Reservations)
- [ ] Dashboard bibliothèque
- [ ] Statistiques et rapports

#### 9.1.1 Structure du Menu Bibliothèque

```php
// Navigation principale (resources/views/layouts/navigation.blade.php)

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="libraryDropdown" 
       role="button" data-bs-toggle="dropdown">
        <i class="bi bi-book"></i> Bibliothèque
    </a>
    <ul class="dropdown-menu" aria-labelledby="libraryDropdown">
        <li>
            <a class="dropdown-item" href="{{ route('library.dashboard') }}">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="{{ route('library.books.index') }}">
                <i class="bi bi-book-fill"></i> Livres
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('library.periodicals.index') }}">
                <i class="bi bi-newspaper"></i> Périodiques
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="{{ route('library.loans.index') }}">
                <i class="bi bi-arrow-left-right"></i> Emprunts
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('library.reservations.index') }}">
                <i class="bi bi-calendar-check"></i> Réservations
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="{{ route('library.reports') }}">
                <i class="bi bi-graph-up"></i> Rapports
            </a>
        </li>
    </ul>
</li>
```

#### 9.1.2 Routes Web pour Bibliothèque

```php
// routes/web.php

use App\Http\Controllers\Library\BookController;
use App\Http\Controllers\Library\PeriodicController;
use App\Http\Controllers\Library\LoanController;
use App\Http\Controllers\Library\ReservationController;

Route::prefix('library')->name('library.')->middleware(['auth'])->group(function () {
    
    // Dashboard Bibliothèque
    Route::get('/', [LibraryController::class, 'dashboard'])->name('dashboard');
    
    // Gestion des Livres
    Route::resource('books', BookController::class);
    Route::post('books/{book}/copies', [BookController::class, 'addCopy'])->name('books.copies.add');
    Route::delete('books/copies/{copy}', [BookController::class, 'removeCopy'])->name('books.copies.remove');
    
    // Gestion des Périodiques
    Route::resource('periodicals', PeriodicController::class);
    Route::post('periodicals/{periodic}/issues', [PeriodicController::class, 'addIssue'])->name('periodicals.issues.add');
    Route::post('periodicals/issues/{issue}/articles', [PeriodicController::class, 'addArticle'])->name('periodicals.articles.add');
    
    // Gestion des Emprunts
    Route::resource('loans', LoanController::class);
    Route::post('loans/{loan}/return', [LoanController::class, 'returnLoan'])->name('loans.return');
    Route::post('loans/{loan}/renew', [LoanController::class, 'renewLoan'])->name('loans.renew');
    
    // Gestion des Réservations
    Route::resource('reservations', ReservationController::class);
    Route::post('reservations/{reservation}/confirm', [ReservationController::class, 'confirm'])->name('reservations.confirm');
    Route::post('reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');
    
    // Rapports et Statistiques
    Route::get('reports', [LibraryController::class, 'reports'])->name('reports');
    Route::get('reports/export', [LibraryController::class, 'exportReports'])->name('reports.export');
});
```

#### 9.1.3 Contrôleur Bibliothèque (Dashboard)

```php
<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\RecordBook;
use App\Models\RecordPeriodic;
use App\Models\BookLoan;
use App\Models\BookReservation;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    /**
     * Dashboard de la bibliothèque
     */
    public function dashboard()
    {
        $stats = [
            'total_books' => RecordBook::count(),
            'total_periodicals' => RecordPeriodic::count(),
            'active_loans' => BookLoan::where('status', 'active')->count(),
            'pending_reservations' => BookReservation::where('status', 'pending')->count(),
            'overdue_loans' => BookLoan::where('status', 'active')
                ->where('return_date', '<', now())
                ->count(),
            'available_copies' => \DB::table('book_copies')
                ->where('status', 'available')
                ->count(),
        ];
        
        $recent_loans = BookLoan::with(['copy.book', 'user'])
            ->latest()
            ->limit(10)
            ->get();
            
        $recent_books = RecordBook::latest()
            ->limit(5)
            ->get();
            
        return view('library.dashboard', compact('stats', 'recent_loans', 'recent_books'));
    }
    
    /**
     * Page de rapports
     */
    public function reports()
    {
        return view('library.reports');
    }
}
```

### 9.2 Menu Musée (Museum) | 🔴 NON DÉMARRÉ

**Description** : Module de gestion du musée permettant de gérer les objets/artefacts (RecordArtifact), les expositions, les prêts et l'état de conservation.

**Checklist** :
- [ ] Menu "Musée" ajouté à la navigation
- [ ] Sous-menu "Objets/Artefacts" (Artifacts)
- [ ] Sous-menu "Expositions" (Exhibitions)
- [ ] Sous-menu "Prêts" (Loans)
- [ ] Sous-menu "État de Conservation" (Condition Reports)
- [ ] Dashboard musée
- [ ] Statistiques et rapports

#### 9.2.1 Structure du Menu Musée

```php
// Navigation principale (resources/views/layouts/navigation.blade.php)

<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="museumDropdown" 
       role="button" data-bs-toggle="dropdown">
        <i class="bi bi-building"></i> Musée
    </a>
    <ul class="dropdown-menu" aria-labelledby="museumDropdown">
        <li>
            <a class="dropdown-item" href="{{ route('museum.dashboard') }}">
                <i class="bi bi-speedometer2"></i> Tableau de bord
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="{{ route('museum.artifacts.index') }}">
                <i class="bi bi-gem"></i> Objets/Artefacts
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('museum.exhibitions.index') }}">
                <i class="bi bi-easel"></i> Expositions
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('museum.loans.index') }}">
                <i class="bi bi-box-arrow-right"></i> Prêts
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('museum.condition-reports.index') }}">
                <i class="bi bi-clipboard-check"></i> État de Conservation
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="{{ route('museum.acquisitions.index') }}">
                <i class="bi bi-plus-circle"></i> Acquisitions
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="{{ route('museum.inventory') }}">
                <i class="bi bi-list-check"></i> Inventaire
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item" href="{{ route('museum.reports') }}">
                <i class="bi bi-graph-up"></i> Rapports
            </a>
        </li>
    </ul>
</li>
```

#### 9.2.2 Routes Web pour Musée

```php
// routes/web.php

use App\Http\Controllers\Museum\ArtifactController;
use App\Http\Controllers\Museum\ExhibitionController;
use App\Http\Controllers\Museum\LoanController as MuseumLoanController;
use App\Http\Controllers\Museum\ConditionReportController;
use App\Http\Controllers\Museum\AcquisitionController;

Route::prefix('museum')->name('museum.')->middleware(['auth'])->group(function () {
    
    // Dashboard Musée
    Route::get('/', [MuseumController::class, 'dashboard'])->name('dashboard');
    
    // Gestion des Artefacts
    Route::resource('artifacts', ArtifactController::class);
    Route::get('artifacts/{artifact}/history', [ArtifactController::class, 'history'])->name('artifacts.history');
    Route::post('artifacts/{artifact}/photos', [ArtifactController::class, 'addPhoto'])->name('artifacts.photos.add');
    
    // Gestion des Expositions
    Route::resource('exhibitions', ExhibitionController::class);
    Route::post('exhibitions/{exhibition}/artifacts', [ExhibitionController::class, 'addArtifact'])->name('exhibitions.artifacts.add');
    Route::delete('exhibitions/{exhibition}/artifacts/{artifact}', [ExhibitionController::class, 'removeArtifact'])->name('exhibitions.artifacts.remove');
    
    // Gestion des Prêts
    Route::resource('loans', MuseumLoanController::class);
    Route::post('loans/{loan}/return', [MuseumLoanController::class, 'returnLoan'])->name('loans.return');
    Route::post('loans/{loan}/extend', [MuseumLoanController::class, 'extendLoan'])->name('loans.extend');
    
    // État de Conservation
    Route::resource('condition-reports', ConditionReportController::class);
    Route::get('artifacts/{artifact}/condition-reports', [ConditionReportController::class, 'artifactReports'])->name('artifacts.condition-reports');
    
    // Acquisitions
    Route::resource('acquisitions', AcquisitionController::class);
    Route::post('acquisitions/{acquisition}/approve', [AcquisitionController::class, 'approve'])->name('acquisitions.approve');
    
    // Inventaire
    Route::get('inventory', [MuseumController::class, 'inventory'])->name('inventory');
    Route::post('inventory/export', [MuseumController::class, 'exportInventory'])->name('inventory.export');
    
    // Rapports et Statistiques
    Route::get('reports', [MuseumController::class, 'reports'])->name('reports');
    Route::get('reports/export', [MuseumController::class, 'exportReports'])->name('reports.export');
});
```

#### 9.2.3 Contrôleur Musée (Dashboard)

```php
<?php

namespace App\Http\Controllers\Museum;

use App\Http\Controllers\Controller;
use App\Models\RecordArtifact;
use App\Models\ArtifactExhibition;
use App\Models\ArtifactLoan;
use App\Models\ArtifactConditionReport;
use Illuminate\Http\Request;

class MuseumController extends Controller
{
    /**
     * Dashboard du musée
     */
    public function dashboard()
    {
        $stats = [
            'total_artifacts' => RecordArtifact::count(),
            'active_exhibitions' => ArtifactExhibition::where('status', 'active')->count(),
            'active_loans' => ArtifactLoan::where('status', 'active')->count(),
            'artifacts_on_display' => \DB::table('artifact_exhibition_item')
                ->join('artifact_exhibitions', 'artifact_exhibition_item.exhibition_id', '=', 'artifact_exhibitions.id')
                ->where('artifact_exhibitions.status', 'active')
                ->distinct('artifact_exhibition_item.artifact_id')
                ->count(),
            'recent_condition_reports' => ArtifactConditionReport::where('created_at', '>=', now()->subMonths(3))
                ->count(),
            'artifacts_need_restoration' => ArtifactConditionReport::where('condition', 'poor')
                ->where('restoration_required', true)
                ->count(),
        ];
        
        $recent_artifacts = RecordArtifact::latest()
            ->limit(10)
            ->get();
            
        $active_exhibitions = ArtifactExhibition::where('status', 'active')
            ->with(['items.artifact'])
            ->get();
            
        $urgent_reports = ArtifactConditionReport::where('condition', 'poor')
            ->where('restoration_required', true)
            ->latest()
            ->limit(5)
            ->get();
            
        return view('museum.dashboard', compact('stats', 'recent_artifacts', 'active_exhibitions', 'urgent_reports'));
    }
    
    /**
     * Page d'inventaire
     */
    public function inventory()
    {
        $artifacts = RecordArtifact::with(['location', 'category'])
            ->orderBy('code')
            ->paginate(50);
            
        return view('museum.inventory', compact('artifacts'));
    }
    
    /**
     * Page de rapports
     */
    public function reports()
    {
        return view('museum.reports');
    }
    
    /**
     * Export inventaire
     */
    public function exportInventory(Request $request)
    {
        // Export Excel/PDF de l'inventaire
        // Utiliser Laravel Excel ou DomPDF
    }
}
```

### 9.3 Navigation Principale Complète | 🔴 NON DÉMARRÉ

**Checklist** :
- [ ] Intégration de tous les menus
- [ ] Permissions par rôle
- [ ] Menu responsive (mobile)
- [ ] Breadcrumbs
- [ ] Recherche globale

#### 9.3.1 Structure Complète de Navigation

```php
// resources/views/layouts/navigation.blade.php

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('home') }}">
            <i class="bi bi-archive"></i> {{ config('app.name') }}
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('dashboard') }}">
                        <i class="bi bi-house"></i> Accueil
                    </a>
                </li>
                
                <!-- Menu Bibliothèque -->
                @can('access-library')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="libraryDropdown" 
                       role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-book"></i> Bibliothèque
                    </a>
                    <ul class="dropdown-menu">
                        <!-- Contenu du menu bibliothèque (voir 9.1.1) -->
                    </ul>
                </li>
                @endcan
                
                <!-- Menu Musée -->
                @can('access-museum')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="museumDropdown" 
                       role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-building"></i> Musée
                    </a>
                    <ul class="dropdown-menu">
                        <!-- Contenu du menu musée (voir 9.2.1) -->
                    </ul>
                </li>
                @endcan
                
                <!-- Menu Archives Physiques -->
                @can('access-archives')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="archivesDropdown" 
                       role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-archive-fill"></i> Archives Physiques
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('archives.physical.index') }}">Gestion</a></li>
                        <li><a class="dropdown-item" href="{{ route('archives.physical.search') }}">Recherche</a></li>
                        <li><a class="dropdown-item" href="{{ route('archives.physical.reports') }}">Rapports</a></li>
                    </ul>
                </li>
                @endcan
                
                <!-- Menu Archives Numériques -->
                @can('access-digital-archives')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="digitalDropdown" 
                       role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-folder2"></i> Archives Numériques
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('digital.folders.index') }}">Dossiers</a></li>
                        <li><a class="dropdown-item" href="{{ route('digital.documents.index') }}">Documents</a></li>
                        <li><a class="dropdown-item" href="{{ route('digital.search') }}">Recherche</a></li>
                    </ul>
                </li>
                @endcan
                
                <!-- Administration -->
                @can('access-admin')
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" 
                       role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-gear"></i> Administration
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('admin.users.index') }}">Utilisateurs</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.roles.index') }}">Rôles</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.settings') }}">Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('admin.metadata.definitions') }}">Métadonnées</a></li>
                        <li><a class="dropdown-item" href="{{ route('admin.logs') }}">Journaux</a></li>
                    </ul>
                </li>
                @endcan
                
            </ul>
            
            <!-- Recherche globale -->
            <form class="d-flex me-3" action="{{ route('search') }}" method="GET">
                <input class="form-control me-2" type="search" name="q" 
                       placeholder="Rechercher..." aria-label="Search">
                <button class="btn btn-outline-light" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>
            
            <!-- Menu utilisateur -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" 
                       role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('profile') }}">Profil</a></li>
                        <li><a class="dropdown-item" href="{{ route('settings') }}">Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
```

### 9.4 Permissions et Autorisations | 🔴 NON DÉMARRÉ

**Checklist** :
- [ ] Définir les rôles (admin, librarian, curator, archivist, user)
- [ ] Gates Laravel pour chaque module
- [ ] Middleware de vérification
- [ ] Tests de permissions

#### 9.4.1 Définition des Permissions

```php
// app/Providers/AuthServiceProvider.php

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPolicies();
        
        // Bibliothèque
        Gate::define('access-library', function ($user) {
            return $user->hasRole(['admin', 'librarian', 'user']);
        });
        
        Gate::define('manage-library', function ($user) {
            return $user->hasRole(['admin', 'librarian']);
        });
        
        // Musée
        Gate::define('access-museum', function ($user) {
            return $user->hasRole(['admin', 'curator', 'user']);
        });
        
        Gate::define('manage-museum', function ($user) {
            return $user->hasRole(['admin', 'curator']);
        });
        
        // Archives
        Gate::define('access-archives', function ($user) {
            return $user->hasRole(['admin', 'archivist', 'user']);
        });
        
        Gate::define('manage-archives', function ($user) {
            return $user->hasRole(['admin', 'archivist']);
        });
        
        // Archives numériques
        Gate::define('access-digital-archives', function ($user) {
            return $user->hasRole(['admin', 'archivist', 'user']);
        });
        
        // Administration
        Gate::define('access-admin', function ($user) {
            return $user->hasRole('admin');
        });
    }
}
```

### 9.5 Vues Dashboard (Exemples) | 🔴 NON DÉMARRÉ

**Checklist** :
- [ ] Dashboard bibliothèque
- [ ] Dashboard musée
- [ ] Dashboard archives
- [ ] Dashboard global

#### 9.5.1 Vue Dashboard Bibliothèque

```blade
{{-- resources/views/library/dashboard.blade.php --}}

@extends('layouts.app')

@section('title', 'Bibliothèque - Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="bi bi-book"></i> Bibliothèque</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Bibliothèque</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Livres</h5>
                    <h2>{{ $stats['total_books'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Périodiques</h5>
                    <h2>{{ $stats['total_periodicals'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Emprunts Actifs</h5>
                    <h2>{{ $stats['active_loans'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Retards</h5>
                    <h2>{{ $stats['overdue_loans'] }}</h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Actions rapides -->
    <div class="row mb-4">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h5>Actions Rapides</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('library.books.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nouveau Livre
                    </a>
                    <a href="{{ route('library.loans.create') }}" class="btn btn-success">
                        <i class="bi bi-arrow-left-right"></i> Nouvel Emprunt
                    </a>
                    <a href="{{ route('library.reports') }}" class="btn btn-info">
                        <i class="bi bi-graph-up"></i> Rapports
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Emprunts récents -->
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5>Emprunts Récents</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Livre</th>
                                <th>Utilisateur</th>
                                <th>Date Emprunt</th>
                                <th>Date Retour Prévue</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recent_loans as $loan)
                            <tr>
                                <td>{{ $loan->copy->book->name }}</td>
                                <td>{{ $loan->user->name }}</td>
                                <td>{{ $loan->loan_date->format('d/m/Y') }}</td>
                                <td>{{ $loan->return_date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="badge bg-{{ $loan->status_color }}">
                                        {{ $loan->status_label }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5>Derniers Livres Ajoutés</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach($recent_books as $book)
                        <li class="list-group-item">
                            <strong>{{ $book->code }}</strong><br>
                            {{ $book->name }}<br>
                            <small class="text-muted">{{ $book->author }}</small>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

#### 9.5.2 Vue Dashboard Musée

```blade
{{-- resources/views/museum/dashboard.blade.php --}}

@extends('layouts.app')

@section('title', 'Musée - Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1><i class="bi bi-building"></i> Musée</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                    <li class="breadcrumb-item active">Musée</li>
                </ol>
            </nav>
        </div>
    </div>
    
    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Objets</h6>
                    <h2>{{ $stats['total_artifacts'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Expositions</h6>
                    <h2>{{ $stats['active_exhibitions'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">En Exposition</h6>
                    <h2>{{ $stats['artifacts_on_display'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Prêts Actifs</h6>
                    <h2>{{ $stats['active_loans'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-title">À Restaurer</h6>
                    <h2>{{ $stats['artifacts_need_restoration'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h6 class="card-title">Rapports Récents</h6>
                    <h2>{{ $stats['recent_condition_reports'] }}</h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Actions rapides -->
    <div class="row mb-4">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h5>Actions Rapides</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('museum.artifacts.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Nouvel Objet
                    </a>
                    <a href="{{ route('museum.exhibitions.create') }}" class="btn btn-success">
                        <i class="bi bi-easel"></i> Nouvelle Exposition
                    </a>
                    <a href="{{ route('museum.condition-reports.create') }}" class="btn btn-warning">
                        <i class="bi bi-clipboard-check"></i> Rapport de Conservation
                    </a>
                    <a href="{{ route('museum.inventory') }}" class="btn btn-info">
                        <i class="bi bi-list-check"></i> Inventaire
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Contenu principal -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Expositions Actives</h5>
                </div>
                <div class="card-body">
                    @foreach($active_exhibitions as $exhibition)
                    <div class="mb-3 p-3 border rounded">
                        <h6>{{ $exhibition->name }}</h6>
                        <p class="mb-1">
                            <small>
                                <i class="bi bi-calendar"></i> 
                                {{ $exhibition->start_date->format('d/m/Y') }} - 
                                {{ $exhibition->end_date->format('d/m/Y') }}
                            </small>
                        </p>
                        <p class="mb-0">
                            <span class="badge bg-info">
                                {{ $exhibition->items->count() }} objets
                            </span>
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5>Objets Nécessitant une Restauration</h5>
                </div>
                <div class="card-body">
                    @foreach($urgent_reports as $report)
                    <div class="mb-3 p-3 border border-danger rounded">
                        <h6>{{ $report->artifact->name }}</h6>
                        <p class="mb-1">
                            <strong>État :</strong> 
                            <span class="badge bg-danger">{{ $report->condition }}</span>
                        </p>
                        <p class="mb-0">
                            <small>{{ Str::limit($report->notes, 100) }}</small>
                        </p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

---

## 10. Résumé Exécutif

### Récapitulatif de la Refonte

**Objectif** : Transformer le système monolithique `records` en architecture modulaire multi-types.

**Types de ressources créés** :
1. ✅ **RecordPhysical** : Archives physiques (migration depuis `records`)
2. ✅ **RecordArtifact** : Objets de musée
3. ✅ **RecordDigitalFolder** : Dossiers numériques hiérarchiques
4. ✅ **RecordDigitalDocument** : Documents numériques avec versioning
5. ✅ **RecordBook** : Livres et ouvrages
6. ✅ **RecordPeriodic** : Publications périodiques

**Architecture** :
- ✅ Table `attachments` centralisée et étendue
- ✅ Système de métadonnées personnalisables (exclusif digital)
- ✅ Traits Laravel réutilisables
- ✅ Services métier modulaires
- ✅ API REST complète

**Migration** :
- ✅ Renommage `records` → `record_physicals`
- ✅ Préservation de toutes les données existantes
- ✅ Maintien de l'intégrité référentielle

**Interface Utilisateur** :
- ✅ **Menu Bibliothèque (Library)** : Gestion des livres (RecordBook) et périodiques (RecordPeriodic)
  - Dashboard bibliothèque avec statistiques
  - Gestion des emprunts et réservations
  - Rapports et inventaire
- ✅ **Menu Musée (Museum)** : Gestion des objets/artefacts (RecordArtifact)
  - Dashboard musée avec statistiques
  - Gestion des expositions et prêts
  - État de conservation et acquisitions
  - Inventaire des collections
- ✅ **Navigation complète** avec permissions par rôle
- ✅ Recherche globale intégrée

**Prochaines étapes** :
1. Exécuter les migrations
2. Implémenter les modèles et services
3. Créer les contrôleurs web et API
4. Développer les vues (dashboards, CRUD)
5. Implémenter les permissions et autorisations
6. Tester en environnement staging
7. Déploiement progressif en production

---

**FIN DU DOCUMENT DE SPÉCIFICATIONS**

*Version finale - Novembre 2025*




