FROM php:8.2.12-fpm-bookworm

RUN mkdir -p /var/www/html
WORKDIR /var/www/html

COPY ./composer.json ./composer.json
COPY ./composer.lock ./composer.lock

RUN apt-get update && apt-get install -y libldap2-dev libldb-dev unzip gettext \
    && ln -s /usr/lib/x86_64-linux-gnu/libldap.so /usr/lib/libldap.so \
    && ln -s /usr/lib/x86_64-linux-gnu/liblber.so /usr/lib/liblber.so
RUN docker-php-ext-install ldap

# install composer
RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer

RUN composer install --no-dev --optimize-autoloader