FROM php:8.4-cli-bookworm AS base

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    unzip \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    libbrotli-dev \
    pkg-config \
    $PHPIZE_DEPS \
    && docker-php-ext-install \
        pdo_mysql \
        bcmath \
        intl \
        pcntl \
        sockets \
        opcache \
        zip \
    && pecl install redis swoole \
    && docker-php-ext-enable redis swoole \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
        $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

FROM base AS vendor

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

FROM base AS runtime

COPY --from=vendor /var/www/html/vendor ./vendor
COPY . .

RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

USER www-data

EXPOSE 8000

CMD ["sh", "-c", "php artisan octane:start --server=swoole --host=0.0.0.0 --port=${PORT:-8000}"]
