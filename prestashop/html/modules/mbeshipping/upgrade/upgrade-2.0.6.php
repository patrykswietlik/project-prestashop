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

function upgrade_module_2_0_6($module)
{
    $result = true;

    $tab_position = 0;

    // AdminMbeShippingController
    $tab_shipments = new Tab((int)Tab::getIdFromClassName('AdminMbeShipping'));
    if (Validate::isLoadedObject($tab_shipments)) {
        $tab_position = $tab_shipments->position;
    }

    // AdminMbeConfigurationController
    $tab_configuration = new Tab();
    $tab_configuration->active = 1;
    $tab_configuration->class_name = 'AdminMbeConfiguration';
    foreach (Language::getLanguages(true) as $lang) {
        $tab_configuration->name[$lang['id_lang']] = $module->l('Configuration');
    }
    $tab_configuration->id_parent = (int)Tab::getIdFromClassName('AdminParentShipping');
    $tab_configuration->module = $module->name;

    $result &= $tab_configuration->add();
    if ($tab_position) {
        $result &= $tab_configuration->updatePosition(0, $tab_position);
    }

    return $result;
}
