FROM php:8.3-apache

# Apache modülleri tamamen sıfırla
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load
RUN a2dismod mpm_event mpm_prefork mpm_worker || true

# Sadece mpm_prefork'u yükle
RUN a2enmod mpm_prefork rewrite

# PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Config
COPY php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html/uploads
