FROM  php:8.1.5-fpm-alpine

COPY ./php/config/php.ini /usr/local/etc/php/php.ini

RUN apk add --no-cache ffmpeg bash zsh libpq-dev imagemagick-dev imagemagick-libs imagemagick
# Install required build dependencies
RUN apk add --no-cache $PHPIZE_DEPS
RUN pecl install imagick && docker-php-ext-enable imagick
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install mysqli pdo pdo_mysql pgsql pdo_pgsql opcache
# Clean up build dependencies
RUN apk del $PHPIZE_DEPS
