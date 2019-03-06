FROM php:7.2-cli

RUN apt-get update && \
    apt-get install -y \
    openssl

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/symfony
ENTRYPOINT ["/entrypoint.sh"]