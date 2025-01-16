#!/bin/sh

echo "Restoring a database from a file ./backup/prestashop_db_backup.sql" 
mysql -h 98.82.14.176 -u root -pprestashop prestashop < "./backup/prestashop_db_backup.sql" 
echo "end"

