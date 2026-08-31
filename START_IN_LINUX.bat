@echo off
title Start Services in Linux (WSL)
color 0B
cls
echo ====================================================================
echo          STARTING ALL SERVICES INSIDE UBUNTU LINUX (WSL)
echo ====================================================================
echo.
wsl.exe -d Ubuntu -u airana --cd /mnt/c/Users/hp/Desktop/ccc bash START_LINUX_SERVICES.sh
echo.
pause
