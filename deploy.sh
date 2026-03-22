#!/bin/bash
set -e

echo "Pulling latest code"
git pull origin main

echo "Building and starting containers"
docker compose up -d --build

echo "Waiting for database to be ready"
sleep 5

echo "Running migrations"
docker compose exec app php artisan migrate --force

echo "Creating storage link"
docker compose exec app php artisan storage:link 2>/dev/null || true

echo "Clearing and caching config"
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

echo "Deployment complete! App is running at:"
echo "   http://$(curl -s ifconfig.me)"
