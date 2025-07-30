# Serveur MCP pour Shelves

Ce serveur implémente le Model Context Protocol (MCP) pour l'application Shelves. Il sert d'intermédiaire entre l'application Laravel et les modèles d'IA locaux via Ollama.

## Fonctionnalités

- Communication avec Ollama pour l'inférence de modèles locaux
- Récupération des paramètres de configuration depuis la base de données MySQL
- Traitement de documents avec différents modèles d'IA
- API RESTful pour l'intégration avec l'application Laravel
- Fonctions spécifiques pour:
  - Génération de résumés de documents
  - Reformulation de titres
  - Extraction de mots-clés
  - Analyse de contenu

## Prérequis

- Node.js v16+ et npm
- Base de données MySQL partagée avec l'application Laravel
- Ollama installé et configuré (avec les modèles requis)

## Installation

1. Cloner le dépôt ou accéder au dossier MCP
2. Installer les dépendances:

```bash
cd mcp
npm install
```

3. Copier le fichier d'environnement et le configurer:

```bash
cp .env.example .env
# Modifier le fichier .env selon votre configuration
```

## Démarrage

```bash
# Démarrer en mode développement
npm run dev

# Démarrer en mode production
npm start
```

## Structure du projet

```
mcp/
├── src/
│   ├── handlers/          # Gestionnaires de requêtes API
│   ├── services/          # Services pour l'IA et autres fonctionnalités
│   ├── utils/             # Utilitaires (DB, logging, etc.)
│   ├── index.js           # Point d'entrée de l'application
│   └── routes.js          # Configuration des routes
├── templates/             # Templates pour les requêtes IA
├── logs/                  # Fichiers de logs
├── .env                   # Variables d'environnement
└── package.json           # Configuration npm
```

## Endpoints API

### Records

- `POST /records/summarize` - Génère un résumé pour un document
- `POST /records/title` - Reformule le titre d'un document
- `POST /records/keywords` - Extrait des mots-clés d'un document
- `POST /records/analyze` - Analyse le contenu d'un document

### Modèles

- `GET /models` - Liste des modèles disponibles
- `GET /models/default` - Modèle par défaut

### Paramètres

- `GET /settings` - Liste tous les paramètres
- `GET /settings/:name` - Récupère un paramètre spécifique

## Intégration avec Laravel

Le serveur MCP est conçu pour être utilisé avec l'application Laravel Shelves. La configuration de connexion est définie dans `config/mcp.php` du projet Laravel.

## Développement

Pour ajouter de nouvelles fonctionnalités ou modifier les existantes:

1. Créez ou modifiez les handlers dans `src/handlers/`
2. Ajoutez les routes nécessaires dans `src/routes.js`
3. Si nécessaire, créez de nouveaux templates dans `templates/`
