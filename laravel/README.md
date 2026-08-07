# 📚 Shelve - Système de Gestion d'Archives Intelligentes

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.21.0-red?style=for-the-badge&logo=laravel" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue?style=for-the-badge&logo=php" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/MySQL-8.0+-orange?style=for-the-badge&logo=mysql" alt="MySQL 8.0+">
  <img src="https://img.shields.io/badge/Vue.js-3.0-green?style=for-the-badge&logo=vue.js" alt="Vue.js 3">
  <img src="https://img.shields.io/badge/AI-Powered-purple?style=for-the-badge&logo=openai" alt="AI Powered">
  <img src="https://img.shields.io/badge/API-RESTful-blue?style=for-the-badge&logo=swagger" alt="RESTful API">
  <img src="https://img.shields.io/badge/Tests-127%20Created-brightgreen?style=for-the-badge" alt="Tests">
  <img src="https://img.shields.io/badge/Progress-92%25-brightgreen?style=for-the-badge" alt="Progress">
</p>

**Shelve** est une plateforme complète de gestion d'archives modernes développée avec Laravel 12, intégrant l'intelligence artificielle pour automatiser et optimiser la gestion documentaire. Ce système offre une solution robuste pour l'archivage, la recherche intelligente, la gestion des workflows et la publication publique de documents.

## 🎯 Projet SpecKit - État Actuel

**Version actuelle** : 1.0-beta  
**Progression** : **95%** (12/13 phases complètes, Phase 13 en cours)  
**Dernière mise à jour** : 7 novembre 2025

### 📊 Project Status

- ✅ **Phases Completed**: 12/13 (95%)
- 🔄 **Current Phase**: Phase 13 - Final Validation (12%)
- 🚀 **Go-Live Date**: November 24, 2025

### Recent Progress

**Phase 13 - Final Validation** 🔄 (12% In Progress - Nov 7, 2025):
- ✅ Validation plan created (PHASE13_PLAN.md - 630 lines)
- ✅ User documentation (USER_MANUAL.md - 510 lines, FAQ.md - 470 lines)
- ✅ API User Guide (API_USER_GUIDE.md - 520 lines)
- ⏳ UAT scenarios preparation
- ⏳ Training materials creation
- ⏳ Security audit execution
- ⏳ Performance validation

**Phase 12 - Production Deployment** ✅ (100% Complete - Nov 7, 2025):
- ✅ Infrastructure documentation (850+ lines)
- ✅ Deployment scripts and CI/CD pipeline (610+ lines)
- ✅ Database migration script (290+ lines)
- ✅ Security hardening script (240+ lines)
- ✅ Performance optimization script (120+ lines)
- ✅ Backup & restore automation (140+ lines)
- ✅ Operations runbook (430+ lines)
- ✅ Testing & validation guide (380+ lines)
- ✅ Go-Live execution plan (480+ lines)

**Total Phase 12 Output**: 11 files, ~3,400 lines  
**Total Phase 13 Output** (so far): 4 files, ~2,130 lines

📖 **Documentation complète** : Voir [`PROJECT_STATUS.md`](PROJECT_STATUS.md), [`PHASE12_COMPLETION_SUMMARY.md`](docs/PHASE12_COMPLETION_SUMMARY.md) et [`PHASE13_PLAN.md`](docs/PHASE13_PLAN.md)

---

## 🚀 Fonctionnalités Principales

### 📁 **Gestion d'Archives Avancée**
- **Gestion de documents** : Upload, organisation et archivage automatisé
- **Support multi-formats** : PDF, images, vidéos, documents Office
- **Générations de codes-barres** : Traçabilité physique des documents
- **Conteneurs intelligents** : Organisation hiérarchique des archives
- **Versioning automatique** : Historique complet des modifications

### 🌐 **API REST Complète**
- **45 endpoints RESTful** : CRUD + workflows + search + statistics
- **OpenAPI 3.0.0** : Documentation interactive Swagger UI
- **Authentication** : Laravel Sanctum (token-based)
- **Rate Limiting** : 60 requêtes/minute
- **File Upload** : Support multipart/form-data (max 50MB)
- **Versioning** : API v1 avec évolution future

### 🔍 **Recherche Intelligente Intégrée**
- **Recherche textuelle avancée** avec TNTSearch
- **Recherche sémantique** basée sur l'IA
- **Filtres multicritères** : dates, auteurs, types, contenus
- **Thésaurus intégré** : Recherche par concepts et relations
- **Indexation automatique** des contenus

### 📧 **Système de Courrier Électronique**
- **Gestion complète des emails** entrants et sortants
- **Workflows automatisés** de traitement du courrier
- **Assignation et délégation** de tâches
- **Suivi des deadlines** et alertes automatiques
- **Archivage intelligent** des correspondances

