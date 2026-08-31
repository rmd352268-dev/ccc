FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies, build tools, Nginx, Python3, and libraries for PHP extensions
RUN apk update && apk add --no-cache \
    nginx \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    zip \
    unzip \
    nodejs \
    npm \
    sqlite \
    sqlite-dev \
    python3 \
    py3-pip \
    bash \
    gettext

# Install and configure PHP extensions for Laravel
RUN docker-php-ext-configure intl && \
    docker-php-ext-install -j$(nproc) pdo pdo_sqlite pdo_mysql bcmath opcache pcntl intl zip fileinfo

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Python dependencies for Telegram bots
RUN pip3 install --no-cache-dir --break-system-packages requests urllib3 bcrypt PySocks

# Copy project files
COPY . .

# Install PHP dependencies without dev dependencies, ignoring platform mismatch if any
RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

# Build frontend assets with Vite
RUN npm install && npm run build

# Setup storage and database permissions
RUN mkdir -p /var/www/html/database /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views /var/www/html/storage/framework/cache /var/www/html/bootstrap/cache && \
    touch /var/www/html/database/database.sqlite && \
    touch /var/www/html/database/support_bot.sqlite && \
    chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Copy Nginx config and startup script
COPY docker/nginx.conf /etc/nginx/nginx.conf.template
COPY docker/start.sh /var/www/html/start.sh
RUN chmod +x /var/www/html/start.sh

# Expose Render port
EXPOSE 10000

CMD ["/var/www/html/start.sh"]
