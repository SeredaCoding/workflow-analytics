FROM php:8.4-fpm

RUN apt-get update && apt-get install -y libpq-dev unzip \
    && docker-php-ext-install -j$(nproc) pgsql pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
