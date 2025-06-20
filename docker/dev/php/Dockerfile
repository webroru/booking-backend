FROM php:8.2-fpm-alpine3.18

# Install dev dependencies
RUN apk add --update --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    linux-headers \
    oniguruma-dev

# Install production dependencies
RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    mysql-client

## Install PECL and PEAR extensions
RUN pecl install xdebug

RUN docker-php-ext-install \
    bcmath \
    intl \
    mbstring \
    opcache \
    pdo \
    pdo_mysql

RUN apk del .build-deps && \
    rm -rf /var/cache/apk/*

ADD custom.ini /usr/local/etc/php/conf.d/custom.ini

# Install and enable php extensions
RUN docker-php-ext-enable xdebug

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER 1
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
