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

function upgrade_module_2_0_0($module)
{
    $module->uninstallOverrides();
    $module->installOverrides();
    fixCarrierOverride();

    Configuration::updateValue('MBESHIPPING_ADVANCED_AUTH_CONF', 1);
    Configuration::updateValue('MBESHIPPING_INITIAL_CONF', 0);

    $is_enabled_custom_mapping = (int)Configuration::get('mbe_enable_custom_mapping');
    $is_set_shipments_csv = !empty(Configuration::get('shipments_csv'));
    $is_set_custom_labels = !empty(Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'configuration` WHERE `name` LIKE \'mbe_custom_label_%\''));

    if ($is_enabled_custom_mapping) {
        Configuration::updateValue('MBESHIPPING_COURIERS_SERVICES_CONF_MODE', 2);
    } elseif ($is_set_shipments_csv) {
        Configuration::updateValue('MBESHIPPING_COURIERS_SERVICES_CONF_MODE', 1);
    } elseif ($is_set_custom_labels) {
        Configuration::updateValue('MBESHIPPING_COURIERS_SERVICES_CONF_MODE', 3);
    }

    $sql = [];

    $sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "mbe_shipping_order`(
                `id_mbeshipping_order` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `id_order` int(10) default 0 not null,
                `is_download_available` int(10) default 1 not null,
                UNIQUE KEY MBE_ORDER_MO_UNIQUE (id_order))";

    foreach ($sql as $query) {
        if (!Db::getInstance()->execute($query)) {
            return Db::getInstance()->getMsgError();
        }
    }

    return true;
}

function fixCarrierOverride()
{
    $find = "require_once(_PS_MODULE_DIR_ . 'mbeshipping' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'custom' . DIRECTORY_SEPARATOR . 'Ws.php');";
    $replace = "\nuse PrestaShop\Module\Mbeshipping\Lib\MbeWs;\nuse PrestaShop\Module\Mbeshipping\Helper\DataHelper;\nuse PrestaShop\Module\Mbeshipping\Helper\RatesHelper;\n";

    $file = _PS_ROOT_DIR_ . '/override/classes/Carrier.php';
    if (file_exists($file)) {
        file_put_contents($file, str_replace($find, $replace, Tools::file_get_contents($file)));
    }
}
