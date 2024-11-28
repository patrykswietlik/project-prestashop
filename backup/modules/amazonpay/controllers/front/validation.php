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

class AmazonpayValidationModuleFrontController extends ModuleFrontController
{
    use AmazonPayRedirectionTrait;

    /**
     * Additional Payment Button integration true/false
     * @var bool
     */
    protected $is_apb = false;

    public function postProcess()
    {
        if (Tools::getValue('apb') == '1' && Tools::getValue('amazonCheckoutSessionId')) {
            $amazonPayCheckoutSession = new AmazonPayCheckoutSession(false, Tools::getValue('amazonCheckoutSessionId'));
            $this->is_apb = true;
        } else {
            if (!isset(Context::getContext()->cookie->amazon_pay_checkout_session_id) ||
                Tools::getValue('amazonCheckoutSessionId') != Context::getContext()->cookie->amazon_pay_checkout_session_id) {
                AmazonPayLogger::getInstance()->addLog(
                    'Not authorized call',
                    1,
                    false,
                    [
                        'getValue' => Tools::getValue('amazonCheckoutSessionId'),
                        'cookie' => (
                        isset(Context::getContext()->cookie->amazon_pay_checkout_session_id) ?
                            Context::getContext()->cookie->amazon_pay_checkout_session_id : false
                        )
                    ]
                );
                $this->errors[] = $this->module->l('There has been an error processing your order.');
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
            $amazonPayCheckoutSession = new AmazonPayCheckoutSession();
        }

        AmazonPayLogger::getInstance()->addLog(
            'AmazonPayCheckoutSession complete call: ' . $amazonPayCheckoutSession->getAmazonPayCheckoutSessionId(),
            3
        );
        $complete = $amazonPayCheckoutSession->complete(
            $amazonPayCheckoutSession->getAmazonPayCheckoutSessionId(),
            Tools::ps_round(Context::getContext()->cart->getOrderTotal(true, Cart::BOTH), 2),
            AmazonPayHelper::getCurrentCurrency()
        );

        AmazonPayLogger::getInstance()->addLog(
            'AmazonPayCheckoutSession fetched: ' . $amazonPayCheckoutSession->getAmazonPayCheckoutSessionId(),
            3
        );
        if (($amazonPayCheckoutSession->isCompleted() || $amazonPayCheckoutSession->isOpenAndInAmazonPayBugWorkarround()) && $complete) {
            AmazonPayLogger::getInstance()->addLog('Starting order validation', 3);

            $cart_id = Context::getContext()->cart->id;
            $currency_id = Context::getContext()->cart->id_currency;
            $customer = Context::getContext()->customer;
            $secure_key = $customer->secure_key;

            $payment_status = Configuration::get('PS_OS_PAYMENT');
            $amount = Tools::ps_round(Context::getContext()->cart->getOrderTotal(true, Cart::BOTH), 2);
            $amazonPayOrder = AmazonPayOrder::findByCheckoutSessionId(
                $amazonPayCheckoutSession->getAmazonPayCheckoutSessionId()
            );

            if (!$amazonPayCheckoutSession->isOpenAndInAmazonPayBugWorkarround()) {
                AmazonPayLogger::getInstance()->addLog('AmazonPayCheckoutSession isCompleted = true, Fetching and saving Auth Info', 3);

                $payment_status = AmazonPayHelper::getStatus('authorized');
                $chargeinfo = $amazonPayCheckoutSession->getCharge();

                if (isset($chargeinfo['chargeId']) && $chargeinfo['chargeId'] != '') {
                    $amount = $chargeinfo['info']['chargeAmount']['amount'];
                    $currency_code = $chargeinfo['info']['chargeAmount']['currencyCode'];
                }

                $amazonPayOrder->amazon_charge_permission_id = $chargeinfo['chargePermissionId'];
                $amazonPayOrder->chargeId = $chargeinfo['chargePermissionId'];
                AmazonPayLogger::getInstance()->addLog('Save Amazon Pay order object', 3);
                $amazonPayOrder->save();

                AmazonPayTransaction::store(
                    $amazonPayOrder->amazon_checkout_session_id,
                    $chargeinfo['chargePermissionId'],
                    'chargePermission',
                    $amount
                );
            } else {
                AmazonPayLogger::getInstance()->addLog('AmazonPayCheckoutSession still in Open State and Optimized mode', 3);
                $payment_status = AmazonPayHelper::getStatus('authorized');
                AmazonPayLogger::getInstance()->addLog('Save Amazon Pay order object', 3);
                $amazonPayOrder->save();
            }

            AmazonPayLogger::getInstance()->addLog(
                'Calling validateOrder method',
                2,
                false,
                [
                    'params' => [
                        'id_cart' =>  $cart_id,
                        'id_order_state' => $payment_status,
                        'amount_paid' => $amount,
                        'payment_method' => 'Amazon Pay',
                        'message' => '',
                        'extra_vars' => [],
                        'currency_special' => $currency_id,
                        'dont_touch_amount' => false,
                        'secure_key' => $secure_key
                    ]
                ]
            );
            try {
                $this->module->validateOrder(
                    $cart_id,
                    $payment_status,
                    $amount,
                    'Amazon Pay',
                    '',
                    array(),
                    $currency_id,
                    false,
                    $secure_key
                );
            } catch (Exception $e) {
                AmazonPayLogger::getInstance()->addLog('Order could not be validated', 1, $e);
                $this->errors[] = $this->module->l('There has been an error processing your order.');
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
            AmazonPayLogger::getInstance()->addLog(
                'Order successfully validated, Order-ID: ' . (int)$this->module->currentOrder,
                3
            );

            $amazonPayOrder->id_order = (int)$this->module->currentOrder;
            $amazonPayOrder->save();

            $order = new Order($this->module->currentOrder);

            try {
                $amazonPayCheckoutSession->updateOrderReference(
                    $amazonPayOrder->chargeId,
                    $order->reference
                );
            } catch (Exception $e) {
                AmazonPayLogger::getInstance()->addLog('updateOrderReference not successful', 1, $e);
            }

            try {
                Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'order_payment`
                                                   SET `transaction_id` = \'' . pSQL($amazonPayOrder->chargeId) . '\'
                                                 WHERE `order_reference` = \'' . pSQL($order->reference) . '\'');
            } catch (Exception $e) {
                AmazonPayLogger::getInstance()->addLog('setting transaction_id in order_payment Table not successful', 2, $e);
            }

            if ($this->is_apb) {
                $amazonPayOrder->amazon_charge_id = $chargeinfo['chargeId'];
                $amazonPayOrder->save();
                if (AmazonPayHelper::captureAtShipping()) {
                    $amazonPayOrder->setOrderStatus(AmazonPayHelper::getStatus('authorized'));
                    AmazonPayTransaction::store(
                        $amazonPayCheckoutSession->getAmazonPayCheckoutSessionId(),
                        $chargeinfo['chargeId'],
                        'chargePermission',
                        $amount
                    );
                } else {
                    $amazonPayOrder->setOrderStatus(AmazonPayHelper::getStatus('captured'));
                    AmazonPayTransaction::store(
                        $amazonPayCheckoutSession->getAmazonPayCheckoutSessionId(),
                        $chargeinfo['chargeId'],
                        'capture',
                        $amount
                    );
                }
            } else {
                if (AmazonPayHelper::captureDirectlyAfterCheckout() && $amazonPayCheckoutSession->isCompleted()) {
                    try {
                        $amazonPayCheckoutSession->capture($chargeinfo['chargeId'], $amount, $currency_code, $order->reference);
                        $amazonPayOrder->amazon_charge_id = $chargeinfo['chargeId'];
                        $amazonPayOrder->save();
                        $amazonPayOrder->setOrderStatus(AmazonPayHelper::getStatus('captured'));
                        AmazonPayLogger::getInstance()->addLog(
                            'Order successfully captured, Charge-ID: ' . $chargeinfo['chargeId'],
                            3
                        );
                    } catch (Exception $e) {
                        AmazonPayLogger::getInstance()->addLog('Order could not be captured', 1, $e);
                    }
                }
            }

            AmazonPayLogger::getInstance()->addLog('Resetting AmazonPayCheckoutSession', 3);
            $amazonPayCheckoutSession->reset();
            $confirmationURL = 'index.php?controller=order-confirmation&id_cart=' .
                (int)$this->context->cart->id .
                '&id_module=' . (int)$this->module->id .
                '&id_order=' . $this->module->currentOrder .
                '&key=' . $customer->secure_key;
            AmazonPayLogger::getInstance()->addLog(
                'Redirecting to confirmation URL: ' . $confirmationURL,
                3
            );
            Tools::redirect($confirmationURL);
        } else {
            AmazonPayLogger::getInstance()->addLog(
                'AmazonPayCheckoutSession not completed: ' . $amazonPayCheckoutSession->getAmazonPayCheckoutSessionId(),
                1
            );
            $info = $amazonPayCheckoutSession->getInformation();
            if (isset($info['statusDetails']['state'])) {
                if ($info['statusDetails']['state'] == 'Canceled') {
                    if ($info['statusDetails']['reasonCode'] == 'Declined') {
                        $errorMessage = $this->module->l('The transaction has been declined, please try to use another payment method.', 'validation');
                    } else {
                        $errorMessage = $this->module->l('The transaction has been canceled, you can try it again.', 'validation');
                    }
                } elseif ($info['statusDetails']['state'] == 'Declined') {
                    $errorMessage = $this->module->l('The transaction has been declined, please try to use another payment method.', 'validation');
                }
            }
            if (isset($errorMessage)) {
                $this->errors[] = $this->module->l($errorMessage);
            } else {
                $this->errors[] = $this->module->l('There has been an error processing your order.');
            }
            AmazonPayLogger::getInstance()->addLog('Resetting AmazonPayCheckoutSession', 3);
            $amazonPayCheckoutSession->reset();
            if ($this->module->isPrestaShop16()) {
                $this->PrestaShopRedirectWithNotifications(
                    $this->context->link->getPageLink('order')
                );
            } else {
                $this->warning = $this->errors;
                $this->errors = [];
                $this->PrestaShopRedirectWithNotifications(
                    $this->context->link->getPageLink('cart')
                );
            }
        }
    }
}
