FROM php:8.4-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       git unzip curl libzip-dev libicu-dev libonig-dev libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite sqlite3 zip intl mbstring \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY docker/php/bootstrap.sh /opt/marketplace/bootstrap.sh
RUN chmod +x /opt/marketplace/bootstrap.sh
EXPOSE 8000
