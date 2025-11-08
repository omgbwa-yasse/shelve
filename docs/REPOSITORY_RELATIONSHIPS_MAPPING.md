# Cartographie des Relations du Module Repository

**Date:** 2025-11-08  
**Statut:** En cours d'analyse  
**Objectif:** Identifier toutes les relations entre Record/RecordPhysical et les autres modèles pour planifier la migration vers l'architecture à 3 modèles

---

## 📊 Vue d'ensemble de l'architecture

### Modèles principaux

1. **RecordPhysical** (Dossiers physiques)
   - Table: `record_physicals`
   - Usage: Documents physiques archivés dans des conteneurs
   - Relations: Conteneurs, étagères, salles, auteurs, pièces jointes

2. **RecordDigitalFolder** (Dossiers numériques)
   - Table: `record_digital_folders`
   - Usage: Organisation hiérarchique des documents numériques
   - Relations: Type, parent/enfants, documents, organisation, créateur

3. **RecordDigitalDocument** (Documents numériques)
   - Table: `record_digital_documents`
   - Usage: Documents versionés avec workflow d'approbation
   - Relations: Type, dossier, versions, pièces jointes, signatures

---

## 🔗 Relations RecordPhysical

### Relations BelongsTo (N:1)

| Relation | Modèle cible | Clé étrangère | Usage | Catégorie |
|----------|--------------|---------------|-------|-----------|
| `status()` | RecordStatus | status_id | Statut du dossier | **SHARED** |
| `support()` | RecordSupport | support_id | Type de support physique | **PHYSICAL ONLY** |
| `level()` | RecordLevel | level_id | Niveau archivistique | **PHYSICAL ONLY** |
| `activity()` | Activity | activity_id | Activité liée | **PHYSICAL ONLY** |
| `organisation()` | Organisation | organisation_id | Organisation propriétaire | **SHARED** |
| `user()` | User | user_id | Créateur | **SHARED** |
| `parent()` | RecordPhysical | parent_id | Dossier parent | **PHYSICAL ONLY** |

### Relations HasMany (1:N)

| Relation | Modèle cible | Clé locale | Usage | Catégorie |
|----------|--------------|------------|-------|-----------|
| `children()` | RecordPhysical | parent_id | Sous-dossiers | **PHYSICAL ONLY** |
| `recordContainers()` | RecordContainer | - | Conteneurs liés | **PHYSICAL ONLY** |

### Relations BelongsToMany (N:N)

| Relation | Modèle cible | Table pivot | Usage | Catégorie |
|----------|--------------|-------------|-------|-----------|
| `containers()` | Container | record_physical_container | Localisation physique | **PHYSICAL ONLY** |
| `authors()` | Author | record_physical_author | Auteurs du dossier | **SHARED** (à vérifier) |
| `attachments()` | Attachment | record_physical_attachment | Fichiers numériques | **SHARED** |
| `keywords()` | Keyword | record_physical_keyword | Mots-clés | **SHARED** |
| `thesaurusConcepts()` | ThesaurusConcept | record_physical_thesaurus_concept | Concepts thésaurus | **SHARED** |

### Relations HasManyThrough

| Relation | Modèle cible | Via | Usage | Catégorie |
|----------|--------------|-----|-------|-----------|
| `shelves()` | Shelf | Container | Étagères via conteneurs | **PHYSICAL ONLY** |
| `rooms()` | Room | Shelf | Salles via étagères | **PHYSICAL ONLY** |

---

## 🔗 Relations RecordDigitalFolder

### Relations BelongsTo (N:1)

| Relation | Modèle cible | Clé étrangère | Usage | Catégorie |
|----------|--------------|---------------|-------|-----------|
| `type()` | RecordDigitalFolderType | type_id | Type de dossier | **FOLDER ONLY** |
| `parent()` | RecordDigitalFolder | parent_id | Dossier parent | **FOLDER ONLY** |
| `creator()` | User | creator_id | Créateur | **SHARED** |
| `organisation()` | Organisation | organisation_id | Organisation | **SHARED** |
| `assignedUser()` | User | assigned_to | Utilisateur assigné | **DIGITAL ONLY** |
| `approver()` | User | approved_by | Approbateur | **DIGITAL ONLY** |

### Relations HasMany (1:N)

| Relation | Modèle cible | Clé locale | Usage | Catégorie |
|----------|--------------|------------|-------|-----------|
| `children()` | RecordDigitalFolder | parent_id | Sous-dossiers | **FOLDER ONLY** |
| `documents()` | RecordDigitalDocument | folder_id | Documents du dossier | **FOLDER ONLY** |

### Relations MorphMany

