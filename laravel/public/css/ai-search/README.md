# AI Search Module Assets

## Structure des assets

### CSS Files
- `public/css/ai-search/chat.css` - Styles pour l'interface de chat
- `public/css/ai-search/results.css` - Styles pour l'affichage des résultats
- `public/css/ai-search/voice.css` - Styles pour la reconnaissance vocale
- `public/css/ai-search/animations.css` - Animations et transitions
- `public/css/ai-search/documentation.css` - Styles pour la documentation
- `public/css/ai-search/test-interface.css` - Styles pour l'interface de test

### JavaScript Files
- `public/js/ai-search/chat.js` - Gestionnaire principal du chat
- `public/js/ai-search/voice.js` - Reconnaissance vocale
- `public/js/ai-search/documentation.js` - Navigation dans la documentation
- `public/js/ai-search/test-interface.js` - Interface de test

### Blade Components
- `resources/views/ai-search/components/` - Composants réutilisables
- `resources/views/ai-search/partials/` - Parties partielles

## Utilisation

### Dans les vues Blade

```blade
@section('styles')
<link rel="stylesheet" href="{{ asset('css/ai-search/chat.css') }}">
<link rel="stylesheet" href="{{ asset('css/ai-search/results.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('js/ai-search/chat.js') }}"></script>
@endsection
```

### Inclusion des composants

```blade
@include('ai-search.components.search-type-selector', ['defaultType' => 'records'])
@include('ai-search.components.chat-interface')
```

## Optimisations possibles

1. **Minification** : Minifier les fichiers CSS et JS en production
2. **Concat** : Combiner les fichiers pour réduire les requêtes HTTP
3. **Cache busting** : Ajouter des hashes aux noms de fichiers
4. **CDN** : Héberger les assets sur un CDN pour de meilleures performances
