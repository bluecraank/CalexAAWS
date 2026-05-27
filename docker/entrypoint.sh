#!/bin/sh
set -e

echo "→ composer install"
composer install --no-dev --optimize-autoloader --no-interaction

if [ "${SKIP_ASSETS:-false}" != "true" ]; then
    echo "→ npm ci"
    npm ci
    echo "→ npm run build"
    npm run build
fi

exec "$@"
