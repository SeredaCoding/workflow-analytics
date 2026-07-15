FROM php:8.4-fpm

RUN apt-get update && apt-get install -y libpq-dev unzip libpng-dev libjpeg-dev libwebp-dev \
    && docker-php-ext-install -j$(nproc) pgsql pdo_pgsql gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

USER www-data
