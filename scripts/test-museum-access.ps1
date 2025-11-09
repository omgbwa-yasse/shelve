# Test d'Accès au Module Museum

Write-Host "`n=== VERIFICATION DU MODULE MUSEUM ===" -ForegroundColor Cyan
Write-Host ""

# 1. Vérifier les routes Museum
Write-Host "1. Routes Museum disponibles :" -ForegroundColor Yellow
php artisan route:list --name=museum 2>&1 | Select-String "Showing"
Write-Host ""

# 2. Vérifier la route principale
Write-Host "2. Route principale (museum.collections.index) :" -ForegroundColor Yellow
php artisan route:list --name=museum.collections.index 2>&1 | Select-String "museum/collections"
Write-Host ""

# 3. Vérifier la permission museum_access
Write-Host "3. Permission museum_access :" -ForegroundColor Yellow
php artisan tinker --execute="echo App\Models\Permission::where('name', 'museum_access')->exists() ? 'EXISTS' : 'NOT FOUND';"
Write-Host ""

# 4. Vérifier que le superadmin a la permission
Write-Host "4. Superadmin a museum_access :" -ForegroundColor Yellow
php artisan tinker --execute="`$role = App\Models\Role::where('name', 'superadmin')->first(); echo `$role->permissions()->where('name', 'museum_access')->exists() ? 'YES' : 'NO';"
Write-Host ""

# 5. Compter les permissions Museum
Write-Host "5. Nombre de permissions Museum :" -ForegroundColor Yellow
php artisan tinker --execute="echo App\Models\Permission::where('category', 'museum')->count() . ' permissions';"
Write-Host ""

# 6. Vérifier les contrôleurs Museum
Write-Host "6. Contrôleurs Museum disponibles :" -ForegroundColor Yellow
$controllers = Get-ChildItem -Path "app\Http\Controllers\museum" -Filter "*.php" -ErrorAction SilentlyContinue
if ($controllers) {
    foreach ($controller in $controllers) {
        Write-Host "   - $($controller.Name)" -ForegroundColor Green
    }
} else {
    Write-Host "   ERREUR: Aucun contrôleur trouvé!" -ForegroundColor Red
}
Write-Host ""

# 7. Vérifier le sous-menu
Write-Host "7. Sous-menu Museum :" -ForegroundColor Yellow
if (Test-Path "resources\views\submenu\museum.blade.php") {
    Write-Host "   - museum.blade.php : EXISTS" -ForegroundColor Green
} else {
    Write-Host "   - museum.blade.php : NOT FOUND" -ForegroundColor Red
}
Write-Host ""

# 8. Résumé final
Write-Host "=== RESULTAT FINAL ===" -ForegroundColor Cyan
Write-Host "Le module Museum devrait maintenant être visible dans le menu principal" -ForegroundColor Green
Write-Host "pour l'utilisateur superadmin@example.com" -ForegroundColor Green
Write-Host ""
Write-Host "Prochaine étape : Connectez-vous et vérifiez que le lien 'Museum'" -ForegroundColor Yellow
Write-Host "apparaît dans la barre de navigation principale." -ForegroundColor Yellow
Write-Host ""
