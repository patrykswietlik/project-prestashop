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

class AmazonpayRedirectModuleFrontController extends ModuleFrontController
{
    use AmazonPayRedirectionTrait;

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        AmazonPayLogger::getInstance()->addLog(
            'Calling redirect',
            3
        );
        $amazonPayCheckoutSession = new AmazonPayCheckoutSession(false);
        if ($amazonPayCheckoutSession->checkStatus()) {
            AmazonPayLogger::getInstance()->addLog(
                'Setting Payment Info for ' . $amazonPayCheckoutSession->getAmazonPayCheckoutSessionId(),
                3
            );
            $returnURL = $amazonPayCheckoutSession->setPaymentInfo($this->context->cart);
            $amazonPayOrder = AmazonPayOrder::findByCheckoutSessionId(
                $amazonPayCheckoutSession->getAmazonPayCheckoutSessionId()
            );
            $amazonPayOrder->id_cart = (int)$this->context->cart->id;
            $amazonPayOrder->save();
            if ($returnURL) {
                AmazonPayLogger::getInstance()->addLog(
                    'Redirecting to MFA for ' . $amazonPayCheckoutSession->getAmazonPayCheckoutSessionId(),
                    3
                );
                Tools::redirect($returnURL);
            } else {
                AmazonPayLogger::getInstance()->addLog(
                    'no returnURL, moving to cart',
                    2
                );
                $this->warning[] = $this->module->l('There has been an error processing your order.');
                if ($this->module->isPrestaShop16()) {
                    $this->PrestaShopRedirectWithNotifications(
                        $this->context->link->getPageLink('order')
                    );
                } else {
                    $this->PrestaShopRedirectWithNotifications(
                        $this->context->link->getPageLink('cart')
                    );
                }
            }
        } else {
            if (!$amazonPayCheckoutSession->getAmazonPayCheckoutSessionId()) {
                AmazonPayLogger::getInstance()->addLog(
                    'no Checkoutsession yet, so redirect to Amazon Pay login',
                    2
                );
                $this->PrestaShopRedirectWithNotifications(
                    $this->context->link->getModuleLink('amazonpay', 'login', ['isCo' => true])
                );
            } else {
                AmazonPayLogger::getInstance()->addLog(
                    'checkStatus negative, moving to cart',
                    2
                );
                $this->warning[] = $this->module->l('There has been an error processing your order.');
                if ($this->module->isPrestaShop16()) {
                    $this->PrestaShopRedirectWithNotifications(
                        $this->context->link->getPageLink('order')
                    );
                } else {
                    $this->PrestaShopRedirectWithNotifications(
                        $this->context->link->getPageLink('cart')
                    );
                }
            }
        }
    }
}
