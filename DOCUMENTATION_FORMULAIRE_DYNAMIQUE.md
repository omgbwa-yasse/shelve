# Formulaire Dynamique de Configuration des Étapes - WorkflowTemplate

## Vue d'ensemble

Cette documentation décrit les nouvelles fonctionnalités de formulaire dynamique ajoutées aux vues de création et d'édition des templates de workflow. Le système permet maintenant de créer et modifier la configuration JSON via un formulaire intuitif avec des lignes d'étapes dynamiques.

## Fonctionnalités Principales

### 1. Interface à Onglets

Le système utilise maintenant une interface à onglets Bootstrap pour offrir deux modes de configuration :

#### Onglet "Formulaire"
- Interface graphique intuitive
- Lignes d'étapes avec champs séparés
- Validation en temps réel
- Bouton "+" pour ajouter des étapes
- Boutons de suppression individuels

#### Onglet "JSON Avancé"
- Éditeur de texte JSON traditionnel
- Fonctionnalités d'import/export
- Validation syntaxique
- Formatage automatique

### 2. Structure du Formulaire Dynamique

#### En-têtes des Colonnes
```
| ID Étape | Nom | Ordre | Action ID | Org. ID | Auto | Actions |
```

#### Champs par Ligne d'Étape
1. **ID Étape** (obligatoire) - Identifiant unique de l'étape
2. **Nom** (obligatoire) - Nom descriptif de l'étape
3. **Ordre** (obligatoire) - Numéro d'ordre d'exécution
4. **Action ID** (obligatoire) - ID de l'action Workflow associée
5. **Org. ID** (optionnel) - ID de l'organisation responsable
6. **Auto** (checkbox) - Assignation automatique
7. **Actions** - Bouton de suppression de la ligne

### 3. Fonctionnalités JavaScript

#### Gestion Dynamique des Lignes
```javascript
// Ajouter une nouvelle ligne d'étape
window.addStepRow = function(stepData = null)

// Supprimer une ligne d'étape
window.removeStepRow = function(rowId)

// Vider toutes les étapes
window.clearAllSteps = function()
```

#### Validation du Formulaire
```javascript
// Valider la configuration du formulaire
window.validateFormConfig = function()

// Récupérer les données des étapes
function getFormSteps()

// Valider les étapes (unicité, continuité)
function validateSteps(steps)
```

#### Synchronisation Entre Onglets
```javascript
// Aperçu du formulaire en JSON
window.previewFormAsJSON = function()

// Synchroniser depuis le formulaire vers JSON
window.syncFromForm = function()

// Charger la configuration existante (mode édition)
window.loadFromExisting = function()
```

### 4. Fonctionnalités Spécifiques par Vue

#### Vue Création (`create.blade.php`)
- Ligne vide par défaut
- Génération automatique des IDs (step_1, step_2, etc.)
- Calcul automatique du prochain ordre disponible
- Conversion automatique en format Laravel lors de la soumission

#### Vue Édition (`edit.blade.php`)
- Chargement automatique de la configuration existante
- Bouton "Charger configuration actuelle"
- Possibilité de réinitialiser vers la version sauvegardée
- Import depuis les étapes de la base de données

### 5. Validation et Contrôles

#### Validation Côté Client
- **Unicité des IDs** - Vérification que chaque étape a un ID unique
- **Unicité des ordres** - Vérification que chaque ordre n'est utilisé qu'une fois
- **Continuité des ordres** - Avertissement si les ordres ne sont pas continus (1, 2, 3...)
- **Champs obligatoires** - Validation HTML5 et JavaScript

#### Types de Messages
- **Erreurs** (rouge) - Empêchent la validation
- **Avertissements** (orange) - N'empêchent pas la validation
- **Succès** (vert) - Confirmation des actions

### 6. Conversion des Données

#### Du Formulaire vers Laravel
```javascript
function convertFormToLaravel(form) {
    const steps = getFormSteps();
    
    steps.forEach((step, index) => {
        Object.keys(step).forEach(key => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `configuration[${index}][${key}]`;
            input.value = typeof step[key] === 'object' ? JSON.stringify(step[key]) : step[key];
            form.appendChild(input);
        });
    });
}
```

