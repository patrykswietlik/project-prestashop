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

require_once(dirname(__FILE__) . '/../../classes/custom/models/PsPackageHelper.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminMbePackageHelperController extends ModuleAdminController
{
    public function __construct()
    {
        $this->module = 'mbeshipping';
        $this->bootstrap = true;
        $this->table = 'mbe_shipping_standard_packages';
        $this->identifier = 'id_mbeshippingpackage';
        $this->className = 'PsPackageHelper';
        $this->lang = false;
        $this->deleted = false;

        $this->context = Context::getContext();

        $this->_orderBy = 'id_mbeshippingpackage';
        $this->_orderWay = 'desc';

        parent::__construct();

        if (_PS_VERSION_ >= 1.7) {
            $this->translator = \Context::getContext()->getTranslator();
        }

        $this->toolbar_title = $this->module->l('Manage packages', 'AdminMbePackageHelperController');
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->fields_list = [
            'id_mbeshippingpackage' => [
                'title' => $this->module->l('ID', 'AdminMbePackageHelperController'),
                'align' => 'center',
            ],
            'max_weight' => [
                'title' => $this->module->l('Max weight', 'AdminMbePackageHelperController'),
                'align' => 'center',
                'type' => 'float'
            ],
            'length' => [
                'title' => $this->module->l('Length', 'AdminMbePackageHelperController'),
                'align' => 'center',
                'type' => 'float'
            ],
            'width' => [
                'title' => $this->module->l('Width', 'AdminMbePackageHelperController'),
                'align' => 'center',
                'type' => 'float'
            ],
            'height' => [
                'title' => $this->module->l('Height', 'AdminMbePackageHelperController'),
                'align' => 'center',
                'type' => 'float'
            ],
            'package_label' => [
                'title' => $this->module->l('Package label', 'AdminMbePackageHelperController'),
                'align' => 'center',
                'type' => 'string'
            ],
            'package_code' => [
                'title' => $this->module->l('Package code', 'AdminMbePackageHelperController'),
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
                'title' => $this->module->l('Add package', 'AdminMbePackageHelperController'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->module->l('Max weight', 'AdminMbePackageHelperController'),
                    'name' => 'max_weight',
                    'class' => 'fixed-width-xxl',
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Length', 'AdminMbePackageHelperController'),
                    'name' => 'length',
                    'class' => 'fixed-width-xxl',
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Width', 'AdminMbePackageHelperController'),
                    'name' => 'width',
                    'class' => 'fixed-width-xxl',
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Height', 'AdminMbePackageHelperController'),
                    'name' => 'height',
                    'class' => 'fixed-width-xxl',
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Package label', 'AdminMbePackageHelperController'),
                    'name' => 'package_label',
                    'class' => 'fixed-width-xxl',
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Package code', 'AdminMbePackageHelperController'),
                    'name' => 'package_code',
                    'class' => 'fixed-width-xxl',
                    'required' => true
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save', 'AdminMbePackageHelperController')
            ]
        ];

        return parent::renderForm();
    }
}
