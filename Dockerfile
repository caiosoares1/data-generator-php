FROM php:8.2-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl libpq-dev zip \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && a2enmod rewrite

# Configure Apache for dynamic port
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    sed -i 's/Listen 80/Listen 0.0.0.0:$PORT/' /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost 0.0.0.0:$PORT>/' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Copy files in optimal order
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts
COPY . .

# Permissions and optimizations
RUN chown -R www-data:www-data storage bootstrap/cache && \
    php artisan config:clear && \
    php artisan view:clear && \
    php artisan route:clear

# Health check script
COPY startup.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/startup.sh

EXPOSE $PORT
CMD ["/usr/local/bin/startup.sh"]