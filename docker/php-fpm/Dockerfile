FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    default-mysql-client \
    && docker-php-ext-install \
       intl \
       pdo_mysql \
       zip \
       opcache \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN useradd -ms /bin/bash symfony \
    && chown -R symfony:symfony /var/www/html

USER symfony

CMD ["php-fpm"]
