@echo off
setlocal
color 0B
title ILikeSci - First Time Setup

echo ===================================================
echo     ILikeSci Capstone - First Time Setup
echo ===================================================
echo.
echo Hello Oyo! This script will prepare your system.
echo.

:: 1. Check for Python
echo [1/4] Checking Python installation...
set PYTHON_BIN=

:: Check if standard python works (not the dummy Windows Store redirector)
python --version >nul 2>&1
if %errorlevel% equ 0 (
    set PYTHON_BIN=python
    goto found_python
)

:: Check if py launcher works
py --version >nul 2>&1
if %errorlevel% equ 0 (
    set PYTHON_BIN=py
    goto found_python
)

:: Check known local user Python installations
if exist "%LOCALAPPDATA%\Python\pythoncore-3.14-64\python.exe" (
    set "PYTHON_BIN=%LOCALAPPDATA%\Python\pythoncore-3.14-64\python.exe"
    goto found_python
)

for /d %%D in ("%LOCALAPPDATA%\Programs\Python\Python3*") do (
    if exist "%%D\python.exe" (
        set "PYTHON_BIN=%%D\python.exe"
        goto found_python
    )
)

echo [ERROR] Working Python executable not found.
echo Please install Python from python.org to use the Content Generator.
pause
exit /b

:found_python
echo [OK] Working Python found: %PYTHON_BIN%

:: 2. Install Dependencies
echo.
echo [2/4] Installing Python dependencies (Science Tools and PPTX Engine)...
"%PYTHON_BIN%" -m pip install mysql-connector-python python-docx python-pptx --quiet
if %errorlevel% neq 0 (
    echo [WARNING] Could not install dependencies automatically.
    echo Make sure you have an internet connection.
) else (
    echo Dependencies installed successfully!
)

:: 3. XAMPP and Database Auto-Start
echo.
echo [3/4] Checking and Starting Local Services...

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

:: Check & Start MySQL
tasklist /fi "imagename eq mysqld.exe" 2>nul | findstr /i "mysqld.exe" >nul
if %errorlevel% equ 0 (
    echo [OK] MySQL is active [Port 3306]
) else (
    if exist "%XAMPP_DIR%\mysql\bin\mysqld.exe" (
        echo [INFO] Auto-starting MySQL Database...
        start "" /b "%XAMPP_DIR%\mysql\bin\mysqld.exe" --defaults-file="%XAMPP_DIR%\mysql\bin\my.ini" --standalone
        timeout /t 2 /nobreak >nul
        echo [OK] MySQL started successfully.
    ) else (
        echo [WARN] MySQL binary not found in XAMPP. SQLite fallback will be used.
    )
)

:: Check & Start Apache
set "APACHE_ACTIVE=0"
tasklist /fi "imagename eq httpd.exe" 2>nul | findstr /i "httpd.exe" >nul
if %errorlevel% equ 0 (
    echo [OK] Apache is active [Port 80]
    set "APACHE_ACTIVE=1"
) else (
    if exist "%XAMPP_DIR%\apache\bin\httpd.exe" (
        echo [INFO] Auto-starting Apache Web Server...
        start "" /b "%XAMPP_DIR%\apache\bin\httpd.exe"
        timeout /t 2 /nobreak >nul
        tasklist /fi "imagename eq httpd.exe" 2>nul | findstr /i "httpd.exe" >nul
        if %errorlevel% equ 0 (
            echo [OK] Apache started successfully.
            set "APACHE_ACTIVE=1"
        )
    )
)

:: 4. Initialize Database
echo.
echo [4/4] Initializing Database Schema...
if exist "%PHP_BIN%" (
    "%PHP_BIN%" init_db.php
) else (
    php init_db.php
)

set "PORTAL_URL=http://localhost/ILikeSci/index.html"
if "%APACHE_ACTIVE%"=="0" (
    echo [INFO] Starting Lightweight PHP Server on Port 8000...
    tasklist /fi "imagename eq php.exe" 2>nul | findstr /i "php.exe" >nul
    if %errorlevel% neq 0 (
        start "" /b "%PHP_BIN%" -S 127.0.0.1:8000
        timeout /t 1 /nobreak >nul
    )
    set "PORTAL_URL=http://127.0.0.1:8000/index.html"
)

echo.
echo ===================================================
echo     Setup Complete! ILikeSci is ready to go.
echo ===================================================
echo.
set /p choice="Do you want to import questions from Curriculums folder now? (Y/N): "
if /i "%choice%"=="Y" (
    echo.
    echo Scanning Curriculums folder...
    "%PYTHON_BIN%" curriculum_importer.py
    echo Import finished!
    pause
)

echo.
echo Launching the ILikeSci Teacher Portal (%PORTAL_URL%)...
start "" "%PORTAL_URL%"

echo.
echo Done! Happy teaching!
ping 127.0.0.1 -n 3 >nul
exit
