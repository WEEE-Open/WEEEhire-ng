FROM php:8-fpm-alpine

RUN mkdir -p /var/www/html
WORKDIR /var/www/html

COPY ./composer.json ./composer.json
COPY ./composer.lock ./composer.lock

# Install system dependencies and LDAP development libraries
RUN apk add --no-cache \
        openldap-dev \
        unzip \
        gettext \
    && docker-php-ext-install ldap

# install composer
RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

RUN composer install --no-dev --optimize-autoloader