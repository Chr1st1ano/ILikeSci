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
py --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Python is not installed or not in PATH.
    echo Please install Python from python.org to use the Content Generator.
    pause
    exit /b
)
echo Python found!

:: 2. Install Dependencies
echo.
echo [2/4] Installing Python dependencies (Science Tools)...
py -m pip install mysql-connector-python python-docx --quiet
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
    py curriculum_importer.py
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
