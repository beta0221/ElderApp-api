#!/bin/sh
set -e

# Generate APP_KEY if not provided, then re-export so config:cache picks it up
if [ -z "$APP_KEY" ]; then
    echo "[entrypoint] Generating APP_KEY..."
    php artisan key:generate --no-interaction
    export APP_KEY=$(grep "^APP_KEY=" /var/www/html/.env | cut -d= -f2-)
fi

# Generate JWT secret if not provided
if [ -z "$JWT_SECRET" ]; then
    echo "[entrypoint] Generating JWT_SECRET..."
    php artisan jwt:secret --no-interaction --force
    export JWT_SECRET=$(grep "^JWT_SECRET=" /var/www/html/.env | cut -d= -f2-)
fi

# Run pending migrations
echo "[entrypoint] Running migrations..."
php artisan migrate --force --no-interaction

# Cache for production
if [ "$APP_ENV" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

echo "[entrypoint] Starting PHP-FPM..."
exec php-fpm
