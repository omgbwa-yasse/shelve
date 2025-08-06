@echo off
echo ===================================================
echo        TEST ENDPOINT REFORMULATION SIMPLIFIEE
echo ===================================================
echo.

REM Configuration
set MCP_URL=http://localhost:3001
set ENDPOINT=%MCP_URL%/api/records/reformulate

echo 🌐 URL du serveur: %MCP_URL%
echo 📡 Endpoint: %ENDPOINT%
echo.

echo 🔍 Test 1: Vérification du serveur...
curl -s %MCP_URL%/api/health > nul
if %errorlevel% neq 0 (
    echo ❌ Serveur non disponible
    echo    Démarrez le serveur avec: npm run dev
    goto :end
)
echo ✅ Serveur disponible
echo.

echo 📤 Test 2: Reformulation simple...
echo Données d'entrée: Document simple avec ID, nom et date
curl -X POST %ENDPOINT% ^
  -H "Content-Type: application/json" ^
  -d "{\"id\":\"TEST001\",\"name\":\"Documents travaux mairie\",\"date\":\"1920-1925\",\"content\":\"Dossier des travaux d'agrandissement de la mairie entre 1920 et 1925\"}"
echo.
echo.

echo 📤 Test 3: Reformulation avec auteur...
echo Données d'entrée: Document avec auteur
curl -X POST %ENDPOINT% ^
  -H "Content-Type: application/json" ^
  -d "{\"id\":\"TEST002\",\"name\":\"Plans école\",\"author\":{\"name\":\"Architecte Dubois\"},\"content\":\"Plans de construction de la nouvelle école primaire\"}"
echo.
echo.

echo 📤 Test 4: Reformulation avec enfants...
echo Données d'entrée: Document avec sous-documents
curl -X POST %ENDPOINT% ^
  -H "Content-Type: application/json" ^
  -d "{\"id\":\"TEST003\",\"name\":\"Dossier construction\",\"children\":[{\"name\":\"Plans\",\"date\":\"1958\"},{\"name\":\"Devis\",\"date\":\"1959\"}]}"
echo.
echo.

echo 📤 Test 5: Document minimal...
echo Données d'entrée: Seulement ID et nom
curl -X POST %ENDPOINT% ^
  -H "Content-Type: application/json" ^
  -d "{\"id\":\"TEST004\",\"name\":\"Registre personnel\"}"
echo.
echo.

:end
echo ===================================================
echo                   TESTS TERMINES
echo ===================================================
echo.
echo 📚 Pour plus de tests: npm run test:reformulation
echo 📖 Documentation: docs/SIMPLE_REFORMULATION.md
pause
