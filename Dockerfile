FROM php:8.2-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl libpq-dev zip \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Configure Apache for port 8000
RUN sed -i 's/Listen 80/Listen 0.0.0.0:8000/' /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost 0.0.0.0:8000>/' /etc/apache2/sites-available/000-default.conf && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Copy composer files first
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Clear caches
RUN php artisan config:clear && \
    php artisan view:clear && \
    php artisan route:clear

# Expose port 8000
EXPOSE 8000

# Copy startup script
COPY startup.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/startup.sh

CMD ["/usr/local/bin/startup.sh"]