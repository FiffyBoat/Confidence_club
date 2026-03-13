#!/usr/bin/env sh
set -e

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views
php artisan storage:link >/dev/null 2>&1 || true

if [ -n "${APP_KEY:-}" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force || true
fi

exec /usr/bin/supervisord -c /etc/supervisord.conf
