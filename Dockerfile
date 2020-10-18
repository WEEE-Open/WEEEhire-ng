FROM nginx:latest

# Install fundamental packages
RUN apt update
RUN apt install -y\
        composer \
        php \
        php-cli \
        php-bcmath \
        php-ctype \
        php-curl \
        php-dom \
        php-fpm \
        php-gd \
        php-iconv \
        php-intl \
        php-json \
        php-ldap \
        php-sqlite3 \
        php-mbstring \
        php-mysqlnd \
        php-opcache \
        php-pdo \
        php-phar \
        php-posix \
        php-soap \
        php-tokenizer \
        php-xml \
        php-xmlwriter \
        php-xmlreader \
        php-zip

# Update dependencies with composer
COPY composer.json /var/www/html/composer.json
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader 

# Create mountpoint for database file
RUN touch weeehire.db

# Launch services in reload.sh script
COPY ./nginx-config/reload.sh /bin/reload.sh
ENTRYPOINT [ "/bin/reload.sh" ]
