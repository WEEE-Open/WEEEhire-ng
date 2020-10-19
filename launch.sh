#!/bin/sh

if [[ ! type sqlite3 > /dev/null ]]; then
	echo "You need to install sqlite3 first, exiting"
	exit 1
fi
if [[ ! type docker > /dev/null ]]; then
	echo "You need to install docker first, exiting"
	exit 1
fi
if [[ ! type docker-compose > /dev/null ]]; then
	echo "You need to install docker-compose first, exiting"
	exit 1
fi
echo "Creating database file"
sqlite3 weeehire.db < database.sql

docker-compose up
