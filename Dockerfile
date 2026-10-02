FROM php:8.3-apache

# 1. ÖNCE conflict'i çöz
RUN a2dismod mpm_event mpm_worker || true
RUN a2enmod mpm_prefork

# 2. Sonra diğer modülleri aç
RUN a2enmod rewrite

# 3. PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# 4. Config dosyaları
COPY php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# 5. Uygulamayı kopyala
WORKDIR /var/www/html
COPY . /var/www/html/

# 6. İzinleri düzelt
RUN chown -R www-data:www-data /var/www/html/uploads
