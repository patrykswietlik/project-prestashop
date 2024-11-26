#!/bin/bash

DB_CONTAINER="prestashop-db-bk"
PRESTASHOP_CONTAINER="prestashop-bk"
BACKUP_FILE="prestashop_db_backup.sql"
THEMES_FOLDER="themes"
IMG_FOLDER="img"
MODULES_FOLDER="modules"
SOURCE_FOLDER="./backup"

if [ ! -f "$SOURCE_FOLDER/$BACKUP_FILE" ]; then
    echo "Error: Database backup file $SOURCE_FOLDER/$BACKUP_FILE does not exist!" >&2
    exit 1
fi

echo "Restoring a database from a file $SOURCE_FOLDER/$BACKUP_FILE"
docker exec -i "$DB_CONTAINER" mysql -u root -pprestashop prestashop < "$SOURCE_FOLDER/$BACKUP_FILE"
if [ $? -eq 0 ]; then
    echo "The database has been successfully restored"
else
    echo "Error while restoring the database" >&2
    exit 1
fi

if [ ! -d "$SOURCE_FOLDER/$THEMES_FOLDER" ]; then
    echo "Error: Folder $SOURCE_FOLDER/$THEMES_FOLDER does not exist" >&2
    exit 1
fi

if [ ! -d "$SOURCE_FOLDER/$IMG_FOLDER" ]; then
    echo "Error: Folder $SOURCE_FOLDER/$IMG_FOLDER does not exist" >&2
    exit 1
fi

if [ ! -d "$SOURCE_FOLDER/$MODULES_FOLDER" ]; then
    echo "Error: Folder $SOURCE_FOLDER/$MODULES_FOLDER does not exist" >&2
    exit 1
fi

echo "Loading folders '$THEMES_FOLDER', '$IMG_FOLDER' i '$MODULES_FOLDER'"
docker cp "$SOURCE_FOLDER/$THEMES_FOLDER" "$PRESTASHOP_CONTAINER:/var/www/html/"
if [ $? -ne 0 ]; then
    echo "Error while loading a folder $THEMES_FOLDER!" >&2
    exit 1
fi

docker cp "$SOURCE_FOLDER/$IMG_FOLDER" "$PRESTASHOP_CONTAINER:/var/www/html/"
if [ $? -ne 0 ]; then
    echo "Error while loading a folder $IMG_FOLDER!" >&2
    exit 1
fi

docker cp "$SOURCE_FOLDER/$MODULES_FOLDER" "$PRESTASHOP_CONTAINER:/var/www/html/"
if [ $? -ne 0 ]; then
    echo "Error while loading a folder $MODULES_FOLDER!" >&2
    exit 1
fi

echo "The operation completed successfully."
