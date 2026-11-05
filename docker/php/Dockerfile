# syntax=docker/dockerfile:1.7
# Images are pinned to supported, security-patched lines (PHP 8.5, Node 26 LTS).
ARG PHP_VERSION=8.5
ARG NODE_VERSION=26

FROM node:${NODE_VERSION}-bookworm-slim AS node
FROM composer:2 AS composer

# ---------------------------------------------------------------- base
FROM php:${PHP_VERSION}-fpm-bookworm AS base

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libicu-dev \
        libpq-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
        unzip \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd intl opcache pcntl pdo_pgsql zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html

# ---------------------------------------------------------------- development
FROM base AS development

ARG UID=1000
ARG GID=1000

COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
    && ln -s /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx \
    && groupmod --gid "${GID}" www-data \
    && usermod --uid "${UID}" --gid "${GID}" www-data

COPY docker/php/php.ini /usr/local/etc/php/conf.d/portfolio.ini
USER www-data
EXPOSE 9000
CMD ["php-fpm"]

# ---------------------------------------------------------------- vendor (no dev dependencies)
FROM base AS vendor
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts --no-autoloader

# ---------------------------------------------------------------- assets
FROM node:${NODE_VERSION}-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY . .
RUN npm run build

# ---------------------------------------------------------------- production
FROM base AS production

COPY --chown=www-data:www-data . .
COPY --from=vendor --chown=www-data:www-data /var/www/html/vendor ./vendor
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative --no-interaction \
    && rm -f /usr/local/bin/composer \
    && mkdir -p storage/app/private storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/php/php.ini /usr/local/etc/php/conf.d/portfolio.ini
COPY docker/php/php-production.ini /usr/local/etc/php/conf.d/zz-production.ini

USER www-data
EXPOSE 9000
CMD ["php-fpm"]

# ---------------------------------------------------------------- web (static files + PHP proxy)
FROM nginx:1.29-alpine AS web
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=production /var/www/html/public /var/www/html/public