#### Structure de Données Générée
```json
[
    {
        "id": "step_1",
        "name": "Première étape",
        "ordre": 1,
        "action_id": 1,
        "organisation_id": null,
        "auto_assign": false,
        "timeout_hours": 24,
        "conditions": {},
        "metadata": {}
    }
]
```

### 7. Interface Utilisateur

#### Styles et Design
- **CSS personnalisé** dans `public/css/workflow-templates.css`
- **Animations** pour l'ajout/suppression de lignes
- **Responsive design** pour les appareils mobiles
- **Tooltips** pour l'aide contextuelle

#### Interactions
- **Hover effects** sur les lignes d'étapes
- **Focus visible** pour l'accessibilité
- **Transitions fluides** pour les changements d'état
- **Loading states** pour les actions asynchrones

### 8. Gestion d'Erreurs

#### Validation en Temps Réel
```javascript
function validateSteps(steps) {
    const errors = [];
    const warnings = [];
    
    // Vérifications d'unicité
    // Vérifications de continuité
    // Vérifications de champs obligatoires
    
    return { errors, warnings };
}
```

#### Affichage des Erreurs
- Alertes Bootstrap colorées
- Messages descriptifs
- Icônes contextuelles
- Auto-hide pour les messages de succès

### 9. Accessibilité

#### Conformité Standards
- **Labels associés** aux contrôles
- **Navigation clavier** supportée
- **Lecteurs d'écran** compatibles
- **Contraste** suffisant pour la lisibilité

#### Améliorations
- Classes `.visually-hidden` pour le contexte
- Focus visible sur tous les éléments interactifs
- Messages d'erreur associés aux champs
- Structure sémantique HTML

### 10. Performance

#### Optimisations
- **Lazy loading** des données
- **Debouncing** pour la validation
- **Mémoire** optimisée pour les grandes configurations
- **DOM manipulation** efficace

#### Limitations
- Maximum recommandé : 50 étapes par configuration
- Validation différée pour éviter les blocages
- Scrolling automatique dans le container

## Exemples d'Utilisation

### Créer une Configuration Simple

1. **Activer la configuration JSON**
   - Cocher "Définir une configuration JSON personnalisée"

2. **Utiliser l'onglet Formulaire**
   - Remplir la première ligne d'étape
   - Cliquer sur "+" pour ajouter d'autres étapes

3. **Valider et sauvegarder**
   - Cliquer sur "Valider Configuration"
   - Soumettre le formulaire

### Importer une Configuration Existante

1. **Mode Édition**
   - Ouvrir un template existant en édition
   - Activer la configuration JSON

2. **Charger les données**
   - Onglet Formulaire > "Charger configuration actuelle"
   - Ou Onglet JSON > configuration pré-remplie

3. **Modifier et sauvegarder**
   - Ajuster les valeurs dans le formulaire
   - Valider et soumettre

### Synchronisation Entre Modes

1. **Du Formulaire vers JSON**
   - Remplir le formulaire
   - Cliquer sur "Aperçu JSON"
   - Basculement automatique vers l'onglet JSON

2. **Du JSON vers Formulaire**
   - Modifier le JSON dans l'onglet avancé
   - Sauvegarder les modifications
   - Recharger pour voir dans le formulaire

## Maintenance et Extensions

### Points d'Extension

1. **Nouveaux Types de Champs**
   - Ajouter des colonnes dans `createStepRow()`
   - Mettre à jour `getFormSteps()`
   - Ajuster la validation

2. **Validation Personnalisée**
   - Étendre `validateSteps()`
   - Ajouter des règles métier
   - Intégrer des API de validation

3. **Interface Améliorée**
   - Drag & drop pour réorganiser
   - Modals d'édition détaillée
   - Auto-complétion pour les champs

### Debugging

#### Outils de Debug
```javascript
// Afficher la configuration actuelle
console.log('Current steps:', getFormSteps());

// Vérifier la validation
console.log('Validation:', validateSteps(getFormSteps()));

// Examiner le DOM
console.log('Step rows:', document.querySelectorAll('.step-row'));
```

#### Messages de Log
- Actions utilisateur loggées
- Erreurs de validation détaillées
- Conversion de données tracée

Cette nouvelle interface offre une expérience utilisateur grandement améliorée pour la configuration des workflows, combinant simplicité d'utilisation et puissance de configuration.
