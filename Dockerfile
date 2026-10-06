FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf && a2enmod mpm_prefork && apache2ctl -M && apache2ctl -t
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html
COPY . /var/www/html/
COPY uploads/ /opt/evetour-default-uploads/

RUN chown -R www-data:www-data /var/www/html/uploads

CMD ["bash", "-c", "mkdir -p /var/www/html/uploads && cp -an /opt/evetour-default-uploads/. /var/www/html/uploads/ && chown -R www-data:www-data /var/www/html/uploads && rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf && a2enmod mpm_prefork && sed -ri \"s/Listen [0-9]+/Listen 8000/\" /etc/apache2/ports.conf && sed -ri \"s/<VirtualHost \\*:[0-9]+>/<VirtualHost *:8000>/\" /etc/apache2/sites-enabled/000-default.conf && apache2ctl -t && exec apache2-foreground"]
