<?php
/**
 * 2017-2022 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    MBE Worldwide
 * @copyright 2017-2024 MBE Worldwide
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of MBE Worldwide
 */

namespace PrestaShop\Module\Mbeshipping\Helper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PickupAddressHelper
{
    protected $_table_name  = 'mbe_shipping_pickup_address';

    public function installPickupAddressTable(): bool
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . bqSQL($this->_table_name) . "`(
                `id_mbe_shipping_pickup_address` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `pickup_address_id` BIGINT NOT NULL,
                `trade_name` VARCHAR(35) NOT NULL,
                `address1` VARCHAR(100) NOT NULL,
                `address2` VARCHAR(35),
                `address3` VARCHAR(35),
                `zip_code` VARCHAR(12) NOT NULL,
                `city` VARCHAR(50) NOT NULL,
                `province` VARCHAR(2) NOT NULL,
                `country` VARCHAR(2) NOT NULL,
                `reference` VARCHAR(35) NOT NULL,
                `phone1` VARCHAR(50) NOT NULL,
                `phone2` VARCHAR(50),
                `email1` VARCHAR(75) NOT NULL,
                `email2` VARCHAR(75),
                `fax` VARCHAR(50),
                `res` TINYINT(1) DEFAULT 0 NOT NULL,
                `mmr` TINYINT(1) DEFAULT 0 NOT NULL,
                `ltz` TINYINT(1) DEFAULT 0 NOT NULL,
                `is_default` TINYINT(1) DEFAULT 0 NOT NULL,
                `deleted` TINYINT(1) DEFAULT 0 NOT NULL,
                UNIQUE KEY MBE_PKUP_ADDR_ID (`pickup_address_id`))";
        return \Db::getInstance()->execute($sql);
    }

    public function uninstallPickupAddressTable(): bool
    {
        $sql = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . bqSQL($this->_table_name) . "`";
        return \Db::getInstance()->execute($sql);
    }

    public function truncate(): bool
    {
        $truncateSql = "TRUNCATE `" . _DB_PREFIX_ . bqSQL($this->_table_name) . "`";
        return \Db::getInstance()->execute($truncateSql);
    }
}
