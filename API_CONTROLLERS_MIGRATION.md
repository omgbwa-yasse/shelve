# API Controllers Migration - Public Controllers

## Vue d'ensemble

Cette migration consiste à déplacer toutes les méthodes API des contrôleurs publics vers des contrôleurs dédiés dans le namespace `App\Http\Controllers\Api\`. Cette approche améliore la séparation des préoccupations, la maintenabilité et l'organisation du code.

## Contrôleurs migrés

### 1. PublicUserApiController
**Fichier :** `app/Http/Controllers/Api/PublicUserApiController.php`
**Migration depuis :** `PublicUserController`

**Méthodes API migrées :**
- `login()` (ex `apiLogin()`)
- `register()` (ex `apiRegister()`)
- `logout()` (ex `apiLogout()`)
- `verifyToken()` (ex `apiVerifyToken()`)
- `forgotPassword()` (ex `apiForgotPassword()`)
- `resetPassword()` (ex `apiResetPassword()`)
- `updateProfile()` (ex `apiUpdateProfile()`)

**Améliorations :**
- Gestion d'erreurs centralisée avec helpers
- Constants pour les règles de validation
- Transformation standardisée des données utilisateur
- Messages de réponse constants
- Type hints et documentation améliorée

### 2. PublicDocumentRequestApiController
**Fichier :** `app/Http/Controllers/Api/PublicDocumentRequestApiController.php`
**Migration depuis :** `PublicDocumentRequestController`

**Méthodes API migrées :**
- `store()` (ex `apiStore()`)
- `index()` (ex `apiIndex()`)
- `show()` (ex `apiShow()`)

**Améliorations :**
- Transformation riche des données de demande
- Labels français pour les statuts et niveaux d'urgence
- Formatage des dates pour l'interface utilisateur
- Gestion des réponses associées

### 3. PublicFeedbackApiController
**Fichier :** `app/Http/Controllers/Api/PublicFeedbackApiController.php`
**Migration depuis :** `PublicFeedbackController`

**Méthodes API migrées :**
- `store()` (ex `apiStore()`)
- `index()` (ex `apiIndex()`)

**Améliorations :**
- Labels français pour les types et priorités
- Transformation des commentaires associés
- Métadonnées enrichies (compteurs, statuts)

### 4. PublicChatApiController
**Fichier :** `app/Http/Controllers/Api/PublicChatApiController.php`
**Migration depuis :** `PublicChatController`

**Méthodes API migrées :**
- `conversations()` (ex `apiConversations()`)
- `createConversation()` (ex `apiCreateConversation()`)
- `messages()` (ex `apiMessages()`)
- `sendMessage()` (ex `apiSendMessage()`)

**Améliorations :**
- Transformation complète des données de conversation
- Gestion des participants avec informations détaillées
- Formatage des messages avec métadonnées utilisateur
- Données de derniers messages pour les listes

## Routes API mises à jour

**Fichier :** `routes/api.php`

Toutes les routes API ont été mises à jour pour pointer vers les nouveaux contrôleurs :

```php
// Avant
Route::post('users/login', [PublicUserController::class, 'apiLogin']);

// Après
Route::post('users/login', [PublicUserApiController::class, 'login']);
```

## Structure standardisée

### Helpers de réponse
Tous les contrôleurs API incluent des helpers standardisés :
- `successResponse(string $message, $data = null, int $status = 200)`
- `errorResponse(string $message, int $status = 400)`

### Constantes de validation
Règles de validation définies en constantes pour éviter la duplication :
```php
private const REQUIRED_STRING = 'required|string';
private const REQUIRED_STRING_MAX_255 = 'required|string|max:255';
```

### Transformation des données
Méthodes privées de transformation pour standardiser les réponses :
- `transformUser($user)` : Données utilisateur
- `transformDocumentRequest($request)` : Demandes de documents
- `transformFeedback($feedback)` : Commentaires
- `transformConversation($conversation)` : Conversations chat

## Contrôleurs existants

Les contrôleurs originaux conservent leurs méthodes API avec des annotations `@deprecated` pour indiquer qu'elles doivent être remplacées par les nouvelles versions.

### Statut de dépréciation
- ✅ **PublicRecordController** - Déjà migré vers `PublicRecordApiController`
- ✅ **PublicEventController** - Déjà migré vers `PublicEventApiController`
- ✅ **PublicNewsController** - Déjà migré vers `PublicNewsApiController`
- ✅ **PublicUserController** - Migré vers `PublicUserApiController`
- ✅ **PublicDocumentRequestController** - Migré vers `PublicDocumentRequestApiController`
- ✅ **PublicFeedbackController** - Migré vers `PublicFeedbackApiController`
- ✅ **PublicChatController** - Migré vers `PublicChatApiController`

## Points d'attention

### 1. Backward Compatibility
Les anciennes méthodes API sont conservées temporairement avec des annotations `@deprecated`. Elles peuvent être supprimées après confirmation que le frontend utilise exclusivement les nouvelles routes.

### 2. Tests
Les tests existants doivent être mis à jour pour utiliser les nouvelles routes et contrôleurs API.

### 3. Frontend
Le frontend React doit être mis à jour pour utiliser les nouvelles structures de réponse API si nécessaire.

### 4. Documentation API
La documentation API (si elle existe) doit être mise à jour pour refléter les nouveaux endpoints et structures de réponse.

## Prochaines étapes

1. **Tests** : Créer/mettre à jour les tests pour les nouveaux contrôleurs API
2. **Frontend** : Vérifier que le frontend React fonctionne avec les nouveaux endpoints
3. **Suppression** : Supprimer les méthodes dépréciées après validation complète
4. **Documentation** : Mettre à jour la documentation API complète
5. **Monitoring** : Surveiller les performances et erreurs des nouvelles APIs

## Bénéfices de cette migration

1. **Séparation claire** : API et Web séparés
2. **Maintenabilité** : Code plus organisé et modulaire
3. **Consistance** : Réponses API standardisées
4. **Évolutivité** : Plus facile d'ajouter de nouvelles fonctionnalités API
5. **Testabilité** : Tests d'API plus ciblés et efficaces
6. **Performance** : Optimisations spécifiques aux APIs possible
