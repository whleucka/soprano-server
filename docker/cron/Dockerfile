FROM  php:8.1-fpm-alpine

RUN docker-php-ext-install mysqli pdo pdo_mysql opcache

COPY ./docker/cron/crontab /etc/crontabs/root

CMD ["crond", "-f"]
