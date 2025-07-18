FROM php:8.2-apache

# Instala dependências
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl libpq-dev zip \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && a2enmod rewrite

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Configura diretório e permissões
WORKDIR /var/www/html
RUN chown -R www-data:www-data /var/www/html

# Copia arquivos de dependência primeiro
COPY --chown=www-data:www-data composer.json composer.lock ./

# Instala dependências
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copia o restante dos arquivos
COPY --chown=www-data:www-data . .

# Configura Apache
RUN echo "Listen 0.0.0.0:80" > /etc/apache2/ports.conf && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Limpa cache sem depender do banco
RUN php artisan config:clear && \
    php artisan view:clear && \
    php artisan route:clear

EXPOSE 80

# Script de inicialização
COPY startup.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/startup.sh
CMD ["/usr/local/bin/startup.sh"]