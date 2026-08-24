#!/usr/bin/env bash
# -------------------------------------------------------------------
# docker_run.sh — Entrypoint script for LBAW Laravel + Nginx container
#
# This script prepares Laravel’s caches for production
# and starts both PHP-FPM (background) and Nginx (foreground).
# -------------------------------------------------------------------
set -euo pipefail

cd /var/www

# Ensure Laravel runtime directories exist
mkdir -p storage/framework/{cache,sessions,views} bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Clear and cache Laravel configuration for faster boot
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Rebuild optimized caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link

# Add cron job into cronfile
echo "* * * * * cd /var/www && php artisan schedule:run >> /dev/null 2>&1" >> cronfile

# Install cron job
crontab cronfile

# Remove temporary file
rm cronfile

# Start cron
cron


# Start PHP-FPM in background
php-fpm -D

# Start nginx in foreground (keeps container alive)
exec nginx -g "daemon off;"