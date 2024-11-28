<?php
/**
 * NOTICE OF LICENSE
 * This source file is subject to the GNU Lesser General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/lgpl-3.0.en.html
 *
 * @author     Autopay S.A.
 * @copyright  Since 2015 Autopay S.A.
 * @license    https://www.gnu.org/licenses/lgpl-3.0.en.html GNU Lesser General Public License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_7_0($module)
{
    $return = true;

    $return &= $module->uninstallTab();
    $return &= $module->installTab();

    $sql = [];

    $sql[] = ' CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'blue_gateway_channels` (
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
                PRIMARY KEY (`id_blue_gateway_channels`)
            ) ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=UTF8;';

    $sql[] = 'TRUNCATE TABLE `' . _DB_PREFIX_ . 'blue_gateway_channels`';
    /*
     ** Here we execute the SQL
     */
    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query)) {
            return Db::getInstance()->getMsgError();
        }
    }

    $return &= $module->registerHook('displayAdminAfterHeader');

    return $return;
}
