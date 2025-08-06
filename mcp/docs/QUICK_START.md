# Guide de Démarrage Rapide - MCP Shelve

## 1. Installation

```bash
# Installer les dépendances
cd mcp
npm install

# Copier et configurer l'environnement
cp .env.example .env
```

## 2. Configuration .env

```env
# Configuration serveur
PORT=3001
NODE_ENV=development

# Base de données
DB_HOST=localhost
DB_PORT=3306
DB_NAME=shelve_mcp
DB_USER=root
DB_PASSWORD=

# Ollama
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_DEFAULT_MODEL=llama3.2

# Logging
LOG_LEVEL=info
```

## 3. Démarrage des services

### 3.1 Démarrer Ollama
```bash
# Windows
ollama serve

# Dans un autre terminal, télécharger le modèle
ollama pull llama3.2
```

### 3.2 Démarrer le serveur MCP
```bash
# Mode développement avec rechargement automatique
npm run dev

# Mode production
npm start
```

## 4. Tests rapides

### 4.1 Vérifier l'état du serveur
```bash
curl http://localhost:3001/api/health
```

**Réponse attendue :**
```json
{
  "status": "healthy",
  "timestamp": "2024-01-15T10:30:00.000Z",
  "version": "1.0.0",
  "services": {
    "ollama": "healthy",
    "database": "healthy"
  }
}
```

### 4.2 Tester la reformulation archivistique

**Requête :**
```bash
curl -X POST http://localhost:3001/api/records/process/title \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Documents construction gymnase municipal",
    "content": "Dossier des travaux de construction du gymnase municipal comprenant plans, devis, correspondance avec entrepreneur 1958-1962",
    "model": "llama3.2",
    "options": {
      "type": "archival",
      "style": "formal",
      "maxLength": 120
    }
  }'
```

**Réponse attendue :**
```json
{
  "success": true,
  "data": {
    "originalTitle": "Documents construction gymnase municipal",
    "reformulatedTitle": "Gymnase municipal. — Construction : plans, devis, correspondance. 1958-1962",
    "type": "archival",
    "model": "llama3.2",
    "processingTime": 1250
  }
}
```

### 4.3 Tester le traitement complet d'un document

**Requête :**
```bash
curl -X POST http://localhost:3001/api/records/process/full \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Dossier personnel enseignant",
    "content": "Ce dossier contient les pièces administratives concernant Madame Dubois, institutrice à l école primaire de 1945 à 1975 : nomination, mutations, évaluations, correspondance.",
    "model": "llama3.2",
    "options": {
      "includeKeywords": true,
      "includeSummary": true,
      "titleType": "archival"
    }
  }'
```

## 5. Vérification des logs

```bash
# Logs en temps réel
tail -f logs/app.log

# Logs d'erreur
tail -f logs/error.log
```

## 6. Endpoints disponibles

| Endpoint | Méthode | Description |
|----------|---------|-------------|
| `/api/health` | GET | État du serveur |
| `/api/models` | GET | Modèles Ollama disponibles |
| `/api/records/process/title` | POST | Reformulation de titre |
| `/api/records/process/keywords` | POST | Extraction de mots-clés |
| `/api/records/process/summary` | POST | Génération de résumé |
| `/api/records/process/analysis` | POST | Analyse complète |
| `/api/records/process/full` | POST | Traitement complet |

## 7. Résolution des problèmes courants

### Ollama non disponible
```
Error: Ollama service unavailable
```
**Solution :** Vérifier qu'Ollama est démarré sur le port 11434

### Modèle non trouvé
```
Error: Model llama3.2 not found
```
**Solution :** Télécharger le modèle avec `ollama pull llama3.2`

### Base de données non accessible
```
Error: Database connection failed
```
**Solution :** Vérifier la configuration dans `.env` et que MySQL est démarré

## 8. Tests avec des documents réels

Utilisez les exemples dans `/docs/TITLE_REFORMULATION.md` pour tester différents types de reformulation.

## 9. Monitoring

- Logs applicatifs : `logs/app.log`
- Logs d'erreur : `logs/error.log` 
- Métriques de performance incluses dans les réponses API
- Endpoint `/api/health` pour surveillance système
