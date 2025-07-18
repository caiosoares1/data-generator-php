#!/bin/bash

# Replace $PORT variable in Apache config
sed -i "s/\$PORT/$PORT/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Debug: Show environment variables
echo "=== Environment Variables ==="
echo "DB_HOST: $DB_HOST"
echo "DB_PORT: $DB_PORT"
echo "DB_DATABASE: $DB_DATABASE"
echo "DB_USERNAME: $DB_USERNAME"
echo "PORT: $PORT"
echo "=========================="

# Check if database variables are set
if [ -z "$DB_HOST" ] || [ -z "$DB_PORT" ] || [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ] || [ -z "$DB_PASSWORD" ]; then
    echo "ERROR: Database environment variables are not set properly!"
    exit 1
fi

# Wait for PostgreSQL with SSL configuration
echo "Waiting for PostgreSQL at $DB_HOST:$DB_PORT..."

for i in {1..30}; do
    if php -r "
        try {
            \$dsn = 'pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname='.getenv('DB_DATABASE').';sslmode=require';
            \$pdo = new PDO(\$dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'), [
                PDO::ATTR_TIMEOUT => 30,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => false
            ]);
            echo 'Database connection successful!';
            exit(0);
        } catch (PDOException \$e) {
            echo 'DB attempt $i: '. \$e->getMessage();
            exit(1);
        }
    "; then
        echo "Database is ready!"
        break
    fi
    
    if [ $i -eq 30 ]; then
        echo "Database connection failed after 30 attempts"
        echo "Starting application anyway - migrations will be skipped"
        break
    fi
    
    echo "Waiting... (attempt $i/30)"
    sleep 10
done

# Run Laravel commands
echo "Running Laravel setup..."
if php artisan migrate --force --no-interaction 2>/dev/null; then
    echo "Migrations completed successfully"
else
    echo "Migrations failed or skipped - continuing anyway"
fi

# Cache configuration
php artisan config:cache || echo "Config cache failed"
php artisan route:cache || echo "Route cache failed"
php artisan view:cache || echo "View cache failed"

# Start Apache
echo "Starting Apache on port $PORT..."
exec apache2-foreground