| Relation | Modèle cible | Type morph | Usage | Catégorie |
|----------|--------------|------------|-------|-----------|
| `attachments()` | Attachment | attachmentable | Fichiers liés | **SHARED** |

---

## 🔗 Relations RecordDigitalDocument

### Relations BelongsTo (N:1)

| Relation | Modèle cible | Clé étrangère | Usage | Catégorie |
|----------|--------------|---------------|-------|-----------|
| `type()` | RecordDigitalDocumentType | type_id | Type de document | **DOCUMENT ONLY** |
| `folder()` | RecordDigitalFolder | folder_id | Dossier parent | **DOCUMENT ONLY** |
| `attachment()` | Attachment | attachment_id | Fichier principal | **DOCUMENT ONLY** |
| `parentVersion()` | RecordDigitalDocument | parent_version_id | Version précédente | **DOCUMENT ONLY** |
| `creator()` | User | creator_id | Créateur | **SHARED** |
| `organisation()` | Organisation | organisation_id | Organisation | **SHARED** |
| `assignedUser()` | User | assigned_to | Utilisateur assigné | **DIGITAL ONLY** |
| `checkedOutUser()` | User | checked_out_by | Utilisateur en checkout | **DOCUMENT ONLY** |
| `signer()` | User | signed_by | Signataire | **DOCUMENT ONLY** |
| `approver()` | User | approved_by | Approbateur | **DIGITAL ONLY** |
| `lastViewer()` | User | last_viewed_by | Dernier lecteur | **DOCUMENT ONLY** |

### Relations HasMany (1:N)

| Relation | Modèle cible | Clé locale | Usage | Catégorie |
|----------|--------------|------------|-------|-----------|
| `childVersions()` | RecordDigitalDocument | parent_version_id | Versions suivantes | **DOCUMENT ONLY** |

### Relations MorphMany

| Relation | Modèle cible | Type morph | Usage | Catégorie |
|----------|--------------|------------|-------|-----------|
| `attachments()` | Attachment | attachmentable | Fichiers liés | **SHARED** |

---

## 📋 Modèles référençant Record (ancien modèle)

### Modèles trouvés avec des références à `Record`:

1. **Dolly** (chariots)
   - `belongsToMany(record::class, 'dolly_records')`
   - **Migration nécessaire:** Ajouter support pour les 3 types

2. **DollySlipRecord**
   - `belongsTo(record::class)`
   - **Migration nécessaire:** Polymorphic relation

3. **PublicRecord**
   - `belongsTo(Record::class, 'record_id')`
   - **Migration nécessaire:** Support des 3 types pour OPAC

4. **RecordAttachment**
   - `belongsToMany(record::class, 'record_attachment')`
   - **Migration nécessaire:** Déjà migré vers attachments polymorphiques

---

## 🔍 Tables pivot identifiées (à analyser)

### Tables pivot RecordPhysical
- `record_physical_container` - Localisation physique
- `record_physical_author` - Auteurs
- `record_physical_attachment` - Pièces jointes (legacy?)
- `record_physical_keyword` - Mots-clés
- `record_physical_thesaurus_concept` - Concepts thésaurus

### Tables potentielles à créer pour Digital
- `record_digital_folder_keyword` - Mots-clés pour folders
- `record_digital_document_keyword` - Mots-clés pour documents
- `record_digital_folder_thesaurus_concept` - Thésaurus pour folders
- `record_digital_document_thesaurus_concept` - Thésaurus pour documents

---

## 🎯 Relations partagées (SHARED)

Ces relations doivent être disponibles pour les 3 types :

### 1. Organisation
- **RecordPhysical:** ✅ Implémenté (`organisation_id`)
- **RecordDigitalFolder:** ✅ Implémenté (`organisation_id`)
- **RecordDigitalDocument:** ✅ Implémenté (`organisation_id`)

### 2. Créateur (User)
- **RecordPhysical:** ✅ Implémenté (`user_id`)
- **RecordDigitalFolder:** ✅ Implémenté (`creator_id`)
- **RecordDigitalDocument:** ✅ Implémenté (`creator_id`)

### 3. Attachments (Pièces jointes)
- **RecordPhysical:** ✅ Via `record_physical_attachment`
- **RecordDigitalFolder:** ✅ Via morphMany
- **RecordDigitalDocument:** ✅ Via morphMany + attachment_id principal

### 4. Keywords (Mots-clés)
- **RecordPhysical:** ✅ Implémenté (`record_physical_keyword`)
- **RecordDigitalFolder:** ⚠️ **À implémenter**
- **RecordDigitalDocument:** ⚠️ **À implémenter**

### 5. Thesaurus Concepts
- **RecordPhysical:** ✅ Implémenté (`record_physical_thesaurus_concept`)
- **RecordDigitalFolder:** ⚠️ **À implémenter**
- **RecordDigitalDocument:** ⚠️ **À implémenter**

