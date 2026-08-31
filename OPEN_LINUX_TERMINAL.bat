@echo off
title Ubuntu Linux Terminal - CCC Project
color 0A
cls
echo ====================================================================
echo             OPENING UBUNTU LINUX TERMINAL (WSL 2)
echo             Direct Folder: /mnt/c/Users/hp/Desktop/ccc
echo ====================================================================
echo.
echo Useful shortcuts inside Linux:
echo   - start_all     : Start Website Server and all Telegram Bots
echo   - stop_all      : Stop all running services
echo   - status        : Check status of running services
echo   - artisan ...   : Run php artisan commands
echo.
echo ====================================================================
echo.
wsl.exe -d Ubuntu -u airana --cd /mnt/c/Users/hp/Desktop/ccc
