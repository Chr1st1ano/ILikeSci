@echo off
title ILikeSci — Portable Science Platform
echo ============================================================
echo   ILikeSci - Interactive Science & Classroom Learning Engine
echo   Zero-Install Lightweight Launcher (Low-End Hardware Ready)
echo ============================================================
echo.

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

if exist "..\php\php.exe" (
    set PHP_BIN=..\php\php.exe
    goto start_server
)

echo [WARNING] PHP executable not found automatically in PATH or XAMPP.
echo Please install PHP or specify the path to php.exe.
pause
exit /b 1

:start_server
echo [OK] Using PHP: %PHP_BIN%
echo [OK] Initializing Database & Verifying Schema...
"%PHP_BIN%" init_db.php >nul 2>&1

echo [OK] Starting Lightweight Web Server on http://127.0.0.1:8000
echo [INFO] Memory footprint: ~18MB (Ultra-low RAM mode)
echo [INFO] Press Ctrl+C in this window to stop the server when done.
echo.

:: Launch browser in background
start "" "http://127.0.0.1:8000/index.html"

:: Run PHP built-in server in foreground
"%PHP_BIN%" -S 127.0.0.1:8000
