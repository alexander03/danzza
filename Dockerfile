# syntax=docker/dockerfile:1
# Imagen de pruebas de DANZZA: PHP 8.5.12 + Apache + Laravel 13
FROM php:8.5.12-apache AS base

# Extensiones requeridas por Laravel, maatwebsite/excel (PhpSpreadsheet), dompdf/tcpdf y milon/barcode
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6-dev libjpeg62-turbo-dev libpng-dev libwebp-dev \
        libzip-dev libicu-dev unzip git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql gd zip intl bcmath exif \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php.ini /usr/local/etc/php/conf.d/zz-danzza.ini
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite headers

WORKDIR /var/www/html

FROM base AS app

# Primero solo las dependencias (aprovecha la caché de capas de Docker)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist

COPY . .

# Web root (public/): no está en el repositorio, se toma de danzza.zip (contexto de build "webroot").
# Su index.php apunta a ../../sisdanzza (ruta del hosting), por eso se reemplaza por el de Laravel 13.
COPY --from=webroot danzza.zip /tmp/danzza.zip
RUN unzip -q /tmp/danzza.zip -d /tmp/webroot \
    && mkdir -p public \
    && cp -a /tmp/webroot/danzza/. public/ \
    && rm -rf public/PHPMailer public/imports/* /tmp/webroot /tmp/danzza.zip
COPY <<'PHP' public/index.php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
PHP

RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache public/imports \
    && chown -R www-data:www-data storage bootstrap/cache public/imports

COPY docker/entrypoint.sh /usr/local/bin/danzza-entrypoint
RUN chmod +x /usr/local/bin/danzza-entrypoint

EXPOSE 80
ENTRYPOINT ["danzza-entrypoint"]
CMD ["apache2-foreground"]
