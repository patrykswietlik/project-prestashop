<?php
/**
 * 2007-2023 patworx.de
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade AmazonPay to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    patworx multimedia GmbH <service@patworx.de>
 *  @copyright 2007-2023 patworx multimedia GmbH
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class AmazonPayAdminConfigFormHelper
{

    const TRANSLATIONKEY = 'amazonpayadminconfigformhelper';

    /**
     * @return array
     * @throws PrestaShopException
     */
    public static function renderForm($module)
    {
        $helper = new AmazonPayFormHelper();

        $helper->show_toolbar = false;
        $helper->table = $module->getVar('table');
        $helper->module = $module;
        $helper->default_form_language = $module->getVar('context')->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $module->getVar('identifier');
        $helper->submit_action = 'submitAmazonpayModule';
        $helper->currentIndex = $module->getVar('context')->link->getAdminLink('AdminModules', false)
            .'&configure='.$module->getVar('name').'&tab_module='.$module->getVar('tab').'&module_name='.$module->getVar('name');
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => self::getConfigFormValues(),
            'languages' => $module->getVar('context')->controller->getLanguages(),
            'id_language' => $module->getVar('context')->language->id,
        );

        $forms = [];
        foreach (self::getConfigForms($module) as $formKey => $formVars) {
            $forms[$formKey] = $helper->generateAmazonForm($module->getVar('context')->smarty, array($formVars));
        }
        return $forms;
    }

    /**
     * Set values for the inputs.
     */
    public static function getConfigFormValues()
    {
        $configFormValuesReturn = array(
            'AMAZONPAY_MERCHANT_ID' => Configuration::get('AMAZONPAY_MERCHANT_ID'),
            'AMAZONPAY_PUBLIC_KEY_ID' => Configuration::get('AMAZONPAY_PUBLIC_KEY_ID'),
            'AMAZONPAY_PRIVATE_KEY' => Configuration::get('AMAZONPAY_PRIVATE_KEY'),
            'AMAZONPAY_STORE_ID' => Configuration::get('AMAZONPAY_STORE_ID'),
            'AMAZONPAY_REGION' => Configuration::get('AMAZONPAY_REGION'),
            'AMAZONPAY_LOGLEVEL' => Configuration::get('AMAZONPAY_LOGLEVEL'),
            'AMAZONPAY_BUTTONS_HIDDEN_MODE' => Configuration::get('AMAZONPAY_BUTTONS_HIDDEN_MODE'),
            'AMAZONPAY_LIVEMODE' => Configuration::get('AMAZONPAY_LIVEMODE'),
            'AMAZONPAY_AUTH_MODE' => Configuration::get('AMAZONPAY_AUTH_MODE'),
            'AMAZONPAY_CAPTURE_MODE' => Configuration::get('AMAZONPAY_CAPTURE_MODE'),
            'AMAZONPAY_SYNC_MODE' => Configuration::get('AMAZONPAY_SYNC_MODE'),
            'AMAZONPAY_AUTHORIZED_STATUS_ID' => Configuration::get('AMAZONPAY_AUTHORIZED_STATUS_ID'),
            'AMAZONPAY_CAPTURED_STATUS_ID' => Configuration::get('AMAZONPAY_CAPTURED_STATUS_ID'),
            'AMAZONPAY_DECLINE_STATUS_ID' => Configuration::get('AMAZONPAY_DECLINE_STATUS_ID'),
            'AMAZONPAY_SHIPPING_STATUS_ID' => Configuration::get('AMAZONPAY_SHIPPING_STATUS_ID'),
            'AMAZONPAY_PLACEMENT_CHECKOUT' => Configuration::get('AMAZONPAY_PLACEMENT_CHECKOUT'),
            'AMAZONPAY_PLACEMENT_MINICART' => Configuration::get('AMAZONPAY_PLACEMENT_MINICART'),
            'AMAZONPAY_PLACEMENT_PRODUCT' => Configuration::get('AMAZONPAY_PLACEMENT_PRODUCT'),
            'AMAZONPAY_PLACEMENT_LOGIN' => Configuration::get('AMAZONPAY_PLACEMENT_LOGIN'),
            'AMAZONPAY_SHOW_STANDARD_PAYMENT_OPTION' => Configuration::get('AMAZONPAY_SHOW_STANDARD_PAYMENT_OPTION'),
            'AMAZONPAY_SHOW_LOGO' => Configuration::get('AMAZONPAY_SHOW_LOGO'),
            'AMAZONPAY_RESTRICTED_CATEGORIES' => Configuration::get('AMAZONPAY_RESTRICTED_CATEGORIES'),
            'AMAZONPAY_BUTTON_COLOR_CART' => Configuration::get('AMAZONPAY_BUTTON_COLOR_CART'),
            'AMAZONPAY_BUTTON_COLOR_CHECKOUT' => Configuration::get('AMAZONPAY_BUTTON_COLOR_CHECKOUT'),
            'AMAZONPAY_BUTTON_COLOR_PRODUCT' => Configuration::get('AMAZONPAY_BUTTON_COLOR_PRODUCT'),
            'AMAZONPAY_PROMO_HEADER' => Configuration::get('AMAZONPAY_PROMO_HEADER'),
            'AMAZONPAY_PROMO_HEADER_STYLE' => Configuration::get('AMAZONPAY_PROMO_HEADER_STYLE'),
            'AMAZONPAY_PROMO_PRODUCT' => Configuration::get('AMAZONPAY_PROMO_PRODUCT'),
            'AMAZONPAY_PROMO_PRODUCT_STYLE' => Configuration::get('AMAZONPAY_PROMO_PRODUCT_STYLE'),
            'AMAZONPAY_PROMO_FOOTER' => Configuration::get('AMAZONPAY_PROMO_FOOTER'),
            'AMAZONPAY_PROMO_FOOTER_STYLE' => Configuration::get('AMAZONPAY_PROMO_FOOTER_STYLE'),
            'AMAZONPAY_ALEXA_DELIVERY_NOTIFICATIONS' => Configuration::get('AMAZONPAY_ALEXA_DELIVERY_NOTIFICATIONS'),
            'AMAZONPAY_CARRIERS_MAPPING' => Configuration::get('AMAZONPAY_CARRIERS_MAPPING'),
        );

        if (Configuration::get('AMAZONPAY_PRIVATE_KEY') != '') {
            $configFormValuesReturn['AMAZONPAY_PRIVATE_KEY'] = '[Secret key]';
        }

        return $configFormValuesReturn;
    }

    /**
     * Creates the structure of config form.
     */
    public static function getConfigForms(Amazonpay $module)
    {
        $auth = array(
            'form' => array(
                'form' => array(
                    'id_form' => 'amazonpay_auth_form'
                ),
                'legend' => array(
                    'title' => $module->l('Authentication', self::TRANSLATIONKEY),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'name' => 'AMAZONPAY_MERCHANT_ID',
                        'label' => $module->l('Merchant ID', self::TRANSLATIONKEY),
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'AMAZONPAY_PUBLIC_KEY_ID',
                        'label' => $module->l('Public Key ID', self::TRANSLATIONKEY),
                    ),
                    array(
                        'type' => 'textarea',
                        'name' => 'AMAZONPAY_PRIVATE_KEY',
                        'label' => $module->l('Private Key', self::TRANSLATIONKEY),
                    ),
                    array(
                        'type' => 'file',
                        'name' => 'AMAZONPAY_PRIVATE_KEY_UPLOAD',
                        'label' => $module->l('Private Key upload', self::TRANSLATIONKEY),
                        'desc' => $module->l('You can upload the private key you received from Amazon Pay here', self::TRANSLATIONKEY),
                    ),
                    array(
                        'type' => 'text',
                        'name' => 'AMAZONPAY_STORE_ID',
                        'label' => $module->l('Store ID/Client ID', self::TRANSLATIONKEY),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $module->l('Region', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_REGION',
                        'options' => array(
                            'query' => array(
                                array('key' => 'EU', 'name' => 'EU'),
                                array('key' => 'US', 'name' => 'US'),
                                array('key' => 'UK', 'name' => 'UK'),
                                array('key' => 'JP', 'name' => 'JP')
                            ),
                            'id' => 'key',
                            'name' => 'name'
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $module->l('Save', self::TRANSLATIONKEY),
                ),
            ),
        );
        $config = array(
            'form' => array(
                'form' => array(
                    'id_form' => 'amazonpay_config_form'
                ),
                'legend' => array(
                    'title' => $module->l('Configuration', self::TRANSLATIONKEY),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $module->l('Livemode', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_LIVEMODE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_livemode',
                                'value' => true,
                                'label' => $module->l('Live', self::TRANSLATIONKEY)
                            ),
                            array(
                                'id' => 'inactive_livemode',
                                'value' => '0',
                                'label' => $module->l('Sandbox', self::TRANSLATIONKEY)
                            )
                        ),
                        'desc' => $module->l('Switch between sandbox and productive mode.', self::TRANSLATIONKEY)
                    ),
                    array(
                        'type' => 'select',
                        'label' => $module->l('Capture mode', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_CAPTURE_MODE',
                        'options' => array(
                            'query' => array(
                                array('key' => 'on_order', 'name' => 'On order'),
                                array('key' => 'on_shipment', 'name' => 'On shipment'),
                                array('key' => 'manual', 'name' => 'Manual capture')
                            ),
                            'id' => 'key',
                            'name' => 'name'
                        ),
                        'desc' => $module->l('When should the amount be captured?', self::TRANSLATIONKEY)
                    ),
                    array(
                        'col' => 3,
                        'type' => 'select',
                        'prefix' => '<i class="icon icon-tag"></i>',
                        'name' => 'AMAZONPAY_AUTHORIZED_STATUS_ID',
                        'label' => $module->l('Order status for authorized payments', self::TRANSLATIONKEY),
                        'options' => array(
                            'query' => array_merge(
                                array(
                                    array(
                                        'id_order_state' => 0,
                                        'id_lang' => (int)Configuration::get('PS_LANG_DEFAULT'),
                                        'name' => ''
                                    )
                                ),
                                OrderState::getOrderStates((int)Configuration::get('PS_LANG_DEFAULT'))
                            ),
                            'id' => 'id_order_state',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'col' => 3,
                        'type' => 'select',
                        'prefix' => '<i class="icon icon-tag"></i>',
                        'name' => 'AMAZONPAY_CAPTURED_STATUS_ID',
                        'label' => $module->l('Order status for captured payments', self::TRANSLATIONKEY),
                        'options' => array(
                            'query' => array_merge(
                                array(
                                    array(
                                        'id_order_state' => 0,
                                        'id_lang' => (int)Configuration::get('PS_LANG_DEFAULT'),
                                        'name' => ''
                                    )
                                ),
                                OrderState::getOrderStates((int)Configuration::get('PS_LANG_DEFAULT'))
                            ),
                            'id' => 'id_order_state',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'col' => 3,
                        'type' => 'select',
                        'prefix' => '<i class="icon icon-tag"></i>',
                        'name' => 'AMAZONPAY_DECLINE_STATUS_ID',
                        'label' => $module->l('Order status for declined payments', self::TRANSLATIONKEY),
                        'options' => array(
                            'query' => array_merge(
                                array(
                                    array(
                                        'id_order_state' => 0,
                                        'id_lang' => (int)Configuration::get('PS_LANG_DEFAULT'),
                                        'name' => ''
                                    )
                                ),
                                OrderState::getOrderStates((int) Configuration::get('PS_LANG_DEFAULT'))
                            ),
                            'id' => 'id_order_state',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'col' => 3,
                        'type' => 'select',
                        'prefix' => '<i class="icon icon-tag"></i>',
                        'name' => 'AMAZONPAY_SHIPPING_STATUS_ID',
                        'label' => $module->l('Order status for shipped orders', self::TRANSLATIONKEY),
                        'options' => array(
                            'query' => array_merge(
                                array(
                                    array(
                                        'id_order_state' => 0,
                                        'id_lang' => (int)Configuration::get('PS_LANG_DEFAULT'),
                                        'name' => ''
                                    )
                                ),
                                OrderState::getOrderStates((int) Configuration::get('PS_LANG_DEFAULT'))
                            ),
                            'id' => 'id_order_state',
                            'name' => 'name'
                        ),
                        'desc' => $module->l('If you use capture mode "on shipment", this status will trigger the capture.', self::TRANSLATIONKEY)
                    ),                    
                ),
                'submit' => array(
                    'title' => $module->l('Save', self::TRANSLATIONKEY),
                ),
            ),
        );

        $promo_header_query_array = array(
            array(
                'id_promo_header_style' => '0',
                'name' => $module->l('LightGrey', self::TRANSLATIONKEY)
            ),
            array(
                'id_promo_header_style' => '1',
                'name' => $module->l('DarkGrey', self::TRANSLATIONKEY)
            )
        );
        if (Context::getContext()->language->iso_code == 'es' || Context::getContext()->language->iso_code == 'fr') {
            $promo_header_query_array[] = array(
                'id_promo_header_style' => '2',
                'name' => $module->l('Alternative payment methods', self::TRANSLATIONKEY)
            );
        }

        $promo_product_query_array = array(
            array(
                'id_promo_product_style' => '0',
                'name' => $module->l('LightGrey', self::TRANSLATIONKEY)
            ),
            array(
                'id_promo_product_style' => '1',
                'name' => $module->l('DarkGrey', self::TRANSLATIONKEY)
            )
        );
        if (Context::getContext()->language->iso_code == 'es' || Context::getContext()->language->iso_code == 'fr') {
            $promo_product_query_array[] = array(
                'id_promo_product_style' => '2',
                'name' => $module->l('Alternative payment methods', self::TRANSLATIONKEY)
            );
        }

        $promo_footer_query_array = array(
            array(
                'id_promo_footer_style' => '0',
                'name' => $module->l('LightGrey', self::TRANSLATIONKEY)
            ),
            array(
                'id_promo_footer_style' => '1',
                'name' => $module->l('DarkGrey', self::TRANSLATIONKEY)
            )
        );
        if (Context::getContext()->language->iso_code == 'es' || Context::getContext()->language->iso_code == 'fr') {
            $promo_footer_query_array[] = array(
                'id_promo_footer_style' => '2',
                'name' => $module->l('Alternative payment methods', self::TRANSLATIONKEY)
            );
        }


        $expert = array(
            'form' => array(
                'form' => array(
                    'id_form' => 'amazonpay_expert_form'
                ),
                'legend' => array(
                    'title' => $module->l('Expert Mode', self::TRANSLATIONKEY),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $module->l('Loglevel', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_LOGLEVEL',
                        'options' => array(
                            'query' => array(
                                array('key' => '0', 'name' => 'Disable logging completely'),
                                array('key' => '1', 'name' => 'Only errors'),
                                array('key' => '2', 'name' => 'Log errors and informations'),
                                array('key' => '3', 'name' => 'Debug mode')
                            ),
                            'id' => 'key',
                            'name' => 'name'
                        ),
                        'desc' => $module->l('Set different log levels. Debug mode will log the most information.', self::TRANSLATIONKEY)
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $module->l('Buttons hidden mode', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_BUTTONS_HIDDEN_MODE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_buttonhiddenmode',
                                'value' => true,
                                'label' => $module->l('Active', self::TRANSLATIONKEY)
                            ),
                            array(
                                'id' => 'inactive_buttonhiddenmode',
                                'value' => '0',
                                'label' => $module->l('Inactive', self::TRANSLATIONKEY)
                            )
                        ),
                        'desc' => $module->l('Useful for debugging without real customers seeing the buttons.', self::TRANSLATIONKEY)
                    ),
                    /*
                    array(
                        'type' => 'select',
                        'label' => $module->l('Authorization mode', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_AUTH_MODE',
                        'options' => array(
                            'query' => array(
                                array('key' => 'authorize', 'name' => 'Authorize'),
                                array('key' => 'confirm', 'name' => 'Confirm')
                            ),
                            'id' => 'key',
                            'name' => 'name'
                        ),
                    ),
                    */
                    array(
                        'type' => 'select',
                        'label' => $module->l('Authorization Processing Mode', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_SYNC_MODE',
                        'options' => array(
                            'query' => array(
                                array('key' => '1', 'name' => 'Synchronous (default)'),
                                array('key' => '0', 'name' => 'Optimized'),
                            ),
                            'id' => 'key',
                            'name' => 'name'
                        ),
                        'desc' => $module->l('Switch between sync/async transaction mode.', self::TRANSLATIONKEY)
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $module->l('Button placement checkout', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_PLACEMENT_CHECKOUT',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_checkout',
                                'value' => true,
                                'label' => $module->l('Active', self::TRANSLATIONKEY)
                            ),
                            array(
                                'id' => 'inactive_checkout',
                                'value' => '0',
                                'label' => $module->l('Inactive', self::TRANSLATIONKEY)
                            )
                        ),
                        'desc' => $module->l('The feature is available for all versions of Prestashop 1.6 and for Prestashop 1.7.6 or newer versions', self::TRANSLATIONKEY)
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $module->l('Button placement mini-cart', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_PLACEMENT_MINICART',
                        'desc' => $module->l('This feature is only available in PrestaShop 1.6 template', self::TRANSLATIONKEY),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_checkoutmini',
                                'value' => true,
                                'label' => $module->l('Active', self::TRANSLATIONKEY)
                            ),
                            array(
                                'id' => 'inactive_checkoutmini',
                                'value' => '0',
                                'label' => $module->l('Inactive', self::TRANSLATIONKEY)
                            )
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $module->l('Button placement product page', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_PLACEMENT_PRODUCT',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_productpage',
                                'value' => true,
                                'label' => $module->l('Active', self::TRANSLATIONKEY)
                            ),
                            array(
                                'id' => 'inactive_productpage',
                                'value' => '0',
                                'label' => $module->l('Inactive', self::TRANSLATIONKEY)
                            )
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $module->l('Button placement login sections', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_PLACEMENT_LOGIN',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_loginsection',
                                'value' => true,
                                'label' => $module->l('Active', self::TRANSLATIONKEY)
                            ),
                            array(
                                'id' => 'inactive_loginsection',
                                'value' => '0',
                                'label' => $module->l('Inactive', self::TRANSLATIONKEY)
                            )
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $module->l('Show the Amazon Pay button as a payment method', self::TRANSLATIONKEY),
                        'hint' => $module->l('Amazon Pay will be displayed at the end of the checkout with other payment methods. This option will redirect to the Amazon Pay checkout page.', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_SHOW_STANDARD_PAYMENT_OPTION',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_showpaymentoption',
                                'value' => true,
                                'label' => $module->l('Yes', self::TRANSLATIONKEY)
                            ),
                            array(
                                'id' => 'inactive_showpaymentoption',
                                'value' => '0',
                                'label' => $module->l('No', self::TRANSLATIONKEY)
                            )
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $module->l('Show the Amazon Pay logo when above option is enabled', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_SHOW_LOGO',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_showlogooption',
                                'value' => true,
                                'label' => $module->l('Yes', self::TRANSLATIONKEY)
                            ),
                            array(
                                'id' => 'inactive_showlogooption',
                                'value' => '0',
                                'label' => $module->l('No', self::TRANSLATIONKEY)
                            )
                        )
                    ),
                    array(
                        'type' => 'categories',
                        'label' => $module->l('Disable Amazon Pay for products in the following categories', self::TRANSLATIONKEY) . '<span id="disableModCats"></span>',
                        'name' => 'AMAZONPAY_RESTRICTED_CATEGORIES',
                        'tree' => array(
                            'root_category' => (int)Category::getRootCategory()->id,
                            'id' => 'id_category',
                            'name' => 'name_category',
                            'use_checkbox' => true,
                            'selected_categories' => explode(",", trim(Configuration::get('AMAZONPAY_RESTRICTED_CATEGORIES'))),
                            'disabled_categories' => [],
                            'use_search' => true,
                        ),
                        'desc' => $module->l('You can select one or more categories.', self::TRANSLATIONKEY)
                    ),
                    array(
                        'type' => 'select',
                        'label' => $module->l('Button color in shopping cart', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_BUTTON_COLOR_CART',
                        'options' => array(
                            'query' => array(
                                array('key' => 'Gold', 'name' => 'Gold'),
                                array('key' => 'DarkGray', 'name' => 'Dark Gray'),
                                array('key' => 'LightGray', 'name' => 'Light Gray')
                            ),
                            'id' => 'key',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $module->l('Button color in checkout', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_BUTTON_COLOR_CHECKOUT',
                        'options' => array(
                            'query' => array(
                                array('key' => 'Gold', 'name' => 'Gold'),
                                array('key' => 'DarkGray', 'name' => 'Dark Gray'),
                                array('key' => 'LightGray', 'name' => 'Light Gray')
                            ),
                            'id' => 'key',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $module->l('Button color in product page', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_BUTTON_COLOR_PRODUCT',
                        'options' => array(
                            'query' => array(
                                array('key' => 'Gold', 'name' => 'Gold'),
                                array('key' => 'DarkGray', 'name' => 'Dark Gray'),
                                array('key' => 'LightGray', 'name' => 'Light Gray')
                            ),
                            'id' => 'key',
                            'name' => 'name'
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $module->l('Amazon Pay banner in your website header', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_PROMO_HEADER',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on_promo_header',
                                'value' => true,
                                'label' => $module->l('Enabled', self::TRANSLATIONKEY)
                            ),
                            array(
                                'id' => 'active_off_promo_header',
                                'value' => '0',
                                'label' => $module->l('Disabled', self::TRANSLATIONKEY)
                            )
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $module->l('Style', self::TRANSLATIONKEY),
                        'desc' => $module->l('Check eligibility for Cofidis products', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_PROMO_HEADER_STYLE',
                        'options' => array(
                            'query' => $promo_header_query_array,
                            'id' => 'id_promo_header_style',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $module->l('Amazon Pay banner on your product pages', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_PROMO_PRODUCT',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on_promo_product',
                                'value' => true,
                                'label' => $module->l('Enabled', self::TRANSLATIONKEY)
                            ),
                            array(
                                'id' => 'active_off_promo_product',
                                'value' => '0',
                                'label' => $module->l('Disabled', self::TRANSLATIONKEY)
                            )
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $module->l('Style', self::TRANSLATIONKEY),
                        'desc' => $module->l('Check eligibility for Cofidis products', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_PROMO_PRODUCT_STYLE',
                        'options' => array(
                            'query' => $promo_product_query_array,
                            'id' => 'id_promo_product_style',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $module->l('Amazon Pay acceptance mark in your website footer', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_PROMO_FOOTER',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on_promo_footer',
                                'value' => true,
                                'label' => $module->l('Enabled', self::TRANSLATIONKEY)
                            ),
                            array(
                                'id' => 'active_off_promo_footer',
                                'value' => '0',
                                'label' => $module->l('Disabled', self::TRANSLATIONKEY)
                            )
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $module->l('Style', self::TRANSLATIONKEY),
                        'desc' => $module->l('Check eligibility for Cofidis products', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_PROMO_FOOTER_STYLE',
                        'options' => array(
                            'query' => $promo_footer_query_array,
                            'id' => 'id_promo_footer_style',
                            'name' => 'name'
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $module->l('Save', self::TRANSLATIONKEY),
                ),
            ),
        );

        $alexa = array(
            'form' => array(
                'form' => array(
                    'id_form' => 'amazonpay_alexa_form'
                ),
                'legend' => array(
                    'title' => $module->l('Alexa delivery notifications', self::TRANSLATIONKEY),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $module->l('Enable Alexa delivery notifications', self::TRANSLATIONKEY),
                        'name' => 'AMAZONPAY_ALEXA_DELIVERY_NOTIFICATIONS',
                        'is_bool' => 'true',
                        'values' => array(
                            array(
                                'id' => 'AMZ_ALEXA_DELIVERY_NOTIFICATIONS_on',
                                'value' => true,
                                'label' => $module->l('Enabled', self::TRANSLATIONKEY)
                            ),
                            array(
                                'id' => 'AMZ_ALEXA_DELIVERY_NOTIFICATIONS_off',
                                'value' => false,
                                'label' => $module->l('Disabled', self::TRANSLATIONKEY)
                            )
                        )
                    ),
                ),
                'submit' => array(
                    'title' => $module->l('Save', self::TRANSLATIONKEY),
                ),
            ),
        );

        return array(
            'auth' => $auth,
            'config' => $config,
            'expert' => $expert,
            'alexa' => $alexa
        );
    }
}
