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

function upgrade_module_2_0_2($module)
{
    $result = true;

    // AdminMbePackageHelperController
    $id_tab2 = (int)Tab::getIdFromClassName('AdminMbePackageHelper');
    if ($id_tab2) {
        $old_tab2 = new Tab($id_tab2);
        $result &= $old_tab2->delete();
    }
    $tab2 = new Tab();
    $tab2->active = 1;
    $tab2->class_name = 'AdminMbePackageHelper';
    $tab2->name = [];
    foreach (Language::getLanguages(true) as $lang) {
        $tab2->name[$lang['id_lang']] = $module->l('Manage packages');
    }
    $tab2->id_parent = -1;
    $tab2->module = $module->name;
    $result &= $tab2->add();

    // AdminMbePackageProductHelperController
    $id_tab3 = (int)Tab::getIdFromClassName('AdminMbePackageProductHelper');
    if ($id_tab3) {
        $old_tab3 = new Tab($id_tab3);
        $result &= $old_tab3->delete();
    }
    $tab3 = new Tab();
    $tab3->active = 1;
    $tab3->class_name = 'AdminMbePackageProductHelper';
    $tab3->name = [];
    foreach (Language::getLanguages(true) as $lang) {
        $tab3->name[$lang['id_lang']] = $module->l('Manage product packages');
    }
    $tab3->id_parent = -1;
    $tab3->module = $module->name;
    $result &= $tab3->add();

    // AdminMbeChecklistController
    $id_tab4 = (int)Tab::getIdFromClassName('AdminMbeChecklist');
    if ($id_tab4) {
        $old_tab4 = new Tab($id_tab4);
        $result &= $old_tab4->delete();
    }
    $tab4 = new Tab();
    $tab4->active = 1;
    $tab4->class_name = 'AdminMbeChecklist';
    $tab4->name = [];
    foreach (Language::getLanguages(true) as $lang) {
        $tab4->name[$lang['id_lang']] = $module->l('Check module functionality');
    }
    $tab4->id_parent = -1;
    $tab4->module = $module->name;
    $result &= $tab4->add();

    return $result;
}
