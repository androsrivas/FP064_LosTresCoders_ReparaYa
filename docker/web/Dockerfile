ARG PHP_VERSION=8.3
FROM php:${PHP_VERSION}-apache

# Librerías del sistema
RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip zip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libicu-dev libxml2-dev \
&& rm -rf /var/lib/apt/lists/*

# Extensiones PHP (deben ajustarse a las disponibles en el servidor de AWS: phpinfo y php -m)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
&& docker-php-ext-install -j$(nproc) \
    pdo_mysql mysqli mbstring zip gd intl bcmath exif

# Apache
RUN a2enmod rewrite headers
COPY docker/web/000-default.conf /etc/apache2/sites-available/000-default.conf

# Composer SOLO dentro del contenedor local
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html