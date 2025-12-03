# Use official PHP 8.4 FPM image
FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

# Install system packages and PHP dependencies
RUN apk add --no-cache \
        zip unzip git curl sqlite bash oniguruma-dev mysql-client mysql-dev autoconf g++ make libc-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring bcmath zip \
    && apk del autoconf g++ make libc-dev

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy project
COPY . /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage bootstrap/cache

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create required directories
RUN mkdir -p storage/framework/{cache,sessions,views} storage/app/public bootstrap/cache

# Laravel optimization
RUN php artisan key:generate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 80

CMD ["php-fpm"]
