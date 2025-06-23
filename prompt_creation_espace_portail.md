# Prompt pour créer le module public Shelve

## Contexte
Créez un service React.js accessible au public qui s'intègre avec l'API Laravel du projet Shelve. Ce module permettra aux utilisateurs externes d'accéder aux fonctionnalités publiques via les contrôleurs : PublicChatController, PublicChatMessageController, PublicChatParticipantController, PublicDocumentRequestController, PublicEventController, PublicEventRegistrationController, PublicFeedbackController, PublicNewsController, PublicPageController, PublicRecordController, PublicResponseAttachmentController, PublicResponseController, PublicSearchLogController, PublicTemplateController, et PublicUserController.

## Instructions de création

### 1. Structure du projet
Créez un projet React avec la structure suivante :

```
shelve-public/
├── public/
│   ├── index.html
│   ├── favicon.ico
│   └── manifest.json
├── src/
│   ├── components/
│   │   ├── common/
│   │   │   ├── Header.jsx
│   │   │   ├── Footer.jsx
│   │   │   ├── Loading.jsx
│   │   │   ├── ErrorMessage.jsx
│   │   │   ├── SearchBar.jsx
│   │   │   └── Pagination.jsx
│   │   ├── pages/
│   │   │   ├── HomePage.jsx
│   │   │   ├── EventsPage.jsx
│   │   │   ├── EventDetail.jsx
│   │   │   ├── NewsPage.jsx
│   │   │   ├── NewsDetail.jsx
│   │   │   ├── RecordsPage.jsx
│   │   │   ├── RecordDetail.jsx
│   │   │   ├── DocumentRequestPage.jsx
│   │   │   ├── FeedbackPage.jsx
│   │   │   ├── UserDashboard.jsx
│   │   │   └── ChatInterface.jsx
│   │   ├── forms/
│   │   │   ├── EventRegistrationForm.jsx
│   │   │   ├── DocumentRequestForm.jsx
│   │   │   ├── FeedbackForm.jsx
│   │   │   ├── UserRegistrationForm.jsx
│   │   │   └── ResponseForm.jsx
│   │   ├── chat/
│   │   │   ├── ChatWindow.jsx
│   │   │   ├── MessageList.jsx
│   │   │   ├── MessageInput.jsx
│   │   │   └── ParticipantsList.jsx
│   │   └── templates/
│   │       ├── TemplateViewer.jsx
│   │       └── TemplateSelector.jsx
│   ├── services/
│   │   ├── api.js
│   │   ├── shelveApi.js
│   │   ├── chatApi.js
│   │   ├── eventApi.js
│   │   ├── documentApi.js
│   │   └── userApi.js
│   ├── hooks/
│   │   ├── useApi.js
│   │   ├── useChat.js
│   │   ├── useEvents.js
│   │   ├── useDocuments.js
│   │   ├── useSearch.js
│   │   └── useAuth.js
│   ├── context/
│   │   ├── AuthContext.js
│   │   ├── ChatContext.js
│   │   └── NotificationContext.js
│   ├── styles/
│   │   ├── globals.css
│   │   ├── components.css
│   │   ├── chat.css
│   │   ├── forms.css
│   │   └── responsive.css
│   ├── utils/
│   │   ├── constants.js
│   │   ├── helpers.js
│   │   ├── validators.js
│   │   ├── dateUtils.js
│   │   └── fileUtils.js
│   ├── App.jsx
│   ├── index.js
│   └── setupTests.js
├── .env.example
├── .env.local
├── package.json
└── README.md
```

### 2. Configuration package.json
```json
{
  "name": "shelve-public",
  "version": "1.0.0",
  "description": "Module public pour le projet Shelve",
  "dependencies": {
    "react": "^18.2.0",
    "react-dom": "^18.2.0",
    "react-router-dom": "^6.8.0",
    "axios": "^1.3.0",
    "react-hook-form": "^7.43.0",
    "react-query": "^3.39.0",
    "styled-components": "^5.3.0",
    "react-toastify": "^9.1.0"
  },
  "devDependencies": {
    "@testing-library/jest-dom": "^5.16.0",
    "@testing-library/react": "^13.4.0",
    "@testing-library/user-event": "^13.5.0",
    "react-scripts": "5.0.1"
  },
  "scripts": {
    "start": "react-scripts start",
    "build": "react-scripts build",
    "test": "react-scripts test",
    "eject": "react-scripts eject"
  }
}
```

### 3. Configuration API (src/services/api.js)
```javascript
import axios from 'axios';

const API_BASE_URL = process.env.REACT_APP_SHELVE_API_URL || 'http://localhost:8000/api';

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  timeout: 10000,
});

// Intercepteur pour les erreurs
api.interceptors.response.use(
  (response) => response,
  (error) => {
    console.error('API Error:', error);
    return Promise.reject(error);
  }
);

export default api;
```

### 4. Services API spécialisés

