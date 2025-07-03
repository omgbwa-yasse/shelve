# MCP pour l'enrichissement des descriptions de records via Ollama

Ce service MCP (Model Context Protocol) permet d'enrichir les descriptions des records dans l'application Shelves en utilisant les modèles d'IA disponibles via Ollama.

## Fonctionnalités

- **Enrichissement de descriptions** : Améliore la clarté et l'exhaustivité des descriptions de records
- **Génération de résumés** : Crée des résumés concis pour les records
- **Analyse de contenu** : Analyse approfondie des records avec thématiques et pertinence historique
- **Formatage de titres** : Convertit les titres au format standardisé objet:action(typologie)
- **Extraction de mots-clés** : Extrait les mots-clés pertinents et les associe au thésaurus
- **Extraction de mots-clés catégorisés** : Extrait et catégorise les mots-clés en trois types (géographique, thématique, typologique)
- **Assignation automatique de termes** : Associe automatiquement les termes trouvés dans le thésaurus aux records

## Architecture

La nouvelle architecture du serveur MCP est organisée selon un modèle MVC (Modèle-Vue-Contrôleur) :

```
/mcp
  /src
    /config        - Configuration centralisée
    /controllers   - Contrôleurs qui gèrent les requêtes
    /middleware    - Middleware pour l'authentification et la validation
    /routes        - Définitions des routes API
    /schemas       - Schémas de validation Zod
    /services      - Services métier (Ollama, Laravel API, enrichissement)
  server.js        - Point d'entrée principal
  package.json     - Configuration du projet
```

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

### Extraire des mots-clés catégorisés

```
POST /api/categorized-keywords
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
  "autoAssign": false
}
```

### Assigner des termes à un record

```
POST /api/assign-terms
```

Corps de la requête :

```json
{
  "recordId": 123,
  "terms": [
    {"id": 1, "name": "France", "type": "geographic"},
    {"id": 2, "name": "Archivistique", "type": "thematic"},
    {"id": 3, "name": "Correspondance", "type": "typologic"}
  ]
}
```

## Modes d'enrichissement

- **enrich** : Enrichit la description en ajoutant du contexte et des informations pertinentes
- **summarize** : Résume la description en un paragraphe concis
- **analyze** : Analyse le document et fournit un résumé structuré (thèmes, pertinence historique, etc.)
- **format_title** : Formate le titre au format objet:action(typologie)
- **extract_keywords** : Extrait les mots-clés et recherche dans le thésaurus
- **categorized_keywords** : Extrait des mots-clés classés par catégorie (géographique, thématique, typologique)

## API pour le transfert d'archives

### Enrichissement des bordereaux de transfert

**GET /api/transfer/slips/:slipId/enhance**

Analyse un bordereau de transfert et suggère des améliorations pour ses métadonnées.

**Paramètres de requête:**
- `model` (optionnel): Le modèle à utiliser (par défaut: llama3)

**Réponse:**
```json
{
  "success": true,
  "originalSlip": {
    "id": 123,
    "name": "Transfert des archives du service comptabilité",
    "code": "TR-2024-123",
    "description": "Description originale"
  },
  "suggestions": {
    "amélioration_description": "Texte amélioré pour la description",
    "mots_clés": ["comptabilité", "finances", "budget"],
    "classification_recommandée": "Série F: Documentation financière",
    "termes_thésaurus_suggérés": ["comptabilité", "documents financiers", "budget"]
  },
  "model": "llama3"
}
```

### Validation des documents pour le transfert

**GET /api/transfer/slips/:slipId/validate**

Valide la conformité d'un ensemble de documents pour le transfert.

**Paramètres de requête:**
- `model` (optionnel): Le modèle à utiliser (par défaut: llama3)

