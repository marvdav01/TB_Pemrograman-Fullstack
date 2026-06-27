#!/usr/bin/env bash
# Build script for Render.com deployment
set -e

echo "==> Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Installing Node.js dependencies..."
npm ci

echo "==> Building frontend assets..."
npm run build

echo "==> Caching Laravel configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Running database migrations..."
php artisan migrate --force

echo "==> Running database seeder..."
php artisan db:seed --force

echo "==> Creating storage symlink..."
php artisan storage:link

echo "==> Build complete!"
