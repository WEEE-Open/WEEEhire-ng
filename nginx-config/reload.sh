#!/bin/bash

# DB creation
echo "Creating database file from database.sql"
sqlite3 weeehire.db < database.sql

# Localization
echo "Generating localization files"
msgfmt resources/locale/en-US/LC_MESSAGES/messages.po --output-file=resources/locale/en-US/LC_MESSAGES/messages.mo

# Without this forced reload the container doesn't start the fpm service
echo "Reloading and starting FPM"
service php7.3-fpm reload
service php7.3-fpm start

# Recreate the original /docker-entrypoint.sh in order to correctly start nginx
echo "Starting NGINX"
/docker-entrypoint.sh nginx -g "daemon off;"
