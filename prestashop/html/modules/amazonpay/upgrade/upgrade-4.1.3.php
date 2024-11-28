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

function upgrade_module_4_1_3($module)
{
    $sql = array();
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

    Configuration::updateValue('AMAZONPAY_USE_OWN_CACHE', 1);

    return $module;
}
