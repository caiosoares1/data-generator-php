FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl libpq-dev zip \
    postgresql-client \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Configure Apache for dynamic port
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    sed -i 's/Listen 80/Listen 0.0.0.0:$PORT/' /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost 0.0.0.0:$PORT>/' /etc/apache2/sites-available/000-default.conf

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Copy composer files first (for better caching)
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts --no-dev

# Copy application files
COPY . .

# Create storage directories and set permissions
RUN mkdir -p storage/logs storage/framework/{cache,sessions,views} bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 755 storage bootstrap/cache

# Clear Laravel caches
RUN php artisan config:clear && \
    php artisan view:clear && \
    php artisan route:clear

# Copy and make startup script executable
COPY startup.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/startup.sh

# Expose port
EXPOSE $PORT

# Use startup script
CMD ["/usr/local/bin/startup.sh"]