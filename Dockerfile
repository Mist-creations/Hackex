FROM richarvey/nginx-php-fpm:latest

WORKDIR /var/www/html

# Copy Laravel project files
COPY . /var/www/html

# Install system packages
RUN apk add --no-cache zip unzip git curl sqlite

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create required Laravel directories
RUN mkdir -p storage/framework/{cache,sessions,views} \
    storage/app/public \
    bootstrap/cache

# Set proper permissions
RUN chown -R nginx:nginx /var/www/html
RUN chmod -R 755 storage bootstrap/cache

# Optimize Laravel
RUN php artisan key:generate --force || true
RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan view:cache || true

# expose port
EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
