#!/bin/sh
set -e

# Copy public assets from image to shared volume on every deploy
# This ensures nginx always serves up-to-date built assets
if [ -d /var/www/html-image/public ]; then
    echo "[entrypoint] Syncing public assets from image to volume..."
    cp -a /var/www/html-image/public/. /var/www/html/public/
    echo "[entrypoint] Done."
fi

mkdir -p /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache \
         /var/www/html/storage/framework/sessions \
         /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

exec "$@"
