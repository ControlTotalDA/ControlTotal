FROM php:8.4-cli-bookworm AS base

RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    curl \
    unzip \
    procps \
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

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

FROM base AS runtime

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY --from=vendor /var/www/html/vendor ./vendor
COPY . .

COPY docker/railway-start.sh /usr/local/bin/railway-start.sh
RUN chmod +x /usr/local/bin/railway-start.sh \
    && composer dump-autoload --optimize \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD ["/usr/local/bin/railway-start.sh"]
