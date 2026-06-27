#!/bin/sh
set -e

cd /var/www/html

echo "=== Control Total API — startup ==="

if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is not set. Run: php artisan key:generate --show"
    echo "Then add the value as APP_KEY in Railway variables."
    exit 1
fi

export APP_ENV="${APP_ENV:-production}"
export APP_DEBUG="${APP_DEBUG:-false}"
export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"
export CACHE_STORE="${CACHE_STORE:-database}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-database}"
export SESSION_DRIVER="${SESSION_DRIVER:-database}"
export BROADCAST_CONNECTION="${BROADCAST_CONNECTION:-log}"

PORT="${PORT:-8000}"

echo "Waiting for MySQL at ${DB_HOST:-mysql}:${DB_PORT:-3306}..."

attempt=0
max_attempts=30

while [ "$attempt" -lt "$max_attempts" ]; do
    if php -r "
        \$host = getenv('DB_HOST') ?: 'mysql';
        \$port = getenv('DB_PORT') ?: '3306';
        \$db   = getenv('DB_DATABASE') ?: 'railway';
        \$user = getenv('DB_USERNAME') ?: 'root';
        \$pass = getenv('DB_PASSWORD') ?: '';
        try {
            new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass, [PDO::ATTR_TIMEOUT => 3]);
            exit(0);
        } catch (Throwable \$e) {
            exit(1);
        }
    "; then
        echo "Database connection OK."
        break
    fi

    attempt=$((attempt + 1))
    echo "Attempt ${attempt}/${max_attempts} — database not ready, retrying..."
    sleep 2
done

if [ "$attempt" -ge "$max_attempts" ]; then
    echo "ERROR: Could not connect to MySQL. Add a MySQL service in Railway and link DB_* variables."
    exit 1
fi

php artisan migrate --force
php artisan config:cache
php artisan route:cache

echo "Starting Octane (Swoole) on 0.0.0.0:${PORT}..."

exec php artisan octane:start --server=swoole --host=0.0.0.0 --port="${PORT}"
