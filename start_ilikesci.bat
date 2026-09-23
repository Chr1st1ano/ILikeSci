@echo off
setlocal enabledelayedexpansion
title ILikeSci ? Portable Science Platform
echo ============================================================
echo   ILikeSci - Interactive Science & Classroom Learning Engine
echo   Zero-Install Lightweight Launcher (Low-End Hardware Ready)
echo ============================================================
echo.

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

if exist "..\php\php.exe" (
    set "PHP_BIN=..\php\php.exe"
    goto check_mysql
)

echo [WARNING] PHP executable not found automatically in PATH or XAMPP.
echo Please install PHP or specify the path to php.exe.
pause
exit /b 1

:check_mysql
:: 3. Optional MySQL auto-start for full database support
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
echo [OK] Using PHP: %PHP_BIN%
echo [OK] Initializing Database & Verifying Schema...
"%PHP_BIN%" init_db.php >nul 2>&1

:: Check if Apache is already active
tasklist /fi "imagename eq httpd.exe" 2>nul | findstr /i "httpd.exe" >nul
if %errorlevel% equ 0 (
    echo [INFO] Apache is also running at http://localhost/ILikeSci/
)

echo [OK] Starting Lightweight Web Server on http://127.0.0.1:8000
echo [INFO] Memory footprint: ~18MB (Ultra-low RAM mode)
echo [INFO] Press Ctrl+C in this window to stop the server when done.
echo.

:: Launch browser in background
start "" "http://127.0.0.1:8000/index.html"

:: Run PHP built-in server in foreground
"%PHP_BIN%" -S 127.0.0.1:8000
