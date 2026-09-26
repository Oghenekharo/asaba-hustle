#!/usr/bin/env sh
set -eu

cd /var/www/html

# Skip everything if we're just running composer
if [ "$1" = "composer" ] || [ "${COMPOSER_RUNNING:-}" = "1" ]; then
    exec "$@"
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true

# Storage link
if [ ! -L public/storage ]; then
    php artisan storage:link >/dev/null 2>&1 || true
fi

BOOTSTRAP_CACHE_STORE="${BOOTSTRAP_CACHE_STORE:-file}"

if [ "${CLEAR_OPTIMIZED_BOOTSTRAP:-false}" = "true" ]; then
    CACHE_STORE="${BOOTSTRAP_CACHE_STORE}" php artisan optimize:clear || true
fi

# Wait for DB (only if needed)
if [ "${WAIT_FOR_DB:-true}" = "true" ] && [ -n "${DB_HOST:-}" ]; then
    echo "Waiting for database..."
    until mysqladmin ping \
        -h"${DB_HOST}" \
        -P"${DB_PORT:-3306}" \
        -uroot \
        -p"${MYSQL_ROOT_PASSWORD:-root}" \
        --silent; do
        sleep 2
    done
fi

# Run migrations safely
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force || true
fi

exec "$@"
