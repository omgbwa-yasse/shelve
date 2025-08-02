@echo off
echo 🔄 Basculement vers Redis en cours...

echo Étape 1: Test de la connexion Redis...
redis-cli ping >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo ❌ Redis n'est pas démarré ou installé
    echo 💡 Veuillez suivre le guide INSTALL_REDIS.md
    pause
    exit /b 1
)

echo ✅ Redis est opérationnel

echo Étape 2: Sauvegarde de la configuration actuelle...
copy .env .env.backup.%date:~-4,4%%date:~-10,2%%date:~-7,2%

echo Étape 3: Application de la configuration Redis...
copy .env.redis-ready .env.redis-temp

REM Remplacer les lignes dans .env
powershell -Command "(Get-Content .env) -replace '^CACHE_STORE=.*', 'CACHE_STORE=redis' | Out-File -encoding UTF8 .env.temp"
powershell -Command "(Get-Content .env.temp) -replace '^SESSION_DRIVER=.*', 'SESSION_DRIVER=redis' | Out-File -encoding UTF8 .env.temp2"
powershell -Command "(Get-Content .env.temp2) -replace '^QUEUE_CONNECTION=.*', 'QUEUE_CONNECTION=redis' | Out-File -encoding UTF8 .env"

del .env.temp .env.temp2 .env.redis-temp

echo Étape 4: Nettoyage et mise en cache...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo Étape 5: Application de la nouvelle configuration...
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ✅ Configuration Redis appliquée avec succès!
echo 🚀 Votre application utilise maintenant Redis pour :
echo   - Cache principal
echo   - Sessions utilisateur
echo   - Files d'attente

echo 💾 Sauvegarde de l'ancienne configuration : .env.backup.%date:~-4,4%%date:~-10,2%%date:~-7,2%

pause
