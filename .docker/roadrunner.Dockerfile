# Stage 1: get RoadRunner binary
FROM spiralscout/roadrunner:2025.1.4 AS rr

# Stage 2: PHP 8.4 with Composer, plus RoadRunner binary copied in
FROM php:8.4-cli-alpine
LABEL authors="Helio"

# Copy RoadRunner binary from the rr image
COPY --from=rr /usr/bin/rr /usr/local/bin/rr

# Add Composer (copy the single binary from the official image)
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Ensure binaries are executable
RUN chmod +x /usr/local/bin/rr /usr/local/bin/composer

# ---- PHP extensions required by Laravel 11 (and Postgres) ----
# Runtime libs first (smaller layers):
RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-libs \
    libzip \
    libpng \
    freetype \
    libjpeg-turbo \
    oniguruma \
    libpq

# Build dependencies for compiling PHP extensions
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    libpng-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libxml2-dev \
    oniguruma-dev \
    linux-headers

# Configure and install extensions
# - Laravel core: intl (locale/Carbon), mbstring, bcmath, zip, gd (images), opcache (perf), pdo_pgsql (PostgreSQL)
# - RoadRunner recommended: pcntl (workers), sockets (RPC/GRPC integrations and networking helpers)
# - XML stack commonly needed by Symfony/Laravel components: dom, xml, xmlwriter, xmlreader, simplexml
RUN set -eux; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install -j"$(nproc)" \
        intl \
        mbstring \
        bcmath \
        pcntl \
        posix \
        sockets \
        pdo_pgsql \
        zip \
        gd \
        opcache \
        dom \
        xml \
        xmlwriter \
        xmlreader \
        simplexml; \
    pecl install redis; \
    pecl install protobuf; \
    docker-php-ext-enable redis; \
    docker-php-ext-enable protobuf; \
    apk del .build-deps; \
    rm -rf /var/cache/apk/* /tmp/pear

# Make Composer-installed binaries globally available in PATH and set working directory
ENV COMPOSER_HOME=/root/.composer
# Explicitly include default PATH entries to avoid literal ${PATH} in Docker ENV
ENV PATH="/app/vendor/bin:/root/.composer/vendor/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"
WORKDIR /app

# Keep the simple top-based entrypoint unless overridden by compose or runtime command
ENTRYPOINT ["./rr", "serve"]