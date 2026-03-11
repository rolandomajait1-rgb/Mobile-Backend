#!/bin/bash
set -e

echo "Starting Laravel application..."

# Clear any cached config
echo "Clearing config cache..."
php artisan config:clear || true

# Wait for database to be ready
echo "Waiting for database..."
sleep 5

# Drop all tables manually (including ones without prefix)
echo "Dropping all tables..."
php artisan db:wipe --force || echo "Wipe failed, continuing..."

# Manually drop password_reset_tokens if it exists (without prefix)
echo "Dropping password_reset_tokens table if exists..."
php artisan tinker --execute="DB::statement('DROP TABLE IF EXISTS password_reset_tokens CASCADE');" || echo "Manual drop failed, continuing..."

# Run migrations fresh (drop all tables and recreate)
echo "Running fresh migrations..."
php artisan migrate:fresh --force --seed || {
    echo "Fresh migration failed! Checking database connection..."
    php artisan tinker --execute="var_dump(DB::connection()->getPdo());" || echo "Database connection failed!"
    exit 1
}

# Database already seeded by migrate:fresh
echo "Database setup complete."

# Cache config and routes
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache

echo "Starting Apache..."
apache2-foreground
