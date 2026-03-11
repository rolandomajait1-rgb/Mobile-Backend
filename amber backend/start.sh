#!/bin/bash
set -e

echo "Starting Laravel application..."

# Clear any cached config
echo "Clearing config cache..."
php artisan config:clear || true

# Wait for database to be ready
echo "Waiting for database..."
sleep 5

# Run migrations
echo "Running migrations..."
php artisan migrate --force || {
    echo "Migration failed! Checking database connection..."
    php artisan tinker --execute="var_dump(DB::connection()->getPdo());" || echo "Database connection failed!"
    exit 1
}

# Cache config and routes
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache

echo "Starting Apache..."
apache2-foreground
