FROM ghcr.io/roadrunner-server/roadrunner:2025.1.4 AS roadrunner

FROM php:8.4-cli-alpine3.21

# change version to latest if you want to use the latest version of xDebug, see https://xdebug.org
ENV XDEBUG_VERSION=3.4.4

RUN apk add --no-cache autoconf g++ make postgresql-dev coreutils libzip-dev zip unzip --update linux-headers \
    && pecl install xdebug-$XDEBUG_VERSION protobuf \
    && rm -rf /tmp/pear \
    && docker-php-ext-configure pgsql --with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo_pgsql sockets intl zip \
    && docker-php-ext-enable xdebug protobuf intl zip \
    && apk del linux-headers make g++ autoconf

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini
COPY ./.docker/roadrunner/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer

RUN addgroup -g 1000 app && adduser -u 1000 -G app -s /bin/sh -D app

WORKDIR /app

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

RUN chown app:app /usr/local/bin/rr && chmod +x /usr/local/bin/rr

# Copy Composer files first to leverage Docker layer caching, then install deps
COPY --chown=app:app src/composer.json src/composer.lock /app/

USER app

# Install PHP dependencies (including require-dev) during build
RUN composer install --no-interaction --prefer-dist --no-progress --ansi

# Copy application source code into the container (after vendor install to improve caching)
COPY --chown=app:app src /app

# Ensure the cache and storage are writable (common for Laravel apps). Ignore errors if paths don't exist.
RUN mkdir -p /app/storage /app/bootstrap/cache || true \
    && chmod -R ug+rwX /app/storage /app/bootstrap/cache || true

ENTRYPOINT ["/usr/local/bin/rr", "serve"]