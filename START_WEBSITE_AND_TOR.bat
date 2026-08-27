@echo off
title Laravel Tor Onion Service & Bot Host
cls
echo ===================================================
echo     STARTING LARAVEL, TOR, BOT & AUTO GIT SYNC
echo ===================================================
echo.

echo [1/5] Checking Tor Daemon...
tasklist /FI "IMAGENAME eq tor.exe" 2>NUL | find /I /N "tor.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo Tor Daemon is already running.
) else (
    echo Starting Tor Daemon...
    start /B "" "C:\Users\hp\tor_service\tor\tor.exe" -f "C:\Users\hp\tor_service\torrc"
)

echo [2/4] Checking Laravel Web Server...
tasklist /FI "IMAGENAME eq php.exe" 2>NUL | find /I /N "php.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo PHP Laravel Server is already running.
) else (
    echo Starting PHP Artisan Server on 127.0.0.1:8000...
    cd /d "C:\Users\hp\Desktop\ccc"
    start /B "" php artisan serve --host=127.0.0.1 --port=8000
)

echo [3/5] Checking Telegram Admin Bot...
start /B "" pythonw.exe "C:\Users\hp\Desktop\ccc\telegram_admin_bot.pyw"
echo Telegram Admin Bot is active.

echo [4/5] Checking Telegram Live Support Bot...
start /B "" pythonw.exe "C:\Users\hp\Desktop\ccc\telegram_support_bot.pyw"
echo Telegram Live Support Bot is active.

echo [5/5] Starting Auto Git Sync Engine...
start /B "" pythonw.exe "C:\Users\hp\Desktop\ccc\auto_git_sync.py"
echo GitHub Auto-Sync Engine is active.

echo.
echo ===================================================
echo   ONION DOMAIN (Open with Tor Browser):
echo   http://7625n5aonepn2vui2qfpnj27kyv565eq7ztwpuowa4heemu2zvy5h5ad.onion
echo ===================================================
echo.
echo Local Link: http://127.0.0.1:8000
echo Telegram Admin Bot: @MypayteAdmin_Bot
echo Telegram Support Bot: @payate_desk_bot
echo GitHub Sync: Auto-Sync Active (rmd352268-dev/ccc)
echo.
echo All services running in background.
echo ===================================================
pause
