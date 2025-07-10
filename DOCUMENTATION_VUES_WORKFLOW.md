# Documentation des Vues WorkflowTemplate

## Vue d'ensemble

Ce document décrit les fonctionnalités et améliorations apportées aux vues du module WorkflowTemplate pour la gestion avancée de la configuration JSON des workflows.

## Structure des Vues

### 1. Vue Index (`index.blade.php`)

**Fonctionnalités principales :**
- Affichage de la liste des templates de workflow
- Filtrage par catégorie, statut et recherche textuelle
- Affichage du nombre d'étapes en base de données et en configuration JSON
- Actions CRUD sur les templates (voir, modifier, activer/désactiver, dupliquer, supprimer)

**Colonnes spécialisées :**
- **Étapes DB** : Nombre d'étapes stockées en base de données
- **Config JSON** : Nombre d'étapes définies dans la configuration JSON
  - Badge vert avec nombre si configuration existe
  - Badge gris avec "-" si aucune configuration

**Actions disponibles :**
- Voir le détail du template
- Modifier le template
- Activer/désactiver le template
- Dupliquer le template
- Supprimer le template (avec vérification des instances actives)

### 2. Vue Détail (`show.blade.php`)

**Structure par onglets :**

#### Onglet "Étapes du workflow"
- Affichage des étapes stockées en base de données
- Informations détaillées pour chaque étape (nom, description, type, assignations)
- Actions d'édition et suppression des étapes
- Possibilité de réorganiser les étapes

#### Onglet "Configuration JSON"
- **Éditeur interactif** pour la configuration JSON
- **Validation en temps réel** de la structure JSON
- **Import/Export** de configurations
- **Synchronisation** avec les étapes de la base de données

**Fonctionnalités de l'éditeur JSON :**
- Ajout d'étapes via interface graphique
- Édition des étapes existantes dans un modal
- Suppression d'étapes avec confirmation
- Validation de la configuration (unicité des IDs, ordres, etc.)
- Sauvegarde directe via API
- Aperçu JSON formaté
- Import depuis les étapes de la base
- Export vers fichier JSON
- Réinitialisation de la configuration

**Interface utilisateur :**
- Cartes Bootstrap pour chaque étape configurée
- Badges pour indiquer l'ordre et le statut
- Modaux pour l'édition détaillée
- Alertes contextuelles pour les actions

### 3. Vue Création (`create.blade.php`)

**Formulaire de base :**
- Nom, catégorie, description
- Statut actif/inactif

**Section Configuration JSON (optionnelle) :**
- Case à cocher pour activer la configuration JSON
- Éditeur de texte avec coloration syntaxique
- Validation JSON en temps réel
- Formatage automatique du JSON
- Exemples pré-configurés
- Import depuis fichier JSON

**Fonctionnalités JavaScript :**
- Validation de la structure JSON
- Vérification de l'unicité des IDs et ordres
- Conversion automatique en données de formulaire Laravel
- Gestion des erreurs avec alertes utilisateur

### 4. Vue Édition (`edit.blade.php`)

**Reprend toutes les fonctionnalités de création avec :**
- Pré-remplissage des données existantes
- Affichage de la configuration actuelle
- Possibilité de désactiver la configuration JSON
- Réinitialisation vers la configuration sauvegardée
- Import depuis les étapes existantes
- Export de la configuration actuelle

## Fonctionnalités JavaScript Avancées

### Validation JSON
```javascript
function validateJSONConfig() {
    // Validation de la structure JSON
    // Vérification des champs obligatoires
    // Contrôle de l'unicité des IDs et ordres
    // Affichage des erreurs et avertissements
}
```

### Gestion des Étapes (Vue Show)
```javascript
// Ajout d'une étape
window.addConfigurationStep = function() { ... }

// Édition d'une étape dans un modal
window.editConfigurationStep = function(index) { ... }

// Suppression avec confirmation
window.removeConfigurationStep = function(index) { ... }

// Sauvegarde via API
window.saveConfiguration = function() { ... }
```

### Import/Export
```javascript
// Import depuis fichier JSON
function importFromFile(event) { ... }

// Export vers fichier JSON
function exportConfiguration() { ... }

// Import depuis étapes DB
window.importFromSteps = function() { ... }
```

## Structure de la Configuration JSON

### Format Attendu
```json
[
    {
        "id": "step_unique_id",
        "name": "Nom de l'étape",
        "organisation_id": 1,
        "action_id": 1,
        "ordre": 1,
        "auto_assign": false,
        "timeout_hours": 24,
        "conditions": {},
        "metadata": {}
    }
]
```

### Champs Obligatoires
- `id` : Identifiant unique de l'étape
- `name` : Nom de l'étape
- `action_id` : ID de l'action associée
- `ordre` : Ordre d'exécution (entier ≥ 1)

