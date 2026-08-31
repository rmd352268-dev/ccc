@echo off
title Installing Ubuntu Linux (WSL)
color 0A

:: Check for Administrator Permissions
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ============================================================
    echo Requesting Administrator Permission to install Ubuntu Linux...
    echo ============================================================
    powershell -Command "Start-Process cmd -ArgumentList '/c \"\"%~f0\"\"' -Verb RunAs"
    exit /b
)

echo ============================================================
echo         INSTALLING UBUNTU LINUX SAFELY ON WINDOWS
echo ============================================================
echo.
echo [1/2] Your Desktop and all existing files are 100%% SAFE.
echo [2/2] Installing Windows Subsystem for Linux (Ubuntu)...
echo.
echo Please wait, downloading and setting up Ubuntu...
echo.

wsl.exe --install -d Ubuntu

echo.
echo ============================================================
echo Setup completed! 
echo If Windows asks to restart your PC, please restart.
echo After restart, Ubuntu will open automatically.
echo ============================================================
echo.
pause
