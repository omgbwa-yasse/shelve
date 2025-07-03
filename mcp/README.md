# MCP pour l'enrichissement des descriptions de records via Ollama

Ce service MCP (Model Context Protocol) permet d'enrichir les descriptions des records dans l'application Shelves en utilisant les modèles d'IA disponibles via Ollama.

## Fonctionnalités

- **Enrichissement de descriptions** : Améliore la clarté et l'exhaustivité des descriptions de records
- **Génération de résumés** : Crée des résumés concis pour les records
- **Analyse de contenu** : Analyse approfondie des records avec thématiques et pertinence historique
- **Formatage de titres** : Convertit les titres au format standardisé objet:action(typologie)
- **Extraction de mots-clés** : Extrait les mots-clés pertinents et les associe au thésaurus

## Installation

1. Installer les dépendances Node.js :

```bash
cd mcp
npm install
```

2. Configurer les variables d'environnement :

Assurez-vous que les variables suivantes sont définies dans le fichier `.env` de l'application Laravel :

```
MCP_BASE_URL=http://localhost:3000
MCP_PORT=3000
OLLAMA_DEFAULT_MODEL=llama3
LARAVEL_API_URL=http://localhost/shelves/api
LARAVEL_API_TOKEN=your_api_token_here
```

## Démarrage du serveur MCP

```bash
cd mcp
npm run start
```

Pour le développement (avec rechargement automatique) :

```bash
npm run dev
```

## API disponible

### Vérifier l'état du serveur

```
GET /health
```

Retourne le statut du serveur MCP.

### Vérifier la connexion à Ollama

```
GET /api/check-ollama
```

Vérifie la connexion à Ollama et retourne la liste des modèles disponibles.

### Enrichir un record

```
POST /api/enrich
```

Corps de la requête :

```json
{
  "recordId": 123,
  "recordData": {
    "id": 123,
    "name": "Titre du document",
    "content": "Description actuelle...",
    "biographical_history": "Contexte biographique...",
    "archival_history": "Historique archivistique...",
    "note": "Notes..."
  },
  "modelName": "llama3",
  "mode": "enrich" // ou "summarize", "analyze", "format_title", "extract_keywords"
}
```

### Formater un titre

```
POST /api/format-title
```

Corps de la requête :

```json
{
  "title": "Procès verbal de la réunion du 25 janvier 2024",
  "modelName": "llama3"
}
```

### Rechercher dans le thésaurus

```
POST /api/thesaurus-search
```

Corps de la requête :

```json
{
  "recordId": 123,
  "content": "Texte à analyser pour en extraire des mots-clés...",
  "modelName": "llama3",
  "maxTerms": 5
}
```

## Modes d'enrichissement

- **enrich** : Enrichit la description en ajoutant du contexte et des informations pertinentes
- **summarize** : Résume la description en un paragraphe concis
- **analyze** : Analyse le document et fournit un résumé structuré (thèmes, pertinence historique, etc.)
- **format_title** : Formate le titre au format objet:action(typologie)
- **extract_keywords** : Extrait les mots-clés et recherche dans le thésaurus

## Utilisation dans l'application Laravel

Le service d'enrichissement est disponible via le contrôleur `RecordEnricherController` avec les routes suivantes :

- `GET /api/records/enrich/status` : Vérifier l'état du service
- `POST /api/records/enrich/{id}` : Enrichir un enregistrement et éventuellement mettre à jour un champ spécifique
- `POST /api/records/enrich/{id}/preview` : Prévisualiser l'enrichissement sans sauvegarder les modifications
- `POST /api/records/enrich/{id}/format-title` : Formater le titre d'un enregistrement
- `POST /api/records/enrich/{id}/extract-keywords` : Extraire des mots-clés et rechercher dans le thésaurus

### Exemples d'utilisation dans l'application

```php
// Injecter le service
public function someMethod(RecordEnricherService $enricherService)
{
    $record = Record::find(123);
    
    // Enrichir un enregistrement
    $result = $enricherService->enrichRecord(
        $record,
        'llama3',  // modèle
        'enrich',  // mode
        Auth::id() // ID utilisateur
    );
    
    if ($result['success']) {
        // Utiliser le contenu enrichi
        $enrichedContent = $result['enrichedContent'];
    }
    
    // Formater un titre
    $titleResult = $enricherService->formatTitle(
        $record->name,
        'llama3',
        Auth::id()
    );
    
    if ($titleResult['success']) {
        $formattedTitle = $titleResult['formattedTitle'];
    }
    
    // Extraire des mots-clés
    $keywordsResult = $enricherService->extractKeywords(
        $record,
        'llama3',
        5,  // nombre maximum de termes
        Auth::id()
    );
    
    if ($keywordsResult['success']) {
        $extractedKeywords = $keywordsResult['extractedKeywords'];
        $matchedTerms = $keywordsResult['matchedTerms'];
    }
}
```
