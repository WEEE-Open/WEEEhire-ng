#!/bin/bash

# Without this forced reload the container doesn't start the fpm service
echo "Reloading and starting FPM"
service php7.3-fpm reload
service php7.3-fpm start

# Recreate the original /docker-entrypoint.sh in order to correctly start nginx
echo "Starting NGINX"
/docker-entrypoint.sh nginx -g "daemon off;"
