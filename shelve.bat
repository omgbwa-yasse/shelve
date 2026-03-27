@echo off
cd /d "%~dp0"

echo Demarrage de SHELVE...

:: Demarrer npm run dev en arriere-plan
start "SHELVE - Vite" cmd /c "npm run dev"

:: Attendre 2 secondes
timeout /t 2 /nobreak >nul

:: Demarrer php artisan serve
start "SHELVE - PHP" cmd /c "php artisan serve --port=8000"

:: Attendre que le serveur demarre
timeout /t 3 /nobreak >nul

:: Ouvrir le navigateur
start http://localhost:8000
