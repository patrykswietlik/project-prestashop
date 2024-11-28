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

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_2_0($module)
{
    $_mdp_table_name = 'mbe_shipping_mdp';

    $sql = [];

    $sql[] = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . bqSQL($_mdp_table_name) . "`";

    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mbe_shipping_dp` (
    `id_mbe_shipping_dp` int(11) NOT NULL AUTO_INCREMENT,
    `id_cart` INT(10) NOT NULL,
    `id_order` INT(10) DEFAULT NULL,
    `mbe_service_id` VARCHAR(255) NOT NULL,
    `network_name` VARCHAR(255) NOT NULL,
    `network_id` VARCHAR(255) NOT NULL,
    `pudo_id` VARCHAR(255) NOT NULL,
    `pudo_data` TEXT NOT NULL,
    `date_add` datetime NOT NULL,
    `date_upd` datetime NOT NULL,
    PRIMARY KEY (`id_mbe_shipping_dp`),
    INDEX `id_order` (`id_order`),
	INDEX `id_cart` (`id_cart`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'mbe_shipping_order` ADD `is_dp` TINYINT(1) DEFAULT 0 NOT NULL';
    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'mbe_shipping_order` ADD `service_dp` VARCHAR(255) NOT NULL';
    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'mbe_shipping_order` ADD `net_tax_duty_total_price` decimal(20,6) DEFAULT NULL';
    $sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'mbe_shipping_order` ADD `custom_duties_guaranteed` tinyint(1) DEFAULT NULL';

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }

    $result = true;

    $result &= $module->registerHook('displayOrderConfirmation');
    $result &= $module->registerHook('displayCarrierList');

    return $result;
}

