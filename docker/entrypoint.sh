#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    php artisan key:generate --force --no-interaction
fi

composer install --no-interaction --prefer-dist

for i in $(seq 1 30); do
    if php artisan migrate --force --no-interaction; then
        break
    fi
    echo "Waiting for database... ($i/30)"
    sleep 2
done

exec "$@"
