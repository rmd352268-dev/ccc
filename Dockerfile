FROM php:8.3-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies, Nginx, Python3, and build tools
RUN apk update && apk add --no-cache \
    nginx \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
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

# Install PHP extensions required by Laravel
RUN docker-php-ext-install pdo pdo_sqlite pdo_mysql bcmath opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Python dependencies for Telegram bots (break system packages allowed in container)
RUN pip3 install --no-cache-dir --break-system-packages requests urllib3 bcrypt

# Copy project files
COPY . .

# Install PHP composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Build frontend assets with Vite
RUN npm install && npm run build

# Setup storage and database permissions
RUN mkdir -p /var/www/html/database /var/www/html/storage /var/www/html/bootstrap/cache && \
    touch /var/www/html/database/database.sqlite && \
    touch /var/www/html/database/support_bot.sqlite && \
    chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Copy Nginx config and startup script
COPY docker/nginx.conf /etc/nginx/nginx.conf.template
COPY docker/start.sh /var/www/html/start.sh
RUN chmod +x /var/www/html/start.sh

# Expose default Render port
EXPOSE 10000

CMD ["/var/www/html/start.sh"]
