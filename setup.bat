@echo off
setlocal EnableDelayedExpansion

echo.
echo ============================================
echo   SHELVE - Installation initiale
echo ============================================
echo.

:: Verifier qu'on est dans le bon repertoire
if not exist "artisan" (
    echo [ERREUR] Le fichier "artisan" est introuvable.
    echo          Lancez ce script depuis le repertoire du projet SHELVE.
    echo.
    pause
    exit /b 1
)

:: Chemins absolus bases sur le repertoire courant
set ROOT=%~dp0
set ROOT=%ROOT:~0,-1%
set PHP=%ROOT%\php\php.exe
set PHPINI=%ROOT%\php\php.ini
set MYSQL_BIN=%ROOT%\mysql\bin
set MYSQL_DATA=%ROOT%\mysql\data
set MYSQL_BASE=%ROOT%\mysql
set MYSQLD=%MYSQL_BIN%\mysqld.exe
set MYSQL=%MYSQL_BIN%\mysql.exe

:: --- Verification PHP ---
if not exist "%PHP%" (
    echo [ERREUR] PHP portable non trouve dans .\php\php.exe
    pause & exit /b 1
)
echo [1/6] PHP detecte :
"%PHP%" -c "%PHPINI%" -r "echo '       PHP ' . PHP_VERSION . PHP_EOL;"
echo.

:: --- Verification MySQL ---
if not exist "%MYSQLD%" (
    echo [ERREUR] MySQL non trouve dans .\mysql\bin\mysqld.exe
    pause & exit /b 1
)

:: --- Initialisation MySQL (seulement si data/ est vide) ---
echo [2/6] Verification MySQL...
if not exist "%MYSQL_DATA%\mysql" (
    echo       Premier lancement : initialisation de la base MySQL...
    "%MYSQLD%" --initialize-insecure --basedir="%MYSQL_BASE%" --datadir="%MYSQL_DATA%" --console 2>&1
    if !ERRORLEVEL! neq 0 (
        echo [ERREUR] Initialisation MySQL echouee.
        pause & exit /b 1
    )
    echo       Base MySQL initialisee.
) else (
    echo       MySQL deja initialise — ignore.
)
echo.

:: --- Demarrage MySQL en arriere-plan ---
echo [3/6] Demarrage de MySQL (port 3307)...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I "mysqld.exe" >nul
if %ERRORLEVEL% equ 0 (
    echo       MySQL deja en cours d'execution — ignore.
) else (
    start /B "" "%MYSQLD%" --basedir="%MYSQL_BASE%" --datadir="%MYSQL_DATA%" --port=3307 --bind-address=127.0.0.1 --console 2>nul
    echo       Attente demarrage MySQL...
    timeout /t 5 /nobreak >nul
)
echo.

:: --- Creation de la base de donnees ---
echo [4/6] Creation de la base de donnees shelve...
"%MYSQL%" -u root -h 127.0.0.1 -P 3307 --connect-timeout=10 -e "CREATE DATABASE IF NOT EXISTS shelve CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>nul
if %ERRORLEVEL% neq 0 (
    echo [ERREUR] Impossible de se connecter a MySQL. Attendez quelques secondes et relancez setup.bat
    pause & exit /b 1
)
echo       Base 'shelve' prete.
echo.

:: --- Configuration .env ---
echo [5/6] Configuration de l'environnement Laravel...
if not exist "%ROOT%\.env" (
    copy "%ROOT%\.env.example" "%ROOT%\.env" >nul
    echo       .env cree depuis .env.example
)

:: Forcer la config MySQL dans .env (port 3307, base shelve)
"%PHP%" -c "%PHPINI%" -r "
$env = file_get_contents('.env');
$env = preg_replace('/^DB_CONNECTION=.*/m', 'DB_CONNECTION=mysql', $env);
$env = preg_replace('/^#?DB_HOST=.*/m', 'DB_HOST=127.0.0.1', $env);
$env = preg_replace('/^#?DB_PORT=.*/m', 'DB_PORT=3307', $env);
$env = preg_replace('/^#?DB_DATABASE=.*/m', 'DB_DATABASE=shelve', $env);
$env = preg_replace('/^#?DB_USERNAME=.*/m', 'DB_USERNAME=root', $env);
$env = preg_replace('/^#?DB_PASSWORD=.*/m', 'DB_PASSWORD=', $env);
file_put_contents('.env', $env);
echo 'Config DB ecrite dans .env' . PHP_EOL;
"
echo.

:: --- Setup Laravel (migrations + seed) ---
echo [6/6] Migrations et seeding Laravel...
"%PHP%" -c "%PHPINI%" artisan app:setup
if %ERRORLEVEL% neq 0 (
    echo.
    echo [ERREUR] app:setup a echoue. Verifiez les messages ci-dessus.
    pause & exit /b 1
)

echo.
echo ============================================
echo   Installation terminee !
echo   Lancez start.bat pour demarrer SHELVE.
echo ============================================
echo.
pause
endlocal
