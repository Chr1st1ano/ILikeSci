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
echo [2/4] Installing Python dependencies (Science Tools)...
"%PYTHON_BIN%" -m pip install mysql-connector-python python-docx --quiet
if %errorlevel% neq 0 (
    echo [WARNING] Could not install dependencies automatically.
    echo Make sure you have an internet connection.
) else (
    echo Dependencies installed successfully!
)

:: 3. XAMPP Check
echo.
echo [3/4] Database Check
echo IMPORTANT: Please open XAMPP Control Panel and START:
echo    - Apache
echo    - MySQL
echo.
echo Once they are GREEN, press any key to continue.
pause

:: 4. Initialize Database
echo.
echo [4/4] Initializing Database...
start http://localhost/ILikeSci/init_db.php
timeout /t 5 >nul

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
echo Launching the ILikeSci Teacher Portal...
start http://localhost/ILikeSci/index.html

echo.
echo Done! Happy teaching!
timeout /t 3 >nul
exit
