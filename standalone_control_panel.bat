@echo off
setlocal enabledelayedexpansion
title ILikeSci ^& ILaykSay ? Standalone Server Control Panel

:: Locate XAMPP Directory dynamically
set "XAMPP_DIR="
for %%I in ("%~dp0..\..") do set "XAMPP_DIR=%%~fI"
if not exist "%XAMPP_DIR%\apache\bin\httpd.exe" (
    if exist "c:\Games\xampp\apache\bin\httpd.exe" set "XAMPP_DIR=c:\Games\xampp"
    if exist "c:\xampp\apache\bin\httpd.exe" set "XAMPP_DIR=c:\xampp"
)

:: Locate PHP binary
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

:MENU
cls
:: Probe real-time service status
set "STATUS_APACHE=[STOPPED]"
set "STATUS_MYSQL=[STOPPED]"
set "STATUS_PHP=[STOPPED]"

tasklist /fi "imagename eq httpd.exe" 2>nul | findstr /i "httpd.exe" >nul && set "STATUS_APACHE=[RUNNING]"
tasklist /fi "imagename eq mysqld.exe" 2>nul | findstr /i "mysqld.exe" >nul && set "STATUS_MYSQL=[RUNNING]"
tasklist /fi "imagename eq php.exe" 2>nul | findstr /i "php.exe" >nul && set "STATUS_PHP=[RUNNING]"

echo ===============================================================================
echo        ILikeSci ^& ILaykSay ? Standalone Portable Server Control Panel
echo             Catered specifically for Low-End Hardware ^& Classroom Testing
echo ===============================================================================
echo  Live Service Status:
echo    - Apache Web Server (Port 80):   !STATUS_APACHE!
echo    - MySQL Database (Port 3306):    !STATUS_MYSQL!
echo    - Standalone PHP Server:         !STATUS_PHP!
echo ===============================================================================
echo.
echo   [1] Start Full Localhost Stack (Apache + MySQL) -^> http://localhost/ILikeSci/
echo   [2] Start ILikeSci Portable (Port 8000)          -^> http://127.0.0.1:8000
echo   [3] Start ILaykSay Testing Instance (Port 8088)  -^> http://127.0.0.1:8088
echo   [4] Start BOTH Portable Instances in Parallel    -^> Ports 8000 ^& 8088
echo   [5] Open XAMPP Diagnostic Tool                   -^> http://localhost/ILikeSci/sync_xampp.php
echo   [6] Initialize / Re-seed Databases              -^> ilikesci_db ^& ilayksay_db
echo   [7] Open ILikeSci in Web Browser                 -^> http://localhost/ILikeSci/index.html
echo   [8] Stop ALL Running Servers (Apache, MySQL, PHP)
echo   [0] Exit
echo.
echo ===============================================================================
set /p CHOICE="Select an option (0-8): "

if "%CHOICE%"=="1" goto START_FULL_XAMPP
if "%CHOICE%"=="2" goto START_ILIKESCI
if "%CHOICE%"=="3" goto START_ILAYKSAY
if "%CHOICE%"=="4" goto START_BOTH
if "%CHOICE%"=="5" goto OPEN_DIAG
if "%CHOICE%"=="6" goto REINIT_DBS
if "%CHOICE%"=="7" goto OPEN_BROWSER
if "%CHOICE%"=="8" goto STOP_ALL
if "%CHOICE%"=="0" exit /b 0

echo [ERROR] Invalid selection.
pause
goto MENU

:START_FULL_XAMPP
echo.
echo Starting Full Localhost Stack (Apache + MySQL)...
tasklist /fi "imagename eq mysqld.exe" 2>nul | findstr /i "mysqld.exe" >nul
if %errorlevel% neq 0 (
    if exist "%XAMPP_DIR%\mysql\bin\mysqld.exe" (
        echo [INFO] Starting MySQL...
        start "" /b "%XAMPP_DIR%\mysql\bin\mysqld.exe" --defaults-file="%XAMPP_DIR%\mysql\bin\my.ini" --standalone
        timeout /t 2 /nobreak >nul
    )
)
tasklist /fi "imagename eq httpd.exe" 2>nul | findstr /i "httpd.exe" >nul
if %errorlevel% neq 0 (
    if exist "%XAMPP_DIR%\apache\bin\httpd.exe" (
        echo [INFO] Starting Apache...
        start "" /b "%XAMPP_DIR%\apache\bin\httpd.exe"
        timeout /t 2 /nobreak >nul
    )
)
echo [OK] Initializing Database Schema...
if exist "%PHP_BIN%" (
    "%PHP_BIN%" init_db.php >nul 2>&1
) else (
    php init_db.php >nul 2>&1
)
start "" "http://localhost/ILikeSci/index.html"
goto MENU

:START_ILIKESCI
echo.
echo Starting ILikeSci on Port 8000...
start "ILikeSci Server (Port 8000)" cmd /c "start_ilikesci.bat"
goto MENU

:START_ILAYKSAY
echo.
echo Starting ILaykSay on Port 8088...
start "ILaykSay Testing Server (Port 8088)" cmd /c "start_ilayksay.bat"
goto MENU

:START_BOTH
echo.
echo Launching both ILikeSci (8000) and ILaykSay (8088) in parallel...
start "" cmd /c "%~dp0start_both_instances.bat"
goto MENU

:OPEN_DIAG
start "" "http://localhost/ILikeSci/sync_xampp.php"
goto MENU

:REINIT_DBS
echo.
echo Re-initializing ilikesci_db and ilayksay_db...
if exist "%PHP_BIN%" (
    "%PHP_BIN%" init_db.php
    if exist "..\ILaykSay\init_db.php" "%PHP_BIN%" "..\ILaykSay\init_db.php"
) else (
    php init_db.php
    if exist "..\ILaykSay\init_db.php" php "..\ILaykSay\init_db.php"
)
echo.
echo [OK] Both databases initialized successfully!
pause
goto MENU

:OPEN_BROWSER
start "" "http://localhost/ILikeSci/index.html"
goto MENU

:STOP_ALL
echo.
echo Stopping all running servers (Apache, MySQL, PHP)...
taskkill /F /IM php.exe /T >nul 2>&1
taskkill /F /IM httpd.exe /T >nul 2>&1
taskkill /F /IM mysqld.exe /T >nul 2>&1
echo [OK] All server instances stopped.
pause
goto MENU
