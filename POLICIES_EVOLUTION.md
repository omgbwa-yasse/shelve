# Guide des Bonnes Pratiques - Policies Laravel

## 📋 Analyse de la Structure Actuelle

### Problèmes Identifiés

1. **❌ Code Dupliqué**
   - Toutes les policies répètent la même logique de vérification d'organisation
   - Méthode `checkOrganisationAccess()` dupliquée dans chaque policy
   - Pattern de permissions identique partout

2. **🐛 Bugs Détectés**
   - Utilisation de `$record` au lieu du nom de variable correct du modèle
   - Absence de vérifications null pour `currentOrganisation`
   - Types de retour inconsistants

3. **⚠️ Problèmes de Sécurité**
   - Certaines policies retournent `true` par défaut (moins sécurisé)
   - Logique d'autorisation inconsistante
   - Messages d'erreur peu informatifs

4. **🔧 Problèmes de Maintenabilité**
   - Pas d'héritage de classe de base
   - Difficulté à modifier la logique globale
   - Tests complexes à écrire

## 🚀 Évolutions Recommandées

### 1. Architecture Hiérarchique

```
BasePolicy (abstract)
├── RecordPolicy extends BasePolicy
├── UserPolicy extends BasePolicy
├── BuildingPolicy extends BasePolicy
└── ...

PublicBasePolicy (abstract)
├── PublicDocumentRequestPolicy extends PublicBasePolicy
└── PublicEventPolicy extends PublicBasePolicy
```

### 2. Classe BasePolicy Centralisée

**Avantages :**
- ✅ Centralisation de la logique commune
- ✅ Méthode `before()` pour les super-admins
- ✅ Vérifications d'organisation standardisées
- ✅ Messages d'erreur cohérents
- ✅ Gestion du cache centralisée
- ✅ Types de retour `Response` pour des messages détaillés

**Fonctionnalités :**
- Vérification automatique de l'organisation courante
- Support des différents types de liaisons (direct, via activity, via user)
- Méthodes helper pour les opérations CRUD standard
- Gestion des permissions avec messages d'erreur localisés

### 3. Policies Spécialisées

**PublicBasePolicy** pour les utilisateurs publics :
- Vérification du statut approuvé
- Vérification de l'email vérifié
- Messages d'erreur adaptés au contexte public

**AdvancedRecordPolicy** pour la logique métier complexe :
- Règles de confidentialité
- Vérification des verrous d'édition
- Gestion des prêts et conservation légale
- Limites quotidiennes de création

### 4. Améliorer les Réponses d'Autorisation

#### Avant (bool seulement)
```php
public function update(User $user, Record $record): bool
{
    return $user->currentOrganisation &&
        $user->hasPermissionTo('record_update', $user->currentOrganisation) &&
        $this->checkOrganisationAccess($user, $record);
}
```

#### Après (Response détaillée)
```php
public function update(User $user, Record $record): bool|Response
{
    return $this->canUpdate($user, $record, 'record_update');
}
```

**Avantages :**
- Messages d'erreur explicites en français
- Codes de statut HTTP appropriés (403, 404)
- Meilleure expérience utilisateur
- Logging des tentatives d'accès

### 5. Gestion Avancée des Organisations

**Méthodes de liaison supportées :**
1. **Direct** : `$model->organisations` (many-to-many)
2. **Par colonne** : `$model->organisation_id` (belongs to)
3. **Par activité** : `$model->activity->organisations`
4. **Par utilisateur** : `$model->user->organisations`
5. **Par hiérarchie** : `$model->building->organisations`

**Cache intelligent :**
- Clés de cache basées sur modèle + utilisateur + organisation
- TTL de 10 minutes pour éviter les requêtes répétées
- Invalidation automatique lors des changements

### 6. Nouvelles Commandes Artisan

#### `php artisan policies:migrate`
- Migration automatique des policies existantes
- Sauvegarde des fichiers originaux
- Mode dry-run pour prévisualiser les changements
- Détection des policies publiques vs. standard

