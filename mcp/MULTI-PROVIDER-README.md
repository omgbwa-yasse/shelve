# MCP Multi-Provider AI Service

## Vue d'ensemble

Le serveur MCP (Model Context Protocol) a été étendu pour supporter plusieurs providers d'IA compatibles avec l'API OpenAI. Cette implémentation permet de basculer facilement entre différents providers selon les besoins et la disponibilité.

## Providers supportés

### 1. Ollama
- **Type**: Native Ollama API
- **URL par défaut**: `http://localhost:11434`
- **Modèles**: Tous les modèles disponibles localement
- **Configuration**: Pas de clé API requise

### 2. LM Studio
- **Type**: Compatible OpenAI API
- **URL par défaut**: `http://localhost:1234`
- **Modèles**: Modèles chargés dans LM Studio
- **Configuration**: Clé API optionnelle

### 3. AnythingLLM
- **Type**: Compatible OpenAI API
- **URL par défaut**: `http://localhost:3001`
- **Modèles**: Modèles configurés dans AnythingLLM
- **Configuration**: Clé API requise

### 4. OpenAI
- **Type**: API OpenAI officielle
- **URL**: `https://api.openai.com/v1`
- **Modèles**: GPT-3.5, GPT-4, etc.
- **Configuration**: Clé API requise, organisation optionnelle

## Configuration

### Base de données

Les paramètres sont stockés dans la table `settings` de Laravel avec les catégories suivantes :

- **Intelligence Artificielle** (catégorie parent)
  - **Providers** (sous-catégorie)
  - **Modèles** (sous-catégorie)

### Paramètres principaux

```
ai_default_provider: 'ollama'|'lmstudio'|'anythingllm'|'openai'
ai_default_model: 'llama3'|'gpt-3.5-turbo'|etc.
ai_request_timeout: 120 (en secondes)
```

### Paramètres par provider

#### Ollama
```
ollama_enabled: true|false
ollama_base_url: 'http://localhost:11434'
```

#### LM Studio
```
lmstudio_enabled: true|false
lmstudio_base_url: 'http://localhost:1234'
lmstudio_api_key: 'optional_api_key'
```

#### AnythingLLM
```
anythingllm_enabled: true|false
anythingllm_base_url: 'http://localhost:3001'
anythingllm_api_key: 'required_api_key'
```

#### OpenAI
```
openai_enabled: true|false
openai_api_key: 'required_api_key'
openai_organization: 'optional_organization_id'
```

### Modèles spécialisés
```
model_summary: 'llama3'      # Modèle pour les résumés
model_keywords: 'llama3'     # Modèle pour l'extraction de mots-clés
model_analysis: 'llama3'     # Modèle pour l'analyse de texte
```

## API Endpoints

### Configuration
- `GET /api/settings/{name}` - Récupère un paramètre
- `POST /api/settings/batch` - Récupère plusieurs paramètres
- `GET /api/settings/categories/ai` - Récupère tous les paramètres IA
- `PUT /api/settings/{name}` - Met à jour un paramètre
- `GET /api/settings/test/providers` - Teste la connectivité des providers

### Enrichissement
- `POST /api/enrich/{id}/title?provider=ollama` - Formate le titre
- `POST /api/enrich/{id}/summary?provider=lmstudio` - Génère un résumé
- `POST /api/enrich/{id}/keywords?provider=openai` - Extrait des mots-clés
- `POST /api/enrich/{id}/categorized-keywords` - Mots-clés catégorisés
- `POST /api/enrich/{id}/complete` - Enrichissement complet

### Gestion des providers
- `GET /api/providers/status` - Statut de tous les providers
- `POST /api/providers/clear-cache` - Vide le cache de configuration

## Utilisation

### Exemple avec un provider spécifique
```javascript
// Utiliser LM Studio pour un résumé
const response = await fetch('/api/enrich/123/summary?provider=lmstudio', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  }
});
```

### Exemple d'enrichissement complet
```javascript
// Enrichissement avec opérations spécifiques
const response = await fetch('/api/enrich/123/complete?operations=title,summary,keywords&provider=openai', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Content-Type': 'application/json'
  }
});
```

### Vérification du statut des providers
```javascript
const status = await fetch('/api/providers/status', {
  headers: {
    'Authorization': 'Bearer ' + token
  }
});

const data = await status.json();
console.log(data.data.providers);
// {
//   "ollama": {"available": true, "models": ["llama3", "mistral"]},
//   "lmstudio": {"available": false, "error": "Connection refused"},
//   "openai": {"available": true, "models": ["gpt-3.5-turbo", "gpt-4"]}
// }
```

## Fallback et récupération d'erreur

1. **Provider indisponible**: Le système utilise automatiquement le provider par défaut
2. **Configuration manquante**: Utilise les valeurs par défaut du fichier de configuration
3. **Cache**: Les paramètres sont mis en cache 5 minutes pour optimiser les performances

## Migration

Pour mettre à jour la base de données avec les nouveaux paramètres :

```bash
php artisan db:seed --class=SettingSeeder
```

## Sécurité

- Les clés API sont stockées chiffrées dans la base de données
- L'authentification Sanctum est requise pour toutes les routes
- Les paramètres système ne peuvent pas être modifiés via l'API

## Performance

- Cache des configurations (5 minutes)
- Timeout configurable par provider
- Requêtes parallèles possibles pour différents providers
