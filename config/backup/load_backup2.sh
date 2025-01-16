#!/bin/sh

echo "Restoring a database from a file ./backup/prestashop_db_backup.sql" 

echo "host: ${DB_SERVER}."
echo "name: ${DB_NAME}. "
echo "user: ${DB_USER}. "
echo "password: ${DB_PASSWD}. "

mysql -h $DB_SERVER -u $DB_USER -p$DB_PASSWD $DB_NAME < "./backup/prestashop_db_backup.sql" 

echo "end"