### 🤖 **Intelligence Artificielle Intégrée**
- **Extraction automatique** de mots-clés et métadonnées
- **Analyse de contenu** et classification automatique
- **Résumés automatiques** de documents longs
- **Chat IA** pour assistance utilisateur
- **Support multi-providers** : Ollama, OpenAI, LM Studio, AnythingLLM

### 🌐 **Portail Public**
- **Publication sélective** de documents publics
- **Interface de recherche** pour le grand public
- **Système de demandes** de documents
- **Gestion des feedbacks** et commentaires
- **Chat public** avec support IA

### 🔄 **Workflows et Automatisation**
- **Workflows configurables** pour tous les processus
- **Assignation automatique** de tâches
- **Escalade conditionnelle** en cas de retard
- **Templates d'emails** personnalisables
- **Notifications multi-canaux**

### 📊 **Thésaurus et Classification**
- **Thésaurus normé SKOS** intégré
- **Relations hiérarchiques** et associatives
- **Import/Export** au format SKOS, CSV, JSON
- **Gestion multilingue** des concepts
- **API RESTful** pour intégrations externes

## 🛠️ Technologies et Architecture

### **Framework Principal**
- **Laravel 12.21.0** - Framework PHP moderne
- **PHP 8.2+** - Langage backend performant
- **MySQL 8.0+** - Base de données relationnelle

### **Frontend et Interface**
- **Vue.js 3** - Interface utilisateur réactive
- **TailwindCSS** - Framework CSS moderne
- **Intervention Image** - Traitement d'images
- **PDF.js** - Visualisation PDF intégrée

### **Intelligence Artificielle**
- **Ollama** - Modèles IA locaux
- **OpenAI API** - GPT et modèles cloud
- **LM Studio** - Modèles locaux personnalisés
- **AnythingLLM** - Plateforme IA unifiée

### **Recherche et Indexation**
- **TNTSearch** - Moteur de recherche Laravel
- **Scout** - Interface de recherche élégante
- **Elasticsearch** (optionnel) - Recherche enterprise

### **Génération de Documents**
- **DomPDF** - Génération PDF
- **PHPWord** - Documents Word
- **Maatwebsite Excel** - Tableurs Excel
- **Codes-barres** - Génération automatique

### **Intégrations et API**
- **RESTful API** complète - 45 endpoints avec OpenAPI 3.0
- **Laravel Sanctum** - Authentication token-based
- **Swagger UI** - Documentation interactive (`/api/documentation`)
- **MCP (Model Context Protocol)** - Serveur d'intégration IA
- **WebSockets** - Notifications temps réel
- **Queue système** - Traitement asynchrone

## 🌐 API REST Documentation

### Accès à la documentation
```bash
# Swagger UI Interactive
http://localhost:8000/api/documentation

# OpenAPI JSON Export
http://localhost:8000/docs

# Fichier local
storage/api-docs/api-docs.json
```

### Endpoints disponibles (45 total)

**Digital Folders** (10 endpoints)
- Liste, détails, CRUD
- Arborescence hiérarchique
- Déplacement de dossiers
- Statistiques

**Digital Documents** (13 endpoints)
- CRUD avec upload multipart
- Gestion des versions
- Workflow d'approbation
- Téléchargement sécurisé
- Recherche avancée

**Artifacts** (12 endpoints)
- Gestion d'artefacts
- Expositions et prêts
- Rapports de conservation
- Valorisation

**Periodicals** (14 endpoints)
- Publications périodiques
- Numéros et articles
- Abonnements
- Recherches multicritères

### Exemple d'utilisation

```bash
# 1. Authentication
POST /api/v1/login
Content-Type: application/json
{
    "email": "user@example.com",
    "password": "password"
}
# Response: { "token": "1|abcdef..." }

# 2. Liste des documents
GET /api/v1/digital-documents?per_page=20
Authorization: Bearer 1|abcdef...

# 3. Upload document
POST /api/v1/digital-documents
Authorization: Bearer 1|abcdef...
Content-Type: multipart/form-data
{
    "name": "Rapport Q3",
    "type_id": 5,
    "folder_id": 12,
    "file": [binary]
}
```

📖 **Documentation complète** : Voir [`docs/OPENAPI_SETUP.md`](docs/OPENAPI_SETUP.md)

## 📋 Prérequis Système

```bash
- PHP >= 8.2
- MySQL >= 8.0 ou MariaDB >= 10.6
- Composer >= 2.0
- Node.js >= 18.0 et npm
- Extensions PHP : pdo, mbstring, openssl, tokenizer, xml, ctype, json, bcmath, fileinfo, gd
- Redis (optionnel, recommandé pour les performances)
- FFmpeg (pour le traitement vidéo)
```

## 🚀 Installation Rapide

### 1. **Cloner le Repository**
```bash
git clone https://github.com/yourusername/shelve.git
cd shelve
```

