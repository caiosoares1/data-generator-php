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

# Wait for PostgreSQL with better error handling
echo "Waiting for PostgreSQL at $DB_HOST:$DB_PORT..."
for i in {1..30}; do
    if php -r "
        try {
            \$pdo = new PDO('pgsql:host='.getenv('DB_HOST').';port='.getenv('DB_PORT').';dbname=postgres', 
                getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
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
        exit 1
    fi
    
    echo "Waiting... (attempt $i/30)"
    sleep 5
done

# Run Laravel commands
echo "Running Laravel setup..."
php artisan migrate --force || echo "Migration failed, continuing..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Apache
echo "Starting Apache on port $PORT..."
exec apache2-foreground