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

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;

require_once dirname(__FILE__) . '/../../src/classes/PrestaShop/AmazonPayPrestaShopCheckoutSession.php';
require_once dirname(__FILE__) . '/../../src/classes/PrestaShop/AmazonPayPrestaShopPaymentOptionsFinder.php';

class AmazonpayCheckoutModuleFrontController extends OrderController
{

    public $isAmazonPayCheckout = true;

    /**
     * @return mixed
     */
    public function initContent()
    {
        if (Module::isInstalled('supercheckout') && Module::isEnabled('supercheckout')) {
            $original_self = $this->php_self;
            $this->php_self = 'amazonpay-checkout';
            parent::initContent();
            $this->php_self = $original_self;
        } else {
            if (Module::isInstalled('checkoutpro') && Module::isEnabled('checkoutpro')) {
                $ret = OrderControllerCore::initContent();
            } else {
                $ret = parent::initContent();
            }
            if (isset($this->checkoutWarning['address']) && is_array($this->checkoutWarning['address'])) {
                if (sizeof($this->checkoutWarning['address']) > 0) {
                    foreach ($this->checkoutProcess->getSteps() as $step) {
                        if ($step->isCurrent()) {
                            if ($step->getIdentifier() == 'checkout-delivery-step') {
                                if (!(isset($this->checkoutWarning['address']['id_address']) && $this->checkoutWarning['address']['id_address'] == 0)) {
                                    Tools::redirect(
                                        $this->context->link->getModuleLink(
                                            'amazonpay',
                                            'setaddress',
                                            ['amazonCheckoutSessionId' => Context::getContext()->cookie->amazon_pay_checkout_session_id]
                                        )
                                    );
                                }
                            }
                        }
                    }
                }
            }
            return $ret;
        }
    }

    /**
     * bootstrapping method
     */
    protected function bootstrap()
    {
        $translator = $this->getTranslator();

        $session = $this->getCheckoutSessionAmazon();

        $this->checkoutProcess = new CheckoutProcess(
            $this->context,
            $session
        );

        /*
         * inactive because no direct address or userdata editing allowed at this point
         *

        $this->checkoutProcess
            ->addStep(new CheckoutPersonalInformationStep(
                $this->context,
                $translator,
                $this->makeLoginForm(),
                $this->makeCustomerForm()
            ))
            ->addStep(new CheckoutAddressesStep(
                $this->context,
                $translator,
                $this->makeAddressForm()
            ));
        */

        if (!$this->context->cart->isVirtualCart()) {
            $checkoutDeliveryStep = new CheckoutDeliveryStep(
                $this->context,
                $translator
            );

            $checkoutDeliveryStep
                ->setRecyclablePackAllowed((bool) Configuration::get('PS_RECYCLABLE_PACK'))
                ->setGiftAllowed((bool) Configuration::get('PS_GIFT_WRAPPING'))
                ->setIncludeTaxes(
                    !Product::getTaxCalculationMethod((int) $this->context->cart->id_customer)
                    && (int) Configuration::get('PS_TAX')
                )
                ->setDisplayTaxesLabel((Configuration::get('PS_TAX') && !Configuration::get('AEUC_LABEL_TAX_INC_EXC')))
                ->setGiftCost(
                    $this->context->cart->getGiftWrappingPrice(
                        $checkoutDeliveryStep->getIncludeTaxes()
                    )
                );

            $this->checkoutProcess->addStep($checkoutDeliveryStep);
        }

        $this->checkoutProcess
            ->addStep(new CheckoutPaymentStep(
                $this->context,
                $translator,
                new AmazonPayPrestaShopPaymentOptionsFinder(),
                new ConditionsToApproveFinder(
                    $this->context,
                    $translator
                )
            ));
    }

    /**
     * @return mixed
     */
    public function setMedia()
    {
        $this->registerJavascript(
            'amazon_checkout_frontend_js',
            '/modules/amazonpay/views/js/checkout.js',
            ['position' => 'bottom', 'priority' => 160]
        );
        if (Module::isInstalled('chronopost') && Module::isEnabled('chronopost')) {
            $module_uri = _MODULE_DIR_.'chronopost';
            $this->context->controller->addCSS($module_uri.'/views/css/chronorelais.css', 'all');
            $this->context->controller->addCSS($module_uri.'/views/css/chronordv.css', 'all');
            $this->context->controller->addCSS($module_uri.'/views/css/leaflet/leaflet.css', 'all');
            $this->context->controller->addJS($module_uri.'/views/js/leaflet.js');
            if (version_compare(_PS_VERSION_, '1.7.0.0', '<')) {
                $this->context->controller->addJS($module_uri.'/views/js/chronorelais.js');
                $this->context->controller->addJS($module_uri.'/views/js/chronordv.js');
            } else {
                $this->context->controller->addJS($module_uri.'/views/js/jquery-1.11.0.min.js');
                $this->context->controller->addJS($module_uri.'/views/js/chronorelais-17.js');
                $this->context->controller->addJS($module_uri.'/views/js/chronordv-17.js');
            }
        }
        return parent::setMedia();
    }

    /**
     * @param string $canonical_url
     *
     * Don't use regular redirection to stay in module controller
     */
    protected function canonicalRedirection($canonical_url = '')
    {
        return;
    }

    /**
     * @return AmazonPayPrestaShopCheckoutSession
     */
    protected function getCheckoutSessionAmazon()
    {
        $deliveryOptionsFinder = new DeliveryOptionsFinder(
            $this->context,
            $this->getTranslator(),
            $this->objectPresenter,
            new PriceFormatter()
        );

        $session = new AmazonPayPrestaShopCheckoutSession(
            $this->context,
            $deliveryOptionsFinder
        );

        return $session;
    }
}
