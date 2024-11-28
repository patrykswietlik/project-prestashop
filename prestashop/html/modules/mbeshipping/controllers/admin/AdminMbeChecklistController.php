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

class AdminMbeChecklistController extends ModuleAdminController
{
    public function postProcess()
    {
        if (Tools::getIsset('ajax') && Tools::getIsset('action')) {
            $method_name = 'ajaxProcess' . Tools::getValue('action');
            if (method_exists($this, $method_name)) {
                $this->$method_name(Tools::getAllValues());
            }
        }
    }

    public function ajaxProcessCheckVersion()
    {
        if (version_compare(_PS_VERSION_, '1.6', '>=') &&
            version_compare(_PS_VERSION_, '8.99', '<=')) {
            exit(json_encode([
                'check' => 'CheckVersion',
                'result' => true
            ]));
        }

        exit(json_encode([
            'check' => 'CheckVersion',
            'result' => false
        ]));
    }

    public function ajaxProcessCheckHooks()
    {
        $hooks = [
            'actionOrderStatusPostUpdate',
            'actionValidateOrder',
            'actionDispatcher'
        ];

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $hooks[] = 'extraCarrier';
        } else {
            $hooks[] = 'displayCarrierExtraContent';
        }

        foreach ($hooks as $hook) {
            $hook_id = Hook::getIdByName($hook);
            if ($this->module->getPosition($hook_id) === 0) {
                exit(json_encode([
                    'check' => 'CheckHooks',
                    'result' => false
                ]));
            }
        }

        exit(json_encode([
            'check' => 'CheckHooks',
            'result' => true
        ]));
    }

    public function ajaxProcessCheckDb()
    {
        $tables = [
            'mbe_shipping_standard_packages',
            'mbe_shipping_standard_package_product',
            'mbeshippingrate',
            'mbe_shipping_order',
            'mbe_shipping_pickup_address',
            'mbe_shipping_rates_cache',
            'mbe_shipping_pickup_batch',
            'mbe_shipping_dp'
        ];

        foreach ($tables as $table) {
            if (empty(Db::getInstance()->executeS('SHOW TABLES LIKE \'' . _DB_PREFIX_ . bqSQL($table) . '\''))) {
                exit(json_encode([
                    'check' => 'CheckDb',
                    'result' => false
                ]));
            }
        }

        exit(json_encode([
            'check' => 'CheckDb',
            'result' => true
        ]));
    }

    public function ajaxProcessCheckTabs()
    {
        $tabs = [
            'AdminMbeShipping',
            'AdminMbePickupOrders',
            'AdminMbePickupAddressHelper',
            'AdminMbePackageProductHelper',
            'AdminMbePackageHelper'
        ];

        foreach ($tabs as $tab) {
            $id_tab = (int)Tab::getIdFromClassName($tab);
            if (!$id_tab) {
                exit(json_encode([
                    'check' => 'CheckTabs',
                    'result' => false
                ]));
            }
        }

        exit(json_encode([
            'check' => 'CheckTabs',
            'result' => true
        ]));
    }

    public function ajaxProcessCheckOverrides()
    {
        $overrides = [
            'classes/Carrier.php' => 'getCarriers'
        ];

        foreach ($overrides as $file => $method) {
            $overridden_file = _PS_OVERRIDE_DIR_ . '/' . $file;
            if (!file_exists($overridden_file)) {
                exit(json_encode([
                    'check' => 'CheckOverrides',
                    'result' => false
                ]));
            }

            if (!$objData = Tools::file_get_contents($overridden_file)) {
                exit(json_encode([
                    'check' => 'CheckOverrides',
                    'result' => false
                ]));
            }

            if (!strpos($objData,  'function ' . $method)){
                exit(json_encode([
                    'check' => 'CheckOverrides',
                    'result' => false
                ]));
            }
        }

        exit(json_encode([
            'check' => 'CheckOverrides',
            'result' => true
        ]));
    }
}