**Réponse:**
```json
{
  "success": true,
  "totalRecords": 15,
  "validationResults": {
    "documents_problematiques": [
      {
        "id": 42,
        "code": "DOC-2024-42",
        "problemes": ["Description incomplète", "Dates manquantes"]
      }
    ],
    "coherence_globale": "L'ensemble est relativement cohérent mais certains documents nécessitent des compléments",
    "recommandations": ["Compléter les métadonnées manquantes", "Vérifier la cohérence des dates"],
    "evaluation_generale": "7/10 - Ensemble globalement satisfaisant avec quelques améliorations à apporter"
  },
  "model": "llama3"
}
```

### Suggestion de plan de classement

**GET /api/transfer/slips/:slipId/classify**

Suggère un plan de classement pour un ensemble de documents.

**Paramètres de requête:**
- `model` (optionnel): Le modèle à utiliser (par défaut: llama3)

**Réponse:**
```json
{
  "success": true,
  "totalRecords": 15,
  "classificationScheme": {
    "plan_classement": [
      {
        "code": "A",
        "intitule": "Administration",
        "description": "Documents administratifs et de gestion",
        "sous_series": [
          {
            "code": "A1",
            "intitule": "Correspondance administrative",
            "description": "Courriers administratifs",
            "documents_associes": [1, 2, 5]
          }
        ]
      }
    ],
    "recommandations": ["Considérer une sous-série pour les documents financiers"]
  },
  "model": "llama3"
}
```

### Génération de rapport de transfert

**GET /api/transfer/slips/:slipId/report**

Génère un rapport détaillé sur un transfert d'archives.

**Paramètres de requête:**
- `model` (optionnel): Le modèle à utiliser (par défaut: llama3)
- `includeValidation` (optionnel): Inclure ou non les résultats de validation (par défaut: true)

**Réponse:**
```json
{
  "success": true,
  "slipData": {
    "id": 123,
    "code": "TR-2024-123",
    "name": "Transfert des archives du service comptabilité"
  },
  "reportContent": "Rapport détaillé sur le transfert...",
  "statistics": {
    "totalRecords": 15,
    "totalSize": 2.5,
    "dateRange": "2019-2023",
    "recordsWithAttachments": 10,
    "typeDistribution": {
      "Administratif": 8,
      "Financier": 7
    }
  },
  "model": "llama3",
  "generatedAt": "2023-07-03T14:30:45.123Z"
}
```

## Utilisation dans l'application Laravel

Le service d'enrichissement est disponible via le contrôleur `RecordEnricherController` avec les routes suivantes :

- `GET /api/records/enrich/status` : Vérifier l'état du service
- `POST /api/records/enrich/{id}` : Enrichir un enregistrement et éventuellement mettre à jour un champ spécifique
- `POST /api/records/enrich/{id}/preview` : Prévisualiser l'enrichissement sans sauvegarder les modifications
- `POST /api/records/enrich/{id}/format-title` : Formater le titre d'un enregistrement
- `POST /api/records/enrich/{id}/extract-keywords` : Extraire des mots-clés et rechercher dans le thésaurus
- `POST /api/records/enrich/{id}/categorized-keywords` : Extraire des mots-clés catégorisés
- `POST /api/records/enrich/{id}/assign-terms` : Assigner des termes du thésaurus à un record

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
    
    // Extraire des mots-clés catégorisés
    $categorizedResult = $enricherService->extractCategorizedKeywords(
        $record,
        'llama3',
        3,  // nombre maximum de termes par catégorie
        true, // assignation automatique des termes
        Auth::id()
    );
    
    if ($categorizedResult['success']) {
        $geoTerms = $categorizedResult['extractedKeywords']['geographic'];
        $thematicTerms = $categorizedResult['extractedKeywords']['thematic'];
        $typologicTerms = $categorizedResult['extractedKeywords']['typologic'];
        $matchedTerms = $categorizedResult['matchedTerms'];
    }
    
    // Assigner des termes à un record
    $termIds = [1, 2, 3]; // IDs des termes du thésaurus
    $assignResult = $enricherService->assignTermsToRecord(
        $record->id,
        $termIds,
        Auth::id()
    );
}
```
