FROM php:8.3-apache
# Conflicting MPM modules'ı devre dışı bırak
RUN a2dismod mpm_event mpm_worker || true
RUN a2enmod mpm_prefork rewrite
RUN docker-php-ext-install pdo pdo_mysql
COPY php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
WORKDIR /var/www/html
COPY . /var/www/html/
RUN chown -R www-data:www-data /var/www/html/uploads
