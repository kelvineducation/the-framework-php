FROM php:7.2-fpm-alpine

# git is for composer
# bcmath is for phpunit
# zip is for composer

# pgsql is for pulse

RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
    && apk add --no-cache \
        git \
        postgresql-dev \
        postgresql-client \
        zlib-dev \
    && docker-php-ext-install \
        bcmath \
        pgsql \
        zip \
    && apk del --no-cache .build-deps

RUN curl -sS https://getcomposer.org/installer >composer-setup.php \
    && php composer-setup.php --quiet \
        --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

WORKDIR /app
