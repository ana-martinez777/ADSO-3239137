#!/bin/sh
# Prepares the API on every start: waits for MySQL, migrates and seeds.
set -e

cd /var/www/html

if [ -z "$APP_KEY" ]; then
    APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
    export APP_KEY
    echo "[sports-gear] APP_KEY is empty: using a temporary key for this run."
fi

echo "[sports-gear] Waiting for the database..."
attempt=0
until php -r 'new PDO("mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE"), getenv("DB_USERNAME"), getenv("DB_PASSWORD"));' >/dev/null 2>&1; do
    attempt=$((attempt + 1))
    if [ "$attempt" -ge 60 ]; then
        echo "[sports-gear] The database did not respond after 120 seconds." >&2
        exit 1
    fi
    sleep 2
done

php artisan config:cache
php artisan migrate --force
php artisan db:seed --force

echo "[sports-gear] API ready."
exec "$@"
