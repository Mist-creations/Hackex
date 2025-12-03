# Use official PHP 8.4 FPM image
FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

# Install system packages including nginx and supervisor
RUN apk add --no-cache zip unzip git curl sqlite bash nginx supervisor oniguruma-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring bcmath zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy project
COPY . /var/www/html

# Copy nginx configuration
COPY nginx.conf /etc/nginx/http.d/default.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage bootstrap/cache

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create required Laravel directories
RUN mkdir -p storage/framework/{cache,sessions,views} \
    storage/app/public \
    storage/app/uploads \
    bootstrap/cache \
    database

# Create SQLite database file
RUN touch database/database.sqlite \
    && chmod 664 database/database.sqlite

# Copy .env.example to .env if not exists and run Laravel optimizations
RUN cp .env.example .env \
    && php artisan key:generate --force \
    && php artisan migrate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Set final permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage database

# Expose HTTP port
EXPOSE 80

# Start PHP-FPM, queue worker, and Nginx
CMD php-fpm -D && php artisan queue:work --daemon & nginx -g "daemon off;"
