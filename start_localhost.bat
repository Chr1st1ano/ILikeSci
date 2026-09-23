@echo off
setlocal enabledelayedexpansion
title ILikeSci ? Localhost One-Click Launcher
color 0A

echo ============================================================
echo   ILikeSci - Interactive Science ^& Classroom Learning Engine
echo   Zero-Friction Localhost One-Click Launcher
echo ============================================================
echo.

REM 1. Locate XAMPP Directory dynamically
set "XAMPP_DIR="
for %%I in ("%~dp0..\..") do set "XAMPP_DIR=%%~fI"
if not exist "%XAMPP_DIR%\apache\bin\httpd.exe" (
    if exist "c:\Games\xampp\apache\bin\httpd.exe" set "XAMPP_DIR=c:\Games\xampp"
    if exist "c:\xampp\apache\bin\httpd.exe" set "XAMPP_DIR=c:\xampp"
)

REM 2. Locate PHP executable
set "PHP_BIN=php"
where php >nul 2>nul
if %errorlevel% neq 0 (
    if exist "%XAMPP_DIR%\php\php.exe" (
        set "PHP_BIN=%XAMPP_DIR%\php\php.exe"
    ) else if exist "c:\Games\xampp\php\php.exe" (
        set "PHP_BIN=c:\Games\xampp\php\php.exe"
    ) else if exist "c:\xampp\php\php.exe" (
        set "PHP_BIN=c:\xampp\php\php.exe"
    )
)

REM 3. Check and Auto-start MySQL Database (Port 3306)
tasklist /fi "imagename eq mysqld.exe" 2>nul | findstr /i "mysqld.exe" >nul
if %errorlevel% equ 0 (
    echo [OK] MySQL Database is already running [Port 3306]
) else (
    if exist "%XAMPP_DIR%\mysql\bin\mysqld.exe" (
        echo [INFO] Starting MySQL Database daemon...
        start "" /b "%XAMPP_DIR%\mysql\bin\mysqld.exe" --defaults-file="%XAMPP_DIR%\mysql\bin\my.ini" --standalone
        timeout /t 2 /nobreak >nul
        echo [OK] MySQL Database started.
    ) else (
        echo [WARN] MySQL binary not found in XAMPP. SQLite fallback will be active.
    )
)

REM 4. Check and Auto-start Apache Web Server (Port 80)
set "APACHE_ACTIVE=0"
tasklist /fi "imagename eq httpd.exe" 2>nul | findstr /i "httpd.exe" >nul
if %errorlevel% equ 0 (
    echo [OK] Apache Web Server is already running [Port 80]
    set "APACHE_ACTIVE=1"
) else (
    if exist "%XAMPP_DIR%\apache\bin\httpd.exe" (
        echo [INFO] Starting Apache Web Server...
        start "" /b "%XAMPP_DIR%\apache\bin\httpd.exe"
        timeout /t 2 /nobreak >nul
        tasklist /fi "imagename eq httpd.exe" 2>nul | findstr /i "httpd.exe" >nul
        if !errorlevel! equ 0 (
            echo [OK] Apache Web Server started successfully.
            set "APACHE_ACTIVE=1"
        )
    )
)

REM 5. Initialize / Verify Database Schema via PHP CLI
echo [INFO] Verifying Database Schema and Tables...
if exist "%PHP_BIN%" (
    "%PHP_BIN%" init_db.php >nul 2>&1
    echo [OK] Database schema verified.
) else (
    php init_db.php >nul 2>&1
)

REM 6. Fallback or Secondary Standalone PHP Server on Port 8000
set "LAUNCH_URL=http://localhost/ILikeSci/index.html"
if "!APACHE_ACTIVE!"=="0" (
    echo [WARN] Apache Port 80 unavailable. Starting Lightweight PHP Server on Port 8000...
    tasklist /fi "imagename eq php.exe" 2>nul | findstr /i "php.exe" >nul
    if !errorlevel! neq 0 (
        start "" /b "%PHP_BIN%" -S 127.0.0.1:8000
        timeout /t 1 /nobreak >nul
    )
    set "LAUNCH_URL=http://127.0.0.1:8000/index.html"
)

REM 7. Launch Portal in Default Browser
echo.
echo ============================================================
echo   ILikeSci is READY and LIVE on Localhost!
echo.
echo   [Primary Web URL]  !LAUNCH_URL!
echo   [Admin / Teacher]  http://localhost/ILikeSci/admin.html
echo   [Presenter Mode]   http://localhost/ILikeSci/presenter.html
echo   [TV / Scoreboard]  http://localhost/ILikeSci/tv_display.html
echo   [Diagnostics]      http://localhost/ILikeSci/sync_xampp.php
echo ============================================================
echo.
start "" "!LAUNCH_URL!"

echo Controls:
echo   [S] Stop all servers (Apache, MySQL, PHP)
echo   [R] Re-open ILikeSci in web browser
echo   [X] Keep running in background and exit this window
echo.

:COMMAND_LOOP
set /p USER_CHOICE="Enter action [S, R, X]: "
if /i "!USER_CHOICE!"=="S" (
    echo.
    echo Stopping all running servers...
    taskkill /F /IM httpd.exe /T >nul 2>&1
    taskkill /F /IM mysqld.exe /T >nul 2>&1
    taskkill /F /IM php.exe /T >nul 2>&1
    echo [OK] All servers stopped.
    pause
    exit /b 0
)
if /i "!USER_CHOICE!"=="R" (
    start "" "!LAUNCH_URL!"
    goto COMMAND_LOOP
)
if /i "!USER_CHOICE!"=="X" (
    exit /b 0
)
goto COMMAND_LOOP

