#!/bin/bash

DB_CONTAINER="prestashop-db"
PRESTASHOP_CONTAINER="prestashop"
BACKUP_FILE="prestashop_db_backup.sql"
IMG_FOLDER="img"
MODULES_FOLDER="modules"
DESTINATION_FOLDER="./backup"

mkdir -p "$DESTINATION_FOLDER"

echo "Backing up the database"
docker exec -i "$DB_CONTAINER" mysqldump -u root -pprestashop prestashop > "$DESTINATION_FOLDER/$BACKUP_FILE"
if [ $? -eq 0 ]; then
    echo "The database backup has been saved in the $DESTINATION_FOLDER/$BACKUP_FILE"
else
    echo "Error during database backup" >&2
    exit 1
fi

echo "Copying 'img' and 'modules' folders"

docker cp "$PRESTASHOP_CONTAINER:/var/www/html/$IMG_FOLDER" "$DESTINATION_FOLDER"
if [ $? -ne 0 ]; then
    echo "Error when copying a folder $IMG_FOLDER!" >&2
    exit 1
fi

docker cp "$PRESTASHOP_CONTAINER:/var/www/html/$MODULES_FOLDER" "$DESTINATION_FOLDER"
if [ $? -ne 0 ]; then
    echo "Error when copying a folder $MODULES_FOLDER!" >&2
    exit 1
fi

echo "The operation completed successfully. The files have been saved in the $DESTINATION_FOLDER"
