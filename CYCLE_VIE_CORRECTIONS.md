# Corrections du Système de Cycle de Vie des Documents

## Problèmes identifiés et corrigés

### 1. **Logique de dates inversée**
- **Avant** : `>` au lieu de `<` pour vérifier si les durées sont écoulées
- **Après** : Utilisation correcte de `DATEDIFF(NOW(), records.date_end) > duration * 365` pour les durées écoulées

### 2. **Champs de dates incorrects**
- **Avant** : utilisation de `created_at` (date de création)
- **Après** : utilisation de `date_end` (date de fin du document) qui est la référence correcte pour les calculs de rétention

### 3. **Code de sort incorrect**
- **Avant** : utilisation de code 'D' pour élimination (inexistant)
- **Après** : utilisation de code 'E' pour élimination (conforme au SortSeeder)

### 4. **Contrainte de validation sur les sorts**
- **Nouveau** : Colonne `code` restreinte aux valeurs E, T, C uniquement
- **Migration** : `2025_07_30_192529_update_sorts_table_restrict_code_values.php`
- **Validation** : Ajout de validation côté modèle et contrôleur

## Fonctionnalités corrigées

### 1. **recordToTransfer()** - Documents à transférer aux archives
- **Logique** : `(date_end + durée_communicabilité) < aujourd'hui`
- **Utilise** : Table `communicabilities` pour la durée de communicabilité

### 2. **recordToStore()** - Documents à archiver définitivement  
- **Logique** : `Sort = C ET (date_end + durée_rétention) < aujourd'hui`
- **Action** : Archivage définitif après expiration de la rétention

### 3. **recordToEliminate()** - Documents à éliminer
- **Logique** : `Sort = E ET (date_end + durée_rétention) < aujourd'hui`
- **Action** : Élimination après expiration de la rétention

### 4. **recordToKeep()** - Documents à conserver
- **Logique** : `Sort = C ET (date_end + durée_rétention) > aujourd'hui`
- **Action** : Conservation pendant la période de rétention active

### 5. **recordToSort()** - Documents à trier
- **Logique** : `Sort = T ET (date_end + durée_rétention) < aujourd'hui`
- **Action** : Tri requis après expiration de la rétention

### 6. **recordToRetain()** - Documents en rétention active
- **Logique** : `Sort = C ET (date_end + durée_rétention) > aujourd'hui`
- **Action** : Conservation active pendant la période de rétention

## Améliorations techniques

### 1. **Refactorisation du contrôleur**
- **Classe renommée** : `lifeCycleController` → `LifeCycleController` (conforme PSR)
- **Constantes** : Ajout de constantes pour éviter la duplication de code
- **Méthodes privées** : Factorisation des requêtes communes et données de vue

### 2. **Validation des sorts**
- **Base de données** : Contrainte ENUM sur la colonne `code`
- **Modèle** : Validation dans le boot() du modèle Sort
- **Contrôleur** : Règle de validation `in:E,T,C`
- **Vues** : Select boxes au lieu de champs texte libres

### 3. **Relations de base de données**
- **Optimisation** : Utilisation de JOIN plutôt que whereHas pour de meilleures performances
- **Eager loading** : Chargement des relations `activity`, `status`, `level`, `user`

## Codes de sort standardisés

- **E** : Élimination - Documents à détruire après la durée légale
- **T** : Tri/Transfert - Documents nécessitant un tri ou transfert
- **C** : Conservation - Documents à archiver définitivement

## Calculs temporels

- **Communicabilité** : Basée sur `communicabilities.duration` en années
- **Rétention** : Basée sur `retentions.duration` en années  
- **Référence** : `records.date_end` comme point de départ des calculs
- **Formule** : `DATEDIFF(NOW(), records.date_end) [>|<=] duration * 365`

Toutes les corrections respectent maintenant la logique archivistique standard et les relations de base de données existantes.
