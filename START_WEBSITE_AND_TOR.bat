@echo off
title Laravel Tor Onion Service Host
cls
echo ===================================================
echo     STARTING LARAVEL AND TOR ONION SERVICE
echo ===================================================
echo.

echo [1/3] Checking Tor Daemon...
tasklist /FI "IMAGENAME eq tor.exe" 2>NUL | find /I /N "tor.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo Tor Daemon is already running.
) else (
    echo Starting Tor Daemon...
    start /B "" "C:\Users\hp\tor_service\tor\tor.exe" -f "C:\Users\hp\tor_service\torrc"
)

echo [2/3] Checking Laravel Web Server...
tasklist /FI "IMAGENAME eq php.exe" 2>NUL | find /I /N "php.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo PHP Laravel Server is already running.
) else (
    echo Starting PHP Artisan Server on 127.0.0.1:8000...
    cd /d "C:\Users\hp\Desktop\ccc"
    start /B "" php artisan serve --host=127.0.0.1 --port=8000
)

echo [3/3] Checking Telegram Admin Bot...
start /B "" pythonw.exe "C:\Users\hp\Desktop\ccc\telegram_admin_bot.pyw"
echo Telegram Admin Bot is running.

echo.
echo ===================================================
echo   ONION DOMAIN (Open with Tor Browser):
echo   http://7625n5aonepn2vui2qfpnj27kyv565eq7ztwpuowa4heemu2zvy5h5ad.onion
echo ===================================================
echo.
echo Local Link: http://127.0.0.1:8000
echo Telegram Bot: @MypayteAdmin_Bot
echo.
echo Do not close this window if you want to monitor.
echo The site will run as long as your PC is on.
echo ===================================================
pause

