FROM php:8.4-cli-bookworm

# The official PHP CLI image already provides SQLite/PDO SQLite.
# Rebuilding those bundled extensions can fail and is unnecessary.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       git unzip curl libzip-dev libicu-dev libonig-dev \
    && docker-php-ext-install -j"$(nproc)" zip intl mbstring \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY docker/php/bootstrap.sh /opt/marketplace/bootstrap.sh
COPY docker/php/routes-web.php /opt/marketplace/routes-web.php
COPY legacy/ /opt/marketplace/legacy/
COPY laravel-overlay/ /opt/marketplace/laravel-overlay/
RUN chmod +x /opt/marketplace/bootstrap.sh

EXPOSE 8000
