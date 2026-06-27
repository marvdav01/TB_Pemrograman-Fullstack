#!/bin/bash
set -e

echo "▶ Menjalankan migrasi database..."
php artisan migrate --force

echo "▶ Menjalankan seeder..."
php artisan db:seed --class=TravelSeeder --force

echo "▶ Membuat storage link..."
php artisan storage:link || true

echo "▶ Membersihkan cache..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Deployment setup selesai!"
