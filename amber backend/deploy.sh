#!/bin/bash

# Render Deployment Script with CORS Fix
# This script runs automatically on Render deployment

echo "🚀 Starting deployment..."

# Clear all Laravel caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run migrations
echo "📊 Running migrations..."
php artisan migrate --force

# Verify CORS configuration
echo "🔍 Verifying CORS configuration..."
php artisan tinker --execute="
    echo 'Frontend URL: ' . env('FRONTEND_URL') . PHP_EOL;
    echo 'App URL: ' . env('APP_URL') . PHP_EOL;
    echo 'Allowed Origins: ' . json_encode(config('cors.allowed_origins')) . PHP_EOL;
"

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache

# Start the server
echo "✅ Starting server on port $PORT..."
php artisan serve --host=0.0.0.0 --port=$PORT
