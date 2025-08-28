#!/bin/bash
set -e
exec > >(tee -a ./deploy-local.log) 2>&1
echo "Local deploy started at $(date)"

cd "$(dirname "$0")"

# Put app in maintenance mode
php artisan down || true
echo "Laravel in maintenance mode (local)"

# Update code
git pull origin master
echo "Git Pull done"

# Install PHP deps
composer install --no-dev --optimize-autoloader
echo "Composer done"

# Clear & rebuild caches
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true

php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true
echo "Laravel cache refreshed (local)"

# Bring app back up
php artisan up
echo "Laravel back online (local)"

echo "Local deploy completed successfully at $(date)"
