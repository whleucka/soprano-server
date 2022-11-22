FROM  php:8.1.5-fpm-alpine

RUN apk add --no-cache bash zsh libpq-dev

COPY ./docker/php/config/php.ini /usr/local/etc/php/php.ini

#RUN docker-php-ext-install mysqli pgsql sqlite3 pdo pdo_mysql pdo_pgsql pdo_sqlite opcache
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install mysqli pdo pdo_mysql pgsql pdo_pgsql opcache
