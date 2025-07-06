# Structure de Base de Données pour Thésaurus SKOS

Cette nouvelle migration remplace l'ancienne structure et permet de gérer plusieurs thésaurus conformes au standard SKOS (Simple Knowledge Organization System).

## État de la migration

✅ **Migration complétée** - La nouvelle structure est en place et opérationnelle.

- **Ancienne migration supprimée** : `2024_11_02_190213_create_term_table.php` 
- **Nouvelles tables créées** : 13 tables conformes SKOS
- **Migration de nettoyage exécutée** : `2025_07_05_120045_drop_old_thesaurus_tables.php`
- **Migration comprehensive exécutée** : `2024_11_02_190213_create_comprehensive_thesaurus_tables.php`

## Principales améliorations

### 1. Gestion multi-thésaurus
- Table `concept_schemes` pour stocker plusieurs thésaurus (typologie documentaire, géographique, thématique, etc.)
- Chaque concept est lié à un schéma spécifique
- Identifiants uniques par thésaurus (T3, GEO, THEM, etc.)

### 2. Conformité SKOS complète
- Support des **ConceptScheme** (schémas de concepts)
- Support des **Concept** avec toutes les propriétés SKOS
- Support de **SKOS-XL** (libellés étendus avec URI)
- Relations hiérarchiques (`skos:broader`/`skos:narrower`)
- Relations associatives (`skos:related`)
- Relations de mapping (`skos:exactMatch`, `skos:closeMatch`, etc.)

### 3. Métadonnées riches
- Métadonnées Dublin Core complètes
- Dates de création et modification (dct:created, dct:modified)
- Statuts ISO-25964
- Notes de portée, historiques, éditoriales
- Support multilingue

### 4. Fonctionnalités avancées
- Collections SKOS pour regrouper des concepts
- Historique des modifications
- Alignements avec référentiels externes
- Propriétés personnalisées par schéma
- Système de traduction entre langues

## Structure des tables

### Tables principales

#### `concept_schemes`
Stocke les différents thésaurus/vocabulaires :
- **uri** : URI SKOS du schéma
- **identifier** : Identifiant court (T3, GEO, etc.)
- **title** : Titre du thésaurus
- **description** : Description du contenu
- **creator**, **publisher** : Métadonnées Dublin Core
- **metadata** : Métadonnées additionnelles en JSON

#### `concepts`
Stocke tous les concepts de tous les thésaurus :
- **concept_scheme_id** : Référence au thésaurus parent
- **uri** : URI SKOS unique du concept
- **notation** : Code/notation (ex: T3-139)
- **preferred_label** : Terme préféré
- **definition**, **scope_note** : Définitions et notes
- **status** : Statut du concept (approved, candidate, deprecated)
- **is_top_concept** : Marqueur pour les concepts de tête

### Tables de libellés

#### `xl_labels`
Libellés étendus SKOS-XL avec URI :
- **uri** : URI unique du libellé
- **label_type** : prefLabel, altLabel, hiddenLabel
- **literal_form** : Forme textuelle du libellé

#### `alternative_labels`
Libellés alternatifs simples (synonymes, variantes) :
- **label** : Forme textuelle
- **relation_type** : Type de relation (synonym, abbreviation, etc.)

### Tables de relations

#### `hierarchical_relations`
Relations hiérarchiques (TG/TS) :
- **broader_concept_id** : Concept générique
- **narrower_concept_id** : Concept spécifique
- **relation_type** : Type (generic, partitive, instance)

#### `associative_relations`
Relations associatives (TA/RT) :
- **concept1_id**, **concept2_id** : Concepts reliés
- **relation_subtype** : Sous-type de relation
- **relation_uri** : URI spécifique (ex: ginco:TermeAssocie)

#### `mapping_relations`
Relations de mapping inter-thésaurus :
- **mapping_type** : exactMatch, closeMatch, broadMatch, etc.
- **target_uri** : URI du concept cible externe

### Tables de support

#### `collections`
Collections SKOS pour regrouper des concepts

#### `external_alignments`
Alignements avec vocabulaires externes

#### `concept_history`
Historique des modifications

## Utilisation

### 1. Migration
```bash
php artisan migrate
```

### 2. Import de données exemples
```bash
php artisan db:seed --class=ThesaurusSeeder
```

### 3. Import de fichier RDF
```bash
php artisan thesaurus:import /path/to/thesaurus.rdf
```

## Exemple d'utilisation multi-thésaurus

```sql
-- Créer un thésaurus géographique
INSERT INTO concept_schemes (identifier, title, language) 
VALUES ('GEO', 'Thésaurus géographique', 'fr');

-- Créer un thésaurus thématique
INSERT INTO concept_schemes (identifier, title, language) 
VALUES ('THEM', 'Thésaurus thématique', 'fr');

-- Ajouter des concepts dans chaque thésaurus
INSERT INTO concepts (concept_scheme_id, notation, preferred_label) 
VALUES 
  (1, 'GEO-FR', 'France'),
  (2, 'THEM-EDU', 'Éducation');
```

## Avantages de cette structure

1. **Extensibilité** : Facilement extensible pour de nouveaux types de thésaurus
2. **Interopérabilité** : Conforme aux standards SKOS et ISO-25964
3. **Flexibilité** : Support des métadonnées riches et des propriétés personnalisées
4. **Performance** : Index optimisés pour les recherches fréquentes
5. **Traçabilité** : Historique des modifications et versioning
6. **Multilingue** : Support natif de plusieurs langues

Cette structure permet d'importer directement les données du fichier RDF "Liste d'autorité Typologie documentaire" et d'autres thésaurus similaires, tout en offrant la possibilité d'ajouter de nouveaux thésaurus géographiques, thématiques, etc.
