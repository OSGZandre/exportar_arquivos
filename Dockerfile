FROM php:8.4-apache

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    git \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install intl opcache pdo_mysql zip gd \
    && pecl install apcu xdebug \
    && docker-php-ext-enable apcu xdebug \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Configurações personalizadas do PHP
RUN { \
    echo "apc.enable_cli=1"; \
    echo "apc.enable=1"; \
    echo "memory_limit=4G"; \
    echo "upload_max_filesize=2G"; \
    echo "post_max_size=2G"; \
    echo "max_file_uploads=100"; \
} > /usr/local/etc/php/conf.d/99-custom.ini

# Configuração do Xdebug
RUN { \
    echo "xdebug.mode=debug"; \
    echo "xdebug.start_with_request=yes"; \
    echo "xdebug.client_host=host.docker.internal"; \
    echo "xdebug.client_port=9003"; \
} > /usr/local/etc/php/conf.d/99-xdebug.ini

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www/html