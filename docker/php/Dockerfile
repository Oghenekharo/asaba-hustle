FROM php:8.2-fpm-bookworm

WORKDIR /var/www/html

# Install system dependencies (Debian-based)
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

# Install PHP extensions (use docker-php-ext-install)
RUN docker-php-ext-install \
    bcmath \
    intl \
    opcache \
    pcntl \
    pdo_mysql \
    sockets \
    zip \
    && docker-php-ext-enable opcache

# Install Redis extension via PECL
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy PHP configs (if you have them)
COPY docker/local/php/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/local/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copy and set up entrypoint
COPY docker/local/php/entrypoint.sh /usr/local/bin/app-entrypoint
RUN chmod +x /usr/local/bin/app-entrypoint

# Use the entrypoint (recommended for consistency)
ENTRYPOINT ["app-entrypoint"]

# Default command (will be overridden in compose for app and reverb)
CMD ["php", "-a"]   # or leave empty; composer and artisan commands will override
