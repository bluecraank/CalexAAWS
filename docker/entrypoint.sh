#!/bin/sh
set -e

# Volume-Mounts können von einem anderen User gehören – git ignoriert das
git config --global --add safe.directory /var/www/html 2>/dev/null || true

echo "→ composer install"
composer install --no-dev --optimize-autoloader --no-interaction

exec "$@"
