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

use PrestaShop\Module\Mbeshipping\Helper\DataHelper;
use PrestaShop\Module\Mbeshipping\Helper\MOrderHelper;
use PrestaShop\Module\Mbeshipping\Helper\OrderHelper;
use PrestaShop\Module\Mbeshipping\Ws;

require_once(dirname(__FILE__) . '/../../classes/custom/models/MbePickupAddressHelper.php');
require_once(dirname(__FILE__) . '/../../classes/custom/models/MbePickupBatchHelper.php');

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminMbePickupOrdersController extends ModuleAdminController
{
    public $module;
    public $_order_ids = [];
    public $_batch_ids = [];
    public $_order_id;
    public $_selected_pickup_address;
    public $_pickup_addresses = [];
    public $_bulk_process = false;
    public $_pickup_batch_id;
    public $_pickup_batch_data;
    public $_action;
    public $ws;

    public function __construct()
    {
        $this->module = Module::getInstanceByName('mbeshipping');
        $this->bootstrap = true;
        $this->display = 'view';
        parent::__construct();
        $this->meta_title = $this->module->l('MBE - Pickup orders', 'AdminMbePickupOrdersController');
        $this->toolbar_title = $this->module->l('MBE - Pickup orders', 'AdminMbePickupOrdersController');
    }

    public function loadPickupAddresses()
    {
        $this->_pickup_addresses = array_map(function ($address) {
            $data = [
                $address['pickup_address_id'],
                $address['trade_name'],
                $address['address1'],
                $address['zip_code'],
                $address['city'],
                $address['province'],
                $address['country'],
            ];

            if ($address['is_default']) {
                $data[] = 'default';

                if (!$this->_selected_pickup_address) {
                    $this->_selected_pickup_address = $address['pickup_address_id'];
                }
            }

            return [
                'id' => $address['pickup_address_id'],
                'name' => implode('|', $data)
            ];
        }, MbePickupAddressHelper::getPickupAddresses(true));
    }

    public function init()
    {
        parent::init();

        // init ws
        $this->ws = new Ws();
        // load pickup addresses list
        $this->loadPickupAddresses();
        // preselect pickup address
        if ($selected_pickup_address = Tools::getValue('selected_pickup_address')) {
            $this->_selected_pickup_address = $selected_pickup_address;
        }

        // actions
        $this->_action = Tools::getValue('action');
        if (!$this->_action) {
            // load order ids
            if ($id_order = Tools::getValue('id_order')) {
                is_array($id_order) ? $this->_order_ids = $id_order : $this->_order_ids[] = $id_order;
            }

            // bulk process
            $this->_bulk_process = count($this->_order_ids) > 1;

        } elseif (in_array($this->_action, ['edit', 'send', 'delete'])) {
            $this->_pickup_batch_id = Tools::getValue('pickup_batch_id');
            if (!$this->_pickup_batch_id) {
                $this->errors[] = $this->module->l('An error occurred while retrieving pickup batch id',
                    'AdminMbePickupOrdersController');
            }

            $this->_pickup_batch_data = MbePickupBatchHelper::getByPickupBatchId($this->_pickup_batch_id);
            if (!$this->_pickup_batch_data) {
                $this->errors[] = $this->module->l('An error occurred while retrieving pickup batch data',
                    'AdminMbePickupOrdersController');
            }

            $this->_order_ids = MbePickupBatchHelper::getOrderIdsByPickupBatchId($this->_pickup_batch_id);
            if (empty($this->_order_ids)) {
                $this->errors[] = $this->module->l('An error occurred while retrieving order ids',
                    'AdminMbePickupOrdersController');;
            }

            // bulk process
            $this->_bulk_process = count($this->_order_ids) > 1;

        } elseif ($this->_action === 'bulk_send') {
            $this->_batch_ids = Tools::getValue('batch_ids');
            if (empty($this->_batch_ids)) {
                $this->errors[] = $this->module->l('An error occurred while retrieving pickup batch ids',
                    'AdminMbePickupOrdersController');;
            }
        } elseif ($this->_action === 'detach') {
            $this->_order_id = Tools::getValue('id_order');
            if (!$this->_order_id) {
                $this->errors[] = $this->module->l('An error occurred while retrieving order id',
                    'AdminMbePickupOrdersController');
            }

            if (!MOrderHelper::hasPickupBatchByOrderId($this->_order_id)) {
                $this->errors[] = $this->module->l('An error occurred while retrieving Pickup for this order id',
                    'AdminMbePickupOrdersController');
            }
        }
    }

    public function postProcess()
    {
        if (in_array($this->_action, ['edit', 'send', 'bulk_send', 'delete', 'detach']) && !empty($this->errors)) {
            $this->setRedirectAfter($this->context->link->getAdminLink('AdminMbeShipping', true, [], [
                'errors' => $this->errors,
            ]));

        } elseif ($this->_action === 'detach') {
            $this->processDetachOrderFromPickupBatch();
            $this->setRedirectAfter($this->context->link->getAdminLink('AdminMbeShipping', true, [], [
                'errors' => $this->errors,
                'confirmations' => $this->confirmations,
            ]));
        } elseif ($this->_action === 'delete') {
            $this->processDeletePickupBatch();
            $this->setRedirectAfter($this->context->link->getAdminLink('AdminMbeShipping', true, [], [
                'errors' => $this->errors,
                'confirmations' => $this->confirmations,
            ]));
        } elseif ($this->_action === 'send') {
            $this->processSendPickupRequests();
            $this->setRedirectAfter($this->context->link->getAdminLink('AdminMbeShipping', true, [], [
                'errors' => $this->errors,
                'confirmations' => $this->confirmations,
            ]));

        } elseif ($this->_action === 'bulk_send') {
            foreach ($this->_batch_ids as $id) {
                $this->_pickup_batch_id = MbePickupBatchHelper::getPickupBatchIdById($id);
                $this->_pickup_batch_data = MbePickupBatchHelper::getByPickupBatchId($this->_pickup_batch_id);
                $this->_order_ids = MbePickupBatchHelper::getOrderIdsByPickupBatchId($this->_pickup_batch_id);
                $this->_bulk_process = count($this->_order_ids) > 1;
                //$this->processSavePickupDefaultData();
                $this->processSendPickupRequests();
            }
            $this->setRedirectAfter($this->context->link->getAdminLink('AdminMbeShipping', true, [], [
                'errors' => $this->errors,
                'confirmations' => $this->confirmations,
            ]));

        } elseif (Tools::isSubmit('submitPickupOrders')) {
            if (!$this->_pickup_batch_id) {
                $this->_pickup_batch_id = $this->generatePickupBatchID();
            }

            $this->_fillPickupBatchData();

            if (Tools::isSubmit('save')) {
                $this->processSavePickupBatch();
            }

            if (Tools::isSubmit('save_and_send')) {
                $this->processSavePickupBatch(false);
                $this->processSendPickupRequests();
            }

            $this->setRedirectAfter($this->context->link->getAdminLink('AdminMbeShipping', true, [], [
                'errors' => $this->errors,
                'confirmations' => $this->confirmations,
            ]));
        }
    }

    public function generatePickupBatchID()
    {
        $username = $this->ws->getCustomer()->Login;
        $timestamp = time();

        return "{$username}_{$timestamp}";
    }

    public function _fillPickupBatchData()
    {
        $values = Tools::getAllValues();
        $this->_pickup_batch_data = [];
        $this->_pickup_batch_data['pickup_batch_id'] = $this->_pickup_batch_id;
        $this->_pickup_batch_data['cutoff_period'] = 'MORNING';
        $this->_pickup_batch_data['cutoff_preferred_from'] = $values['MBESHIPPING_PICKUP_CUTOFF_PREFERRED_FROM'];
        $this->_pickup_batch_data['cutoff_preferred_to'] = $values['MBESHIPPING_PICKUP_CUTOFF_PREFERRED_TO'];
        $this->_pickup_batch_data['cutoff_alternative_from'] = $values['MBESHIPPING_PICKUP_CUTOFF_ALTERNATIVE_FROM'];
        $this->_pickup_batch_data['cutoff_alternative_to'] = $values['MBESHIPPING_PICKUP_CUTOFF_ALTERNATIVE_TO'];
        $this->_pickup_batch_data['notes'] = $values['MBESHIPPING_PICKUP_NOTES'];
        $this->_pickup_batch_data['date'] = $values['MBESHIPPING_PICKUP_DATE'];

        if ($this->_bulk_process) {
            $this->_pickup_batch_data['pickup_address_id'] = $values['MBESHIPPING_PICKUP_ADDRESS_ID'];
        } else {

            $this->_pickup_batch_data['pickup_address_id'] = null;

            $pickup_address = MbePickupAddressHelper::getByPickupAddressId($values['MBESHIPPING_PICKUP_ADDRESS_ID']);
            if (!$pickup_address) {
                $this->errors[] = "[{$this->_pickup_batch_id}] " . $this->module->l('An error occurred while retrieving pickup address',
                        'AdminMbePickupOrdersController');
                return;
            }

            $this->_pickup_batch_data['sender_name'] = $pickup_address['trade_name'];
            $this->_pickup_batch_data['sender_company'] = $pickup_address['trade_name'];
            $this->_pickup_batch_data['sender_address'] = $pickup_address['address1'] . (!empty($pickup_address['address2']) ? ' ' . $pickup_address['address2'] : '') . (!empty($pickup_address['address3']) ? ' ' . $pickup_address['address3'] : '');
            $this->_pickup_batch_data['sender_phone'] = $pickup_address['phone1'];
            $this->_pickup_batch_data['sender_zip'] = $pickup_address['zip_code'];
            $this->_pickup_batch_data['sender_city'] = $pickup_address['city'];
            $this->_pickup_batch_data['sender_state'] = $pickup_address['province'];
            $this->_pickup_batch_data['sender_country'] = $pickup_address['country'];
            $this->_pickup_batch_data['sender_email'] = $pickup_address['email1'];
            $this->_pickup_batch_data['is_single_pickup'] = true;
        }
    }

    public function processSavePickupDefaultData()
    {
        if (!empty($this->errors)) {
            return;
        }

        $result = $this->ws->setPickupDefaultData(
            $this->_pickup_batch_data['cutoff_period'],
            $this->_pickup_batch_data['cutoff_preferred_from'],
            $this->_pickup_batch_data['cutoff_preferred_to'],
            $this->_pickup_batch_data['cutoff_alternative_from'],
            $this->_pickup_batch_data['cutoff_alternative_to'],
            $this->_pickup_batch_data['notes']
        );

        if (!$result) {
            $this->errors[] = "[{$this->_pickup_batch_id}] " . $this->module->l('An error occurred while saving pickup default data',
                    'AdminMbePickupOrdersController');
        }
    }

    public function processSavePickupBatch($show_confirmation = true)
    {
        if (!empty($this->errors)) {
            return;
        }

        if ($this->_action === 'edit') {
            $result = MbePickupBatchHelper::edit(...array_values($this->_pickup_batch_data));
            if (!$result) {
                $this->errors[] = "[{$this->_pickup_batch_id}] " . $this->module->l('An error occurred while saving pickup batch data',
                        'AdminMbePickupOrdersController');
            }
        } else {
            if (!$id = MbePickupBatchHelper::insert(...array_values($this->_pickup_batch_data))) {
                $this->errors[] = "[{$this->_pickup_batch_id}] " . $this->module->l('An error occurred while saving pickup batch data',
                        'AdminMbePickupOrdersController');
            }

            $helper = new MOrderHelper();

            foreach ($this->_order_ids as $order_id) {
                $helper->setOrderPickupMode($order_id, true);
                $helper->setOrderPickupBatch($order_id, $id);
            }
        }

        if (empty($this->errors) && $show_confirmation) {
            $this->confirmations[] = "[{$this->_pickup_batch_id}] " . $this->module->l('Pickup batch saved successfully',
                    'AdminMbePickupOrdersController');
        }
    }

    public function processDetachOrderFromPickupBatch($show_confirmation = true)
    {
        if (!empty($this->errors)) {
            return;
        }

        $order_obj = new Order($this->_order_id);
        if(!Validate::isLoadedObject($order_obj)) {
            $this->errors[] = "Order ID ". $order_obj->id .":". $this->module->l('An error occurred while detaching order id',
                    'AdminMbePickupOrdersController');
        }

        if(!MOrderHelper::detachOrderIdFromPickupBatch($order_obj->id)) {
            $this->errors[] = "Order ID ". $order_obj->id .":". $this->module->l('An error occurred while detaching order id',
                    'AdminMbePickupOrdersController');
        }

        if (empty($this->errors)) {
            if ($show_confirmation) {
                $this->confirmations[] = "Order ID ". $order_obj->id .":" . $this->module->l('Order successfully detached from Pickup',
                        'AdminMbePickupOrdersController');
            }
        }

    }

    public function processDeletePickupBatch($show_confirmation = true)
    {
        if (!empty($this->errors)) {
            return;
        }

        $pickub_batch_obj = new MbePickupBatchHelper($this->_pickup_batch_data['id_mbeshipping_pickup_batch']);
        if(!Validate::isLoadedObject($pickub_batch_obj)) {
            $this->errors[] = "[{$this->_pickup_batch_id}] " . $this->module->l('An error occurred while deleting pickup batch',
                    'AdminMbePickupOrdersController');
        }

        if(!$pickub_batch_obj->delete()) {
            $this->errors[] = "[{$this->_pickup_batch_id}] " . $this->module->l('An error occurred while deleting pickup batch',
                    'AdminMbePickupOrdersController');
        }

        if (empty($this->errors)) {

            if ($show_confirmation) {
                $this->confirmations[] = "[{$this->_pickup_batch_id}] " . $this->module->l('Pickup batch successfully deleted',
                        'AdminMbePickupOrdersController');
            }
        }

    }

    public function processSendPickupRequests($show_confirmation = true)
    {
        if (!empty($this->errors)) {
            return;
        }

        $this->processPickupShippingRequests();
        $this->processPickupClosure();

        if (empty($this->errors)) {
            MbePickupBatchHelper::setPickupStatus($this->_pickup_batch_id, 'CONFIRMED');

            if ($show_confirmation) {
                $this->confirmations[] = "[{$this->_pickup_batch_id}] " . $this->module->l('Pickup batch sent successfully',
                        'AdminMbePickupOrdersController');
            }
        }

    }

    public function processPickupClosure()
    {
        if (!empty($this->errors)) {
            return;
        }

        // Closure request is sent only if there are 1+n shipments
        if ($this->_bulk_process) {
            $errors = [];
            $this->ws->closePickupShipping(
                $this->_pickup_batch_id,
                $this->_pickup_batch_data['cutoff_preferred_from'],
                $this->_pickup_batch_data['cutoff_preferred_to'],
                $this->_pickup_batch_data['cutoff_alternative_from'],
                $this->_pickup_batch_data['cutoff_alternative_to'],
                $this->_pickup_batch_data['notes'],
                $this->_pickup_batch_data['date'],
                $errors
            );

            foreach ($errors as $error) {
                $this->errors[] = "[{$this->_pickup_batch_id}] " . $this->module->l('An error occurred while sending pickup closure request',
                        'AdminMbePickupOrdersController') . (!empty($error['desc']) ? ": {$error['desc']}" : '');
            }
        }
    }

    public function processPickupShippingRequests()
    {
        if (!empty($this->errors)) {
            return;
        }

        $order_helper = new OrderHelper();
        $data_helper = new DataHelper();

        $pickup_batch_inizalized = false;

        foreach ($this->_order_ids as $order_id) {
            $order = new Order($order_id);
            if (!Validate::isLoadedObject($order)) {
                $this->errors[] = "[{$this->_pickup_batch_id}] " . $this->module->l("An error occurred while loading order",
                        'AdminMbePickupOrdersController') . ": {$order_id}";
                continue;
            }

            // order was already processed
            if ($data_helper->hasTracking($order->id)) {
                continue;
            }

            $service = \Configuration::get('carrier_' . $order->id_carrier);
            if (!$service) {
                $carrier = new \Carrier($order->id_carrier);
                $service = $data_helper->getCustomMappingService($carrier->id);
            }

            $creation_errors = $order_helper->addShipping($order, $service, true);
            if(empty($creation_errors) && !$pickup_batch_inizalized) {
                $pickup_batch_inizalized = true;
                $pickup_batch_row = MbePickupBatchHelper::getByPickupBatchId($this->_pickup_batch_id);
                if(is_array($pickup_batch_row) && isset($pickup_batch_row['id_mbeshipping_pickup_batch'])) {
                    $pickup_batch_obj = new MbePickupBatchHelper($pickup_batch_row['id_mbeshipping_pickup_batch']);
                    $pickup_batch_obj->status = 'INITIALIZED';
                    $pickup_batch_obj->update();
                }
            }
            array_push($this->errors, ...$creation_errors);
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $this->addJqueryUI('ui.datepicker');
        $this->addJS($this->module->getPathUri() . 'views/js/select2.min.js');
        $this->addJS($this->module->getPathUri() . 'views/js/common.js');
        $this->addCSS($this->module->getPathUri() . 'views/css/select2.min.css');
        $this->addCSS($this->module->getPathUri() . 'views/css/common.css');
    }

    public function renderView()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->module = $this->module;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPickupOrders';
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm([
            $this->getFormFields(),
        ]);
    }

    public function getValues()
    {
        $inputs = [];

        if ($this->_action === 'edit') {
            $inputs['MBESHIPPING_PICKUP_ADDRESS_ID'] = $this->_pickup_batch_data['pickup_address_id'];
            $inputs['MBESHIPPING_PICKUP_DATE'] = $this->_pickup_batch_data['date'];
            $inputs['MBESHIPPING_PICKUP_CUTOFF_PERIOD'] = '';
            $inputs['MBESHIPPING_PICKUP_CUTOFF_PREFERRED_FROM'] = $this->_pickup_batch_data['cutoff_preferred_from'];
            $inputs['MBESHIPPING_PICKUP_CUTOFF_PREFERRED_TO'] = $this->_pickup_batch_data['cutoff_preferred_to'];
            $inputs['MBESHIPPING_PICKUP_CUTOFF_ALTERNATIVE_FROM'] = $this->_pickup_batch_data['cutoff_alternative_from'];
            $inputs['MBESHIPPING_PICKUP_CUTOFF_ALTERNATIVE_TO'] = $this->_pickup_batch_data['cutoff_alternative_to'];
            $inputs['MBESHIPPING_PICKUP_NOTES'] = $this->_pickup_batch_data['notes'];
        } else {
            $pickupDataInputs = [
                'Cutoff' => 'MBESHIPPING_PICKUP_CUTOFF_PERIOD',
                'PreferredFrom' => 'MBESHIPPING_PICKUP_CUTOFF_PREFERRED_FROM',
                'PreferredTo' => 'MBESHIPPING_PICKUP_CUTOFF_PREFERRED_TO',
                'AlternativeFrom' => 'MBESHIPPING_PICKUP_CUTOFF_ALTERNATIVE_FROM',
                'AlternativeTo' => 'MBESHIPPING_PICKUP_CUTOFF_ALTERNATIVE_TO',
                'Notes' => 'MBESHIPPING_PICKUP_NOTES',
            ];

            $ws = new Ws();
            $defaultPickupData = $ws->getPickupDefaultData();

            if (!$defaultPickupData) {
                $this->_errors[] = $this->module->l('Error while retrieving pickup default data',
                    'AdminMbePickupOrdersController');
            } else {
                foreach ($defaultPickupData as $key => $value) {
                    if (!isset($pickupDataInputs[$key])) {
                        continue;
                    }

                    Configuration::updateValue($pickupDataInputs[$key], $value);
                }
            }

            foreach ($this->getFormFields()['form']['input'] as $input) {
                if (!isset($input['name'])) {
                    continue;
                }

                if ($input['name'] === 'MBESHIPPING_PICKUP_ADDRESS_ID') {
                    $inputs['MBESHIPPING_PICKUP_ADDRESS_ID'] = $this->_selected_pickup_address;
                    continue;
                }

                if ($input['name'] === 'MBESHIPPING_PICKUP_DATE') {
                    $inputs['MBESHIPPING_PICKUP_DATE'] = date('Y-m-d', strtotime('+1 day'));
                    continue;
                }

                $inputs[$input['name']] = Configuration::get($input['name']);
            }
        }

        return $inputs;
    }

    public function getFormFields()
    {
        $form_inputs = [];

        $form_inputs[] = [
            'name' => 'mbe_pickup_alert',
            'type' => 'custom_text',
            'class' => 'alert alert-warning',
            'text' => $this->l('Pickup for PUDO shipments will be booked automatically after shipments closure for the next working day.')
        ];

        $form_inputs[] = [
            'label' => $this->module->l('Orders list', 'AdminMbePickupOrdersController'),
            'name' => 'MBESHIPPING_PICKUP_ORDERS',
            'desc' => $this->l('This list contains the order(s) for which you will be associating pickup data, during shipment creation.'),
            'type' => 'table',
            'columns' => [
                $this->module->l('Order ID', 'AdminMbePickupOrdersController'),
                $this->module->l('Reference', 'AdminMbePickupOrdersController'),
            ],
            'rows' => array_map(function ($order_id) {
                return [
                    'id_order' => $order_id,
                    'reference' => Order::getUniqReferenceOf($order_id),
                ];
            }, $this->_order_ids),
        ];

        $form_inputs[] = [
            'label' => $this->module->l('Pickup address', 'AdminMbePickupOrdersController'),
            'name' => 'MBESHIPPING_PICKUP_ADDRESS_ID',
            'desc' => $this->l('Choose the pickup address to be associated with the selected batch of shipments. The drop-down menu shows all the existing addresses that can be selected. If you want to add a new address, click on Add new pickup address.'),
            'required' => true,
            'type' => 'select_with_alt_button',
            'button_link' => $this->context->link->getAdminLink('AdminMbePickupAddressHelper') . '&' . http_build_query([
                    'add' . MbePickupAddressHelper::$definition['table'] => 1,
                    'back' => $this->context->link->getAdminLink('AdminMbePickupOrders') . '&' . http_build_query([
                            'orders' => Tools::getValue('orders'),
                        ]),
                ]),
            'button_title' => $this->module->l('Add new pickup address', 'AdminMbePickupOrdersController'),
            'disabled' => empty($this->_pickup_addresses),
            'search' => true,
            'options' => [
                'query' => !empty($this->_pickup_addresses) ? $this->_pickup_addresses : '-',
                'id' => 'id',
                'name' => 'name',
            ],
        ];

        $form_inputs[] = [
            'type' => 'date',
            'label' => $this->module->l('Pickup Date', 'AdminMbePickupOrdersController'),
            'name' => 'MBESHIPPING_PICKUP_DATE',
            'required' => true,
            'desc' => $this->l('Select the date on which you would like courier pickup to take place. This date will be communicated directly to the courier. N.B. It is highly recommended that you choose a working day'),
        ];

        /*
        $form_inputs[] = [
            'type' => 'select',
            'label' => $this->module->l('Pickup cutoff - Period'),
            'name' => 'MBESHIPPING_PICKUP_CUTOFF_PERIOD',
            'options' => [
                'query' => [
                    [
                        'id_option' => 'MORNING',
                        'name' => $this->l('Morning')
                    ],
                    [
                        'id_option' => 'AFTERNOON',
                        'name' => $this->l('Afternoon')
                    ],
                ],
                'id' => 'id_option',
                'name' => 'name',
            ],
            'required' => true,
            'desc' => $this->l('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed ornare scelerisque orci nec tincidunt. Morbi non aliquam ipsum, vel sollicitudin magna.'),
        ];
        */

        $form_inputs[] = [
            'type' => 'time',
            'label' => $this->module->l('Pickup Time - Preferred from'),
            'name' => 'MBESHIPPING_PICKUP_CUTOFF_PREFERRED_FROM',
            'placeholder' => 'HH:MM',
            'class' => 'fixed-width-xxl',
            'required' => true,
            'desc' => $this->l('Minimum pickup time that will be communicated to the courier (N.B. pickup time is approximate and may not be observed by the final courier)'),
        ];

        $form_inputs[] = [
            'type' => 'time',
            'label' => $this->module->l('Pickup Time - Preferred to'),
            'name' => 'MBESHIPPING_PICKUP_CUTOFF_PREFERRED_TO',
            'placeholder' => 'HH:MM',
            'class' => 'test fixed-width-xxl',
            'required' => true,
            'desc' => $this->l('Maximum pickup time that will be communicated to the courier (N.B. pickup time is approximate and may not be observed by the final courier)'),
        ];

        $form_inputs[] = [
            'type' => 'time',
            'label' => $this->module->l('Pickup Time - Alternative from'),
            'name' => 'MBESHIPPING_PICKUP_CUTOFF_ALTERNATIVE_FROM',
            'placeholder' => 'HH:MM',
            'class' => 'fixed-width-xxl',
            'desc' => $this->l('Alternative minimum pickup time that will be communicated to the courier (N.B. pickup time is approximate and may not be observed by the final courier)'),
        ];

        $form_inputs[] = [
            'type' => 'time',
            'label' => $this->module->l('Pickup Time - Alternative to'),
            'name' => 'MBESHIPPING_PICKUP_CUTOFF_ALTERNATIVE_TO',
            'placeholder' => 'HH:MM',
            'class' => 'fixed-width-xxl',
            'desc' => $this->l('Alternative maximum pickup time that will be communicated to the courier (N.B. pickup time is approximate and may not be observed by the final courier)'),
        ];

        $form_inputs[] = [
            'type' => 'text',
            'label' => $this->module->l('Pickup notes'),
            'name' => 'MBESHIPPING_PICKUP_NOTES',
            'class' => 'fixed-width-xxl',
            'desc' => $this->l('Notes to be included within the pickup request and that will be forwarded to the final carrier'),
        ];

        return [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Pickup orders management'),
                ],
                'input' => $form_inputs,
                'buttons' => [
                    [
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'name' => 'save_and_send',
                        'title' => $this->module->l('Save and send', 'AdminMbePickupOrdersController')
                    ],
                    [
                        'type' => 'submit',
                        'class' => 'pull-right',
                        'name' => 'save',
                        'title' => $this->module->l('Save', 'AdminMbePickupOrdersController')
                    ],
                    [
                        'type' => 'button',
                        'class' => 'pull-left m-0',
                        'name' => 'cancel',
                        'title' => $this->module->l('Cancel', 'AdminMbePickupOrdersController'),
                        'js' => 'javascript:window.location.href = \''.$this->context->link->getAdminLink('AdminMbeShipping').'\''
                    ]
                ]
            ]
        ];
    }
}
