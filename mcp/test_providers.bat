@echo off
echo ===========================================
echo   Test des providers IA - Serveur MCP
echo ===========================================
echo.

cd %~dp0

echo 1. Vérification de l'installation des dépendances...
if not exist node_modules (
    echo Installation des dépendances NPM...
    npm install
) else (
    echo Dépendances déjà installées.
)
echo.

echo 2. Vérification de la configuration...
if not exist ..\.env (
    echo ERREUR: Fichier .env non trouvé dans le répertoire parent
    echo Veuillez configurer les variables d'environnement
    pause
    exit /b 1
)
echo Configuration trouvée.
echo.

echo 3. Test de connectivité des providers...
echo.
echo Ollama (http://localhost:11434):
curl -s http://localhost:11434/api/tags > nul
if %ERRORLEVEL%==0 (
    echo   ✓ Ollama disponible
) else (
    echo   ✗ Ollama non disponible
)

echo LM Studio (http://localhost:1234):
curl -s http://localhost:1234/v1/models > nul
if %ERRORLEVEL%==0 (
    echo   ✓ LM Studio disponible
) else (
    echo   ✗ LM Studio non disponible
)

echo AnythingLLM (http://localhost:3001):
curl -s http://localhost:3001/v1/models > nul
if %ERRORLEVEL%==0 (
    echo   ✓ AnythingLLM disponible
) else (
    echo   ✗ AnythingLLM non disponible
)
echo.

echo 4. Démarrage du serveur MCP en mode test...
npm run test
echo.

echo Test terminé. Appuyez sur une touche pour continuer...
pause > nul
