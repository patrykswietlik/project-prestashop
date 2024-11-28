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

use mbeshipping\classes\custom\helper;

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_5_0()
{
    $sql = [];

    $sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "mbe_shipping_standard_packages`(
                `id_mbeshippingpackage` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `max_weight` decimal(12,4) default 0 not null,
                `length` decimal(12,4) default 0 not null,
                `width` decimal(12,4) default 0 not null,
                `height` decimal(12,4) default 0 not null,
                `package_label` varchar(255) not null,
                `package_code` varchar(55) not null,                
                 UNIQUE KEY MBE_PKG_PROD_UNIQUE (package_code))";

    $sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "mbe_shipping_standard_package_product`(
                `id_mbeshippingpackageproduct` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `custom_package` tinyint(1) NULL,
                `single_parcel` tinyint(1) NULL,
                `product_sku` varchar(64) NOT NULL DEFAULT '',
                `package_code` varchar(50) NOT NULL DEFAULT '',                                
                UNIQUE KEY MBE_PKG_PROD_PACKAGE_PRODUCT_UNIQUE  (package_code, product_sku),
                UNIQUE KEY MBE_PKG_PROD_PRODUCT_SKU  (product_sku),
                KEY MBE_PKG_PROD_PACKAGE_CODE  (package_code))";

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return Db::getInstance()->getMsgError();
        }
    }
    return true;
}
