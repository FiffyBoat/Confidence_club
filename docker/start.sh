#!/usr/bin/env sh
set -e

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views
php artisan storage:link >/dev/null 2>&1 || true

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ] && [ -n "${DB_DATABASE:-}" ]; then
    case "$DB_DATABASE" in
        /*) SQLITE_DB_PATH="$DB_DATABASE" ;;
        *) SQLITE_DB_PATH="/var/www/html/$DB_DATABASE" ;;
    esac

    SQLITE_TEMPLATE_PATH="${SQLITE_TEMPLATE_PATH:-/var/www/html/ccm_db_empty}"

    mkdir -p "$(dirname "$SQLITE_DB_PATH")"

    if [ ! -f "$SQLITE_DB_PATH" ]; then
        if [ -f "$SQLITE_TEMPLATE_PATH" ] && [ "$SQLITE_TEMPLATE_PATH" != "$SQLITE_DB_PATH" ]; then
            cp "$SQLITE_TEMPLATE_PATH" "$SQLITE_DB_PATH"
        else
            touch "$SQLITE_DB_PATH"
        fi
    fi
fi

if [ -n "${APP_KEY:-}" ]; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force || true
fi

if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    php artisan db:seed --force || true
fi

exec /usr/bin/supervisord -c /etc/supervisord.conf
