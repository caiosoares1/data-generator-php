FROM php:8.2-apache

# Install dependencies including netcat
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl libpq-dev zip netcat-openbsd \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Configure Apache to listen on all interfaces
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    sed -i 's/Listen 80/Listen 0.0.0.0:80/' /etc/apache2/ports.conf && \
    sed -i 's/VirtualHost \*:80/VirtualHost 0.0.0.0:80/' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Clear caches that don't need DB
RUN php artisan config:clear && \
    php artisan view:clear && \
    php artisan route:clear

# Explicit port exposure
EXPOSE 80

# Health check script
COPY startup.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/startup.sh

CMD ["/usr/local/bin/startup.sh"]