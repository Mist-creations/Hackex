FROM richarvey/nginx-php-fpm:latest

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY hackex-app /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    zip \
    unzip \
    git \
    curl \
    sqlite

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create necessary directories
RUN mkdir -p /var/www/html/storage/app/uploads \
    /var/www/html/storage/framework/cache \
    /var/www/html/storage/framework/sessions \
    /var/www/html/storage/framework/views \
    /var/www/html/storage/logs \
    /var/www/html/bootstrap/cache

# Set permissions
RUN chown -R nginx:nginx /var/www/html
RUN chmod -R 755 /var/www/html/storage
RUN chmod -R 755 /var/www/html/bootstrap/cache

# Copy environment file
RUN cp .env.example .env || true

# Laravel optimization commands
RUN php artisan key:generate --force || true
RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan view:cache || true

# Run migrations
RUN php artisan migrate --force || true

# Start queue worker in background and nginx
CMD php artisan queue:work --daemon & php-fpm -D && nginx -g "daemon off;"

EXPOSE 80
