FROM php:8.2-apache

# Instala extensões e dependências
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl libpq-dev zip \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && a2enmod rewrite

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define diretório de trabalho
WORKDIR /var/www/html

# Copia apenas o necessário para instalar dependências primeiro
COPY composer.json composer.lock ./

# Instala dependências do Laravel (sem scripts para evitar erros)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Copia o restante dos arquivos
COPY . .

# Executa scripts pós-instalação e otimiza a aplicação
RUN composer run-script post-install-cmd \
    && php artisan optimize:clear \
    && php artisan optimize \
    && chown -R www-data:www-data storage bootstrap/cache

# Configuração do Apache
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && echo "AllowEncodedSlashes On" >> /etc/apache2/apache2.conf

EXPOSE 80

# Comando de saúde para verificar se o app está pronto
HEALTHCHECK --interval=30s --timeout=30s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/health || exit 1