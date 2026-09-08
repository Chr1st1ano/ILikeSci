@echo off
title ILaykSay — Testing Instance (Port 8088)
echo ============================================================
echo   ILaykSay - Testing ^& Development Instance (Port 8088)
echo   Standalone Lightweight Launcher (Isolated Database)
echo ============================================================
echo.

set TARGET_DIR=%~dp0..\ILaykSay
if not exist "%TARGET_DIR%" set TARGET_DIR=%~dp0

:: 1. Search for PHP executable
set PHP_BIN=php
where php >nul 2>nul
if %errorlevel% equ 0 goto start_server

if exist "c:\Games\xampp\php\php.exe" (
    set PHP_BIN=c:\Games\xampp\php\php.exe
    goto start_server
)

if exist "c:\xampp\php\php.exe" (
    set PHP_BIN=c:\xampp\php\php.exe
    goto start_server
)

echo [WARNING] PHP executable not found automatically.
pause
exit /b 1

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
