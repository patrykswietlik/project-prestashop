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

trait AmazonPayFrontendHooksTrait
{
    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        if ($this->isReadyForFrontend()) {
            $this->context->smarty->assign('moduleViewPath', $this->getViewPath());
            if ($this->isPrestaShop16()) {
                $this->context->controller->addJS(AmazonPayHelper::getCheckoutJSURL());
                $this->context->controller->addJS($this->_path.'/views/js/button.js');
                $this->context->controller->addJS($this->_path.'/views/js/button_ps16.js');
                if (isset($this->context->controller->php_self) && $this->context->controller->php_self == 'order' || $this->context->controller->php_self == 'order-opc') {
                    if (AmazonPayHelper::isAmazonPayCheckout()) {
                        $this->context->controller->addJS($this->_path . '/views/js/checkout.js');
                    }
                }
                $this->context->controller->addCSS($this->_path.'/views/css/front.css');
                if (AmazonPayHelper::isButtonHiddenMode()) {
                    $this->context->controller->addCSS($this->_path.'/views/css/button_hidden_mode.css');
                }
                $this->addJsDef();
            } else {
                $return = '';
                if (isset(Context::getContext()->controller->isAmazonPayCheckout)) {
                    if (Module::isInstalled('dpdfrance') && Module::isEnabled('dpdfrance')) {
                        $this->context->controller->registerStylesheet(
                            'module-dpdfrance-css',
                            '/modules/dpdfrance/views/css/front/dpdfrance.css',
                            array('media' => 'all')
                        );
                        $this->context->controller->registerJavascript(
                            'module-dpdfrance-jquery',
                            '/js/jquery/jquery-1.11.0.min.js',
                            array('position' => 'head', 'priority' => 1)
                        );
                        $dpd_cnt = 0;
                        foreach (glob(_PS_MODULE_DIR_.'dpdfrance/views/js/front/*.js') as $dpdfrance_file) {
                            $dpd_cnt++;
                            $this->context->controller->registerJavascript(
                                'module-dpdfrance-js-' . $dpd_cnt,
                                '/modules/dpdfrance/views/js/front/' . basename($dpdfrance_file),
                                array('position' => 'bottom', 'priority' => 100)
                            );
                        }
                        $this->context->controller->registerJavascript(
                            'module-dpdfrance-gmaps',
                            'https://maps.googleapis.com/maps/api/js?key='.Configuration::get('DPDFRANCE_GOOGLE_API_KEY'),
                            array('priority' => 100, 'server' => 'remote')
                        );
                        Media::addJsDef(
                            [
                                'dpdfranceRelaisCarrierId' => (int) Configuration::get('DPDFRANCE_RELAIS_CARRIER_ID'),
                                'dpdfrancePredictCarrierId' => (int) Configuration::get('DPDFRANCE_PREDICT_CARRIER_ID'),
                                'psVer' => (float) _PS_VERSION_,
                                'dpdfrance_cart_id' => $this->context->cart->id,
                                'dpdfrance_base_dir' => __PS_BASE_URI__.'modules/dpdfrance',
                                'dpdfrance_token' => Tools::encrypt('dpdfrance/ajax')
                            ]
                        );
                    }
                }
                return $return;
            }
        }
    }

    /**
     * @param $params
     */
    public function hookActionFrontControllerSetMedia($params)
    {
        if (!$this->isPrestaShop16()) {
            $this->context->controller->registerJavascript(
                'amazonpay_checkout.js',
                AmazonPayHelper::getCheckoutJSURL(),
                ['position' => 'bottom', 'priority' => 100, 'server' => 'remote']
            );
            $this->context->controller->registerJavascript(
                'amazonpay_button.js',
                '/modules/'.$this->name.'/views/js/button.js',
                ['position' => 'bottom', 'priority' => 150]
            );
            $this->context->controller->registerStylesheet(
                'amazonpay_front.css',
                'modules/'.$this->name.'/views/css/front.css'
            );
            if (AmazonPayHelper::isButtonHiddenMode()) {
                $this->context->controller->registerStylesheet(
                    'amazonpay_front_hiddenmode.css',
                    'modules/'.$this->name.'/views/css/button_hidden_mode.css'
                );
            }
            $this->addJsDef();
        }
    }

    /**
     * This method is used to render the payment button,
     * Take care if the button should be displayed or not.
     */
    public function hookPayment($params)
    {
        if (!$this->isReadyForFrontend()) {
            return false;
        }
        if (!$this->isPrestaShop16()) {
            return false;
        }
        if (AmazonPayRestrictedProductsHelper::cartHasRestrictedProducts()) {
            return false;
        }
        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int)$currency_id);

        $this->smarty->assign('module_dir', $this->_path);
        $this->smarty->assign('amazon_card', AmazonPayHelper::getPaymentDescriptor());
        if (AmazonPayHelper::isAmazonPayCheckout()) {
            $this->context->smarty->assign('isInAmazonPayCheckout', true);
        } else {
            if (!AmazonPayHelper::showStandardCheckoutPaymentOption()) {
                return false;
            }
        }
        $this->context->smarty->assign('isOnePageCheckoutPSInstalled', Amazonpay::isOnePageCheckoutPSInstalled());
        return $this->display($this->this_file, 'views/templates/hook/payment.tpl');
    }

    /**
     * Advanced EU Compliance - PrestaShop 1.6
     *
     * @param $params
     * @return array|void
     */
    public function hookDisplayPaymentEU($params)
    {
        if (!$this->isReadyForFrontend()) {
            return;
        }
        if (!$this->isPrestaShop16()) {
            return $this->hookPaymentOptions($params);
        }
        if (AmazonPayRestrictedProductsHelper::cartHasRestrictedProducts()) {
            return;
        }
        if (!$this->active) {
            return;
        }
        if (! $this->checkCurrency($params['cart'])) {
            return;
        }

        $this->context->controller->addJS($this->getPathUri() . 'views/js/amazonpay_16_eucompl.js');

        $payment_options = array(
            'cta_text' => $this->l('Pay with Amazon') . AmazonPayHelper::getPaymentDescriptor(),
            'logo' => Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/logo.gif'),
            'action' => $this->context->link->getModuleLink($this->name, 'redirect', array(), true)
        );

        return $payment_options;
    }

    /**
     * This hook is used to display the order confirmation page.
     */
    public function hookPaymentReturn($params)
    {
        if (!$this->isPrestaShop16()) {
            return;
        }
        if (!$this->isReadyForFrontend()) {
            return;
        }
        if ($this->active == false) {
            return;
        }

        $order = $params['objOrder'];

        if ($order->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }

        $this->smarty->assign(array(
            'id_order' => $order->id,
            'reference' => $order->reference,
            'params' => $params,
            'total' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
        ));

        return $this->display($this->this_file, 'views/templates/hook/confirmation.tpl');
    }

    /**
     * Return payment options available for PS 1.7+
     *
     * @param array Hook parameters
     *
     * @return array|null
     */
    public function hookPaymentOptions($params)
    {
        if (!$this->isReadyForFrontend()) {
            return;
        }
        if (!$this->active) {
            return;
        }
        if (AmazonPayRestrictedProductsHelper::cartHasRestrictedProducts()) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $option = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $option->setCallToActionText($this->l('Amazon Pay') . AmazonPayHelper::getPaymentDescriptor())
            ->setAction($this->context->link->getModuleLink($this->name, 'redirect', array(), true));

        $controller = Context::getContext()->controller;
        if (!isset($controller->isAmazonPayCheckout)) {
            if (!AmazonPayHelper::showStandardCheckoutPaymentOption()) {
                return;
            }
            if (AmazonPayHelper::isButtonHiddenMode()) {
                return;
            }
            $this->context->smarty->assign('AmazonPayButtonColor', AmazonPayHelper::getButtonColor('checkout'));
            $this->context->smarty->assign('modulePath', dirname(__FILE__) . '/../../');
            if (AmazonPayHelper::showStandardCheckoutLogo()) {
                $logo_path = _PS_MODULE_DIR_ . $this->name . '/views/img/amazonpay-logo.png';
                if (file_exists(_PS_THEME_DIR_ . 'modules/' . $this->name . '/views/img/amazonpay-logo.png')) {
                    $logo_path = _PS_THEME_DIR_ . 'modules/' . $this->name . '/views/img/amazonpay-logo.png';
                }
                $option->setLogo(
                    Media::getMediaPath($logo_path)
                );
            }
            if (AmazonPayHelper::hideAmazonPayButtonInRegularCheckoutStep() && !$this->isOnePageCheckoutPSInstalled() && !$this->isTheCheckoutInstalled() && !$this->isSupercheckoutInstalled()) {
                if (!$this->isOnePageCheckoutPSV5Installed()) {
                    $option->setForm('<form action="JavaScript:amazonPayInitApb();" onsubmit="amazonPayInitApb(); return false;"></form>');
                }
            } else {
                if (!$this->isTheCheckoutInstalled()) {
                    $option->setBinary(true);
                    if (!$this->isSupercheckoutInstalled()) {
                        $option->setAdditionalInformation(
                            $this->context->smarty->fetch(
                                'module:amazonpay/views/templates/hook/embeddedPaymentOption.tpl'
                            )
                        );
                    }
                }
            }
        }
        return [
            $option
        ];
    }

    /**
     * @param $params
     * @return string
     */
    public function hookDisplayExpressCheckout($params)
    {
        if (AmazonPayRestrictedProductsHelper::cartHasRestrictedProducts()) {
            return;
        }
        if ($this->isOnePageCheckoutPSInstalled()) {
            if ($this->context->controller->php_self == 'order' || $this->context->controller->php_self == 'order-opc') {
                return;
            }
        }
        if ($this->isReadyForFrontend()) {
            if (AmazonPayHelper::orderAllowed()) {
                $this->context->smarty->assign('AmazonPayButtonColor', AmazonPayHelper::getButtonColor('cart'));
                return $this->display($this->this_file, 'views/templates/hook/displayExpressCheckout.tpl');
            }
        }
    }

    /**
     * Runs only in PrestaShop 1.6
     *
     * @param $params
     * @return string
     */
    public function hookDisplayShoppingCartFooter($params)
    {
        if (AmazonPayRestrictedProductsHelper::cartHasRestrictedProducts()) {
            return false;
        }
        if ($this->isPrestaShop16() && $this->isReadyForFrontend()) {
            if (AmazonPayHelper::isAmazonPayCheckout()) {
                $this->context->smarty->assign('resetlink', $this->context->link->getModuleLink($this->name, 'reset', array(), true));
                $this->context->smarty->assign('isInAmazonPayCheckout', true);
            }
            $this->context->smarty->assign('AmazonPayButtonColor', AmazonPayHelper::getButtonColor('cart'));
            return $this->display($this->this_file, 'views/templates/hook/displayShoppingCartFooter.tpl');
        }
    }

    /**
     * @param $params
     * @return string
     */
    public function hookDisplayPersonalInformationTop($params)
    {
        if ($this->isReadyForFrontend()) {
            if (AmazonPayHelper::showInCheckoutSection()) {
                $this->context->smarty->assign('AmazonPayButtonColor', AmazonPayHelper::getButtonColor('checkout'));
                return $this->display($this->this_file, 'views/templates/hook/displayPersonalInformationTop.tpl');
            }
        }
    }

    /**
     * @param $params
     * @return string
     */
    public function hookDisplayProductActions($params)
    {
        $return = '';
        if ($this->isReadyForFrontend()) {
            if (Configuration::get('AMAZONPAY_PLACEMENT_PRODUCT') && $this->amazonPayV2SupportsProductLevel()) {
                if (is_array($params['product'])) {
                    if (AmazonPayRestrictedProductsHelper::isRestrictedProduct($params['product']['id_product'])) {
                        return false;
                    }
                } else {
                    if (AmazonPayRestrictedProductsHelper::isRestrictedProduct($params['product']->id_product)) {
                        return false;
                    }
                }
                if (AmazonPayHelper::orderAllowed('product', $params['product'])) {
                    $this->context->smarty->assign('AmazonPayButtonColor', AmazonPayHelper::getButtonColor('product'));
                    if (is_array($params['product'])) {
                        if (isset($params['product']['available_for_order']) && $params['product']['available_for_order'] != 1) {
                            return;
                        }
                        $this->context->smarty->assign(
                            ['amazonPayButtonId' => 'AmazonPayProduct' . $params['product']['id_product']]
                        );
                    } else {
                        if (!isset($params['product']->id_product)) {
                            return;
                        }
                        if (isset($params['product']->available_for_order) && $params['product']->available_for_order != 1) {
                            return;
                        }
                        $this->context->smarty->assign(
                            ['amazonPayButtonId' => 'AmazonPayProduct' . $params['product']->id_product]
                        );
                    }
                    $return .= $this->fetch($this->local_path . 'views/templates/hook/displayProductActions.tpl');
                }
            }
        }
        return $return;
    }

    /**
     * @param $params
     * @return string
     */
    public function hookDisplayBanner($params)
    {
        $return = '';
        if (Configuration::get('AMAZONPAY_PROMO_HEADER') == '1') {
            $banners = AmazonPayHelper::getBannersForLanguageCode();
            $banners_style = Configuration::get('AMAZONPAY_PROMO_HEADER_STYLE') == '1' ? 'dark' : 'light';
            if (Context::getContext()->language->iso_code == 'es' || Context::getContext()->language->iso_code == 'fr') {
                if (Configuration::get('AMAZONPAY_PROMO_HEADER_STYLE') == '2') {
                    $banners_style = 'bnpl';
                }
            }
            $this->context->smarty->assign('banner_url', $banners[$banners_style]['header']);
            $return .= $this->fetch($this->local_path . 'views/templates/hook/displayBanner.tpl');
        }
        return $return;
    }

    /**
     * @param $params
     * @return string
     */
    public function hookDisplayFooter($params)
    {
        if (Configuration::get('AMAZONPAY_PROMO_FOOTER') == '1') {
            $banners = AmazonPayHelper::getBannersForLanguageCode();
            $banners_style = Configuration::get('AMAZONPAY_PROMO_FOOTER_STYLE') == '1' ? 'dark' : 'light';
            if (Context::getContext()->language->iso_code == 'es' || Context::getContext()->language->iso_code == 'fr') {
                if (Configuration::get('AMAZONPAY_PROMO_FOOTER_STYLE') == '2') {
                    $banners_style = 'bnpl';
                }
            }
            $this->context->smarty->assign('banner_url', $banners[$banners_style]['footer']);
        }
        if ($this->isPrestaShop16() && $this->isReadyForFrontend()) {
            if (Configuration::get('AMAZONPAY_PLACEMENT_MINICART') == '1') {
                if (AmazonPayHelper::orderAllowed()) {
                    $this->context->smarty->assign('show_amazonpay_button', true);
                }
            }
        }
        return $this->fetch($this->local_path . 'views/templates/hook/displayFooter.tpl');
    }

    /**
     * @param $params
     * @return string
     */
    public function hookDisplayProductAdditionalInfo($params)
    {
        $return = '';
        if ($this->isReadyForFrontend()) {
            if (is_array($params['product'])) {
                if (AmazonPayRestrictedProductsHelper::isRestrictedProduct($params['product']['id_product'])) {
                    return false;
                }
            } else {
                if (AmazonPayRestrictedProductsHelper::isRestrictedProduct($params['product']->id_product)) {
                    return false;
                }
            }
            if (!$this->isPrestaShop16() && !$this->isPrestaShop176()) {
                $return.= $this->hookDisplayProductActions($params);
            }
            if (Configuration::get('AMAZONPAY_PROMO_PRODUCT') == '1') {
                $banners = AmazonPayHelper::getBannersForLanguageCode();
                $banners_style = Configuration::get('AMAZONPAY_PROMO_PRODUCT_STYLE') == '1' ? 'dark' : 'light';
                if (Context::getContext()->language->iso_code == 'es' || Context::getContext()->language->iso_code == 'fr') {
                    if (Configuration::get('AMAZONPAY_PROMO_PRODUCT_STYLE') == '2') {
                        $banners_style = 'bnpl';
                    }
                }
                $this->context->smarty->assign('banner_url', $banners[$banners_style]['product']);
                $return .= $this->fetch($this->local_path . 'views/templates/hook/displayProductAdditionalInfo.tpl');
            }
        }
        return $return;
    }

    /**
     * Runs only in PrestaShop 1.6
     *
     * @param $params
     * @return string
     */
    public function hookDisplayProductButtons($params)
    {
        $return = '';
        if ($this->isPrestaShop16() && $this->isReadyForFrontend()) {
            $return.= $this->hookDisplayProductActions($params);
            if (Configuration::get('AMAZONPAY_PROMO_PRODUCT') == '1') {
                $banners = AmazonPayHelper::getBannersForLanguageCode();
                $banners_style = Configuration::get('AMAZONPAY_PROMO_PRODUCT_STYLE') == '1' ? 'dark' : 'light';
                if (Context::getContext()->language->iso_code == 'es' || Context::getContext()->language->iso_code == 'fr') {
                    if (Configuration::get('AMAZONPAY_PROMO_PRODUCT_STYLE') == '2') {
                        $banners_style = 'bnpl';
                    }
                }
                $this->context->smarty->assign('banner_url', $banners[$banners_style]['product']);
                $return .= $this->fetch($this->local_path . 'views/templates/hook/displayProductAdditionalInfo.tpl');
            }
        }
        return $return;
    }

    /**
     * @param $params
     * @return mixed
     */
    public function hookDisplayCustomerLoginFormAfter($params)
    {
        if (AmazonPayHelper::showInLoginSection()) {
            return $this->getLoginButtonCode();
        }
    }

    /**
     * @param $params
     * @return bool|string
     */
    public function hookDisplayOverrideTemplate($params)
    {
        if (isset($params['controller']->is_amazon_pay)) {
            if ($params['controller']->is_amazon_pay) {
                if ($params['template_file'] == 'customer/address') {
                    return 'module:amazonpay/views/templates/front/address.tpl';
                }
            }
        }
        return false;
    }

    /**
     * @param $params
     */
    public function hookActionCustomerLogoutAfter($params)
    {
        $checkoutSession = new AmazonPayCheckoutSession(false);
        $checkoutSession->reset();
    }

    /**
     * @param $params
     */
    public function hookActionDispatcher($params)
    {
        if ($this->isPrestaShop16()) {
            if (isset($this->context->controller->php_self)) {
                if ($this->context->controller->php_self == 'order' || $this->context->controller->php_self == 'order-opc') {
                    if (Configuration::get('PS_ORDER_PROCESS_TYPE') == 1) { // opc
                        if ($this->context->controller->php_self == 'order-opc') {
                            $this->PrestaShopNotificationsFetcher($this->context->controller);
                            if (isset($this->errors)) {
                                if (count($this->errors)) {
                                    $this->context->smarty->assign('errors', $this->errors);
                                    $this->context->smarty->assign('amazon_errors', true);
                                }
                            }
                        }
                    } else {
                        $this->PrestaShopNotificationsFetcher($this->context->controller);
                    }
                }
            }
        }
    }

    /**
     * Use this method to return the result of a smarty template when assign data only locally with $this->smarty->assign().
     *
     * @param string $templatePath relative path the template file, from the module root dir
     * @param null $cache_id
     * @param null $compile_id
     *
     * @return mixed
     */
    public function fetch($templatePath, $cache_id = null, $compile_id = null)
    {
        if ($cache_id !== null) {
            Tools::enableCache();
        }

        $template = $this->context->smarty->createTemplate(
            $templatePath,
            $cache_id,
            $compile_id,
            $this->smarty
        );

        if ($cache_id !== null) {
            Tools::restoreCacheSettings();
        }

        return $template->fetch();
    }
}