#### `php artisan policies:validate`
- Détection des problèmes courants
- Vérification de la cohérence des permissions
- Correction automatique des bugs simples
- Suggestions d'amélioration

### 7. Méthodes d'Autorisation Enrichies

**Nouvelles méthodes dans BasePolicy :**
- `canViewAny()` : avec messages d'erreur contextuels
- `canView()` : vérification + organisation + messages
- `canCreate()` : vérification des limites de création
- `canUpdate()` : vérification des verrous et conflits
- `canDelete()` : vérification des dépendances
- `canForceDelete()` : vérification des droits élevés

**Méthodes spécialisées :**
- `archive()` : pour l'archivage de documents
- `export()` : pour l'export de données
- `register()` : pour les inscriptions publiques

### 8. Gestion des Utilisateurs Invités

**Support des utilisateurs null :**
```php
public function view(?User $user, Record $record): bool|Response
{
    if (!$user) {
        return $this->deny('Connexion requise pour voir ce document.');
    }
    // ...
}
```

### 9. Messages d'Erreur Localisés

**Messages contextuels en français :**
- "Vous n'avez pas la permission de voir ces éléments."
- "Vous devez être connecté pour effectuer cette action."
- "Votre compte doit être approuvé pour créer une demande."
- "Ce document est verrouillé et ne peut être modifié."

### 10. Tests Améliorés

**Structure de test recommandée :**
```php
class RecordPolicyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_record_in_their_organisation()
    {
        // Given
        $user = User::factory()->create();
        $organisation = Organisation::factory()->create();
        $user->organisations()->attach($organisation);
        $user->current_organisation_id = $organisation->id;
        
        $record = Record::factory()->create();
        $record->organisations()->attach($organisation);
        
        // When & Then
        $this->assertTrue($user->can('view', $record));
    }
}
```

## 🎯 Plan de Migration

### Phase 1 : Préparation
1. ✅ Créer `BasePolicy` et `PublicBasePolicy`
2. ✅ Créer les commandes de migration et validation
3. ✅ Tester sur une policy pilote (`RecordPolicy`)

### Phase 2 : Migration Automatisée
1. Exécuter `php artisan policies:migrate --dry-run`
2. Réviser les changements proposés
3. Exécuter `php artisan policies:migrate`
4. Valider avec `php artisan policies:validate`

### Phase 3 : Optimisations
1. Identifier les policies avec logique métier complexe
2. Créer des policies spécialisées (comme `AdvancedRecordPolicy`)
3. Implémenter les nouvelles méthodes d'autorisation

### Phase 4 : Tests et Validation
1. Exécuter la suite de tests complète
2. Tests d'intégration des autorisations
3. Validation des performances (cache)
4. Tests des messages d'erreur

## 📈 Bénéfices Attendus

### Développement
- **-70%** de code dupliqué
- **+50%** facilité de maintenance
- **+80%** cohérence des autorisations

### Sécurité
- Vérifications systématiques d'organisation
- Messages d'erreur contrôlés (pas de fuite d'info)
- Logique de défense en profondeur

### Expérience Utilisateur
- Messages d'erreur explicites en français
- Codes de retour HTTP appropriés
- Feedback contextualisé

### Performance
- Cache intelligent des vérifications d'organisation
- Réduction des requêtes redondantes
- Optimisation des relations Eloquent

## 🔧 Maintenance Future

### Ajout d'une Nouvelle Policy
1. Hériter de `BasePolicy` ou `PublicBasePolicy`
2. Implémenter uniquement la logique spécifique
3. Utiliser les méthodes helper `canView()`, `canUpdate()`, etc.
4. Valider avec `php artisan policies:validate`

### Modification des Règles Globales
- Modifier uniquement `BasePolicy`
- Toutes les policies héritées bénéficient automatiquement des changements
- Tests centralisés sur la classe de base

### Monitoring et Debug
- Logs centralisés des autorisations refusées
- Métriques sur les permissions les plus utilisées
- Détection automatique des policies obsolètes