### 6. Authors (Auteurs)
- **RecordPhysical:** ✅ Implémenté (`record_physical_author`)
- **RecordDigitalFolder:** ❓ À décider (metadata?)
- **RecordDigitalDocument:** ❓ À décider (creator suffisant?)

---

## 🚧 Relations PHYSICAL ONLY

Ces relations sont spécifiques aux dossiers physiques et ne s'appliquent pas au numérique :

1. **Container/Shelf/Room** - Localisation physique
2. **RecordSupport** - Type de support (papier, microfilm, etc.)
3. **RecordLevel** - Niveau archivistique (fonds, série, etc.)
4. **Activity** - Activité productrice

---

## 💻 Relations DIGITAL ONLY

Ces relations sont spécifiques aux documents/dossiers numériques :

### Workflow & Approbation
1. **assigned_to** - Utilisateur assigné
2. **approved_by** - Approbateur
3. **requires_approval** - Flag d'approbation

### Versioning (Documents uniquement)
1. **parent_version_id** - Gestion des versions
2. **checked_out_by** - Check-out/Check-in
3. **version_number** - Numéro de version

### Signature (Documents uniquement)
1. **signed_by** - Signataire
2. **signature_status** - Statut de signature
3. **signature_data** - Données de signature

### Type spécifiques
1. **RecordDigitalFolderType** - Types de dossiers numériques
2. **RecordDigitalDocumentType** - Types de documents numériques

---

## 📊 Statistiques actuelles

| Catégorie | RecordPhysical | RecordDigitalFolder | RecordDigitalDocument |
|-----------|----------------|---------------------|----------------------|
| Relations BelongsTo | 7 | 6 | 11 |
| Relations HasMany | 2 | 2 | 1 |
| Relations BelongsToMany | 5 | 0 | 0 |
| Relations MorphMany | 0 | 1 | 1 |
| Relations HasManyThrough | 2 | 0 | 0 |
| **Total Relations** | **16** | **9** | **13** |

---

## ⚠️ Travaux nécessaires

### Phase 1: Relations manquantes (HIGH PRIORITY)

1. **Keywords pour Digital**
   - Créer `RecordDigitalFolder::keywords()` relation
   - Créer `RecordDigitalDocument::keywords()` relation
   - Créer migrations pour tables pivot
   - Adapter SearchRecordController pour rechercher dans keywords

2. **Thesaurus pour Digital**
   - Créer `RecordDigitalFolder::thesaurusConcepts()` relation
   - Créer `RecordDigitalDocument::thesaurusConcepts()` relation
   - Créer migrations pour tables pivot

3. **Auteurs pour Digital** (À DÉCIDER)
   - Évaluer si nécessaire pour folders/documents
   - Alternative: utiliser metadata JSON ou creator_id uniquement

### Phase 2: Migration des modèles legacy (MEDIUM PRIORITY)

1. **Dolly** - Transformer en relation polymorphique
   ```php
   // Ancien: dolly_records (dolly_id, record_id)
   // Nouveau: dolly_items (dolly_id, item_id, item_type)
   ```

2. **PublicRecord** - Support multi-types pour OPAC
   ```php
   // Ancien: public_records (id, record_id)
   // Nouveau: public_records (id, record_id, record_type)
   ```

3. **DollySlipRecord** - Relation polymorphique
   ```php
   // morphTo() au lieu de belongsTo(Record::class)
   ```

### Phase 3: Consolidation Attachment (LOW PRIORITY)

- Vérifier usage de `record_physical_attachment`
- Migrer vers morphMany si nécessaire
- Supprimer table pivot si obsolète

---

## 📝 Prochaines étapes

1. ✅ **Cartographie des relations** (en cours)
2. ⏭️ **Analyse des contrôleurs utilisant Record**
3. ⏭️ **Création des migrations pour relations manquantes**
4. ⏭️ **Tests de régression**
5. ⏭️ **Documentation API**
6. ⏭️ **Migration des données existantes**

---

## 🔗 Fichiers de référence

- RecordPhysical: `app/Models/RecordPhysical.php`
- RecordDigitalFolder: `app/Models/RecordDigitalFolder.php`
- RecordDigitalDocument: `app/Models/RecordDigitalDocument.php`
- SearchRecordController: `app/Http/Controllers/SearchRecordController.php`
- FolderController: `app/Http/Controllers/Web/FolderController.php`
- DocumentController: `app/Http/Controllers/Web/DocumentController.php`

---

**Dernière mise à jour:** 2025-11-08  
**Analysé par:** GitHub Copilot  
**Statut:** Document vivant - sera mis à jour au fur et à mesure des découvertes
