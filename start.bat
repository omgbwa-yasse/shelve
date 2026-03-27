@echo off
setlocal EnableDelayedExpansion

echo.
echo ============================================
echo   SHELVE - Demarrage
echo ============================================
echo.

if not exist "artisan" (
    echo [ERREUR] Lancez ce script depuis le repertoire du projet SHELVE.
    pause & exit /b 1
)

set ROOT=%~dp0
set ROOT=%ROOT:~0,-1%
set PHP=%ROOT%\php\php.exe
set PHPINI=%ROOT%\php\php.ini
set MYSQLD=%ROOT%\mysql\bin\mysqld.exe
set MYSQL_DATA=%ROOT%\mysql\data
set MYSQL_BASE=%ROOT%\mysql

if not exist "%PHP%" (
    echo [ERREUR] PHP non trouve. Lancez d'abord setup.bat
    pause & exit /b 1
)

if not exist "%MYSQL_DATA%\mysql" (
    echo [ERREUR] MySQL non initialise. Lancez d'abord setup.bat
    pause & exit /b 1
)

:: Demarrer MySQL si pas deja lance
tasklist /FI "IMAGENAME eq mysqld.exe" 2>nul | find /I "mysqld.exe" >nul
if %ERRORLEVEL% neq 0 (
    echo Demarrage de MySQL (port 3307)...
    start /B "" "%MYSQLD%" --basedir="%MYSQL_BASE%" --datadir="%MYSQL_DATA%" --port=3307 --bind-address=127.0.0.1 --console 2>nul
    timeout /t 3 /nobreak >nul
) else (
    echo MySQL deja en cours d'execution.
)

echo.
echo Demarrage de SHELVE...
echo Acces : http://localhost:8000
echo Appuyez sur Ctrl+C pour arreter.
echo.
"%PHP%" -c "%PHPINI%" artisan serve --port=8000
endlocal
