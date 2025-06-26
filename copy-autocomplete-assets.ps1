# Script PowerShell pour copier les assets d'autocomplétion vers le dossier public
# À exécuter depuis la racine du projet Laravel

Write-Host "Copie des assets d'autocomplétion..." -ForegroundColor Green

# Créer les dossiers s'ils n'existent pas
if (-not (Test-Path "public\js")) {
    New-Item -ItemType Directory -Path "public\js" -Force
    Write-Host "Dossier public\js créé" -ForegroundColor Yellow
}

if (-not (Test-Path "public\css")) {
    New-Item -ItemType Directory -Path "public\css" -Force
    Write-Host "Dossier public\css créé" -ForegroundColor Yellow
}

# Copier les fichiers
try {
    Copy-Item "resources\js\record-autocomplete.js" "public\js\record-autocomplete.js" -Force
    Write-Host "✓ JavaScript copié: public\js\record-autocomplete.js" -ForegroundColor Green

    Copy-Item "resources\css\record-autocomplete.css" "public\css\record-autocomplete.css" -Force
    Write-Host "✓ CSS copié: public\css\record-autocomplete.css" -ForegroundColor Green

    Write-Host "`nAssets d'autocomplétion copiés avec succès!" -ForegroundColor Green
    Write-Host "Les fichiers sont maintenant accessibles via:" -ForegroundColor Cyan
    Write-Host "- {{ asset('js/record-autocomplete.js') }}" -ForegroundColor White
    Write-Host "- {{ asset('css/record-autocomplete.css') }}" -ForegroundColor White
}
catch {
    Write-Host "Erreur lors de la copie: $_" -ForegroundColor Red
    exit 1
}

Write-Host "`nTerminé!" -ForegroundColor Green
