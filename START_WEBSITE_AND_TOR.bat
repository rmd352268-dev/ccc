@echo off
title Payate CC - 1-Click Server & Telegram Bots Launcher
color 0b
cls

echo ====================================================================
echo             PAYATE CC - 24/7 SERVER & TELEGRAM BOTS HOST
echo ====================================================================
echo.

:: 1. Start Tor Daemon
echo [1/5] Checking Tor Hidden Service Daemon...
tasklist /FI "IMAGENAME eq tor.exe" 2>NUL | find /I /N "tor.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo   [OK] Tor Daemon is already active.
) else (
    echo   [+] Starting Tor Daemon in background...
    if exist "C:\Users\hp\tor_service\tor\tor.exe" (
        start /B "" "C:\Users\hp\tor_service\tor\tor.exe" -f "C:\Users\hp\tor_service\torrc"
    ) else (
        start /B "" tor.exe -f "C:\Users\hp\tor_service\torrc"
    )
    echo   [OK] Tor Daemon launched.
)

:: 2. Start Laravel PHP Server
echo.
echo [2/5] Checking Laravel Web Server (Port 8000)...
tasklist /FI "IMAGENAME eq php.exe" 2>NUL | find /I /N "php.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo   [OK] PHP Web Server is already active on Port 8000.
) else (
    echo   [+] Starting PHP Artisan Server on 127.0.0.1:8000...
    cd /d "C:\Users\hp\Desktop\ccc"
    if exist "C:\Users\hp\php\php.exe" (
        start /B "" "C:\Users\hp\php\php.exe" artisan serve --host=127.0.0.1 --port=8000
    ) else (
        start /B "" php artisan serve --host=127.0.0.1 --port=8000
    )
    echo   [OK] PHP Web Server launched.
)

:: 3. Start Telegram Admin Bot
echo.
echo [3/5] Starting Telegram Master Admin Bot...
cd /d "C:\Users\hp\Desktop\ccc"
if exist "C:\Program Files\Python314\pythonw.exe" (
    start /B "" "C:\Program Files\Python314\pythonw.exe" telegram_admin_bot.pyw
) else (
    start /B "" pythonw.exe telegram_admin_bot.pyw
)
echo   [OK] Admin Bot is active and listening.

:: 4. Start Telegram Public Support Bot
echo.
echo [4/5] Starting Telegram Live Support Bot (@payate_desk_bot)...
if exist "C:\Program Files\Python314\pythonw.exe" (
    start /B "" "C:\Program Files\Python314\pythonw.exe" telegram_support_bot.pyw
) else (
    start /B "" pythonw.exe telegram_support_bot.pyw
)
echo   [OK] Public Customer Support Bot is active and relaying messages.

:: 5. Start Auto Git Sync Engine
echo.
echo [5/5] Starting GitHub Auto-Sync & Backup Engine...
if exist "C:\Program Files\Python314\pythonw.exe" (
    start /B "" "C:\Program Files\Python314\pythonw.exe" auto_git_sync.py
) else (
    start /B "" pythonw.exe auto_git_sync.py
)
echo   [OK] Auto Git Sync Engine is active.

echo.
echo ====================================================================
echo                   ALL SYSTEMS RUNNING & HOSTED!
echo ====================================================================
echo.
echo  * Onion Domain (Tor Browser):
echo    http://7625n5aonepn2vui2qfpnj27kyv565eq7ztwpuowa4heemu2zvy5h5ad.onion
echo.
echo  * Local Website Link:
echo    http://127.0.0.1:8000
echo.
echo  * Telegram Admin Bot:   @MypayteAdmin_Bot
echo  * Telegram Support Bot: @payate_desk_bot
echo.
echo  All services are hosted and running in the background.
echo  You can close this window at anytime.
echo ====================================================================
echo.
pause
