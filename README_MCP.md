# Module MCP (Model Context Protocol) - Implémentation Ollama Laravel

Ce module implémente les 3 fonctionnalités MCP demandées pour le système d'archivage, en utilisant Ollama et Laravel pour traiter automatiquement les archives selon les règles ISAD(G).

## 🎯 Fonctionnalités Implémentées

### 1. **Reformulation du Titre Record (ISAD-G)**
- Reformule automatiquement les titres d'archives selon les règles ISAD(G)
- Applique les règles spécifiques selon le nombre d'objets (1, 2, ou 3+)
- Respecte la ponctuation et structure archivistique
- Modèle recommandé : `llama3.1:8b`

### 2. **Indexation Thésaurus**
- Extrait automatiquement 5 mots-clés avec 3 synonymes chacun
- Recherche dans votre thésaurus existant
- Associe les concepts trouvés au record avec pondération
- Modèle recommandé : `mistral:7b`

### 3. **Résumé ISAD(G) - Élément 3.3.1**
- Génère automatiquement le résumé "Portée et contenu"
- Adapte le format selon le niveau (fonds, série, dossier)
- Intègre le contexte parent/enfant
- Modèle recommandé : `llama3.1:8b`

## 🚀 Installation et Configuration

### 1. Installation d'Ollama

```bash
# Sur Windows (via PowerShell administrateur)
winget install ollama

# Ou télécharger depuis https://ollama.ai

# Démarrer Ollama
ollama serve

# Télécharger les modèles nécessaires
ollama pull llama3.1:8b
ollama pull mistral:7b
```

### 2. Configuration Laravel

Ajoutez ces variables à votre `.env` :

```env
# Configuration Ollama MCP
OLLAMA_URL=http://127.0.0.1:11434
OLLAMA_CONNECTION_TIMEOUT=300

# Modèles pour chaque fonctionnalité
OLLAMA_MCP_TITLE_MODEL=llama3.1:8b
OLLAMA_MCP_THESAURUS_MODEL=mistral:7b
OLLAMA_MCP_SUMMARY_MODEL=llama3.1:8b
OLLAMA_MCP_KEYWORD_MODEL=mistral:7b

# Paramètres optimisés pour l'archivage
OLLAMA_MCP_TEMPERATURE=0.2
OLLAMA_MCP_TOP_P=0.9
OLLAMA_MCP_MAX_TOKENS=2000

# Traitement automatique
MCP_AUTO_PROCESS_CREATE=true
MCP_AUTO_PROCESS_UPDATE=false

# Performance
MCP_CACHE_RESPONSES=true
MCP_CACHE_TTL=3600
MCP_BATCH_SIZE=10
```

## 📖 Utilisation

### Via l'API REST

#### Traitement complet d'un record
```bash
# Traitement avec toutes les fonctionnalités
curl -X POST "http://your-domain/api/mcp/records/123/process" \
  -H "Content-Type: application/json" \
  -d '{
    "features": ["title", "thesaurus", "summary"],
    "async": false
  }'

# Traitement asynchrone (recommandé pour lots)
curl -X POST "http://your-domain/api/mcp/records/123/process" \
  -H "Content-Type: application/json" \
  -d '{
    "features": ["title", "thesaurus", "summary"],
    "async": true
  }'
```

#### Fonctionnalités individuelles
```bash
# Reformulation de titre uniquement
curl -X POST "http://your-domain/api/mcp/records/123/title/reformulate"

# Aperçu sans sauvegarde
curl -X POST "http://your-domain/api/mcp/records/123/title/preview"

# Indexation thésaurus
curl -X POST "http://your-domain/api/mcp/records/123/thesaurus/index"

# Génération de résumé
curl -X POST "http://your-domain/api/mcp/records/123/summary/generate"
```

#### Traitement par lots
```bash
curl -X POST "http://your-domain/api/mcp/batch/process" \
  -H "Content-Type: application/json" \
  -d '{
    "record_ids": [123, 124, 125],
    "features": ["thesaurus"],
    "async": true
  }'
```

### Via les Commandes Artisan

#### Traiter un record spécifique
```bash
# Traitement complet
php artisan mcp:process-record 123 --features=title,thesaurus,summary

# Prévisualisation (sans sauvegarde)
php artisan mcp:process-record 123 --features=title,summary --preview

# Fonctionnalité unique
php artisan mcp:process-record 123 --features=title
```

#### Traitement par lots
```bash
# Traitement synchrone de 50 records
php artisan mcp:batch-process --limit=50 --features=thesaurus

# Traitement asynchrone avec filtres
php artisan mcp:batch-process \
  --organisation_id=1 \
  --level_id=3 \
  --features=title,summary \
  --async \
  --limit=100

# Filtre par activité
php artisan mcp:batch-process \
  --activity_id=5 \
  --features=title \
  --limit=25
```

### Via le Code PHP

```php
use App\Services\MCP\McpManagerService;
use App\Models\Record;

// Injection du service
$mcpManager = app(McpManagerService::class);
$record = Record::find(123);

// Traitement complet
$results = $mcpManager->processRecord($record, ['title', 'thesaurus', 'summary']);

// Prévisualisation
$previews = $mcpManager->previewProcessing($record, ['title', 'summary']);

// Traitement par lots
$recordIds = [123, 124, 125];
$batchResults = $mcpManager->batchProcessRecords($recordIds, ['thesaurus']);

// Fonctionnalités individuelles
$titleService = app(\App\Services\MCP\McpTitleReformulationService::class);
$newTitle = $titleService->reformulateTitle($record);

$thesaurusService = app(\App\Services\MCP\McpThesaurusIndexingService::class);
$indexingResult = $thesaurusService->indexRecord($record);

$summaryService = app(\App\Services\MCP\McpContentSummarizationService::class);
$summary = $summaryService->generateSummary($record);
```

