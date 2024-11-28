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

function upgrade_module_2_1_6($module)
{
    $result = true;

    // Install mbe_shipping_pickup_batch table
    $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "mbe_shipping_pickup_batch`(
                `id_mbeshipping_pickup_batch` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `pickup_batch_id` VARCHAR(255) NOT NULL,
                `cutoff_period` ENUM('MORNING','AFTERNOON'),
                `cutoff_preferred_from` VARCHAR(5),
                `cutoff_preferred_to` VARCHAR(5),
                `cutoff_alternative_from` VARCHAR(5),
                `cutoff_alternative_to` VARCHAR(5),
                `notes` TEXT,
                `date` DATE,
                `pickup_address_id` BIGINT,
                `sender_name` VARCHAR(35),
                `sender_company` VARCHAR(35),
                `sender_address` VARCHAR(100),
                `sender_phone` VARCHAR(50),
                `sender_zip` VARCHAR(12),
                `sender_city` VARCHAR(50),
                `sender_state` VARCHAR(2),
                `sender_country` VARCHAR(2),
                `sender_email` VARCHAR(75),
                `is_single_pickup` TINYINT(1) DEFAULT 0 NOT NULL,
                `status` ENUM('READY','INITIALIZED','SENT','CONFIRMED') DEFAULT 'READY' NOT NULL,
                UNIQUE KEY MBE_PKUP_BATCH_UNIQUE (pickup_batch_id))";
    $result &= Db::getInstance()->execute($sql);

    // Add id_mbeshipping_pickup_batch reference to mbe_shipping_order table
    $result &= Db::getInstance()->execute(
        'ALTER TABLE `' . _DB_PREFIX_ . 'mbe_shipping_order` '.
        'ADD `id_mbeshipping_pickup_batch` int(10) default 0 not null'
    );

    // Re-install module tabs
    $result &= $module->uninstallTab();
    $result &= $module->installTab();

    return $result;
}
