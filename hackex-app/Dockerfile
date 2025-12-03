# Use official PHP 8.4 FPM image
FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

# Install system packages
RUN apk add --no-cache zip unzip git curl sqlite bash oniguruma-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring bcmath zip

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy project
COPY . /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage bootstrap/cache

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create required directories
RUN mkdir -p storage/framework/{cache,sessions,views} storage/app/public bootstrap/cache

# Ensure .env exists (avoid key:generate error)
RUN if [ ! -f .env ]; then cp .env.example .env; fi

# Laravel optimization
RUN php artisan key:generate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Expose HTTP port
EXPOSE 80

# Start built-in PHP server
CMD ["php", "-S", "0.0.0.0:80", "-t", "public"]