#### Service principal (src/services/shelveApi.js)
Intégrez les endpoints des contrôleurs publics :
- Events (PublicEventController) : getEvents, getEvent, registerToEvent
- News (PublicNewsController) : getNews, getNewsArticle
- Records (PublicRecordController) : getRecords, searchRecords
- Pages (PublicPageController) : getPages, getPage
- Templates (PublicTemplateController) : getTemplates, getTemplate
- Search (PublicSearchLogController) : performSearch, getSearchHistory
- Feedback (PublicFeedbackController) : submitFeedback, getFeedbackStatus
- Document Requests (PublicDocumentRequestController) : submitRequest, trackRequest
- Responses (PublicResponseController) : getResponses, submitResponse
- User Management (PublicUserController) : registerUser, getUserProfile

#### Service Chat (src/services/chatApi.js)
Intégrez les endpoints de chat :
- Chat (PublicChatController) : getChats, createChat, joinChat
- Messages (PublicChatMessageController) : getMessages, sendMessage
- Participants (PublicChatParticipantController) : getParticipants, addParticipant

#### Service Documents (src/services/documentApi.js)
Intégrez les endpoints de documents :
- Attachments (PublicResponseAttachmentController) : uploadAttachment, downloadAttachment

### 5. Hooks personnalisés spécialisés

#### Hook Chat (src/hooks/useChat.js)
Gérez l'état du chat en temps réel, les messages, les participants et les notifications

#### Hook Events (src/hooks/useEvents.js)
Gérez les événements, les inscriptions et les filtres d'événements

#### Hook Documents (src/hooks/useDocuments.js)
Gérez les demandes de documents, le tracking et les téléchargements

#### Hook Search (src/hooks/useSearch.js)
Implémentez la recherche avancée avec historique et suggestions

#### Hook Auth (src/hooks/useAuth.js)
Gérez l'authentification publique et les profils utilisateurs

### 6. Routage principal 
Implémentez les routes suivantes dans App.jsx :
- `/` : Page d'accueil avec actualités et événements récents
- `/events` : Liste des événements publics avec filtres et recherche
- `/events/:id` : Détail d'un événement avec formulaire d'inscription
- `/news` : Liste des actualités avec pagination
- `/news/:id` : Article de presse détaillé
- `/records` : Recherche et consultation des archives publiques
- `/records/:id` : Détail d'un enregistrement
- `/documents/request` : Formulaire de demande de documents
- `/documents/track/:id` : Suivi d'une demande de document
- `/feedback` : Formulaire de commentaires et suggestions
- `/chat` : Interface de chat public (si activé)
- `/user/register` : Inscription utilisateur
- `/user/dashboard` : Tableau de bord utilisateur
- `/templates` : Consultation des modèles publics
- `/search` : Page de recherche avancée

### 7. Fonctionnalités principales à implémenter

#### Gestion des événements
- Affichage calendaire des événements
- Filtres par catégorie, date, lieu
- Système d'inscription avec confirmation
- Gestion des listes d'attente

#### Système de chat
- Chat public en temps réel
- Gestion des participants
- Historique des messages
- Modération automatique

#### Demandes de documents
- Formulaire de demande avec upload de pièces justificatives
- Système de tracking avec notifications
- Téléchargement sécurisé des réponses

#### Archives et recherche
- Moteur de recherche avancé avec filtres
- Consultation des archives publiques
- Historique des recherches
- Export des résultats

#### Feedback et réponses
- Système de commentaires publics
- Formulaires de réponse adaptés
- Suivi des demandes
- Notifications automatiques

#### Gestion utilisateur
- Inscription et profil public
- Historique des activités
- Préférences de notifications
- Gestion des abonnements

### 8. Intégrations techniques

#### Temps réel
Implémentez WebSockets ou Server-Sent Events pour :
- Messages de chat en temps réel
- Notifications de nouveaux événements
- Mises à jour de statut des demandes
- Alertes système

#### Recherche avancée
Intégrez un système de recherche avec :
- Suggestions automatiques
- Filtres multiples
- Historique personnalisé
- Sauvegarde des recherches

#### Gestion des fichiers
Implémentez :
- Upload de fichiers avec validation
- Prévisualisation des documents
- Téléchargement sécurisé
- Compression automatique

### 9. Interface utilisateur

#### Design responsive
Créez une interface adaptée à :
- Desktop (navigation complète)
- Tablette (navigation adaptée)
- Mobile (navigation simplifiée)

#### Composants réutilisables
Développez des composants pour :
- Calendrier d'événements
- Interface de chat
- Formulaires dynamiques
- Système de notifications
- Galerie de documents
- Moteur de recherche

#### Accessibilité
Implémentez :
- Navigation au clavier
- Support des lecteurs d'écran
- Contrastes élevés
- Textes alternatifs

### 10. Performance et sécurité

#### Optimisations
- Lazy loading des composants
- Cache intelligent des données
- Compression des images
- Minification des assets

#### Sécurité
- Validation côté client et serveur
- Protection CSRF
- Limitation du taux de requêtes
- Sanitisation des entrées utilisateur
