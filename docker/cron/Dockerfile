FROM  php:8.1.5-fpm-alpine

RUN apk add --no-cache bash zsh libpq-dev

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install mysqli pdo pdo_mysql pgsql pdo_pgsql opcache

COPY ./docker/cron/crontab /etc/crontabs/root

CMD ["crond", "-f"]
