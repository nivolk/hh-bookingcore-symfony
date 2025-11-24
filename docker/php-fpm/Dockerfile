FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    default-mysql-client \
    bash \
    && docker-php-ext-install \
       intl \
       pdo_mysql \
       zip \
       opcache \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . /var/www/html

RUN composer install --optimize-autoloader --no-interaction \
    && mkdir -p var/cache var/log

CMD ["php-fpm"]
