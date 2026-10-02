FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

WORKDIR /var/www/html

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html/uploads

RUN sed -i 's/Listen 80/Listen 8000/' /etc/apache2/ports.conf
RUN sed -i 's/:80>/:8000>/' /etc/apache2/sites-enabled/000-default.conf

EXPOSE 8000
