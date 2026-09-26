FROM php:8.2-cli-bookworm

WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    default-mysql-client \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    bcmath \
    intl \
    opcache \
    pcntl \
    pdo_mysql \
    sockets \
    zip \
    && docker-php-ext-enable opcache

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy configs
COPY docker/dev/php/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/dev/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copy entrypoint
COPY docker/dev/php/entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint

# Create storage directories and set permissions
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
    bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

ENTRYPOINT ["app-entrypoint"]
CMD ["php", "-a"]
