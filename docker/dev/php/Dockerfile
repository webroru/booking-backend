FROM php:8-fpm-alpine

# Install dev dependencies
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    oniguruma-dev

# Install production dependencies
RUN apk add --no-cache \
    bash \
    curl \
    git \
    mysql-client

## Install PECL and PEAR extensions
RUN pecl install xdebug

RUN docker-php-ext-install \
    bcmath \
    mbstring \
    opcache \
    pdo \
    pdo_mysql

RUN apk del .build-deps && \
    rm -rf /var/cache/apk/*

RUN mkdir -p /code/var/cache && \
    mkdir -p /code/var/log && \
    chown -R www-data. /code/var

# Install and enable php extensions
RUN docker-php-ext-enable xdebug

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER 1
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

USER www-data
