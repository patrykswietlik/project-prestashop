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

use BluePayment\Analyse\Amplitude;
use BluePayment\Api\BlueAPI;
use BluePayment\Api\BlueGateway;
use BluePayment\Api\BlueGatewayChannels;
use BluePayment\Config\Config;
use BluePayment\Config\ConfigBanner;
use BluePayment\Config\ConfigServices;
use BluePayment\Until\AdminHelper;
use BluePayment\Until\Helper;
use Configuration as Cfg;

class AdminBluepaymentPaymentsController extends ModuleAdminController
{
    private $configIframe;
    private $configIframeServices;

    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
        Context::getContext()->smarty->assign('src_img', $this->module->getAssetImages());
        $this->configIframe = new ConfigBanner();
        $this->configIframeServices = new ConfigServices();
        $this->chceckConfigurationService();
    }

    private function chceckConfigurationService(){
        foreach (Helper::checkConfigurationServices() as $isocode => $configurationCurrency){
            if (!$configurationCurrency){
                $this->warnings [] = $this->l('Service configuration for currency: ').$isocode.$this->l(' is not complete');
            }
        }
    }

    public function renderView()
    {
        return $this->renderForm();
    }

    public function initContent()
    {
        if (!$this->loadObject(true)) {
            return;
        }

        parent::initContent();

        if (Tools::getValue('ajax') && Tools::getValue('action') == 'updatePositions') {
            $position = new BlueGatewayChannels();
            $position->updatePosition(
                Tools::getValue('id'),
                Tools::getValue('way'),
                Tools::getValue('id_blue_gateway_channels')
            );

            $data = [
                'events' => [
                    'event_type' => 'payment methods order updated',
                    'event_properties' => [
                        'source' => 'Setup',
                    ],
                ],
            ];

            $amplitude = Amplitude::getInstance();
            $amplitude->sendEvent($data);
        }

        $this->context->controller->addCSS($this->module->getPathUrl() . 'views/css/admin.css');
        $this->context->controller->addJS($this->module->getPathUrl() . 'views/js/admin.min.js');

        $this->content .= $this->renderForm();

        try {
            $gateway = new BlueGateway($this->module, new BlueAPI($this->module));
            $gateway->getChannels();
            $gateway->getTransfers();
        } catch (RuntimeException $e) {
            $this->errors[] = $e->getMessage();
        }

        $this->context->smarty->assign([
            'content' => $this->content,
        ]);
    }

    public function ajaxProcessUpdatePositions()
    {
        $idPosition = (int) Tools::getValue('id');
        $way = (int) Tools::getValue('way');
        $positions = Tools::getValue('blue_gateway_channels');

        if (is_array($positions)) {
            foreach ($positions as $position => $value) {
                $pos = explode('_', $value);
                if ((isset($pos[1], $pos[2])) && ($pos[2] == $idPosition)) {
                    $GatewayChannels = new BlueGatewayChannels($idPosition);
                    if (Validate::isLoadedObject($GatewayChannels)) {
                        if (isset($position) && $GatewayChannels->updatePosition($idPosition, $way, $position)) {
                            Hook::exec('actionBlueGatewayChannelsUpdate');
                            exit(true);
                        } else {
                            exit('{"hasError" : true, errors : "Can not update position"}');
                        }
                    } else {
                        exit('{"hasError" : true, "errors" : "This can not be loaded"}');
                    }
                }
            }
        }
    }

    public function renderForm()
    {
        $fields_form = [];
        $id_default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $statuses = OrderState::getOrderStates($id_default_lang, true);
        $currency = $this->context->currency;

        $alior = BlueGatewayChannels::isChannelActive(Config::GATEWAY_ID_ALIOR, $currency->iso_code);

        $fields_form[0]['form'] = [
            'section' => [
                'title' => $this->l('Authentication'),
            ],
            'legend' => [
                'title' => $this->l('TESTING ENVIRONMENT'),
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->l('Use a test environment'),
                    'name' => $this->module->name_upper . '_TEST_ENV',
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                    'help' => $this->l(
                        'It allows you to verify the operation of the module without the need to actually pay
                         for the order (in the test mode, no fees are charged for the order).'
                    ),
                ],

                [
                    'name' => '',
                    'type' => 'description',
                    'content' => 'module:bluepayment/views/templates/admin/_configure/helpers/form/notification-info.tpl',
                ],
            ],
            'submit' => [
                'save_event' => 'ŚRODOWISKO TESTOWE',
                'title' => $this->l('Save'),
                'class' => 'btn btn-primary pull-right',
            ],
        ];

        $fields_form[1]['form'] = [
            'section' => [
                'title' => $this->l('Authentication'),
            ],
            'legend' => [
                'title' => $this->l('Authentication'),
            ],

            'input' => [
                [
                    'name' => '',
                    'type' => 'description',
                    'content' => './auth-info.tpl',
                ],
            ],

            'submit' => [
                'save_event' => 'UWIERZYTELNIANIE',
                'title' => $this->l('Save'),
                'class' => 'btn btn-primary pull-right',
            ],
        ];

        foreach (AdminHelper::getSortCurrencies() as $currency) {
            $fields_form[1]['form']['form_group']['fields'][] = [
                'form' => [
                    'legend' => [
                        'title' => $currency['name'] . ' (' . $currency['iso_code'] . ')',
                    ],
                    'input' => [
                        [
                            'type' => 'text',
                            'label' => $this->l('Service partner ID'),
                            'name' => $this->module->name_upper . '_SERVICE_PARTNER_ID_' . $currency['iso_code'],
                            'help' => $this->l('It only contains numbers. It is different for each store'),
                        ],
                        [
                            'type' => 'text',
                            'label' => $this->l('Shared key'),
                            'name' => $this->module->name_upper . '_SHARED_KEY_' . $currency['iso_code'],
                            'help' => $this->l('Contains numbers and lowercase letters. It is used to verify
                            communication with the payment gateway. It should not be made available to the public'),
                        ],
                    ],
                    'submit' => [
                        'save_event' => 'authentication',
                        'title' => $this->l('Save'),
                    ],
                ],
            ];
        }

        $fields_form[2]['form'] = [
            'section' => [
                'title' => $this->l('Payment settings'),
            ],
            'legend' => [
                'title' => $this->l('VISIBILITY OF PAYMENT METHODS'),
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->l('Show payment methods in the store'),
                    'name' => $this->module->name_upper . '_SHOW_PAYWAY',
                    'modal' => 'bm-helper-visibility',
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('The name of the payment module in the store'),
                    'name' => $this->module->name_upper . '_PAYMENT_NAME',
                    'size' => 40,
                    'lang' => true,
                    'help' => $this->l('We recommend that you keep the above name. Changing it may have a negative
                    impact on the customers understanding of the payment methods.'),
                    'modal' => 'bm-helper-main-name',
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('The name of the payment module in the store'),
                    'name' => $this->module->name_upper . '_PAYMENT_GROUP_NAME',
                    'size' => 40,
                    'lang' => true,
                    'help' => $this->l('We recommend that you keep the above name. Changing it may have a negative
                    impact on the customers understanding of the payment methods.'),
                    'modal' => 'bm-helper-main-name',
                ],
            ],
            'submit' => [
                'save_event' => 'WIDOCZNOŚĆ METOD PŁATNOŚCI',
                'title' => $this->l('Save'),
                'class' => 'btn btn-primary pull-right',
            ],
        ];

        $fields_form[3]['form'] = [
            'section' => [
                'title' => $this->l('Payment settings'),
            ],
            'legend' => [
                'title' => $this->l('Payment redirection settings'),
            ],
            'input' => [
                [
                    'name' => '',
                    'type' => 'description',
                    'content' => './redirect-info.tpl',
                ],

                [
                    'type' => 'switch-choose',
                    'label' => $this->l('Entering BLIK in a store'),
                    'name' => $this->module->name_upper . '_BLIK_REDIRECT',
                    'size' => 'full',
                    'modal' => 'bm-helper-blik',
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 0,
                            'label' => $this->l('No redirection'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 1,
                            'label' => $this->l('With redirection'),
                        ],
                    ],
                ],
                [
                    'type' => 'switch-choose',
                    'label' => $this->l('Google Pay'),
                    'name' => $this->module->name_upper . '_GPAY_REDIRECT',
                    'size' => 'full',
                    'modal' => 'bm-helper-gpay',
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 0,
                            'label' => $this->l('No redirection'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 1,
                            'label' => $this->l('With redirection'),
                        ],
                    ],
                ],
            ],
            'submit' => [
                'save_event' => 'USTAWIENIA PRZEKIEROWAŃ PŁATNOŚCI',
                'title' => $this->l('Save'),
                'class' => 'btn btn-primary pull-right',
            ],
        ];

        if ($alior) {
            $fields_form[4]['form'] = [
                'section' => [
                    'title' => $this->l('Payment settings'),
                ],
                'legend' => [
                    'title' => $this->l('PAYMENT PROMOTION'),
                ],
                'input' => [
                    [
                        'type' => 'infoheading',
                        'name' => false,
                        'label' => $this->l('Why promote?'),
                    ],
                    [
                        'name' => false,
                        'type' => 'description',
                        'content' => './promote-icons.tpl',
                    ],

                    [
                        'type' => 'infoheading',
                        'name' => false,
                        'label' => $this->l('Installment and deferred payments'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Pay later'),
                        'name' => $this->module->name_upper . '_PROMO_PAY_LATER',
                        'image' => 'switcher1.png',
                        'size' => 'auto',
                        'class' => $alior ? 'bm-active' : 'bm-no-active',
                        'modal' => 'bm-helper-alior',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Show'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Hide'),
                            ],
                        ],
                    ],

                    //                    [
                    //                        'type' => 'switch',
                    //                        'label' => $this->l('Matching instalments'),
                    //                        'name' => $this->module->name_upper . '_PROMO_MATCHED_INSTALMENTS',
                    //                        'image' => 'switcher3.png',
                    //                        'size' => 'auto',
                    //                        'values' => [
                    //                            [
                    //                                'id' => 'active_on',
                    //                                'value' => 1,
                    //                                'label' => $this->l('Show'),
                    //                            ],
                    //                            [
                    //                                'id' => 'active_off',
                    //                                'value' => 0,
                    //                                'label' => $this->l('Hide'),
                    //                            ],
                    //                        ],
                    //                    ],
                    [
                        'type' => 'infoheading',
                        'name' => false,
                        'label' => $this->l('Show payment information on the site'),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('At the top of the page'),
                        'name' => $this->module->name_upper . '_PROMO_HEADER',
                        'size' => 'auto',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Show'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Hide'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Above the footer'),
                        'name' => $this->module->name_upper . '_PROMO_FOOTER',
                        'size' => 'auto',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Show'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Hide'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('In the product list under filters'),
                        'name' => $this->module->name_upper . '_PROMO_LISTING',
                        'size' => 'auto',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Show'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Hide'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('On the product page under the buttons'),
                        'name' => $this->module->name_upper . '_PROMO_PRODUCT',
                        'size' => 'auto',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Show'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Hide'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('In the shopping cart under products'),
                        'name' => $this->module->name_upper . '_PROMO_CART',
                        'size' => 'auto',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Show'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Hide'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('On the list of payment methods'),
                        'name' => $this->module->name_upper . '_PROMO_CHECKOUT',
                        'size' => 'auto',
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Show'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Hide'),
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'save_event' => 'PROMOWANIE PŁATNOŚCI',
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-primary pull-right',
                ],
            ];
        } else {
            $fields_form[4]['form'] = [
                'section' => [
                    'title' => $this->l('Payment settings'),
                ],
                'legend' => [
                    'title' => $this->l('PAYMENT PROMOTION'),
                ],
                'input' => [
                    [
                        'type' => 'infoheading',
                        'name' => false,
                        'label' => $this->l('Why promote?'),
                    ],
                    [
                        'name' => false,
                        'type' => 'description',
                        'content' => './promote-icons.tpl',
                    ],
                ],
                'submit' => [
                    'save_event' => 'PROMOWANIE PŁATNOŚCI',
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-primary pull-right',
                ],
            ];
        }

        $fields_form[5]['form'] = [
            'section' => [
                'title' => $this->l('Payment settings'),
            ],
            'legend' => [
                'title' => $this->l('Statuses'),
            ],
            'input' => [
                [
                    'type' => 'select',
                    'name' => $this->module->name_upper . '_STATUS_WAIT_PAY_ID',
                    'label' => $this->l('Payment started'),
                    'options' => [
                        'query' => $statuses,
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'name' => $this->module->name_upper . '_STATUS_ACCEPT_PAY_ID',
                    'label' => $this->l('Payment approved'),
                    'options' => [
                        'query' => $statuses,
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'name' => $this->module->name_upper . '_STATUS_ERROR_PAY_ID',
                    'label' => $this->l('Payment failed'),
                    'options' => [
                        'query' => $statuses,
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'name' => $this->module->name_upper . '_STATUS_CHANGE_PAY_ID',
                    'label' => $this->l('Order statuses for which the payment status is to be changed'),
                    'multiple' => true,
                    'options' => [
                        'query' => $statuses,
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ],
                ],
            ],
            'submit' => [
                'save_event' => 'STATUSY PŁATNOŚCI',
                'title' => $this->l('Save'),
                'class' => 'btn btn-primary pull-right',
            ],
        ];

        $fields_form[6]['form'] = [
            'section' => [
                'title' => $this->l('Analitics'),
            ],
            'legend' => [
                'title' => $this->l('Google Analitics'),
            ],
            'input' => [
                [
                    'name' => '',
                    'type' => 'description',
                    'content' => './analitics-info.tpl',
                ],
                [
                    'type' => 'switch-choose',
                    'label' => $this->l('Your version of Google Analytics'),
                    'name' => $this->module->name_upper . '_GA_TYPE',
                    'size' => 'full',
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Universal Analytics'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 2,
                            'label' => $this->l('Google Analytics 4'),
                        ],
                    ],
                    'help' => $this->l('Indicate which version of Google Analytics you are using.'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Google Account ID'),
                    'name' => $this->module->name_upper . '_GA_TRACKER_ID',
                    'size' => 40,
                    'help' => $this->l('In Universal Analytics, this is the "Tracking ID" (e.g. UA-000000-2). ')
                        . ' <a target="#" data-toggle="modal" data-target="#bm-helper-analitics-ga-id">'
                        . $this->l('Where can I find the identifier?') . '</a>',
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Google Analytics Measurement ID 4'),
                    'name' => $this->module->name_upper . '_GA4_TRACKER_ID',
                    'size' => 40,
                    'help' => $this->l('The identifier is in the format G-XXXXXXX. ')
                        . ' <a target="#" data-toggle="modal" data-target="#bm-helper-analitics-ga4-id">'
                        . $this->l('Where can I find the measurement ID?') . '</a>',
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('API secret '),
                    'name' => $this->module->name_upper . '_GA4_SECRET',
                    'size' => 40,
                    'help' => '<a target="#" data-toggle="modal" data-target="#bm-helper-analitics-ga4-key">'
                        . $this->l('How do I create an API secret?') . '</a>',
                ],
            ],
            'submit' => [
                'save_event' => 'GOOGLE ANALITICS',
                'title' => $this->l('Save'),
                'class' => 'btn btn-primary pull-right',
            ],
        ];

        $fields_form[7]['form'] = [
            'section' => [
                'title' => $this->l('Analitics'),
            ],
            'legend' => [
                'title' => $this->l('Events'),
            ],
            'input' => [
                [
                    'name' => '',
                    'type' => 'description',
                    'content' => './ga-events.tpl',
                ],
            ],
        ];

        $fields_form[8]['form'] = [
            'section' => [
                'title' => $this->l('Help'),
            ],
            'input' => [
                [
                    'name' => '',
                    'type' => 'description',
                    'content' => './benefits-help.tpl',
                ],
            ],
        ];

        $fields_form[9]['form'] = [
            'section' => [
                'title' => $this->l('Help'),
            ],
            'legend' => [
                'title' => $this->l('Help'),
            ],
            'input' => [
                [
                    'name' => '',
                    'type' => 'description',
                    'content' => './help.tpl',
                ],
            ],
        ];

        $fields_form[10]['form'] = [
            'section' => [
                'title' => $this->l('Services for you'),
            ],
            'legend' => [
                'title' => $this->l('Services for you'),
            ],
            'input' => [
                [
                    'name' => '',
                    'type' => 'description',
                    'content' => './services-for-you.tpl',
                ],
            ],
        ];

        $helper = new HelperForm();

        // Moduł, token i currentIndex
        $helper->module = $this->module;
        $helper->name_controller = $this->module->name;
        $helper->token = Tools::getAdminTokenLite('AdminBluepaymentPayments');
        $helper->currentIndex = AdminController::$currentIndex;

        // Domyślny język
        $helper->default_form_language = $id_default_lang;
        $helper->allow_employee_form_lang = $id_default_lang;

        // Tytuł i belka narzędzi
        $helper->title = $this->module->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->module->name;

        $link = new Link();
        $ajax_controller = $link->getAdminLink('AdminBluepaymentAjax');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
            'ajax_controller' => $ajax_controller,
            'ajax_token' => Tools::getAdminTokenLite('AdminBluepaymentAjax'),
            'ajax_payments_token' => Tools::getAdminTokenLite('AdminBluepaymentPayments'),
            'amplitude_user_id' => Amplitude::getUserId(),
            'amplitude_id' => Amplitude::getInstance()->getAmplitudeId(),
            'bm_assets_images' => $this->module->getAssetImages(),
            'url_iframe_banner' => $this->configIframe->getLinkIframeByIsoCode($this->context->language->iso_code, $this->context->currency->iso_code),
            'url_iframe_services' => $this->configIframeServices->getLinkIframeByIsoCode($this->context->language->iso_code),
        ];

        return $helper->generateForm($fields_form);
    }

    /**
     * Get form values
     *
     * @return array
     */
    private function getConfigFieldsValues()
    {
        $data = [];
        foreach (Helper::getFields() as $field) {
            $data[$field] = Tools::getValue($field, Cfg::get($field));
        }

        foreach (Helper::getFieldsMultiple() as $field) {
            $fieldReplace = str_replace('[]', '', $field);
            $data[$field] = Tools::getValue($fieldReplace, explode(',', Cfg::get($fieldReplace)));
        }

        foreach (Helper::getFieldsLang() as $field) {
            foreach (Language::getLanguages(true) as $lang) {
                if (Cfg::get($field, $lang['id_lang'])) {
                    $data[$field][$lang['id_lang']] = Cfg::get($field, $lang['id_lang']);
                }
            }
        }

        foreach (Helper::getFieldsService() as $field) {
            foreach (AdminHelper::getSortCurrencies() as $currency) {
                $data[$field . '_' . $currency['iso_code']]
                    = Helper::parseConfigByCurrency($field, $currency['iso_code']);
            }
        }

        return $data;
    }
}
