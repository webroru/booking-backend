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

RUN docker-php-ext-install \
    bcmath \
    mbstring \
    opcache \
    pdo \
    pdo_mysql

RUN apk del .build-deps && \
    rm -rf /var/cache/apk/*

ADD custom.ini /usr/local/etc/php/conf.d/custom.ini

RUN mkdir -p /code/var/cache && \
    mkdir -p /code/var/log && \
    chown -R www-data. /code/var

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER 1
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
