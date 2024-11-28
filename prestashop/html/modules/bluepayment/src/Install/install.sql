CREATE TABLE IF NOT EXISTS `_DB_PREFIX_blue_gateway_transfers`
(
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `gateway_id` int(11) NOT NULL,
    `gateway_status` int(11) NOT NULL,
    `bank_name` varchar(100) NOT NULL,
    `gateway_name` varchar(100) NOT NULL,
    `gateway_description` varchar(255) DEFAULT NULL,
    `position` int(11) DEFAULT NULL,
    `gateway_currency` varchar(50) NOT NULL,
    `gateway_type` varchar(50) NOT NULL,
    `gateway_logo_url` varchar(500) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = _MYSQL_ENGINE_
  DEFAULT CHARSET = UTF8;

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_blue_gateway_transfers_shop`
(
    `id` INT(10) NOT NULL AUTO_INCREMENT,
    `id_shop` INT(10) unsigned NOT NULL,
    PRIMARY KEY (`id`,`id_shop`)
) ENGINE = _MYSQL_ENGINE_
  DEFAULT CHARSET = UTF8;

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_blue_gateway_channels`
(
    `id_blue_gateway_channels` int(11) NOT NULL AUTO_INCREMENT,
    `gateway_id` int(11) NOT NULL,
    `gateway_status` int(11) NOT NULL,
    `bank_name` varchar(100) NOT NULL,
    `gateway_name` varchar(100) NOT NULL,
    `gateway_description` varchar(255) DEFAULT NULL,
    `position` int(11) DEFAULT NULL,
    `gateway_currency` varchar(50) NOT NULL,
    `gateway_type` varchar(50) NOT NULL,
    `gateway_payments` int(11) NOT NULL,
    `gateway_logo_url` varchar(500) DEFAULT NULL,
    `min_amount` DECIMAL(14,6) NOT NULL DEFAULT "0.000000",
    `max_amount` DECIMAL(14,6) NOT NULL DEFAULT "0.000000",
    PRIMARY KEY (`id_blue_gateway_channels`)
) ENGINE = _MYSQL_ENGINE_
  DEFAULT CHARSET = UTF8;

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_blue_gateway_channels_shop`
(
    `id_blue_gateway_channels` INT(10) NOT NULL AUTO_INCREMENT,
    `id_shop` INT(10) unsigned NOT NULL,
    PRIMARY KEY (`id_blue_gateway_channels`,`id_shop`)
) ENGINE = _MYSQL_ENGINE_
  DEFAULT CHARSET = UTF8;

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_blue_transactions`
(
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` varchar(256) DEFAULT NULL,
    `gtag_uid` varchar(256) DEFAULT NULL,
    `gtag_state` int(1) DEFAULT NULL,
    `remote_id` varchar(128) DEFAULT NULL,
    `amount` DECIMAL(17,2) DEFAULT NULL,
    `currency` varchar(32) DEFAULT NULL,
    `gateway_id` varchar(32) DEFAULT NULL,
    `payment_date` DATETIME DEFAULT NULL,
    `payment_status` varchar(64) DEFAULT NULL,
    `payment_status_details` varchar(128) DEFAULT NULL,
    `blik_status` varchar(32) DEFAULT NULL,
    `blik_code` varchar(32) DEFAULT NULL,
    `created_at` DATETIME DEFAULT NULL,
    `updated_at` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE = _MYSQL_ENGINE_
  DEFAULT CHARSET = UTF8;
