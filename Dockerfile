FROM php:8.2-apache
RUN docker-php-ext-install mysqli pdo_mysql && docker-php-ext-enable mysqli pdo_mysql
RUN printf "upload_max_filesize = 10M\npost_max_size = 12M\n" > /usr/local/etc/php/conf.d/uploads.ini