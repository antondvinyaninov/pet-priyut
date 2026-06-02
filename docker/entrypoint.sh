#!/bin/sh
set -e

cd /var/www/html

mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    database

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_FILE="${DB_DATABASE:-database/database.sqlite}"

    case "$DB_FILE" in
        /*) ;;
        *) DB_FILE="/var/www/html/$DB_FILE" ;;
    esac

    mkdir -p "$(dirname "$DB_FILE")"
    touch "$DB_FILE"
fi

chown -R www-data:www-data storage bootstrap/cache database || true

php artisan storage:link || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

chown -R www-data:www-data storage bootstrap/cache database || true

exec "$@"
