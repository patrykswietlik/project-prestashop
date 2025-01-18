#!/bin/sh

echo "host: ${DB_SERVER}."
echo "name: ${DB_NAME}. "
echo "user: ${DB_USER}. "
echo "password: ${DB_PASSWD}. "
echo "<?php return array (
  'parameters' => 
  array (
    'database_host' => '${DB_SERVER}',
    'database_port' => '',
    'database_name' => '${DB_NAME}',
    'database_user' => '${DB_USER}',
    'database_password' => '${DB_PASSWD}',
    'database_prefix' => 'ps_',
    'database_engine' => 'InnoDB',
    'mailer_transport' => 'smtp',
    'mailer_host' => '127.0.0.1',
    'mailer_user' => NULL,
    'mailer_password' => NULL,
    'secret' => 'L77j02yu7eG8hr4DKAMnBNRM5mejcsn9ggoqsKEKjmmHtvweo2WZfsuK5u1tRqug',
    'ps_caching' => 'CacheMemcache',
    'ps_cache_enable' => false,
    'ps_creation_date' => '2024-12-08',
    'locale' => 'en-US',
    'use_debug_toolbar' => true,
    'cookie_key' => '7fRLDmNMIFjrQhhgK4QK04OacZut1fomguptb4YmrtFosQLOZZQbeq4Wd2NTzVvq',
    'cookie_iv' => 'IMyO4aphsKOTK2x0D1oBymmZKb9BHyk6',
    'new_cookie_key' => 'def00000250407408e5a3b0111fe0ffb21fc56831c7b3b8d225b1b8676f99e63fb24987c285129ad816bdba3feef7ab51314165bd3738d5e349e9d00bbae7bb491a53ebf',
  ),
);" > /var/www/html/app/config/parameters.php

echo "# Doctrine Configuration
doctrine:
  dbal:
    default_connection: default

    connections:
      default:
        driver:   pdo_mysql
        host:     "%database_host%"
        port:     "%database_port%"
        dbname:   "%database_name%"
        user:     "%database_user%"
        password: "%database_password%"
        charset:  utf8mb4
        mapping_types:
          enum: string

        options:
          # PDO::MYSQL_ATTR_INIT_COMMAND
          1002: "SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))"
          # PDO::MYSQL_ATTR_MULTI_STATEMENTS
          1013: '%env(const:runtime:_PS_ALLOW_MULTI_STATEMENTS_QUERIES_)%'

  orm:
    auto_generate_proxy_classes: "%kernel.debug%"
    naming_strategy: prestashop.database.naming_strategy
    auto_mapping: true
    dql:
      string_functions:
        regexp: DoctrineExtensions\Query\Mysql\Regexp
" > /var/www/html/app/config/doctrine.yml

echo "Restoring a database from a file ./backup/prestashop_db_backup.sql" 

until mysql -h $DB_SERVER -u $DB_USER -p$DB_PASSWD -e "SELECT 1;" > /dev/null 2>&1; do
    echo "Waiting for MySQL to be ready..."
    sleep 5
done

mysql -h $DB_SERVER -u $DB_USER -p$DB_PASSWD $DB_NAME < "./backup/prestashop_db_backup.sql"

echo "end"

