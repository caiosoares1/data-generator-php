FROM php:8.2-apache

# Instala extensões e dependências
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl libpq-dev zip \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && a2enmod rewrite

# Instala Composer (como usuário não-root)
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Define diretório de trabalho
WORKDIR /var/www/html

# Cria usuário não-root para segurança
RUN useradd -G www-data,root -d /home/laraveluser laraveluser \
    && mkdir -p /home/laraveluser \
    && chown -R laraveluser:laraveluser /home/laraveluser

# Copia apenas o necessário para instalar dependências primeiro
COPY --chown=laraveluser:laraveluser composer.json composer.lock ./

# Instala dependências como usuário não-root
USER laraveluser
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts
USER root

# Copia o restante dos arquivos
COPY --chown=laraveluser:laraveluser . .

# Otimiza a aplicação Laravel
RUN php artisan optimize:clear \
    && php artisan optimize \
    && chown -R www-data:www-data storage bootstrap/cache

# Configuração do Apache
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && echo "AllowEncodedSlashes On" >> /etc/apache2/apache2.conf

EXPOSE 80

# Script de entrada personalizado
COPY --chown=laraveluser:laraveluser startup.sh /usr/local/bin/startup.sh
RUN chmod +x /usr/local/bin/startup.sh
CMD ["/usr/local/bin/startup.sh"]