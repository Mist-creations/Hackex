# Use official PHP 8.4 FPM image
FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
        zip \
        unzip \
        git \
        curl \
        sqlite \
        bash \
        oniguruma-dev \
        mysql-client \
        mysql-dev \
        libzip-dev \
        autoconf \
        g++ \
        make \
        libc-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring bcmath zip \
    && apk del autoconf g++ make libc-dev

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy project files
COPY . /var/www/html

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage bootstrap/cache

# Install PHP dependencies via Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create required Laravel directories
RUN mkdir -p storage/framework/{cache,sessions,views} storage/app/public bootstrap/cache

# Copy .env.example to .env if not exists and run Laravel optimizations
RUN cp .env.example .env \
    && php artisan key:generate --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Expose port 80
EXPOSE 80

# Start PHP-FPM
CMD ["php-fpm"]