### Jobs en Arrière-Plan

```php
use App\Jobs\ProcessRecordWithMcp;

// Lancer un job pour traitement asynchrone
ProcessRecordWithMcp::dispatch($record, ['title', 'thesaurus', 'summary']);

// Job avec délai
ProcessRecordWithMcp::dispatch($record, ['summary'])->delay(now()->addMinutes(5));

// Job sur une queue spécifique
ProcessRecordWithMcp::dispatch($record, ['title'])->onQueue('mcp-processing');
```

## 🔧 Monitoring et Administration

### Vérification de l'état de santé
```bash
# Via API
curl -X GET "http://your-domain/api/mcp/health"

# Réponse exemple
{
  "overall_status": "ok",
  "components": {
    "ollama_connection": {
      "status": "ok",
      "response_time": 0.25,
      "model": "llama3.1:8b"
    },
    "database": {
      "status": "ok"
    },
    "models": {
      "title_reformulation": {
        "configured": true,
        "model_name": "llama3.1:8b"
      }
    }
  }
}
```

### Statistiques d'utilisation
```bash
curl -X GET "http://your-domain/api/mcp/stats"
```

### Surveillance des jobs
```bash
# Voir les jobs en attente
php artisan queue:work --queue=mcp-light,mcp-medium,mcp-heavy

# Monitoring en temps réel
php artisan horizon

# Statistiques des jobs
php artisan queue:monitor
```

## 📊 Exemples de Résultats

### Reformulation de Titre
**Avant :** "Documents mairie 1950-1960"
**Après :** "Personnel de la mairie, attribution de la médaille du travail : liste des bénéficiaires. 1950-1960"

### Indexation Thésaurus
```json
{
  "keywords_extracted": [
    {
      "term": "personnel",
      "synonyms": ["employés", "agents", "fonctionnaires"]
    },
    {
      "term": "médaille",
      "synonyms": ["récompense", "distinction", "décoration"]
    }
  ],
  "concepts_found": 3,
  "concepts": [
    {
      "preferred_label": "Personnel municipal",
      "weight": 0.85
    }
  ]
}
```

### Résumé ISAD(G)
**Généré :** "La série comprend les listes nominatives et la correspondance concernant l'attribution de la médaille du travail au personnel municipal et couvrant la période 1950-1960. Contient notamment les demandes, les rapports d'évaluation et les arrêtés préfectoraux d'attribution."

## 🚨 Dépannage

### Problèmes courants

1. **Ollama non accessible**
   ```bash
   # Vérifier le service
   curl http://127.0.0.1:11434/api/tags
   
   # Redémarrer Ollama
   ollama serve
   ```

2. **Modèle non trouvé**
   ```bash
   # Lister les modèles installés
   ollama list
   
   # Télécharger un modèle manquant
   ollama pull llama3.1:8b
   ```

3. **Timeout de connexion**
   ```env
   # Augmenter le timeout
   OLLAMA_CONNECTION_TIMEOUT=600
   ```

4. **Performance lente**
   ```env
   # Activer le cache
   MCP_CACHE_RESPONSES=true
   
   # Réduire la température pour plus de déterminisme
   OLLAMA_MCP_TEMPERATURE=0.1
   
   # Limiter les tokens
   OLLAMA_MCP_MAX_TOKENS=1000
   ```

### Logs de débogage
```bash
# Voir les logs Laravel
tail -f storage/logs/laravel.log

# Activer les logs détaillés
MCP_LOGGING_ENABLED=true
MCP_LOG_REQUESTS=true
MCP_LOG_ERRORS=true
```

## 🔒 Sécurité et Performance

### Rate Limiting
- 60 requêtes par minute par utilisateur par défaut
- 1000 requêtes par heure maximum
- Configurable via `MCP_RATE_LIMIT_REQUESTS`

### Cache Intelligent
- Mise en cache des réponses Ollama (1h par défaut)
- Invalidation automatique lors des modifications
- Économise les ressources et améliore les performances

### Validation des Données
- Vérification de la longueur minimum/maximum du contenu
- Validation des champs requis
- Contrôle des types de fonctionnalités demandées

## 📝 Notes Importantes

1. **Modèles Recommandés :**
   - `llama3.1:8b` : Excellent pour la reformulation et résumés
   - `mistral:7b` : Optimal pour l'extraction de mots-clés
   - `codellama:7b` : Si besoin de traitement de code

2. **Performance :**
   - Utilisez le traitement asynchrone pour les lots > 10 records
   - Activez le cache pour éviter les requêtes redondantes
   - Surveillez l'utilisation mémoire d'Ollama

3. **Qualité des Résultats :**
   - Temperature basse (0.1-0.3) pour plus de cohérence
   - Prompts spécialisés selon les règles ISAD(G)
   - Validation et nettoyage automatique des réponses

4. **Intégration :**
   - Compatible avec votre structure de thésaurus existante
   - Respect des relations parent/enfant des records
   - Pas de modification des données existantes sans confirmation

Cette implémentation MCP est maintenant prête à être utilisée dans votre système d'archivage ! 🎉