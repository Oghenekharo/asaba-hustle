#!/bin/sh
set -eu

cd /var/www/html

mkdir -p \
  storage/framework/cache \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache

if [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
  until mysqladmin ping -h"${DB_HOST:-mysql}" -P"${DB_PORT:-3306}" -u"${DB_USERNAME:-asaba}" -p"${DB_PASSWORD:-secret}" --silent; do
    sleep 2
  done
fi

BOOTSTRAP_CACHE_STORE="${BOOTSTRAP_CACHE_STORE:-file}"

CACHE_STORE="${BOOTSTRAP_CACHE_STORE}" php artisan optimize:clear
CACHE_STORE="${BOOTSTRAP_CACHE_STORE}" php artisan package:discover --ansi
CACHE_STORE="${BOOTSTRAP_CACHE_STORE}" php artisan storage:link || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  CACHE_STORE="${BOOTSTRAP_CACHE_STORE}" php artisan migrate --force --no-interaction
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

case "${APP_ROLE:-app}" in
  app)
    exec php artisan serve --host=0.0.0.0 --port=8000
    ;;
  queue)
    exec php artisan queue:work --sleep=3 --tries=3 --timeout=120 --verbose
    ;;
  reverb)
    exec php artisan reverb:start --host=0.0.0.0 --port="${REVERB_SERVER_PORT:-8080}"
    ;;
  *)
    echo "Unknown APP_ROLE: ${APP_ROLE:-app}" >&2
    exit 1
    ;;
esac
