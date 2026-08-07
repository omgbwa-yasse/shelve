# Script de déploiement Phase 1 - Extension Attachments
# Date: 6 novembre 2025
# Description: Exécute la migration et les tests de la Phase 1

Write-Host "============================================" -ForegroundColor Cyan
Write-Host "  Phase 1: Extension Attachments" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan
Write-Host ""

# 1. Vérifier l'environnement
Write-Host "[1/5] Vérification de l'environnement..." -ForegroundColor Yellow
php artisan --version
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Erreur: PHP/Laravel non disponible" -ForegroundColor Red
    exit 1
}
Write-Host "✅ Environnement Laravel OK" -ForegroundColor Green
Write-Host ""

# 2. Vérifier la connexion à la base de données
Write-Host "[2/5] Vérification de la connexion à la BDD..." -ForegroundColor Yellow
php artisan db:show --database=mysql
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Erreur: Impossible de se connecter à la BDD" -ForegroundColor Red
    exit 1
}
Write-Host "✅ Connexion BDD OK" -ForegroundColor Green
Write-Host ""

# 3. Afficher le statut des migrations
Write-Host "[3/5] Statut des migrations..." -ForegroundColor Yellow
php artisan migrate:status
Write-Host ""

# 4. Demander confirmation
Write-Host "⚠️  Voulez-vous exécuter la migration d'extension des attachments ?" -ForegroundColor Yellow
Write-Host "   Fichier: 2025_11_06_000001_extend_attachments_table.php" -ForegroundColor Gray
$confirmation = Read-Host "   Continuer ? (O/n)"
if ($confirmation -eq 'n' -or $confirmation -eq 'N') {
    Write-Host "❌ Migration annulée par l'utilisateur" -ForegroundColor Yellow
    exit 0
}

# 5. Exécuter la migration
Write-Host ""
Write-Host "[4/5] Exécution de la migration..." -ForegroundColor Yellow
php artisan migrate --path=database/migrations/2025_11_06_000001_extend_attachments_table.php

if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Erreur lors de la migration" -ForegroundColor Red
    Write-Host "   Utilisez cette commande pour annuler:" -ForegroundColor Gray
    Write-Host "   php artisan migrate:rollback --step=1" -ForegroundColor Gray
    exit 1
}
Write-Host "✅ Migration exécutée avec succès" -ForegroundColor Green
Write-Host ""

# 6. Vérifier la structure de la table
Write-Host "[5/5] Vérification de la structure de la table attachments..." -ForegroundColor Yellow
php artisan db:table attachments
Write-Host ""

# 7. Exécuter les tests
Write-Host "════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  Tests de validation" -ForegroundColor Cyan
Write-Host "════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "Exécution des tests d'intégrité..." -ForegroundColor Yellow
php artisan test --filter=AttachmentExtensionTest

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "⚠️  Certains tests ont échoué" -ForegroundColor Yellow
    Write-Host "   La migration est toujours active." -ForegroundColor Gray
    Write-Host "   Vérifiez les erreurs ci-dessus." -ForegroundColor Gray
} else {
    Write-Host ""
    Write-Host "✅ Tous les tests sont passés avec succès !" -ForegroundColor Green
}

# 8. Résumé
Write-Host ""
Write-Host "════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "  Résumé" -ForegroundColor Cyan
Write-Host "════════════════════════════════════════" -ForegroundColor Cyan
Write-Host ""
Write-Host "✅ Phase 1 - Extension Attachments: TERMINÉE" -ForegroundColor Green
Write-Host ""
Write-Host "Modifications appliquées:" -ForegroundColor White
Write-Host "  • 6 nouveaux types ENUM ajoutés" -ForegroundColor Gray
Write-Host "  • 10 nouveaux champs ajoutés" -ForegroundColor Gray
Write-Host "  • 4 index de performance créés" -ForegroundColor Gray
Write-Host ""
Write-Host "Fichiers concernés:" -ForegroundColor White
Write-Host "  • database/migrations/2025_11_06_000001_extend_attachments_table.php" -ForegroundColor Gray
Write-Host "  • app/Models/Attachment.php" -ForegroundColor Gray
Write-Host "  • tests/Feature/AttachmentExtensionTest.php" -ForegroundColor Gray
Write-Host ""
Write-Host "Prochaine étape: Phase 2 - Renommage Records → RecordPhysicals" -ForegroundColor Cyan
Write-Host ""
Write-Host "Pour annuler cette migration:" -ForegroundColor Yellow
Write-Host "  php artisan migrate:rollback --step=1" -ForegroundColor Gray
Write-Host ""