### 2. **Installation des Dépendances**
```bash
# Dépendances PHP
composer install

# Dépendances JavaScript
npm install && npm run build
```

### 3. **Configuration de l'Environnement**
```bash
# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate
```

### 4. **Configuration Base de Données**
```env
# Éditer .env avec vos paramètres
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=shelve_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 5. **Migration et Initialisation**
```bash
# Créer la base de données
php artisan migrate

# Installer les données de base
php artisan db:seed

# Optimiser l'application
php artisan optimize
```

### 6. **Démarrage du Serveur**
```bash
# Serveur de développement
php artisan serve

# Queue worker (terminal séparé)
php artisan queue:work
```

## 👤 Comptes par Défaut

Après l'installation, vous pouvez vous connecter avec :

```
🔐 Superadministrateur
Email: superadmin@example.com
Mot de passe: superadmin
Permissions: Accès complet au système (125 permissions)
```

## 🔧 Configuration Avancée

### **Configuration IA (Optionnel)**
```env
# Ollama (recommandé pour usage local)
OLLAMA_BASE_URL=http://localhost:11434

# OpenAI (pour usage cloud)
OPENAI_API_KEY=your_openai_key

# LM Studio (modèles locaux personnalisés)
LMSTUDIO_BASE_URL=http://localhost:1234
```

### **Configuration Redis (Recommandé)**
```env
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### **Configuration Email**
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

## 📖 Documentation Complète

### **Modules Principaux**
- 📁 [Gestion d'Archives](docs/archives.md) - Documents, conteneurs, codes-barres
- 📧 [Système de Courrier](docs/mail.md) - Workflows, assignation, archivage
- � [Transferts de Courriers (Parapheur)](docs/batch_transfers.md) - Vers boîtes et chariots, limites et erreurs
- �🔍 [Recherche Avancée](docs/search.md) - TNTSearch, filtres, thésaurus
- 🤖 [Intelligence Artificielle](docs/ai.md) - Configuration, modèles, intégrations
- 🌐 [Portail Public](docs/public.md) - Publication, demandes, chat
- 📊 [Thésaurus](docs/thesaurus.md) - SKOS, import/export, API

### **Administration**
- ⚙️ [Configuration Système](docs/admin/settings.md)
- 👥 [Gestion Utilisateurs](docs/admin/users.md)
- 🔐 [Permissions et Rôles](docs/admin/permissions.md)
- 📈 [Rapports et Analytics](docs/admin/reports.md)

### **Développement**
- 🔧 [API Documentation](docs/api/README.md)
- 🧪 [Tests](docs/development/testing.md)
- 🚀 [Déploiement](docs/deployment/README.md)
- 🔌 [Extensions](docs/development/extensions.md)

## 🛠️ Scripts d'Optimisation

Le projet inclut des scripts d'optimisation prêts à l'emploi :

```bash
# Optimisation pour production
./optimize-production.sh     # Linux/Mac
optimize-production.bat      # Windows

# Basculement vers Redis
./switch-to-redis.bat        # Configuration Redis automatique
```

## 🧪 Tests et Qualité

```bash
# Tests unitaires et fonctionnels
php artisan test

# Analyse de code statique
./vendor/bin/phpstan analyse

# Formatage du code
./vendor/bin/php-cs-fixer fix
```

## 🔐 Sécurité

- **Authentification multi-niveaux** avec Laravel Sanctum
- **Permissions granulaires** par module et action
- **Chiffrement** des données sensibles
- **Logs d'audit** complets
- **Validation** stricte des entrées utilisateur
- **Protection CSRF** et XSS

## 📊 Performances

- **Cache intelligent** avec Redis
- **Optimisation base de données** avec indexation
- **Compression d'images** automatique
- **Lazy loading** des relations Eloquent
- **Queue système** pour tâches lourdes
- **CDN ready** pour assets statiques

## 🌍 Support Multilingue

- **Interface** en français et anglais
- **Thésaurus multilingue** avec traductions
- **Dates et formats** localisés
- **Extensible** pour autres langues

## 🤝 Contribution

Ce projet est développé par **Omgbwa Yasse Emmanuel Fabrice** et **Njandjeu Lahakio David Andreas**.

Pour contribuer :
1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commit vos changements (`git commit -am 'Ajout nouvelle fonctionnalité'`)
4. Push vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Ouvrir une Pull Request

## 📝 Licence

Ce projet est sous licence **MIT**. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 🆘 Support et Contact

- **Documentation** : [docs/](docs/)
- **Issues** : [GitHub Issues](https://github.com/yourusername/shelve/issues)
- **Discussions** : [GitHub Discussions](https://github.com/yourusername/shelve/discussions)

---

<p align="center">
  <strong>Shelve - Révolutionnez votre gestion d'archives avec l'IA</strong><br>
  Développé avec ❤️ en Laravel 12
</p>
