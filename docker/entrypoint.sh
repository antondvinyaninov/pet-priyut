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

if [ "${DB_CONNECTION:-sqlite}" = "pgsql" ]; then
    DB_HOST="${DB_HOST:-127.0.0.1}"
    DB_PORT="${DB_PORT:-5432}"
    DB_USERNAME="${DB_USERNAME:-postgres}"
    DB_DATABASE="${DB_DATABASE:-postgres}"
    export PGPASSWORD="${DB_PASSWORD:-}"

    ATTEMPTS=0
    until pg_isready -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d "$DB_DATABASE" >/dev/null 2>&1; do
        ATTEMPTS=$((ATTEMPTS + 1))

        if [ "$ATTEMPTS" -ge 30 ]; then
            echo "PostgreSQL is not ready after ${ATTEMPTS} attempts."
            exit 1
        fi

        echo "Waiting for PostgreSQL at ${DB_HOST}:${DB_PORT}..."
        sleep 2
    done
fi

chown -R www-data:www-data storage bootstrap/cache database || true

php artisan storage:link || true

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

chown -R www-data:www-data storage bootstrap/cache database || true

exec "$@"
