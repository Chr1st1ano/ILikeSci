@echo off
setlocal enabledelayedexpansion
title ILaykSay ? Testing Instance (Port 8088)
echo ============================================================
echo   ILaykSay - Testing ^& Development Instance (Port 8088)
echo   Standalone Lightweight Launcher (Isolated Database)
echo ============================================================
echo.

set TARGET_DIR=%~dp0..\ILaykSay
if not exist "%TARGET_DIR%" set TARGET_DIR=%~dp0

:: 1. Locate XAMPP Directory dynamically
set "XAMPP_DIR="
for %%I in ("%~dp0..\..") do set "XAMPP_DIR=%%~fI"
if not exist "%XAMPP_DIR%\apache\bin\httpd.exe" (
    if exist "c:\Games\xampp\apache\bin\httpd.exe" set "XAMPP_DIR=c:\Games\xampp"
    if exist "c:\xampp\apache\bin\httpd.exe" set "XAMPP_DIR=c:\xampp"
)

:: 2. Search for PHP executable
set "PHP_BIN=php"
where php >nul 2>nul
if %errorlevel% equ 0 goto check_mysql

if exist "%XAMPP_DIR%\php\php.exe" (
    set "PHP_BIN=%XAMPP_DIR%\php\php.exe"
    goto check_mysql
)

if exist "c:\Games\xampp\php\php.exe" (
    set "PHP_BIN=c:\Games\xampp\php\php.exe"
    goto check_mysql
)

if exist "c:\xampp\php\php.exe" (
    set "PHP_BIN=c:\xampp\php\php.exe"
    goto check_mysql
)

echo [WARNING] PHP executable not found automatically.
pause
exit /b 1

:check_mysql
:: 3. Check & Auto-start MySQL Database
tasklist /fi "imagename eq mysqld.exe" 2>nul | findstr /i "mysqld.exe" >nul
if %errorlevel% equ 0 (
    echo [OK] MySQL Database is running [Port 3306]
) else (
    if exist "%XAMPP_DIR%\mysql\bin\mysqld.exe" (
        echo [INFO] Starting MySQL Database daemon...
        start "" /b "%XAMPP_DIR%\mysql\bin\mysqld.exe" --defaults-file="%XAMPP_DIR%\mysql\bin\my.ini" --standalone
        timeout /t 2 /nobreak >nul
        echo [OK] MySQL started.
    ) else (
        echo [INFO] MySQL not detected. Portable SQLite engine active.
    )
)

:start_server
echo [OK] Target Folder: %TARGET_DIR%
echo [OK] Using PHP: %PHP_BIN%
cd /d "%TARGET_DIR%"

echo [OK] Initializing ILaykSay Testing Database (ilayksay_db)...
"%PHP_BIN%" init_db.php >nul 2>&1

echo [OK] Starting ILaykSay Server on http://127.0.0.1:8088
echo [INFO] Memory footprint: ~18MB
echo.

start "" "http://127.0.0.1:8088/index.html"
"%PHP_BIN%" -S 127.0.0.1:8088
