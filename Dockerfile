FROM php:7.3-apache as production

LABEL maintainer="honza@getrector.org"

WORKDIR /var/www/rector-ci.org

# Install php extensions + cleanup
RUN apt-get update && apt-get install -y \
        git \
        unzip \
        g++ \
        zlib1g-dev \
        libicu-dev \
        libzip-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl \
    && pecl -q install \
        zip \
    && docker-php-ext-enable zip \
    && apt-get clean \
    && rm -rf /tmp/* /usr/local/lib/php/doc/* /var/cache/apt/*

# Installing composer and prestissimo globally
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER 1
RUN composer global require "hirak/prestissimo:^0.3" --prefer-dist --no-progress --no-suggest --classmap-authoritative --no-plugins --no-scripts

COPY ./.docker/apache/apache.conf /etc/apache2/sites-available/000-default.conf

# TODO: performance optimizations https://symfony.com/doc/current/performance.html

COPY composer.json composer.lock ./

RUN composer install --prefer-dist --no-scripts --no-autoloader --no-progress --no-suggest \
    && composer clear-cache

COPY . .

RUN mkdir -p ./var/cache \
    ./var/log \
        && composer dump-autoload -o \
        && chown -R www-data ./var


## Local build with xdebug
FROM production as dev

COPY ./.docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

## Install Xdebug extension + cleanup
RUN pecl -q install xdebug \
    && docker-php-ext-enable xdebug \
    && apt-get clean \
    && rm -rf /tmp/* /usr/local/lib/php/doc/* /var/cache/apt/*
