FROM php:8.3-apache

# Apache modüllerini düzelt
RUN a2dismod mpm_event mpm_worker || true
RUN a2enmod mpm_prefork rewrite

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Config dosyası
COPY php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Uygulama
WORKDIR /var/www/html
COPY . /var/www/html/

# İzinler
RUN chown -R www-data:www-data /var/www/html/uploads
