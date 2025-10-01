# Configuration du Provider IA dans Drag & Drop

## Vue d'ensemble

Le système de Drag & Drop utilise maintenant le provider et le modèle IA configurés dans la base de données via la table `settings` au lieu de valeurs codées en dur.

## Modifications apportées

### 1. Contrôleur (`RecordDragDropController.php`)

Le contrôleur récupère maintenant les configurations IA depuis la base de données :

```php
public function dragDropForm()
{
    Gate::authorize('records_create');
    $settingService = app(SettingService::class);
    $appUploadMaxMb = (int) $settingService->get('upload_max_file_size_mb', 50);
    
    // Récupérer le provider et le modèle depuis la base de données
    $provider = $settingService->get('ai_default_provider', 'ollama');
    $model = $settingService->get('ai_default_model', config('ollama-laravel.model', 'gemma3:4b'));
    
    return view('records.drag-drop', [
        'server_post_max' => ini_get('post_max_size'),
        'server_upload_max_filesize' => ini_get('upload_max_filesize'),
        'server_max_file_uploads' => ini_get('max_file_uploads'),
        'app_upload_max_file_size_mb' => $appUploadMaxMb,
        'ai_provider' => $provider,
        'ai_model' => $model,
    ]);
}
```

### 2. Vue (`drag-drop.blade.php`)

#### Affichage dans l'en-tête

La configuration active est maintenant affichée dans l'en-tête de la page :

```blade
<div class="card-header bg-primary text-white">
    <h4 class="mb-0">
        <i class="bi bi-cloud-upload me-2"></i>
        {{ __('Drag & Drop - Création automatique de records') }}
    </h4>
    <div class="mt-2">
        <small class="badge bg-light text-dark">
            <i class="bi bi-robot me-1"></i>Provider: <strong>{{ $ai_provider ?? 'ollama' }}</strong>
        </small>
        <small class="badge bg-light text-dark ms-2">
            <i class="bi bi-cpu me-1"></i>Modèle: <strong>{{ $ai_model ?? 'gemma3:4b' }}</strong>
        </small>
    </div>
</div>
```

#### Configuration JavaScript

Les valeurs sont également disponibles dans le JavaScript :

```javascript
// Configuration IA depuis la base de données
const AI_PROVIDER = '{{ $ai_provider ?? "ollama" }}';
const AI_MODEL = '{{ $ai_model ?? "gemma3:4b" }}';

console.log('Configuration IA active:', { provider: AI_PROVIDER, model: AI_MODEL });
```

#### Message de progression

Le message de progression affiche maintenant le provider et le modèle utilisés :

```javascript
showProgress(`Traitement par l'IA (${AI_PROVIDER} / ${AI_MODEL})...`, 70);
```

## Configuration dans la base de données

Les paramètres sont stockés dans la table `settings` avec les clés suivantes :

- `ai_default_provider` : Le provider IA à utiliser (ex: "ollama", "openai", "gemini", etc.)
- `ai_default_model` : Le modèle IA à utiliser (ex: "gemma3:4b", "gpt-4", etc.)

### Ordre de priorité des paramètres

Le `SettingService` applique l'ordre de priorité suivant :

1. Paramètre spécifique utilisateur + organisation
2. Paramètre spécifique utilisateur seul
3. Paramètre spécifique organisation seule
4. Paramètre global (valeur par défaut)

## Avantages

1. **Configuration centralisée** : Un seul endroit pour configurer le provider IA
2. **Flexibilité** : Possibilité de changer le provider sans modifier le code
3. **Transparence** : L'utilisateur voit quel provider et modèle sont utilisés
4. **Traçabilité** : Les logs console affichent la configuration active
5. **Multi-organisation** : Chaque organisation peut avoir sa propre configuration

## Utilisation

1. Accédez à l'interface d'administration des paramètres
2. Modifiez les valeurs de `ai_default_provider` et `ai_default_model`
3. Les changements sont immédiatement pris en compte lors du prochain chargement de la page

## Providers supportés

Les providers suivants sont supportés (selon la configuration de `AiProvidersSeeder`) :

- `ollama` : Provider local Ollama
- `openai` : OpenAI (GPT-4, etc.)
- `gemini` : Google Gemini
- `claude` : Anthropic Claude
- `openrouter` : OpenRouter
- `onn` : ONN
- `ollama_turbo` : Ollama Turbo
- `openai_custom` : OpenAI personnalisé
- `mistral` : Mistral AI

## Dépannage

### Le provider ne change pas

1. Vérifiez que la valeur est bien enregistrée dans la table `settings`
2. Videz le cache Laravel : `php artisan cache:clear`
3. Rechargez la page du navigateur

### Le provider n'est pas disponible

1. Vérifiez que le provider est correctement configuré dans `ProviderRegistry`
2. Vérifiez que le service du provider est accessible (ex: Ollama doit être démarré)
3. Consultez les logs pour plus de détails

## Fichiers modifiés

- `app/Http/Controllers/RecordDragDropController.php`
- `resources/views/records/drag-drop.blade.php`
