#!/bin/bash

# Verify Apache is listening on port 80
if ! grep -q "Listen 0.0.0.0:80" /etc/apache2/ports.conf; then
    echo "ERROR: Apache not configured for port 80"
    exit 1
fi

# Wait for PostgreSQL using PHP (no netcat dependency)
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