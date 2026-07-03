FROM php:7.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    curl \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    zip \
    unzip \
    oniguruma-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    bash

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Get Composer 2.2 (LTS for PHP 7.4)
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy existing application directory contents
COPY . /var/www

# Install Laravel dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Set permissions and create .env
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache \
    && touch /var/www/.env \
    && chown www-data:www-data /var/www/.env \
    && chmod 775 /var/www/.env

# Setup Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Setup required directories for Nginx
RUN mkdir -p /run/nginx

EXPOSE 80

# Start Nginx & PHP-FPM
CMD ["sh", "-c", "rm -f /var/www/bootstrap/cache/*.php && php-fpm -D && nginx -g 'daemon off;'"]
