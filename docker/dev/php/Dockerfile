FROM php:8.2-fpm-alpine3.18

# Install dev dependencies (for building extensions)
RUN apk add --update --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    icu-dev \
    linux-headers \
    libxml2-dev \
    oniguruma-dev

# Install production dependencies
RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu \
    libxml2 \
    mysql-client

## Install PECL and PEAR extensions
RUN pecl install xdebug

RUN docker-php-ext-install \
    bcmath \
    intl \
    mbstring \
    opcache \
    pdo \
    pdo_mysql \
    soap

# Remove build dependencies to reduce image size
RUN apk del .build-deps && \
    rm -rf /var/cache/apk/* /tmp/*

# Add custom PHP config
ADD custom.ini /usr/local/etc/php/conf.d/custom.ini

# Install and enable php extensions
RUN docker-php-ext-enable xdebug

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