### Champs Optionnels
- `organisation_id` : ID de l'organisation responsable
- `auto_assign` : Assignation automatique (booléen)
- `timeout_hours` : Délai d'expiration en heures
- `conditions` : Conditions d'exécution (objet JSON)
- `metadata` : Métadonnées personnalisées (objet JSON)

## Validations Métier

### Côté Client (JavaScript)
- Validation de la syntaxe JSON
- Vérification des champs obligatoires
- Contrôle de l'unicité des IDs
- Contrôle de l'unicité des ordres

### Côté Serveur (Laravel)
- Validation des types de données
- Vérification de l'existence des références (action_id, organisation_id)
- Validation métier via `performConfigurationValidation()`
- Contrôles d'intégrité lors de la sauvegarde

## API Endpoints Utilisés

### Lecture
- `GET /api/workflows/templates/{template}/configuration` - Récupérer la configuration
- `POST /api/workflows/templates/{template}/configuration/validate` - Valider la configuration

### Écriture
- `PUT /api/workflows/templates/{template}/configuration` - Mettre à jour la configuration complète
- `POST /api/workflows/templates/{template}/configuration/steps` - Ajouter une étape
- `PUT /api/workflows/templates/{template}/configuration/steps/{stepId}` - Modifier une étape
- `DELETE /api/workflows/templates/{template}/configuration/steps/{stepId}` - Supprimer une étape
- `PUT /api/workflows/templates/{template}/configuration/reorder` - Réorganiser les étapes

## Intégration Bootstrap & Icons

### Composants Utilisés
- **Cards** : Organisation du contenu
- **Tabs** : Navigation entre étapes DB et config JSON
- **Modals** : Édition des étapes
- **Alerts** : Feedback utilisateur
- **Badges** : Statuts et compteurs
- **Buttons & Dropdowns** : Actions utilisateur

### Icônes Bootstrap
- `bi-code-square` : Configuration JSON
- `bi-check-circle` : Validation
- `bi-save` : Sauvegarde
- `bi-upload/download` : Import/Export
- `bi-plus-lg` : Ajout
- `bi-pencil` : Édition
- `bi-trash` : Suppression

## Accessibilité et UX

### Améliorations UX
- Feedback immédiat sur les actions
- Confirmations pour les actions destructives
- Auto-hide des messages de succès
- Validation en temps réel
- Formatage automatique du JSON

### Gestion des Erreurs
- Messages d'erreur contextuels
- Validation côté client et serveur
- Rollback en cas d'erreur API
- Préservation des données utilisateur

## Exemple d'Utilisation

### Création d'un Template avec Configuration JSON

1. **Accéder au formulaire de création**
   - Aller sur `/workflows/templates/create`
   
2. **Remplir les informations de base**
   - Nom, catégorie, description

3. **Activer la configuration JSON**
   - Cocher "Définir une configuration JSON personnalisée"

4. **Définir la configuration**
   - Utiliser l'éditeur ou importer un fichier JSON
   - Valider la structure avec le bouton "Valider JSON"

5. **Sauvegarder**
   - Le formulaire convertit automatiquement le JSON en données Laravel

### Modification d'une Configuration Existante

1. **Accéder aux détails du template**
   - Aller sur `/workflows/templates/{id}`

2. **Onglet Configuration JSON**
   - Cliquer sur l'onglet "Configuration JSON"

3. **Utiliser l'éditeur interactif**
   - Ajouter/modifier/supprimer des étapes
   - Valider les modifications
   - Sauvegarder via l'API

## Sécurité

### Protection CSRF
- Tous les formulaires incluent `@csrf`
- Les requêtes AJAX incluent le token CSRF

### Permissions
- Utilisation des Gates et Policies Laravel
- Contrôle d'accès par action (create, update, delete)
- Vérification des autorisations avant affichage des boutons

### Validation des Données
- Validation côté client (UX)
- Validation côté serveur (sécurité)
- Sanitisation des entrées utilisateur
- Contrôle des types de données

## Performance

### Optimisations
- Chargement lazy des configurations JSON
- Requêtes AJAX pour les actions ponctuelles
- Pagination sur la liste des templates
- Mise en cache des validations côté client

### Bonnes Pratiques
- Éviter les requêtes N+1 avec `withCount()`
- Utilisation d'index sur les champs fréquemment utilisés
- Limitation de la taille des configurations JSON
- Timeout sur les requêtes AJAX

## Maintenance et Évolutions

### Points d'Extension
- Ajout de nouveaux types de validation
- Extension de la structure JSON
- Nouveaux types d'import/export
- Intégration avec d'autres modules

### Logging et Debug
- Logs des actions critiques
- Validation des erreurs côté serveur
- Debugging facilité avec les messages explicites
- Traçabilité des modifications de configuration

Ce système offre une solution complète et robuste pour la gestion des configurations JSON de workflows, avec une interface utilisateur intuitive et des fonctionnalités avancées d'import/export et de validation.
