<?php
/**
 * 2007-2023 patworx.de
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade AmazonPay to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    patworx multimedia GmbH <service@patworx.de>
 *  @copyright 2007-2023 patworx multimedia GmbH
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

$sql = array();

$sql[] = '
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'amazonpay_transactions` (
        `amazon_transaction_id` int(11) NOT NULL AUTO_INCREMENT,
        `amazon_checkout_session` varchar(255) NOT NULL,
        `amazon_transaction` varchar(255) NOT NULL,
        `transaction_type` varchar(16) NOT NULL,
        `transaction_amount` float NOT NULL,
        `transaction_reference` varchar(255) NOT NULL,
        `date_add` datetime NOT NULL,
        `date_upd` datetime NOT NULL,
        PRIMARY KEY (`amazon_transaction_id`),
        KEY `amazon_checkout_session` (`amazon_checkout_session`),
        KEY `amazon_transaction` (`amazon_transaction`),
        KEY `transaction_type` (`transaction_type`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
';

$sql[] = '
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'amazonpay_orders` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `id_order` int(11) NOT NULL,
        `id_cart` int(11) NOT NULL,
        `amazon_checkout_session_id` varchar(255) NOT NULL,
        `amazon_charge_permission_id` varchar(255) NOT NULL,
        `amazon_charge_id` varchar(255) NOT NULL,
        PRIMARY KEY (`id`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
';

$sql[] = '
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'amazonpay_address_reference` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `id_address` int(11) NOT NULL,
        `amazon_order_reference_id` varchar(255) NOT NULL,
        `amazon_hash` varchar(255) NOT NULL,
        PRIMARY KEY (`id`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
';

$sql[] = '
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'amazonpay_customer_reference` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `id_customer` int(11) NOT NULL,
        `amazon_customer_id` varchar(255) NOT NULL,
        PRIMARY KEY (`id`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
';

$sql[] = '
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'amazonpay_ipn` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `notification_id` varchar(255) NOT NULL,
        `message_payload` TEXT NOT NULL,
        PRIMARY KEY (`id`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'amazonpay_cache` (
    `id_amazonpay_cache` int(11) NOT NULL AUTO_INCREMENT,
    `id_lang` INT(11) NOT NULL,
    `cache_key` VARCHAR(255) NOT NULL,
    `cache_value` MEDIUMTEXT NOT NULL,
    `date_add` DATETIME,
    PRIMARY KEY  (`id_amazonpay_cache`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'ALTER TABLE `'._DB_PREFIX_.'amazonpay_cache` ADD INDEX (`cache_key`)';
$sql[] = 'ALTER TABLE `'._DB_PREFIX_.'amazonpay_cache` ADD INDEX (`id_lang`)';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
