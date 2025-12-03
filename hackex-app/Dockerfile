# Use PHP 8.4 FPM image with Alpine
FROM richarvey/nginx-php-fpm:latest

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY hackex-app /var/www/html

# Install system packages and PHP dependencies
RUN apk add --no-cache \
        zip \
        unzip \
        git \
        curl \
        sqlite \
        bash \
        oniguruma-dev \
        pkgconfig \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        bcmath \
        zip

# Expose port 80
EXPOSE 80

# Start PHP-FPM (the base image entrypoint handles nginx+php-fpm)
CMD ["php-fpm"]
