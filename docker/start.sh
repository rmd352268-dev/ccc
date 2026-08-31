#!/bin/bash
set -e

# Default port to 10000 if not set by Render
export PORT=${PORT:-10000}

echo "--> Configuring Nginx for port $PORT..."
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

echo "--> Preparing storage and database permissions..."
mkdir -p /var/www/html/database /var/www/html/storage/framework/{sessions,views,cache} /var/www/html/bootstrap/cache
touch /var/www/html/database/database.sqlite
touch /var/www/html/database/support_bot.sqlite
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

echo "--> Running Laravel migrations and optimizations..."
php artisan migrate --force || true
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

echo "--> Starting Telegram Bots Supervisor Engine..."
# Supervised Telegram Admin Bot runner (Auto-restarts on unexpected crash)
if [ -f "telegram_admin_bot.pyw" ]; then
    (
        echo "[Admin Bot Daemon] Initiating supervisor loop..."
        while true; do
            echo "[Admin Bot Daemon] Starting telegram_admin_bot.pyw at $(date)..."
            python3 telegram_admin_bot.pyw >> /var/www/html/storage/logs/admin_bot.log 2>&1 || true
            echo "[Admin Bot Daemon] Process exited. Auto-recovering in 3 seconds..."
            sleep 3
        done
    ) &
fi

# Supervised Telegram Support Bot runner (Auto-restarts on unexpected crash)
if [ -f "telegram_support_bot.pyw" ]; then
    (
        echo "[Support Bot Daemon] Initiating supervisor loop..."
        while true; do
            echo "[Support Bot Daemon] Starting telegram_support_bot.pyw at $(date)..."
            python3 telegram_support_bot.pyw >> /var/www/html/storage/logs/support_bot.log 2>&1 || true
            echo "[Support Bot Daemon] Process exited. Auto-recovering in 3 seconds..."
            sleep 3
        done
    ) &
fi

# Keepalive Ping Loop (Pings local health check every 5 minutes)
(
    sleep 30
    while true; do
        curl -s -o /dev/null "http://127.0.0.1:${PORT}/up" || true
        sleep 300
    done
) &

echo "--> Starting PHP-FPM..."
php-fpm -D

echo "--> Starting Nginx Web Server on port $PORT..."
exec nginx -g "daemon off;"
