#!/bin/bash
# ====================================================================
# Payate CC - Linux WSL 1-Click Server & Telegram Bots Launcher
# ====================================================================

PROJECT_DIR="/mnt/c/Users/hp/Desktop/ccc"
cd "$PROJECT_DIR" || exit 1

echo "===================================================================="
echo "         PAYATE CC - UBUNTU LINUX HOST & BOT CONTROLLER"
echo "===================================================================="
echo ""

# 1. Start Tor Service
echo "[1/5] Checking Tor Daemon..."
if pgrep -x "tor" > /dev/null; then
    echo "  [OK] Tor Daemon is already active."
else
    echo "  [+] Starting Tor Daemon..."
    sudo service tor start 2>/dev/null || tor --RunAsDaemon 1 2>/dev/null &
    sleep 1
    echo "  [OK] Tor Daemon initiated."
fi

# 2. Start Laravel PHP Server
echo ""
echo "[2/5] Checking Laravel Web Server (Port 8000)..."
if pgrep -f "artisan serve" > /dev/null; then
    echo "  [OK] Laravel Server is already running on port 8000."
else
    echo "  [+] Starting Laravel PHP Server (0.0.0.0:8000)..."
    nohup php artisan serve --host=0.0.0.0 --port=8000 > storage/logs/laravel_server.log 2>&1 &
    sleep 1
    echo "  [OK] Laravel Server launched."
fi

# 3. Start Telegram Admin Bot
echo ""
echo "[3/5] Starting Telegram Master Admin Bot..."
if pgrep -f "telegram_admin_bot.pyw" > /dev/null; then
    echo "  [OK] Telegram Admin Bot is already running."
else
    nohup python3 telegram_admin_bot.pyw > storage/logs/admin_bot.log 2>&1 &
    echo "  [OK] Telegram Admin Bot is active and listening."
fi

# 4. Start Telegram Public Support Bot
echo ""
echo "[4/5] Starting Telegram Live Support Bot (@payate_desk_bot)..."
if pgrep -f "telegram_support_bot.pyw" > /dev/null; then
    echo "  [OK] Telegram Support Bot is already running."
else
    nohup python3 telegram_support_bot.pyw > storage/logs/support_bot.log 2>&1 &
    echo "  [OK] Support Bot is active and relaying messages."
fi

# 5. Start Auto Git Sync Engine
echo ""
echo "[5/5] Starting GitHub Auto-Sync & Backup Engine..."
if pgrep -f "auto_git_sync.py" > /dev/null; then
    echo "  [OK] Auto Git Sync Engine is already running."
else
    nohup python3 auto_git_sync.py > storage/logs/git_sync.log 2>&1 &
    echo "  [OK] Auto Git Sync Engine is active."
fi

echo ""
echo "===================================================================="
echo "                   ALL SERVICES ACTIVE IN LINUX!"
echo "===================================================================="
echo ""
echo "  * Onion Domain (Tor Browser):"
echo "    http://7625n5aonepn2vui2qfpnj27kyv565eq7ztwpuowa4heemu2zvy5h5ad.onion"
echo ""
echo "  * Local Website Link (Windows & Linux):"
echo "    http://127.0.0.1:8000"
echo ""
echo "  * Telegram Admin Bot:   @MypayteAdmin_Bot"
echo "  * Telegram Support Bot: @payate_desk_bot"
echo ""
echo "===================================================================="
