@echo off
title Payate CC - Stop All Services
color 0c
cls

echo ====================================================================
echo                   STOPPING ALL PAYATE SERVICES
echo ====================================================================
echo.
echo Stopping Tor Daemon...
taskkill /F /IM tor.exe /T 2>NUL
echo Stopping PHP Web Server...
taskkill /F /IM php.exe /T 2>NUL

echo.
echo All website and Tor processes have been stopped successfully.
echo.
echo ====================================================================
pause
