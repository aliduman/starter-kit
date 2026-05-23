FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
        bash \
        git \
        icu-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        libzip-dev \
        oniguruma-dev \
        mysql-client \
        nodejs \
        npm \
        autoconf \
        g++ \
        make \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) bcmath gd intl mbstring opcache pdo_mysql zip exif \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY deploy/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY deploy/php/php.ini      /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /var/www/html

EXPOSE 9000
CMD ["php-fpm"]
