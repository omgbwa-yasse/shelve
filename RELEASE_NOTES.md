# Shelve v2.2 - Notes de Release

## 🎯 Points clés

- Nouveau flux d'import par Glisser-Déposer (Drag & Drop) pour créer et déposer des archives
- Traitement IA plus robuste avec exceptions dédiées et meilleure extraction des pièces jointes
- Limitation de taille d'upload côté app + affichage des limites serveur (utile pour php.ini)
- Comptage des pages PDF optionnel et retour d'information à l'utilisateur
- Nouveau module Contacts pour les Organisations (modèle, migration, contrôleur, vues)
- Migration vers Vite pour les assets (première étape) et amélioration du layout

## 🆕 Nouvelles fonctionnalités

- Drag & Drop pour la création/dépôt d'archives avec traitement IA
- Limite d'upload dans l'UI + messages explicites
- Option de comptage des pages PDF et affichage des contraintes serveur
- Module Contacts Organisations (CRUD complet)

## 🛠️ Améliorations

- Extraction de pièces jointes plus fiable et logique IA durcie
- Gestion des erreurs mieux structurée via exceptions personnalisées
- Améliorations de l'UX (libellés, extensions supportées) en glisser-déposer
- Optimisations et correctifs sur les dépôts (compteurs, cohérence)
- Vite configuré pour les assets; stacking de styles dans le layout

## 🐞 Corrections

- Corrections sur le module de dépôts (affichages et comptages)
- Nettoyage de fichiers de test dépréciés

## ⚙️ Technique

- Exceptions personnalisées pour traitements IA
- Configuration initiale Vite

## 📦 Mise à jour

1. Mettre à jour les dépendances PHP et JS
2. Recompiler les assets (Vite) si utilisé
3. Vider puis reconstruire les caches

Étapes recommandées:

```powershell
composer install
npm install ; npm run build
php artisan config:clear ; php artisan route:clear ; php artisan view:clear
php artisan config:cache ; php artisan route:cache ; php artisan view:cache
```

## ❗ Points d'attention

- Si vous utilisez des limites d'upload personnalisées, synchronisez php.ini (post_max_size, upload_max_filesize) avec l'UI
- La migration vers Vite est amorcée, vérifiez votre pipeline d'assets si vous utilisiez Mix uniquement

---

## Shelve v2.0 "Eyenga deborah" - Notes de Release

## 🚀 Version majeure avec améliorations de sécurité

Cette version 2.0 apporte des améliorations significatives en matière de sécurité et de robustesse.

## 🔐 Corrections de Sécurité

### Vulnérabilités Critiques Corrigées
- **API Routes sécurisées** : Restriction des routes publiques pour éviter l'exposition non autorisée des données
- **Protection XSS renforcée** : Correction de 6 vulnérabilités Cross-Site Scripting dans les vues Blade
  - `resources/views/public/templates/show.blade.php`
  - `resources/views/public/pages/show.blade.php`
  - `resources/views/public/news/show.blade.php`
  - `resources/views/bulletin-boards/posts/show.blade.php`
  - `resources/views/bulletin-boards/events/show.blade.php`
  - `resources/views/slips/show.blade.php`

### Vulnérabilités de Priorité Moyenne Corrigées
- **Protection IDOR** : Implémentation d'autorisations Gate dans les contrôleurs
  - `BulletinBoardController` : Ajout d'autorisations pour bulletinboards_*
  - `MailReceivedController` : Ajout d'autorisations pour mail_*
- **Configuration de production** : Sécurisation du mode debug et des variables d'environnement

### Vulnérabilités de Priorité Basse Corrigées
- **Routes de test supprimées** : Élimination de la route `/test-batch` de développement
- **En-têtes de sécurité** : Ajout d'un middleware SecurityHeadersMiddleware
  - Protection contre le clickjacking (X-Frame-Options)
  - Prévention du sniffing MIME (X-Content-Type-Options)
  - Protection XSS pour navigateurs anciens (X-XSS-Protection)
  - Politique de sécurité du contenu (CSP)
  - Politique de permissions restrictive
- **CORS sécurisé** : Configuration CORS plus restrictive avec domaines autorisés
- **Mots de passe robustes** : Nouvelle règle StrongPassword avec critères stricts

## 🛠️ Nouvelles Fonctionnalités de Sécurité

### Middleware de Sécurité
- **SecurityHeadersMiddleware** : Ajoute automatiquement les en-têtes de sécurité essentiels
- **CorsMiddleware amélioré** : Configuration basée sur les domaines autorisés

### Validation des Mots de Passe
- **StrongPassword Rule** : Validation stricte des mots de passe
  - Minimum 8 caractères
  - Au moins une majuscule, une minuscule, un chiffre
  - Au moins un caractère spécial
  - Protection contre les mots de passe courants

### Système d'Autorisation
- **Migration vers Gates** : Remplacement des policies par des Gates respectant PermissionSeeder
- **Permissions granulaires** : Contrôle d'accès fin par fonctionnalité

## 📋 Configuration

### Variables d'Environnement
Nouvelles variables à configurer dans `.env` :
```bash
# Configuration CORS sécurisée
CORS_ALLOWED_ORIGINS=http://localhost,https://votredomaine.com
```

### En-têtes de Sécurité
Les en-têtes suivants sont automatiquement ajoutés :
- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `X-XSS-Protection: 1; mode=block`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Content-Security-Policy: default-src 'self'...`
- `Permissions-Policy: camera=(), microphone=(), geolocation=()`

## 🔧 Améliorations Techniques

### Structure de Code
- Suppression des routes de développement/test
- Nettoyage des vulnérabilités de sécurité
- Amélioration de la validation des entrées utilisateur

### Performance
- Optimisation du cache des routes
- Middleware de sécurité léger et performant

## 🧪 Tests et Validation

- ✅ Tests de sécurité réussis
- ✅ Cache des routes fonctionnel
- ✅ Middleware de sécurité opérationnel
- ✅ Validation des mots de passe active

## 📦 Installation et Mise à Jour

1. Mettre à jour les dépendances : `composer install --no-dev`
2. Vider les caches : `php artisan config:clear && php artisan route:clear`
3. Reconstruire les caches : `php artisan config:cache && php artisan route:cache`
4. Configurer les variables d'environnement
5. Vérifier les permissions des utilisateurs

## ⚠️ Notes Importantes

- **Breaking Changes** : La validation des mots de passe est plus stricte
- **Configuration requise** : CORS_ALLOWED_ORIGINS doit être configuré en production
- **Permissions** : Vérifier que les utilisateurs ont les bonnes permissions Gate

---

**Dédiée à Eyenga Deborah** - Cette release renforce significativement la sécurité de l'application Shelve.
