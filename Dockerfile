FROM php:7.3-apache
RUN apt update && \
    docker-php-ext-install mysqli && \
    a2enmod rewrite
