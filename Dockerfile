FROM dunglas/frankenphp:php8.4

RUN install-php-extensions pdo_mysql intl zip

COPY --from=composer/composer:2-bin /composer /usr/bin/composer

COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /app