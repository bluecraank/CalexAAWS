#!/bin/sh
set -e

git config --global --add safe.directory /var/www/html 2>/dev/null || true

# .env anlegen falls nicht vorhanden
[ -f .env ] || cp .env.example .env

echo "→ composer install"
composer install --no-dev --optimize-autoloader --no-interaction

# Schreibrechte für php-fpm Worker (laufen als www-data)
chown -R www-data:www-data storage bootstrap/cache

if [ "${SKIP_ASSETS:-false}" != "true" ]; then
    echo "→ npm install"
    npm install
    echo "→ npm run build"
    npm run build
fi

# APP_KEY nur generieren falls noch keiner gesetzt ist
grep -q "^APP_KEY=base64:" .env 2>/dev/null || php artisan key:generate --force

echo "→ php artisan storage:link"
php artisan storage:link --force 2>/dev/null || true

echo "→ php artisan migrate"
php artisan migrate --force --no-interaction

exec "$@"
