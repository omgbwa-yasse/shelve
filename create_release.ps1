# Script PowerShell pour créer la release GitHub v2.0
$owner = "omgbwa-yasse"
$repo = "shelve"
$tag = "v2.0"
$releaseName = "v2.0 'Eyenga deborah'"

$releaseBody = @"
## 🚀 Version majeure avec améliorations de sécurité

Cette version 2.0 apporte des améliorations significatives en matière de sécurité et de robustesse.

## 🔐 Corrections de Sécurité

### Vulnérabilités Critiques Corrigées
- **API Routes sécurisées** : Restriction des routes publiques pour éviter l'exposition non autorisée des données
- **Protection XSS renforcée** : Correction de 6 vulnérabilités Cross-Site Scripting dans les vues Blade
  - ``resources/views/public/templates/show.blade.php``
  - ``resources/views/public/pages/show.blade.php``
  - ``resources/views/public/news/show.blade.php``
  - ``resources/views/bulletin-boards/posts/show.blade.php``
  - ``resources/views/bulletin-boards/events/show.blade.php``
  - ``resources/views/slips/show.blade.php``

### Vulnérabilités de Priorité Moyenne Corrigées
- **Protection IDOR** : Implémentation d'autorisations Gate dans les contrôleurs
  - ``BulletinBoardController`` : Ajout d'autorisations pour bulletinboards_*
  - ``MailReceivedController`` : Ajout d'autorisations pour mail_*
- **Configuration de production** : Sécurisation du mode debug et des variables d'environnement

### Vulnérabilités de Priorité Basse Corrigées
- **Routes de test supprimées** : Élimination de la route ``/test-batch`` de développement
- **En-têtes de sécurité** : Ajout d'un middleware SecurityHeadersMiddleware
- **CORS sécurisé** : Configuration CORS plus restrictive avec domaines autorisés
- **Mots de passe robustes** : Nouvelle règle StrongPassword avec critères stricts

## 🛠️ Nouvelles Fonctionnalités de Sécurité

### Middleware de Sécurité
- **SecurityHeadersMiddleware** : Ajoute automatiquement les en-têtes de sécurité essentiels
- **CorsMiddleware amélioré** : Configuration basée sur les domaines autorisés

### Validation des Mots de Passe
- **StrongPassword Rule** : Validation stricte des mots de passe avec critères robustes

### Système d'Autorisation
- **Migration vers Gates** : Remplacement des policies par des Gates respectant PermissionSeeder
- **Permissions granulaires** : Contrôle d'accès fin par fonctionnalité

## 📦 Installation et Mise à Jour

1. Mettre à jour les dépendances : ``composer install --no-dev``
2. Vider les caches : ``php artisan config:clear && php artisan route:clear``
3. Reconstruire les caches : ``php artisan config:cache && php artisan route:cache``
4. Configurer les variables d'environnement
5. Vérifier les permissions des utilisateurs

## ⚠️ Notes Importantes

- **Breaking Changes** : La validation des mots de passe est plus stricte
- **Configuration requise** : CORS_ALLOWED_ORIGINS doit être configuré en production
- **Permissions** : Vérifier que les utilisateurs ont les bonnes permissions Gate

---

**Dédiée à Eyenga Deborah** - Cette release renforce significativement la sécurité de l'application Shelve.
"@

$releaseData = @{
    tag_name = $tag
    target_commitish = "main"
    name = $releaseName
    body = $releaseBody
    draft = $false
    prerelease = $false
} | ConvertTo-Json -Depth 3

# Afficher les instructions pour créer la release
Write-Host "Pour créer la release GitHub, exécutez cette commande avec votre token GitHub :" -ForegroundColor Green
Write-Host ""
Write-Host "curl -X POST \\" -ForegroundColor Yellow
Write-Host "  -H 'Accept: application/vnd.github.v3+json' \\" -ForegroundColor Yellow
Write-Host "  -H 'Authorization: token YOUR_GITHUB_TOKEN' \\" -ForegroundColor Yellow
Write-Host "  https://api.github.com/repos/$owner/$repo/releases \\" -ForegroundColor Yellow
Write-Host "  -d '$($releaseData -replace '"', '\"')'" -ForegroundColor Yellow
Write-Host ""
Write-Host "Ou visitez : https://github.com/$owner/$repo/releases/new?tag=$tag" -ForegroundColor Cyan
