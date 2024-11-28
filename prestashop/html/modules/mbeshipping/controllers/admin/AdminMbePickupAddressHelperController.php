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

use PrestaShop\Module\Mbeshipping\Ws;

require_once(dirname(__FILE__) . '/../../classes/custom/models/MbePickupAddressHelper.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminMbePickupAddressHelperController extends ModuleAdminController
{
    protected $helper;
    protected $ws;
    protected $pickup_address_id;

    protected $COUNTRY_CODES = ['AC', 'AD', 'AE', 'AF', 'AG', 'AI', 'AL', 'AM', 'AN', 'AO', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AW', 'AX', 'AZ', 'BA', 'BB', 'BD', 'BE', 'BF', 'BG', 'BH', 'BI', 'BJ', 'BL', 'BM', 'BN', 'BO', 'BQ', 'BR', 'BS', 'BT', 'BU', 'BV', 'BW', 'BY', 'BZ', 'CA', 'CC', 'CD', 'CF', 'CG', 'CH', 'CI', 'CK', 'CL', 'CM', 'CN', 'CO', 'CP', 'CR', 'CS', 'CU', 'CV', 'CW', 'CX', 'CY', 'CZ', 'DE', 'DG', 'DJ', 'DK', 'DM', 'DO', 'DZ', 'EA', 'EC', 'EE', 'EG', 'EH', 'ER', 'ES', 'ET', 'EU', 'EZ', 'FI', 'FJ', 'FK', 'FM', 'FO', 'FR', 'FX', 'GA', 'GB', 'GD', 'GE', 'GF', 'GG', 'GH', 'GI', 'GL', 'GM', 'GN', 'GP', 'GQ', 'GR', 'GS', 'GT', 'GU', 'GW', 'GY', 'HK', 'HM', 'HN', 'HR', 'HT', 'HU', 'IC', 'ID', 'IE', 'IL', 'IM', 'IN', 'IO', 'IQ', 'IR', 'IS', 'IT', 'JE', 'JM', 'JO', 'JP', 'KE', 'KG', 'KH', 'KI', 'KM', 'KN', 'KP', 'KR', 'KW', 'KY', 'KZ', 'LA', 'LB', 'LC', 'LI', 'LK', 'LR', 'LS', 'LT', 'LU', 'LV', 'LY', 'MA', 'MC', 'MD', 'ME', 'MF', 'MG', 'MH', 'MK', 'ML', 'MM', 'MN', 'MO', 'MP', 'MQ', 'MR', 'MS', 'MT', 'MU', 'MV', 'MW', 'MX', 'MY', 'MZ', 'NA', 'NC', 'NE', 'NF', 'NG', 'NI', 'NL', 'NO', 'NP', 'NR', 'NT', 'NU', 'NZ', 'OM', 'PA', 'PE', 'PF', 'PG', 'PH', 'PK', 'PL', 'PM', 'PN', 'PR', 'PS', 'PT', 'PW', 'PY', 'QA', 'RE', 'RO', 'RS', 'RU', 'RW', 'SA', 'SB', 'SC', 'SD', 'SE', 'SF', 'SG', 'SH', 'SI', 'SJ', 'SK', 'SL', 'SM', 'SN', 'SO', 'SR', 'SS', 'ST', 'SU', 'SV', 'SX', 'SY', 'SZ', 'TA', 'TC', 'TD', 'TF', 'TG', 'TH', 'TJ', 'TK', 'TL', 'TM', 'TN', 'TO', 'TP', 'TR', 'TT', 'TV', 'TW', 'TZ', 'UA', 'UG', 'UK', 'UM', 'US', 'UY', 'UZ', 'VA', 'VC', 'VE', 'VG', 'VI', 'VN', 'VU', 'WF', 'WS', 'XI', 'XU', 'XK', 'YE', 'YT', 'YU', 'ZA', 'ZM', 'ZR', 'ZW'];

    public function __construct()
    {
        $this->module = 'mbeshipping';
        $this->bootstrap = true;
        $this->table = 'mbe_shipping_pickup_address';
        $this->identifier = 'id_mbe_shipping_pickup_address';
        $this->className = 'MbePickupAddressHelper';
        $this->lang = false;
        $this->deleted = false;

        $this->context = Context::getContext();

        $this->_orderBy = 'id_mbe_shipping_pickup_address';
        $this->_orderWay = 'desc';
        $this->_where = 'AND a.`deleted` = 0';

        parent::__construct();

        if (_PS_VERSION_ >= 1.7) {
            $this->translator = \Context::getContext()->getTranslator();
        }

        $this->toolbar_title = $this->module->l('MBE - Pickup Address', 'AdminMbePickupAddressHelperController');

        $this->ws = new Ws();
        $this->syncPickupAddresses();
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->fields_list = [
            /*'id_mbe_shipping_pickup_address' => [
                'title' => $this->module->l('ID', 'AdminMbePickupAddressHelperController'),
                'align' => 'center',
                'type' => 'integer'
            ],*/
            'pickup_address_id' => [
                'title' => $this->module->l('Address ID', 'AdminMbePickupAddressHelperController'),
                'align' => 'center',
                'type' => 'integer'
            ],
            'trade_name' => [
                'title' => $this->module->l('Trade Name', 'AdminMbePickupAddressHelperController'),
                'align' => 'center',
                'type' => 'string'
            ],
            'address1' => [
                'title' => $this->module->l('Address', 'AdminMbePickupAddressHelperController') . ' 1',
                'align' => 'center',
                'type' => 'string'
            ],
            /*'address2' => [
                'title' => $this->module->l('Address', 'AdminMbePickupAddressHelperController') . ' 2',
                'align' => 'center',
                'type' => 'string'
            ],*/
            /*'address3' => [
                'title' => $this->module->l('Address', 'AdminMbePickupAddressHelperController') . ' 3',
                'align' => 'center',
                'type' => 'string'
            ],*/
            'zip_code' => [
                'title' => $this->module->l('Postcode', 'AdminMbePickupAddressHelperController'),
                'align' => 'center',
                'type' => 'string'
            ],
            'city' => [
                'title' => $this->module->l('City', 'AdminMbePickupAddressHelperController'),
                'align' => 'center',
                'type' => 'string'
            ],
            'province' => [
                'title' => $this->module->l('Province', 'AdminMbePickupAddressHelperController'),
                'align' => 'center',
                'type' => 'string'
            ],
            'country' => [
                'title' => $this->module->l('Country', 'AdminMbePickupAddressHelperController'),
                'align' => 'center',
                'type' => 'string'
            ],
            'reference' => [
                'title' => $this->module->l('Reference', 'AdminMbePickupAddressHelperController'),
                'align' => 'center',
                'type' => 'string'
            ],
            'phone1' => [
                'title' => $this->module->l('Telephone', 'AdminMbePickupAddressHelperController') . ' 1',
                'align' => 'center',
                'type' => 'string'
            ],
            /*'phone2' => [
                'title' => $this->module->l('Telephone', 'AdminMbePickupAddressHelperController') . ' 2',
                'align' => 'center',
                'type' => 'string'
            ],*/
            'email1' => [
                'title' => $this->module->l('E-mail', 'AdminMbePickupAddressHelperController') . ' 1',
                'align' => 'center',
                'type' => 'string'
            ],
            /*'email2' => [
                'title' => $this->module->l('e-Mail', 'AdminMbePickupAddressHelperController') . ' 2',
                'align' => 'center',
                'type' => 'string'
            ],*/
            /*'fax' => [
                'title' => $this->module->l('Fax', 'AdminMbePickupAddressHelperController'),
                'align' => 'center',
                'type' => 'string'
            ],*/
            /*'res' => [
                'title' => $this->module->l('RES', 'AdminMbePickupAddressHelperController'),
                'align' => 'center',
                'active' => 'toggle_res',
                'type' => 'bool',
                'orderby' => false,
            ],*/
            /*'mmr' => [
                'title' => $this->module->l('MMR', 'AdminMbePickupAddressHelperController'),
                'align' => 'center',
                'active' => 'toggle_mmr',
                'type' => 'bool',
                'orderby' => false,
            ],*/
            /*'ltz' => [
                'title' => $this->module->l('LTZ', 'AdminMbePickupAddressHelperController'),
                'align' => 'center',
                'active' => 'toggle_ltz',
                'type' => 'bool',
                'orderby' => false,
            ],*/
            'is_default' => [
                'title' => $this->module->l('Default', 'AdminMbePickupAddressHelperController'),
                'align' => 'center',
                'active' => 'toggle_is_default',
                'type' => 'bool',
                'orderby' => false,
            ],
        ];

        return parent::renderList();
    }

    public function renderForm()
    {
        $this->fields_form = [
            'legend' => [
                'title' => $this->module->l('Add Pickup address', 'AdminMbePickupAddressHelperController'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->module->l('Trade Name', 'AdminMbePickupAddressHelperController'),
                    'name' => 'trade_name',
                    'class' => 'fixed-width-xxl',
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Address', 'AdminMbePickupAddressHelperController') . ' 1',
                    'name' => 'address1',
                    'class' => 'fixed-width-xxl',
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Address', 'AdminMbePickupAddressHelperController') . ' 2',
                    'name' => 'address2',
                    'class' => 'fixed-width-xxl',
                    'required' => false
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Address', 'AdminMbePickupAddressHelperController') . ' 3',
                    'name' => 'address3',
                    'class' => 'fixed-width-xxl',
                    'required' => false
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Postcode', 'AdminMbePickupAddressHelperController'),
                    'name' => 'zip_code',
                    'class' => 'fixed-width-xxl',
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('City', 'AdminMbePickupAddressHelperController'),
                    'name' => 'city',
                    'class' => 'fixed-width-xxl',
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Province', 'AdminMbePickupAddressHelperController'),
                    'name' => 'province',
                    'class' => 'fixed-width-xxl',
                    'required' => true
                ],
                [
                    'type' => 'select',
                    'label' => $this->module->l('Country', 'AdminMbePickupAddressHelperController'),
                    'name' => 'country',
                    'class' => 'fixed-width-xxl',
                    'options' => [
                        'query' => array_map(function ($country) {
                            return [
                                'id_option' => $country,
                                'name' => $country
                            ];
                        }, $this->COUNTRY_CODES),
                        'id' => 'id_option',
                        'name' => 'name',
                    ],
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Reference', 'AdminMbePickupAddressHelperController'),
                    'name' => 'reference',
                    'class' => 'fixed-width-xxl',
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Telephone', 'AdminMbePickupAddressHelperController') . ' 1',
                    'name' => 'phone1',
                    'class' => 'fixed-width-xxl',
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Telephone', 'AdminMbePickupAddressHelperController') . ' 2',
                    'name' => 'phone2',
                    'class' => 'fixed-width-xxl',
                    'required' => false
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('E-mail', 'AdminMbePickupAddressHelperController') . ' 1',
                    'name' => 'email1',
                    'class' => 'fixed-width-xxl',
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('E-mail', 'AdminMbePickupAddressHelperController') . ' 2',
                    'name' => 'email2',
                    'class' => 'fixed-width-xxl',
                    'required' => false
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Fax', 'AdminMbePickupAddressHelperController'),
                    'name' => 'fax',
                    'class' => 'fixed-width-xxl',
                    'required' => false
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('RES', 'AdminMbePickupAddressHelperController'),
                    'name' => 'res',
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                        ],
                    ],
                    'class' => 'fixed-width-xxl',
                    'required' => false
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('MMR', 'AdminMbePickupAddressHelperController'),
                    'name' => 'mmr',
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                        ],
                    ],
                    'class' => 'fixed-width-xxl',
                    'required' => false
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('LTZ', 'AdminMbePickupAddressHelperController'),
                    'name' => 'ltz',
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                        ],
                    ],
                    'class' => 'fixed-width-xxl',
                    'required' => false
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Default', 'AdminMbePickupAddressHelperController'),
                    'name' => 'is_default',
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                        ],
                    ],
                    'class' => 'fixed-width-xxl',
                    'required' => false
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save', 'AdminMbePickupAddressHelperController')
            ]
        ];

        if ($this->display == 'edit') {
            array_unshift($this->fields_form['input'], [
                'type' => 'hidden',
                'name' => 'pickup_address_id'
            ]);
        }

        return parent::renderForm();
    }

    public function validateRules($class_name = false)
    {
        if (Tools::getValue('country') == 'IT' && strlen(Tools::getValue('province')) != 2) {
            $this->errors[] = $this->module->l('Field "Province" must contain two letters', 'AdminMbePickupAddressHelperController');
        }

        if (Tools::getValue('mmr') == 1 && Tools::getValue('res') == 1) {
            $this->errors[] = $this->module->l('The fields "MMR" and "RES" cannot be both active', 'AdminMbePickupAddressHelperController');
        }

        parent::validateRules($class_name);
    }

    /*protected function copyFromPost(&$object, $table)
    {
        parent::copyFromPost($object, $table);

        // set pickup_address_id like remote one, before adding/updating
        $object->pickup_address_id = $this->pickup_address_id;
    }*/

    public function postProcess() {
        $this->validateRules();

        return parent::postProcess();
    }

    public function processAdd()
    {
        if (count($this->errors)) {
            $this->display = 'edit';
            return;
        }

        $values = Tools::getAllValues();
        $pickup_container = $this->buildPickupContainer($values);
        if (!$this->pickup_address_id = $this->ws->savePickupAddress($pickup_container)) {
            $this->errors[] = $this->trans('An error occurred while creating an object.', [], 'Admin.Notifications.Error');
        }

        if ($back = Tools::getValue('back')) {
            $this->syncPickupAddresses();
            $this->redirect_after = rawurldecode($back) . '&' . http_build_query([
                'conf' => 3,
                'selected_pickup_address' => $this->pickup_address_id
            ]);
            return;
        }

        // redirect to controller page to re-sync addresses
        $this->redirect_after = self::$currentIndex . '&' . $this->identifier . '=' . $this->object->id . '&conf=3&token=' . $this->token;
    }

    public function processUpdate()
    {
        if (count($this->errors)) {
            $this->display = 'edit';
            return;
        }

        $values = Tools::getAllValues();
        $pickup_container = $this->buildPickupContainer($values);
        if (!$this->pickup_address_id = $this->ws->savePickupAddress($pickup_container)) {
            $this->errors[] = $this->trans('An error occurred while updating an object.', [], 'Admin.Notifications.Error');
        }

        if ($back = Tools::getValue('back')) {
            $this->syncPickupAddresses();
            $this->redirect_after = rawurldecode($back) . '&conf=4';
            return;
        }

        // redirect to controller page to re-sync addresses
        $this->redirect_after = self::$currentIndex . '&' . $this->identifier . '=' . $this->object->id . '&conf=4&token=' . $this->token;
    }

    public function processDelete()
    {
        $this->loadObject();
        if (!$this->ws->deletePickupAddress($this->object->pickup_address_id)) {
            $this->errors[] = $this->trans('An error occurred during deletion.', [], 'Admin.Notifications.Error');
        }

        // redirect to controller page to re-sync addresses
        $this->redirect_after = self::$currentIndex . '&conf=1&token=' . $this->token;
    }

    protected function syncPickupAddresses() {
        // get all local pickup addresses
        $local_pickup_addresses = MbePickupAddressHelper::getPickupAddresses();

        // get all remote pickup addresses
        $remote_pickup_addresses = $this->ws->getPickupAddresses();

        // if no remote pickup addresses, soft delete all local pickup addresses
        if (empty($remote_pickup_addresses)) {
            MbePickupAddressHelper::softDeleteAll();
            return;
        }

        // soft delete all local pickup addresses that are not in remote pickup addresses
        foreach ($local_pickup_addresses as $local_pickup_address) {
            if (in_array($local_pickup_address['pickup_address_id'], array_column($remote_pickup_addresses, 'PickupAddressId'))) {
                continue;
            }

            MbePickupAddressHelper::softDeleteById($local_pickup_address['pickup_address_id']);
        }

        // add/update local pickup addresses
        foreach ($remote_pickup_addresses as $remote_pickup_address) {
            $address_data = [
                'pickup_address_id' => $remote_pickup_address['PickupAddressId'],
                'trade_name' => $remote_pickup_address['TradeName'],
                'address1' => $remote_pickup_address['Address1'],
                'address2' => $remote_pickup_address['Address2'] ?? '',
                'address3' => $remote_pickup_address['Address3'] ?? '',
                'zip_code' => $remote_pickup_address['ZipCode'],
                'city' => $remote_pickup_address['City'],
                'province' => $remote_pickup_address['Province'],
                'country' => $remote_pickup_address['Country'],
                'reference' => $remote_pickup_address['Reference'],
                'phone1' => $remote_pickup_address['Phone1'],
                'phone2' => $remote_pickup_address['Phone2'] ?? '',
                'email1' => $remote_pickup_address['Email1'],
                'email2' => $remote_pickup_address['Email2'] ?? '',
                'fax' => $remote_pickup_address['Fax'] ?? '',
                'res' => $remote_pickup_address['RES'],
                'mmr' => $remote_pickup_address['MMR'],
                'ltz' => $remote_pickup_address['LTZ'],
                'is_default' => $remote_pickup_address['IsDefault'],
                'deleted' => 0
            ];

            // check for existing local pickup address
            if (in_array($remote_pickup_address['PickupAddressId'], array_column($local_pickup_addresses, 'pickup_address_id'))) {
                // update pickup address
                MbePickupAddressHelper::updatePickupAddress($address_data);
                continue;
            }

            // add pickup address
            MbePickupAddressHelper::createPickupAddress($address_data);
        }
    }

    protected function buildPickupContainer($values) {
        $pickupContainer = new \stdClass();
        if (isset($values['pickup_address_id']) && (int)$values['pickup_address_id'] > 0) {
            $pickupContainer->PickupAddressId = $values['pickup_address_id'];
        }
        $pickupContainer->TradeName = $values['trade_name'];
        $pickupContainer->Address1 = $values['address1'];
        $pickupContainer->Address2 = $values['address2'];
        $pickupContainer->Address3 = $values['address3'];
        $pickupContainer->ZipCode = $values['zip_code'];
        $pickupContainer->City = $values['city'];
        $pickupContainer->Province = $values['province'];
        $pickupContainer->Country = $values['country'];
        $pickupContainer->Reference = $values['reference'];
        $pickupContainer->Phone1 = $values['phone1'];
        $pickupContainer->Phone2 = $values['phone2'];
        $pickupContainer->Email1 = $values['email1'];
        $pickupContainer->Email2 = $values['email2'];
        $pickupContainer->Fax = $values['fax'];
        $pickupContainer->RES = $values['res'];
        $pickupContainer->MMR = $values['mmr'];
        $pickupContainer->LTZ = $values['ltz'];
        $pickupContainer->IsDefault = $values['is_default'];

        return $pickupContainer;
    }
}
