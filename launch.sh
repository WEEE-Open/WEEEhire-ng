#!/bin/bash
function check_or_exit {
	if ! type $1 > /dev/null ; then
		echo "You need to install $1 first, exiting"
		exit 1
	fi
}

check_or_exit sqlite3
check_or_exit docker
check_or_exit docker-compose

echo "Creating database file"
if [[ ! -f database/weeehire.db ]]; then
	sqlite3 database/weeehire.db < database/database.sql
	sqlite3 database/weeehire.db < database/example-data.sql
fi

echo "Copying example configuration"
if [[ ! -f config/config.php ]]; then
	cp config/config-example.php config/config.php
fi

if [[ $# -eq 1 ]]; then
	if [[ $1 == "d" ]] || [[ $1 == "detach" ]]; then
		docker-compose up -d
	elif [[ $1 == "p" ]] || [[ $1 == "php" ]]; then
		docker-compose up -d
		docker-compose logs -f app
		docker-compose down
	else
		echo "Invalid parameter $1" >&2
		exit 1
	fi
else
	docker-compose up
fi
