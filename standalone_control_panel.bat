@echo off
setlocal enabledelayedexpansion
title ILikeSci ^& ILaykSay — Standalone Server Control Panel

:MENU
cls
echo ===============================================================================
echo        ILikeSci ^& ILaykSay — Standalone Portable Server Control Panel
echo             Catered specifically for Low-End Hardware ^& Classroom Testing
echo ===============================================================================
echo.
echo   [1] Start ILikeSci (Main System)      -^> http://127.0.0.1:8000
echo   [2] Start ILaykSay (Testing Instance) -^> http://127.0.0.1:8088
echo   [3] Start BOTH Instances in Parallel  -^> Ports 8000 ^& 8088
echo   [4] Open XAMPP Diagnostic Tool        -^> http://127.0.0.1:8000/sync_xampp.php
echo   [5] Initialize / Re-seed Databases   -^> ilikesci_db ^& ilayksay_db
echo   [6] Open Both in Web Browser
echo   [7] Stop All Running Servers
echo   [0] Exit
echo.
echo ===============================================================================
set /p CHOICE="Select an option (0-7): "

if "%CHOICE%"=="1" goto START_ILIKESCI
if "%CHOICE%"=="2" goto START_ILAYKSAY
if "%CHOICE%"=="3" goto START_BOTH
if "%CHOICE%"=="4" goto OPEN_DIAG
if "%CHOICE%"=="5" goto REINIT_DBS
if "%CHOICE%"=="6" goto OPEN_BROWSER
if "%CHOICE%"=="7" goto STOP_ALL
if "%CHOICE%"=="0" exit /b 0

echo [ERROR] Invalid selection.
pause
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
start "ILikeSci Server (Port 8000)" cmd /c "start_ilikesci.bat"
timeout /t 2 /nobreak >nul
start "ILaykSay Testing Server (Port 8088)" cmd /c "start_ilayksay.bat"
goto MENU

:OPEN_DIAG
start "" "http://127.0.0.1:8000/sync_xampp.php"
goto MENU

:REINIT_DBS
echo.
echo Re-initializing ilikesci_db and ilayksay_db...
php init_db.php
if exist "..\ILaykSay\init_db.php" php "..\ILaykSay\init_db.php"
echo.
echo [OK] Both databases initialized successfully!
pause
goto MENU

:OPEN_BROWSER
start "" "http://127.0.0.1:8000/index.html"
start "" "http://127.0.0.1:8088/index.html"
goto MENU

:STOP_ALL
echo.
echo Stopping all running PHP server instances...
taskkill /F /IM php.exe /T >nul 2>&1
echo [OK] All standalone server instances stopped.
pause
goto MENU
