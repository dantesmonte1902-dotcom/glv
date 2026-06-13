FROM php:8.3-fpm

RUN apt-get update     && apt-get install -y git unzip libpq-dev libzip-dev curl libicu-dev libonig-dev     && docker-php-ext-install pdo_pgsql intl pcntl zip     && pecl install redis     && docker-php-ext-enable redis     && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]
