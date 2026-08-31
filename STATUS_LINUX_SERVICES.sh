#!/bin/bash
# ====================================================================
# Payate CC - Check Linux Services Status
# ====================================================================

echo "===================================================================="
echo "                   LINUX SERVICES STATUS"
echo "===================================================================="

# Check Laravel
if pgrep -f "artisan serve" > /dev/null; then
    echo " [ACTIVE]  Laravel Web Server (Port 8000)"
else
    echo " [STOPPED] Laravel Web Server"
fi

# Check Tor
if pgrep -x "tor" > /dev/null; then
    echo " [ACTIVE]  Tor Daemon"
else
    echo " [STOPPED] Tor Daemon"
fi

# Check Admin Bot
if pgrep -f "telegram_admin_bot.pyw" > /dev/null; then
    echo " [ACTIVE]  Telegram Admin Bot"
else
    echo " [STOPPED] Telegram Admin Bot"
fi

# Check Support Bot
if pgrep -f "telegram_support_bot.pyw" > /dev/null; then
    echo " [ACTIVE]  Telegram Support Bot"
else
    echo " [STOPPED] Telegram Support Bot"
fi

# Check Auto Git Sync
if pgrep -f "auto_git_sync.py" > /dev/null; then
    echo " [ACTIVE]  Auto Git Sync Engine"
else
    echo " [STOPPED] Auto Git Sync Engine"
fi

echo "===================================================================="
