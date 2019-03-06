FROM php:7.2-fpm

RUN apt-get update && \
    apt-get install -y \
    git \
    unzip \
    openssl \
    cron \
    supervisor

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN rm /etc/localtime && \
    ln -s /usr/share/zoneinfo/Europe/Istanbul /etc/localtime

COPY ./.docker/fpm/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

COPY .docker/fpm/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

RUN echo "0 0 * * * root php /var/www/symfony/bin/console app:scan-repositories" >> /etc/crontab

RUN docker-php-ext-install pdo pdo_mysql
WORKDIR /var/www/symfony
ENTRYPOINT ["/entrypoint.sh"]