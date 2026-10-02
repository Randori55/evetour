FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite

COPY php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/uploads

EXPOSE 80

CMD ["bash", "-lc", "a2dismod mpm_event mpm_worker || true; rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*; a2enmod mpm_prefork; sed -i \"s/^Listen 80$/Listen ${PORT:-80}/\" /etc/apache2/ports.conf; sed -i \"s/:80>/:${PORT:-80}>/g\" /etc/apache2/sites-enabled/000-default.conf; apache2ctl -t; exec apache2-foreground"]
