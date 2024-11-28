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

require_once(dirname(__FILE__) . '/../../classes/custom/models/PsPackageProductHelper.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminMbePackageProductHelperController extends ModuleAdminController
{
    public function __construct()
    {
        $this->module = 'mbeshipping';
        $this->bootstrap = true;
        $this->table = 'mbe_shipping_standard_package_product';
        $this->identifier = 'id_mbeshippingpackageproduct';
        $this->className = 'PsPackageProductHelper';
        $this->lang = false;
        $this->deleted = false;

        $this->context = Context::getContext();

        $this->_orderBy = 'id_mbeshippingpackageproduct';
        $this->_orderWay = 'desc';

        parent::__construct();

        if (_PS_VERSION_ >= 1.7) {
            $this->translator = \Context::getContext()->getTranslator();
        }

        $this->toolbar_title = $this->module->l('Manage product packages', 'AdminMbePackageProductHelperController');
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->fields_list = [
            'id_mbeshippingpackageproduct' => [
                'title' => $this->module->l('ID', 'AdminMbePackageProductHelperController'),
                'align' => 'center',
            ],
            'custom_package' => [
                'title' => $this->module->l('Custom package', 'AdminMbePackageProductHelperController'),
                'align' => 'center',
                'type' => 'integer'
            ],
            'single_parcel' => [
                'title' => $this->module->l('Single parcel', 'AdminMbePackageProductHelperController'),
                'align' => 'center',
                'type' => 'integer'
            ],
            'product_sku' => [
                'title' => $this->module->l('Product SKU', 'AdminMbePackageProductHelperController'),
                'align' => 'center',
                'type' => 'string'
            ],
            'package_code' => [
                'title' => $this->module->l('Package code', 'AdminMbePackageProductHelperController'),
                'align' => 'center',
                'type' => 'string'
            ]
        ];

        return parent::renderList();
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->module->l('Add product package', 'AdminMbePackageProductHelperController'),
            ],
            'input' => [
                [
                    'type' => 'radio',
                    'label' => $this->module->l('Custom package', 'AdminMbePackageProductHelperController'),
                    'name' => 'custom_package',
                    'required' => true,
                    'isBool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes', 'AdminMbePackageProductHelperController')
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->module->l('No', 'AdminMbePackageProductHelperController')
                        ],
                    ]
                ],
                [
                    'type' => 'radio',
                    'label' => $this->module->l('Single parcel', 'AdminMbePackageProductHelperController'),
                    'name' => 'single_parcel',
                    'required' => true,
                    'isBool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->module->l('Yes', 'AdminMbePackageProductHelperController')
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->module->l('No', 'AdminMbePackageProductHelperController')
                        ],
                    ]
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Product SKU', 'AdminMbePackageProductHelperController'),
                    'name' => 'product_sku',
                    'class' => 'fixed-width-xxl',
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Package code', 'AdminMbePackageProductHelperController'),
                    'name' => 'package_code',
                    'class' => 'fixed-width-xxl',
                    'required' => true
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save', 'AdminMbePackageProductHelperController')
            ]
        ];

        return parent::renderForm();
    }
}
