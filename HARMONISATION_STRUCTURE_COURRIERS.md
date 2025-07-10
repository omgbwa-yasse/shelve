# Harmonisation de la structure des pages de création de courriers

## Objectif
Appliquer la même structure de la page `/mails/send/create` à la page `/mails/received/create` pour assurer une cohérence dans l'interface utilisateur.

## Modifications apportées

### 1. Structure générale
**Avant :** Structure avec cartes (cards) séparées pour chaque section
**Après :** Structure unifiée sans cartes, similaire à la page des courriers sortants

### 2. En-tête et titre
- **Avant :** `<h1 class="h3 mb-4">Créer un courrier entrant</h1>`
- **Après :** `<h1 class="mb-4">Créer Courrier entrant</h1>`
- Suppression de la classe `py-4` du container

### 3. Organisation des champs
**Nouveau layout :**
```html
<div class="row">
    <h5 class="card-title mb-4">Informations générales</h5>
    
    <div class="col-md-4 mb-3">
        <!-- Référence -->
    </div>
    <div class="col-md-4 mb-3">
        <!-- Date -->
    </div>
    <div class="col-md-4 mb-3">
        <!-- Typologie -->
    </div>
</div>
```

### 4. Ajout du système de type d'expéditeur
**Nouvelle fonctionnalité :** Boutons radio pour sélectionner le type d'expéditeur
- Interne
- Contact externe  
- Organisation externe

### 5. Gestion dynamique des sections
**JavaScript ajouté :**
- Affichage/masquage des sections selon le type d'expéditeur sélectionné
- Gestion des champs requis dynamiquement
- Validation des formulaires

### 6. Structure des champs d'expéditeur
**Nouvelles sections conditionnelles :**
- `internal_sender` : Pour les expéditeurs internes
- `external_contact_sender` : Pour les contacts externes
- `external_organization_sender` : Pour les organisations externes

### 7. Simplification de la section pièces jointes
**Avant :** Dans une carte séparée avec description
**Après :** Section directe avec label standard

### 8. Bouton de soumission
**Avant :** `<i class="bi bi-check-lg me-2"></i>Créer le courrier`
**Après :** `<i class="bi bi-inbox"></i> Créer le courrier entrant`

## Nouvelles variables attendues dans le contrôleur

Le contrôleur doit maintenant fournir ces variables :
```php
- $senderOrganisations  // Pour la liste des organisations internes
- $users               // Pour la liste des utilisateurs
- $externalContacts    // Pour la liste des contacts externes
- $externalOrganizations // Pour la liste des organisations externes
```

## Fonctionnalités JavaScript

### 1. Gestion des types d'expéditeur
```javascript
function handleSenderTypeChange() {
    // Affiche/masque les sections selon le type sélectionné
    // Gère les attributs 'required' dynamiquement
}
```

### 2. Gestion des fichiers (drag & drop)
- Upload par glisser-déposer
- Validation de taille (10MB max par fichier)
- Limitation du nombre de fichiers (5 max)
- Prévisualisation et suppression

### 3. Validation du formulaire
- Validation HTML5
- Contrôles personnalisés pour les fichiers
- Affichage des erreurs

## Classes CSS utilisées

### Structure responsive
- `row` / `col-md-*` pour la grille Bootstrap
- `mb-3`, `mb-4`, `mt-3` pour les marges

### Formulaires
- `form-label`, `form-control`, `form-select`
- `form-check`, `form-check-input`, `form-check-label`
- `needs-validation`, `was-validated`

### Affichage conditionnel
- `d-none` pour masquer les sections
- `sender-field`, `internal-field`, `external-contact-field`, `external-org-field`

## Cohérence avec la page des courriers sortants

✅ **Structure HTML identique**
✅ **Style et disposition similaires**
✅ **Fonctionnalités JavaScript équivalentes**
✅ **Validation de formulaire unifiée**
✅ **Gestion des pièces jointes identique**

## Tests recommandés

1. **Affichage des sections** : Vérifier que les sections s'affichent/se masquent correctement
2. **Validation** : Tester la validation des champs requis
3. **Upload de fichiers** : Tester le drag & drop et la validation des fichiers
4. **Responsive** : Vérifier l'affichage sur mobile/tablette
5. **Données** : S'assurer que le contrôleur fournit toutes les variables nécessaires

## Prochaines étapes

1. Mettre à jour le contrôleur pour fournir les nouvelles variables
2. Tester l'interface utilisateur
3. Valider la soumission du formulaire
4. Appliquer la même harmonisation aux pages d'édition si nécessaire
