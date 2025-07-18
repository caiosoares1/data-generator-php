FROM php:8.2-apache

# Instala extensões e dependências
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl libpq-dev zip \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && a2enmod rewrite

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Cria usuário não-root
RUN useradd -G www-data,root -d /home/laraveluser laraveluser \
    && mkdir -p /home/laraveluser \
    && chown -R laraveluser:laraveluser /home/laraveluser

WORKDIR /var/www/html

# Copia arquivos de dependência primeiro
COPY --chown=laraveluser:laraveluser composer.json composer.lock ./

# Instala dependências
USER laraveluser
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts
USER root

# Copia o restante dos arquivos
COPY --chown=laraveluser:laraveluser . .

# Configurações de otimização (sem dependência do banco)
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear \
    && (php artisan cache:clear || true) \
    && php artisan optimize \
    && chown -R www-data:www-data storage bootstrap/cache

# Configuração do Apache
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && echo "AllowEncodedSlashes On" >> /etc/apache2/apache2.conf

EXPOSE 80

# Script de inicialização
COPY startup.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/startup.sh
CMD ["/usr/local/bin/startup.sh"]