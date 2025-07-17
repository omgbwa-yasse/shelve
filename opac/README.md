# Shelve Public Portal

Module public pour l'application Shelve - Interface utilisateur React.js pour l'accès public aux fonctionnalités d'archivage.

## 🚀 Fonctionnalités

- **Consultation publique** des fonds d'archives
- **Recherche avancée** dans les documents et événements
- **Interface de chat** avec IA pour assistance
- **Gestion des demandes** de consultation de documents
- **Actualités** et événements liés aux archives
- **Dashboard utilisateur** pour le suivi des demandes
- **Interface responsive** et accessible

## 📋 Prérequis

- Node.js >= 16.0.0
- npm >= 8.0.0
- API Laravel Shelve en fonctionnement

## 🛠️ Installation

1. **Naviguer vers le projet**
```bash
cd c:\wamp64\www\shelves\shelve-public
```

2. **Installer les dépendances**
```bash
npm install
```

3. **Configuration**
Copier le fichier `.env.example` vers `.env.local` et configurer les variables :
```bash
copy .env.example .env.local
```

Configurer les variables dans `.env.local` :
```
REACT_APP_API_URL=http://localhost:8000/api
REACT_APP_WS_URL=ws://localhost:8000
REACT_APP_APP_NAME=Shelve Portal
```

## 🎯 Développement

**Démarrer le serveur de développement :**
```bash
npm start
```
L'application sera accessible sur http://localhost:3000

**Construire pour la production :**
```bash
npm run build
```

**Lancer les tests :**
```bash
npm test
```

## 📁 Structure du projet

```
src/
├── components/          # Composants React
│   ├── common/         # Composants réutilisables
│   ├── forms/          # Composants de formulaires
│   ├── pages/          # Pages de l'application
│   └── chat/           # Interface de chat
├── context/            # Contextes React
├── hooks/              # Hooks personnalisés
├── services/           # Services API
├── styles/             # Feuilles de style
└── utils/              # Utilitaires
```

## 🔗 Intégration API

L'application se connecte à l'API Laravel Shelve pour :
- Authentification des utilisateurs
- Consultation des fonds d'archives
- Recherche dans les documents
- Gestion des demandes de consultation
- Chat avec IA (Ollama)
- Gestion des actualités et événements

## 🎨 Technologies utilisées

- **React 18** - Interface utilisateur
- **React Router 6** - Routage
- **React Query** - Gestion des données
- **Styled Components** - Styles CSS-in-JS
- **Axios** - Requêtes HTTP
- **Socket.io** - WebSocket pour le chat
- **React Hook Form** - Gestion des formulaires
- **React Toastify** - Notifications
- **Date-fns** - Manipulation des dates

## 🚀 Déploiement

1. **Build de production :**
```bash
npm run build
```

2. **Servir les fichiers statiques :**
```bash
npm install -g serve
serve -s build
```

3. **Configuration serveur web**
Configurer votre serveur web (Apache/Nginx) pour servir les fichiers du dossier `build/` et rediriger toutes les routes vers `index.html`.

## 🔧 Configuration avancée

### Variables d'environnement

- `REACT_APP_API_URL` - URL de l'API Laravel
- `REACT_APP_WS_URL` - URL WebSocket pour le chat
- `REACT_APP_APP_NAME` - Nom de l'application
- `REACT_APP_DEBUG` - Mode debug (true/false)

### Personnalisation des styles

Les styles peuvent être personnalisés dans :
- `src/styles/globals.css` - Styles globaux
- `src/styles/components.css` - Styles des composants

## 📖 Documentation

### Pages principales

- `/` - Page d'accueil
- `/events` - Liste des événements
- `/events/:id` - Détail d'un événement
- `/records` - Consultation des fonds
- `/records/:id` - Détail d'un document
- `/documents/request` - Demande de consultation
- `/news` - Actualités
- `/news/:id` - Détail d'une actualité
- `/chat` - Interface de chat IA
- `/dashboard` - Dashboard utilisateur
- `/feedback` - Formulaire de feedback

### Hooks personnalisés

- `useAuth` - Gestion de l'authentification
- `useApi` - Requêtes API avec cache
- `useChat` - Interface de chat
- `useSearch` - Recherche avancée
- `useEvents` - Gestion des événements

## 🐛 Dépannage

### Erreurs communes

1. **Port déjà utilisé** : Changer le port avec `PORT=3001 npm start`
2. **Erreurs CORS** : Vérifier la configuration de l'API Laravel
3. **WebSocket non connecté** : Vérifier l'URL WebSocket dans .env.local

### Support

Pour les questions et problèmes, consulter la documentation de l'API Laravel Shelve.

## 📄 Licence

Ce projet est sous licence propriétaire. Voir le fichier LICENSE pour plus de détails.
