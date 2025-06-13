FROM php:8-fpm-alpine

# Install dev dependencies
RUN apk add --update --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    icu-dev \
    linux-headers \
    oniguruma-dev

# Install production dependencies
RUN apk add --no-cache \
    bash \
    curl \
    git \
    icu-dev \
    mysql-client

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

ADD schedule-run /etc/crontabs/schedule-run
RUN chmod 0644 /etc/crontabs/schedule-run && chmod +x /etc/crontabs/schedule-run
RUN crontab -u www-data /etc/crontabs/schedule-run

RUN mkdir -p /code/var/cache && \
    mkdir -p /code/var/log && \
    mkdir -p /code/public/uploads && \
    chown -R www-data. /code/var && \
    chown www-data. /code/public/uploads

# Install composer
ENV COMPOSER_ALLOW_SUPERUSER 1
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
