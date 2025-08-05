# Rate Limiting Documentation - Système SHELVE

## Configuration

Le système de rate limiting est configuré pour utiliser différents drivers de cache :

- **Cache principal** : `file` (stockage dans `storage/framework/cache/data/`)
- **Rate Limiter** : `database` (stockage dans la table `cache`)

### Variables d'environnement

```env
CACHE_STORE=file
CACHE_PREFIX=shelve
CACHE_LIMITER_STORE=database
```

## Service RateLimitService

Le service `App\Services\RateLimitService` centralise la gestion du rate limiting avec des limites prédéfinies :

### Limites configurées

| Action | Limite | Durée |
|--------|--------|-------|
| `communication_create` | 10 tentatives | 1 heure |
| `reservation_create` | 15 tentatives | 1 heure |
| `search` | 100 tentatives | 1 heure |
| `export` | 5 tentatives | 1 heure |
| `api_general` | 1000 tentatives | 1 heure |

### Utilisation du service

```php
use App\Services\RateLimitService;

public function store(Request $request, RateLimitService $rateLimitService)
{
    // Vérifier si l'utilisateur a dépassé la limite
    if ($rateLimitService->tooManyAttempts('communication_create')) {
        return redirect()->back()
            ->withErrors(['rate_limit' => $rateLimitService->getErrorMessage('communication_create')]);
    }

    // Exécuter l'action avec rate limiting
    $result = $rateLimitService->attempt(
        'communication_create',
        function() use ($validated) {
            // Votre logique métier ici
            return Communication::create($validated);
        }
    );

    if (!$result) {
        return redirect()->back()
            ->withErrors(['rate_limit' => 'Trop de tentatives']);
    }

    return redirect()->route('communications.show', $result);
}
```

## Middleware RateLimitMiddleware

Le middleware peut être appliqué aux routes pour une protection automatique :

```php
// Dans routes/api.php
Route::post('users/login', [Controller::class, 'login'])
    ->middleware('rate.limit:auth,5,60'); // 5 tentatives par heure

Route::apiResource('records', Controller::class)
    ->middleware('rate.limit:search,50,60'); // 50 recherches par heure
```

### Paramètres du middleware

1. **key** : Clé d'identification de l'action
2. **maxAttempts** : Nombre maximum de tentatives
3. **decayMinutes** : Durée en minutes avant réinitialisation

## Utilisation directe du Rate Limiter

Pour des besoins spécifiques, vous pouvez utiliser directement la facade :

```php
use Illuminate\Support\Facades\RateLimiter;

// Vérifier les tentatives
if (RateLimiter::tooManyAttempts('action:' . $userId, 10)) {
    $seconds = RateLimiter::availableIn('action:' . $userId);
    // Bloquer l'utilisateur
}

// Incrémenter le compteur
RateLimiter::increment('action:' . $userId, 1, 3600); // 1 heure

// Exécuter avec callback
$result = RateLimiter::attempt(
    'action:' . $userId,
    10, // max tentatives
    function() {
        // Votre logique
        return 'success';
    },
    3600 // 1 heure
);

// Nettoyer les tentatives
RateLimiter::clear('action:' . $userId);
```

## Protection des routes API

Toutes les routes API publiques sont protégées avec des limites appropriées :

### Routes d'authentification
- Connexion : 5 tentatives/heure
- Inscription : 3 tentatives/heure  
- Reset password : 3 tentatives/heure

### Routes de recherche
- Records : 50 recherches/heure
- API générale : 100 requêtes/heure

### Routes sécurisées
- Utilisateurs authentifiés : 200 requêtes/heure
- Demandes de documents : 20 demandes/heure

## Commandes Artisan

### Afficher les statistiques

```bash
# Statistiques générales
php artisan rate-limit:stats

# Statistiques d'un utilisateur
php artisan rate-limit:stats --user-id=1

# Effacer les limites d'une action
php artisan rate-limit:stats --clear=communication_create
```

## Monitoring et maintenance

### Vérification des tables de cache

La table `cache` stocke les données du rate limiter :

```sql
SELECT * FROM cache WHERE key LIKE '%rate_limit%';
```

### Nettoyage automatique

Les entrées de cache expirent automatiquement selon la durée configurée.

### Logs

Les tentatives de rate limiting peuvent être loggées :

```php
if (RateLimiter::tooManyAttempts($key, $max)) {
    Log::warning('Rate limit exceeded', [
        'user_id' => Auth::id(),
        'key' => $key,
        'ip' => request()->ip()
    ]);
}
```

## Bonnes pratiques

1. **Différencier les actions** : Utilisez des clés spécifiques pour chaque type d'action
2. **Ajuster les limites** : Adaptez selon l'usage réel de votre application
3. **Messages explicites** : Informez clairement l'utilisateur des limites
4. **Monitoring** : Surveillez les limites atteintes pour détecter les abus
5. **Graceful degradation** : Proposez des alternatives quand les limites sont atteintes

## Exemples d'intégration

### Dans un contrôleur
```php
class CommunicationController extends Controller
{
    public function store(Request $request, RateLimitService $rateLimitService)
    {
        $communication = $rateLimitService->attempt(
            'communication_create',
            fn() => $this->createCommunication($request->validated())
        );

        return $communication 
            ? redirect()->route('communications.show', $communication)
            : back()->withErrors(['rate_limit' => $rateLimitService->getErrorMessage('communication_create')]);
    }
}
```

### Dans un middleware de groupe
```php
Route::middleware(['auth', 'rate.limit:general,100,60'])->group(function () {
    Route::resource('communications', CommunicationController::class);
    Route::resource('reservations', ReservationController::class);
});
```

## Dépannage

### Redis non disponible
Si Redis n'est pas installé, le système utilise automatiquement la base de données comme fallback.

### Performances
Pour de meilleures performances avec de nombreux utilisateurs, considérez Redis ou Memcached.

### Débloquer un utilisateur
```bash
php artisan rate-limit:stats --clear=action_name
```
