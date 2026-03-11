#!/bin/bash
set -e

echo "Starting Laravel application..."

# Clear any cached config
echo "Clearing config cache..."
php artisan config:clear || true

# Wait for database to be ready
echo "Waiting for database..."
sleep 5

# Run migrations (will skip if tables exist)
echo "Running migrations..."
php artisan migrate --force || {
    echo "Migration failed! Trying to continue anyway..."
}

# Seed database if empty
echo "Checking if database needs seeding..."
CATEGORY_COUNT=$(php artisan tinker --execute="echo \App\Models\Category::count();" 2>/dev/null | tail -1)
if [ "$CATEGORY_COUNT" = "0" ] || [ -z "$CATEGORY_COUNT" ]; then
    echo "Database is empty. Running seeders..."
    php artisan db:seed --force || echo "Seeding failed, continuing anyway..."
else
    echo "Database already has data. Skipping seeding."
fi

# Cache config and routes
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache

echo "Starting Apache..."
apache2-foreground
