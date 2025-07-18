#!/bin/bash

# Replace $PORT variable in Apache config
sed -i "s/\$PORT/$PORT/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Wait for PostgreSQL
echo "Waiting for PostgreSQL at $DB_HOST:$DB_PORT..."
while ! php -r "try {
    new PDO('pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname=postgres',
    getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    exit(0);
} catch (PDOException \$e) {
    file_put_contents('php://stderr', 'DB waiting: '. \$e->getMessage() . PHP_EOL);
    exit(1);
}" >/dev/null 2>&1; do
    sleep 5
done

# Run migrations and cache
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache
exec apache2-foreground