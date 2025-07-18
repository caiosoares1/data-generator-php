#!/bin/bash

# Verify Apache is configured for port 8000
if ! grep -q "Listen 0.0.0.0:8000" /etc/apache2/ports.conf; then
    echo "Configuring Apache for port 8000..."
    sed -i 's/Listen 80/Listen 0.0.0.0:8000/' /etc/apache2/ports.conf
    sed -i 's/<VirtualHost \*:80>/<VirtualHost 0.0.0.0:8000>/' /etc/apache2/sites-available/000-default.conf
fi

# Wait for PostgreSQL
echo "Waiting for database at $DB_HOST:$DB_PORT..."
until php -r "try {
    new PDO('pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname=postgres',
    getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
    exit(0);
} catch (PDOException \$e) {
    file_put_contents('php://stderr', 'Database connection failed: '. \$e->getMessage() . PHP_EOL);
    exit(1);
}" >/dev/null 2>&1; do
    sleep 5
done

# Run migrations and optimize
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache
exec apache2-foreground