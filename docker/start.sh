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

echo "--> Starting Telegram Admin Bot in background..."
if [ -f "telegram_admin_bot.pyw" ]; then
    python3 telegram_admin_bot.pyw &
fi

echo "--> Starting Telegram Support Bot in background..."
if [ -f "telegram_support_bot.pyw" ]; then
    python3 telegram_support_bot.pyw &
fi

echo "--> Starting PHP-FPM..."
php-fpm -D

echo "--> Starting Nginx Web Server on port $PORT..."
exec nginx -g "daemon off;"
