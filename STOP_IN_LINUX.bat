@echo off
title Stop Services in Linux (WSL)
color 0C
cls
echo ====================================================================
echo          STOPPING ALL SERVICES INSIDE UBUNTU LINUX (WSL)
echo ====================================================================
echo.
wsl.exe -d Ubuntu -u airana --cd /mnt/c/Users/hp/Desktop/ccc bash STOP_LINUX_SERVICES.sh
echo.
pause
