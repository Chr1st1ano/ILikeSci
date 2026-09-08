@echo off
title ILikeSci ^& ILaykSay Dual Launcher
echo ============================================================
echo   Launching Both ILikeSci (Port 8000) ^& ILaykSay (Port 8088)
echo ============================================================
echo.

start "ILikeSci (Main System - 8000)" cmd /c "%~dp0start_ilikesci.bat"
timeout /t 2 /nobreak >nul
start "ILaykSay (Testing Instance - 8088)" cmd /c "%~dp0start_ilayksay.bat"

echo Both instances launched successfully!
echo - Main System:       http://127.0.0.1:8000
echo - Testing Instance:  http://127.0.0.1:8088
