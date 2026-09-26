FROM composer:2 AS composer_deps

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts


FROM node:22-bookworm AS frontend_builder

WORKDIR /app

COPY package.json package-lock.json ./

RUN npm ci

COPY . .

ARG VITE_REVERB_APP_KEY=
ARG VITE_REVERB_HOST=
ARG VITE_REVERB_PORT=443
ARG VITE_REVERB_SCHEME=https

ENV VITE_REVERB_APP_KEY=${VITE_REVERB_APP_KEY} \
    VITE_REVERB_HOST=${VITE_REVERB_HOST} \
    VITE_REVERB_PORT=${VITE_REVERB_PORT} \
    VITE_REVERB_SCHEME=${VITE_REVERB_SCHEME}

RUN npm run build


FROM php:8.2-cli-bookworm AS app_runtime

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        default-mysql-client \
        libicu-dev \
        libonig-dev \
        libpng-dev \
        libsqlite3-dev \
        libxml2-dev \
        libzip-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        pcntl \
        pdo_mysql \
        pdo_sqlite \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

ENV APP_ENV=production \
    APP_DEBUG=false

COPY docker/prod/php/production.ini \
    /usr/local/etc/php/conf.d/zz-production.ini

COPY . .

COPY --from=composer_deps /app/vendor \
    /var/www/html/vendor

COPY --from=frontend_builder /app/public/build \
    /var/www/html/public/build

COPY docker/prod/php/entrypoint.prod.sh \
    /usr/local/bin/app-entrypoint

RUN mkdir -p \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R ug+rwx storage bootstrap/cache \
    && chmod +x /usr/local/bin/app-entrypoint

USER www-data

EXPOSE 8000 8080

ENTRYPOINT ["app-entrypoint"]

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
