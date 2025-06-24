# Portail Public - SHELVES

## Description

Le portail public est un module de l'application SHELVES qui permet aux utilisateurs externes d'accéder à certaines fonctionnalités du système d'archivage. Il offre une interface séparée pour la consultation, la demande de documents et l'interaction avec les services d'archives.

## Fonctionnalités

### Gestion des Utilisateurs
- **Inscription** : Nouveaux utilisateurs peuvent créer un compte
- **Approbation** : Système d'approbation manuelle ou automatique des comptes
- **Vérification email** : Validation de l'adresse email obligatoire
- **Authentification** : Connexion sécurisée avec tokens Sanctum

### Documents et Records
- **Consultation** : Accès aux documents publics publiés
- **Recherche** : Système de recherche avancée avec filtres
- **Demandes** : Possibilité de demander l'accès à des documents
- **Téléchargement** : Téléchargement sécurisé des documents autorisés

### Événements
- **Consultation** : Liste des événements publics
- **Inscription** : Inscription aux événements ouverts
- **Notifications** : Alertes pour les événements à venir

### Communication
- **Chat** : Système de messagerie pour communiquer avec les archivistes
- **Feedback** : Système de retours et évaluations
- **Actualités** : Consultation des actualités du service d'archives

### Pages Statiques
- **CMS Simple** : Gestion de pages d'information
- **Navigation** : Système de navigation hiérarchique

## Architecture

### Backend (Laravel)
```
app/
├── Models/               # Modèles Eloquent pour le portail public
│   ├── PublicUser.php
│   ├── PublicRecord.php
│   ├── PublicEvent.php
│   └── ...
├── Controllers/          # Contrôleurs API et Web
│   ├── PublicUserController.php
│   ├── PublicDocumentRequestController.php
│   └── ...
├── Policies/            # Politiques d'autorisation
│   ├── PublicDocumentRequestPolicy.php
│   └── PublicEventPolicy.php
├── Middleware/          # Middleware spécifiques
│   └── EnsurePublicUserIsApproved.php
└── Helpers/
    └── PublicPortalHelper.php
```

### Frontend (React)
```
shelve-public/
├── src/
│   ├── components/      # Composants React réutilisables
│   ├── pages/          # Pages principales
│   ├── services/       # Services API
│   ├── hooks/          # Hooks personnalisés
│   └── utils/          # Utilitaires
└── public/
```

### Base de Données
- **15 tables** dédiées au portail public avec préfixe `public_`
- **Relations** bien définies avec contraintes d'intégrité
- **Soft Deletes** activé sur toutes les tables
- **Indexes** optimisés pour les performances

## Installation et Configuration

### 1. Migration de la base de données
```bash
php artisan migrate
```

### 2. Génération de données de test
```bash
php artisan db:seed --class=PublicPortalSeeder
```

### 3. Configuration des variables d'environnement
Copier les variables du portail public depuis `.env.example` vers `.env` :

```bash
# Configuration du portail public
PUBLIC_PORTAL_AUTO_APPROVE=false
PUBLIC_PORTAL_REQUIRES_VERIFICATION=true
PUBLIC_PORTAL_MAX_REQUESTS_PER_DAY=10
PUBLIC_PORTAL_CHAT_ENABLED=true
# ... (voir .env.example pour la liste complète)
```

### 4. Configuration du frontend React
```bash
cd shelve-public
npm install
npm start
```

## API

### Authentification
```bash
# Connexion
POST /api/public/users/login
Content-Type: application/json

{
    "email": "user@example.com",
    "password": "password"
}
```

### Documents
```bash
# Liste des documents publics
GET /api/public/records

# Recherche
POST /api/public/records/search
Content-Type: application/json

{
    "query": "terme de recherche",
    "filters": {}
}

# Demande d'accès
POST /api/public/documents/request
Authorization: Bearer <token>
Content-Type: application/json

{
    "record_id": 1,
    "request_type": "digital",
    "reason": "Recherche personnelle"
}
```

### Événements
```bash
# Liste des événements
GET /api/public/events

# Inscription à un événement
POST /api/public/events/{id}/register
Authorization: Bearer <token>
```

## Tests

### Exécution des tests
```bash
# Tests complets
php artisan test

# Tests spécifiques au portail public
php artisan test --filter="Public"

# Tests avec couverture
php artisan test --coverage
```

### Tests disponibles
- **PublicUserControllerTest** : Tests d'authentification et gestion des utilisateurs
- **PublicDocumentRequestTest** : Tests des demandes de documents
- **Factories** : Factories pour générer des données de test

## Sécurité

### Autorisations
- **Policies** : Contrôle d'accès basé sur les politiques Laravel
- **Middleware** : Vérification de l'approbation des utilisateurs
- **Rate Limiting** : Limitation du nombre de requêtes par minute

### Validation
- **Validation côté serveur** : Toutes les entrées sont validées
- **CSRF Protection** : Protection contre les attaques CSRF
- **XSS Protection** : Échappement automatique des données

### Données sensibles
- **Chiffrement** : Mots de passe hashés avec bcrypt
- **Tokens** : Authentification par tokens Sanctum
- **Logs** : Journalisation des actions importantes

## Performance

### Optimisations
- **Indexes** : Index sur les colonnes fréquemment utilisées
- **Pagination** : Pagination des listes longues
- **Cache** : Mise en cache des requêtes coûteuses
- **Eager Loading** : Chargement optimisé des relations

### Monitoring
- **Logs de recherche** : Suivi des termes de recherche populaires
- **Métriques** : Statistiques d'utilisation du portail
- **Rate Limiting** : Surveillance de l'usage de l'API

## Maintenance

### Tâches périodiques
- **Nettoyage des logs** : Suppression automatique des anciens logs
- **Expiration des liens** : Suppression des liens expirés
- **Archivage des messages** : Archivage des anciens messages de chat

### Commandes Artisan personnalisées
```bash
# Nettoyage des données expirées
php artisan public-portal:cleanup

# Synchronisation des données
php artisan public-portal:sync

# Génération de rapports
php artisan public-portal:report
```

## Support et Documentation

### Logs
- **Application** : `storage/logs/laravel.log`
- **Accès API** : Logs spécifiques dans les contrôleurs
- **Erreurs** : Reporting automatique des erreurs

### Configuration avancée
Toutes les configurations sont centralisées dans `config/public_portal.php` et peuvent être surchargées via les variables d'environnement.

### Développement
- **Code Style** : Respect des standards PSR-12
- **Documentation** : Documentation inline avec PHPDoc
- **Tests** : Couverture de tests élevée pour les fonctionnalités critiques
