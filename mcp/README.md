# Shelve MCP Server

Serveur MCP (Model Context Protocol) pour le système d'archivage Shelve avec intégration IA Ollama.

## 🚀 Fonctionnalités

- **Résumé automatique** : Génération de résumés de documents avec différents niveaux de détail
- **Extraction de mots-clés** : Identification automatique des termes clés avec scores de pertinence
- **Reformulation de titres** : Amélioration et optimisation des titres de documents
- **Analyse de contenu** : Analyse structurelle, sentimentale et technique des documents
- **Traitement complet** : Combinaison de tous les traitements en une seule requête
- **API REST** : Interface HTTP simple et documentée
- **Gestion d'erreurs** : Système robuste de gestion et logging des erreurs
- **Health checks** : Surveillance de l'état du système et des services

## 📁 Structure du projet

```
mcp/
├── src/
│   ├── config/           # Configuration centralisée
│   ├── controllers/      # Contrôleurs REST
│   ├── services/         # Logique métier
│   │   ├── ai/          # Services IA (Ollama)
│   │   ├── database/    # Services base de données
│   │   └── processing/  # Services de traitement
│   ├── middleware/       # Middlewares Express
│   ├── routes/          # Définition des routes
│   ├── utils/           # Utilitaires
│   └── models/          # Modèles de données
├── templates/           # Templates de prompts
├── tests/              # Tests unitaires et d'intégration
├── docs/               # Documentation
├── scripts/            # Scripts utilitaires
└── logs/               # Fichiers de logs
```

## ⚙️ Installation

### Prérequis

- Node.js >= 16.0.0
- npm >= 8.0.0
- Ollama installé et fonctionnel
- MySQL/MariaDB (optionnel)

### Installation des dépendances

```bash
cd mcp
npm install
```

### Configuration

1. Copier le fichier d'environnement :
```bash
copy .env.example .env
```

2. Modifier les variables d'environnement dans `.env` :
```env
# Configuration du serveur
PORT=3001
NODE_ENV=development
LOG_LEVEL=debug

# Configuration Ollama
OLLAMA_BASE_URL=http://localhost:11434
OLLAMA_DEFAULT_MODEL=llama3.2

# Configuration base de données (optionnel)
DB_HOST=localhost
DB_DATABASE=shelve
DB_USERNAME=root
DB_PASSWORD=
```

### Démarrage

```bash
# Mode développement
npm run dev

# Mode production
npm start
```

Le serveur sera accessible sur `http://localhost:3001`

## 📚 Utilisation de l'API

### Endpoints principaux

#### Résumé de contenu
```http
POST /api/records/summarize
Content-Type: application/json

{
  "content": "Votre texte à résumer...",
  "model": "llama3.2",
  "options": {
    "maxLength": 200,
    "type": "basic",
    "language": "fr"
  }
}
```

#### Extraction de mots-clés
```http
POST /api/records/keywords
Content-Type: application/json

{
  "content": "Votre texte...",
  "count": 10,
  "model": "llama3.2"
}
```

#### Reformulation de titre
```http
POST /api/records/reformulate-title
Content-Type: application/json

{
  "title": "Titre actuel",
  "content": "Contenu du document...",
  "model": "llama3.2"
}
```

#### Analyse de contenu
```http
POST /api/records/analyze
Content-Type: application/json

{
  "content": "Votre texte...",
  "type": "general",
  "model": "llama3.2"
}
```

#### Traitement complet
```http
POST /api/records/process-complete
Content-Type: application/json

{
  "content": "Votre texte...",
  "model": "llama3.2",
  "options": {
    "summary": {"maxLength": 200},
    "keywordCount": 10,
    "analysisType": "general"
  }
}
```

### Endpoints utilitaires

- `GET /api/health` - État du système
- `GET /api/models` - Liste des modèles disponibles
- `GET /api/settings` - Configuration du serveur

## 🧪 Tests

```bash
# Exécuter tous les tests
npm test

# Tests en mode watch
npm run test:watch

# Coverage des tests
npm run test:coverage
```

## 📊 Monitoring et Logs

Les logs sont automatiquement générés dans le dossier `logs/` :

- `app.log` - Tous les logs de l'application
- `error.log` - Logs d'erreurs uniquement
- `access.log` - Logs d'accès HTTP

Niveaux de log : `error`, `warn`, `info`, `debug`

## 🔧 Configuration avancée

### Templates de prompts

Les templates sont stockés dans `templates/` et utilisent un système de variables :

```text
Vous êtes un expert en {{task}}. 
Analysez le contenu suivant : {{content}}
```

### Modèles Ollama

Configuration des modèles par tâche dans `.env` :

```env
OLLAMA_SUMMARY_MODEL=llama3.2
OLLAMA_KEYWORDS_MODEL=llama3.2
OLLAMA_TITLE_MODEL=llama3.2
OLLAMA_ANALYSIS_MODEL=llama3.2
```

### Rate Limiting

```env
RATE_LIMIT_WINDOW=900000  # 15 minutes
RATE_LIMIT_MAX=100        # 100 requêtes par fenêtre
```

## 🚀 Déploiement

### Avec Docker (à venir)

```bash
docker build -t shelve-mcp .
docker run -p 3001:3001 shelve-mcp
```

### Avec PM2

```bash
npm install -g pm2
pm2 start src/index.js --name "shelve-mcp"
```

## 🤝 Contribution

1. Forkez le projet
2. Créez votre branche de fonctionnalité
3. Committez vos changements
4. Poussez vers la branche
5. Ouvrez une Pull Request

## 📋 Scripts disponibles

- `npm start` - Démarrage en production
- `npm run dev` - Démarrage en développement avec nodemon
- `npm test` - Exécution des tests
- `npm run lint` - Vérification du code avec ESLint
- `npm run lint:fix` - Correction automatique des erreurs ESLint
- `npm run docs` - Génération de la documentation JSDoc

## 🐛 Résolution de problèmes

### Ollama indisponible
- Vérifiez qu'Ollama est démarré : `ollama serve`
- Vérifiez l'URL de connexion dans `.env`

### Erreurs de modèle
- Listez les modèles disponibles : `ollama list`
- Téléchargez un modèle : `ollama pull llama3.2`

### Problèmes de performance
- Ajustez `OLLAMA_MAX_CONCURRENT` dans `.env`
- Réduisez `OLLAMA_MAX_TOKENS` pour les requêtes plus rapides

## 📞 Support

Pour signaler un bug ou demander une fonctionnalité, veuillez ouvrir une issue sur le dépôt GitHub.

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 🙏 Remerciements

- [Ollama](https://ollama.ai/) pour l'interface IA
- [Express.js](https://expressjs.com/) pour le framework web
- [Winston](https://github.com/winstonjs/winston) pour le logging
