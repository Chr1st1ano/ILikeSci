@echo off
setlocal enabledelayedexpansion
title ILikeSci ^& ILaykSay Dual Launcher
echo ============================================================
echo   Launching Both ILikeSci (Port 8000) ^& ILaykSay (Port 8088)
echo ============================================================
echo.

:: 1. Locate XAMPP Directory dynamically
set "XAMPP_DIR="
for %%I in ("%~dp0..\..") do set "XAMPP_DIR=%%~fI"
if not exist "%XAMPP_DIR%\apache\bin\httpd.exe" (
    if exist "c:\Games\xampp\apache\bin\httpd.exe" set "XAMPP_DIR=c:\Games\xampp"
    if exist "c:\xampp\apache\bin\httpd.exe" set "XAMPP_DIR=c:\xampp"
)

:: 2. Ensure MySQL is active for shared database connectivity
tasklist /fi "imagename eq mysqld.exe" 2>nul | findstr /i "mysqld.exe" >nul
if %errorlevel% neq 0 (
    if exist "%XAMPP_DIR%\mysql\bin\mysqld.exe" (
        echo [INFO] Starting MySQL Database daemon...
        start "" /b "%XAMPP_DIR%\mysql\bin\mysqld.exe" --defaults-file="%XAMPP_DIR%\mysql\bin\my.ini" --standalone
        timeout /t 2 /nobreak >nul
    )
)

start "ILikeSci (Main System - 8000)" cmd /c "%~dp0start_ilikesci.bat"
timeout /t 2 /nobreak >nul
start "ILaykSay (Testing Instance - 8088)" cmd /c "%~dp0start_ilayksay.bat"

echo.
echo Both instances launched successfully!
echo - Main System:       http://127.0.0.1:8000
echo - Testing Instance:  http://127.0.0.1:8088
echo.
