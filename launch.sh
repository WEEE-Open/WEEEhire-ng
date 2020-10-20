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
if [[ ! -f weeehire.db ]]; then
	sqlite3 weeehire.db < database.sql
fi
docker-compose up
