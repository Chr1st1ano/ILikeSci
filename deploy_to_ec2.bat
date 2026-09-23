@echo off
title ILikeSci — Deploy to AWS EC2
color 0B

echo ============================================================
echo   ILikeSci - Deploy to AWS EC2 Instance
echo ============================================================
echo.

powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0deploy_to_ec2.ps1"

echo.
pause
