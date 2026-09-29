FROM php:8.2-apache

# 1. Instala as dependências de sistema necessárias para a extensão GD
#    (libfreetype-dev, libjpeg62-turbo-dev e libpng-dev)
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && rm -rf /var/lib/apt/lists/*

# 2. Configura e instala as extensões PHP
#    A ordem é importante: configurar a GD antes de instalá-la garante
#    o suporte a JPEG e FreeType.
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql gd

# 3. Habilita o mod_rewrite do Apache
RUN a2enmod rewrite

# 4. Configurações de desenvolvimento (mostra erros, aumenta limite de upload)
RUN echo "display_errors=On" >> /usr/local/etc/php/conf.d/dev.ini \
 && echo "error_reporting=E_ALL" >> /usr/local/etc/php/conf.d/dev.ini \
 && echo "upload_max_filesize=8M" >> /usr/local/etc/php/conf.d/dev.ini \
 && echo "post_max_size=10M" >> /usr/local/etc/php/conf.d/dev.ini \
 && echo "memory_limit=256M" >> /usr/local/etc/php/conf.d/dev.ini

# 5. Aponta o DocumentRoot do Apache para /var/www/html
ENV APACHE_DOCUMENT_ROOT=/var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html