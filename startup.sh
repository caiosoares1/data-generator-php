#!/bin/bash

# Verifica conexão com o PostgreSQL usando PHP nativo
while ! php -r "try {
    \$pdo = new PDO('pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE'), 
    getenv('DB_USERNAME'), 
    getenv('DB_PASSWORD'));
    exit(0);
} catch (PDOException \$e) {
    file_put_contents('php://stderr', 'Waiting for database... '. \$e->getMessage() . PHP_EOL);
    exit(1);
}" >/dev/null 2>&1; do
    sleep 5
done

# Executa migrações e otimizações
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Inicia Apache
exec apache2-foreground