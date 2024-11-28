<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class SumuppaymentgatewayPaymentModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        if ($this->module->isPs17) {
            if (!$this->container) {
                $this->container = $this->buildContainer();
            }
        }

        if (Tools::isSubmit('submitValidateOrder')) {
            $responce = Tools::getAllValues();
            $responce = json_decode(json_encode($responce));

            try {
                $checkoutId = Tools::getValue('id');

                $checkoutReference = $responce->checkout_reference;
                $id_card = $this->context->cart->id;

                $retrievedCheckoutBody = $this->module->retrieveCheckout($checkoutId);
                $retrievedCheckoutId = $retrievedCheckoutBody->id;
                $retrievedCheckoutReference = $retrievedCheckoutBody->checkout_reference;
                $retrievedCheckoutStatus = $retrievedCheckoutBody->status;

                $retrievedCard = explode('id_card=', $retrievedCheckoutReference);
                $retrievedCardId = (int) end($retrievedCard);

                if ($retrievedCheckoutReference !== $checkoutReference || $retrievedCheckoutId !== $checkoutId || $id_card !== $retrievedCardId) {
                    $msg = $this->l('SumUp has an error on confirmation order process: Can not create order.');
                    PrestaShopLogger::addLog($msg, 3, null, 'Cart', $id_card, true);

                    return $this->displayError('payment_error');
                }

                if ($responce->status === 'PAID' && $retrievedCheckoutStatus === 'PAID') {
                    return $this->confirmOrder($responce);
                }

                return $this->displayError('payment_error');
            } catch (SumUp\Exceptions\SumUpSDKException $e) {
                return $this->displayError('payment_error');
            }
        }

        $link = new Link();
        $checkoutId = Tools::getValue('checkoutId');
        $amount = Tools::getValue('amount');
        $currency = Tools::getValue('currency');
        $paymentUrl = $link->getModuleLink('sumuppaymentgateway', 'payment');
        $this->context->smarty->assign('secure_key', Context::getContext()->customer->secure_key);
        $this->context->smarty->assign('zip_code', (bool) Configuration::get('SUMUP_ZIP_CODE'));
        $this->context->smarty->assign('paymentControllerLink', $paymentUrl);
        $this->context->smarty->assign('checkoutId', $checkoutId);
        $this->context->smarty->assign('locale', $this->module->getLocale());
        $this->context->smarty->assign('paymentCurrency', $currency);
        $this->context->smarty->assign('paymentAmount', $amount);

        $templateName = 'sumup_payment_page.tpl';

        if ($this->module->isPs17) {
            $templateName = 'module:sumuppaymentgateway/views/templates/front/sumup_payment_page17.tpl';
        }

        return $this->setTemplate($templateName);
    }

    private function confirmOrder($responce)
    {
        $cart = new Cart((int) Context::getContext()->cart->id);
        $customer = new Customer((int) $cart->id_customer);
        $paidPrice = $responce->amount;

        $msg = 'SumUp confirm order';
        PrestaShopLogger::addLog($msg, 1, null, 'Cart', (int) $cart->id, true);

        $payment_status = (int) Configuration::get('PS_OS_PAYMENT');

        $message = ''; // You can add a comment directly into the order so the merchant will see it in the BO.
        $message .= 'Transact Code: ' . $responce->transaction_code . ' - ';
        $message .= 'Transact Id: ' . $responce->transaction_id . ' - ';
        $message .= 'Checkout Id: ' . $responce->id . ' - ';
        $message .= 'Checkout Reference: ' . $responce->checkout_reference;
        /**
         * Converting cart into a valid order
         */
        $module_name = $this->module->displayName;
        $currency_id = (int) Context::getContext()->currency->id;
        $extra_vars = [];
        $extra_vars['transaction_id'] = $responce->transaction_code;
        $this->module->validateOrder(Context::getContext()->cart->id, $payment_status, $paidPrice, $module_name, $message, $extra_vars, $currency_id, false, $customer->secure_key);
        $order_id = (int) Order::getOrderByCartId((int) $cart->id);

        if (isset($order_id)) {
            $msg = 'SumUp successfully confirm order';
            PrestaShopLogger::addLog($msg, 1, null, 'Cart', (int) Context::getContext()->cart->id, true);
            $module_id = $this->module->id;
            Tools::redirect('index.php?controller=order-confirmation&id_cart=' . Context::getContext()->cart->id . '&id_module=' . $module_id . '&id_order=' . $order_id . '&key=' . $customer->secure_key);
        } else {
            $msg = 'SumUp has an error on confirmation order process: Can not create order.';
            PrestaShopLogger::addLog($msg, 3, null, 'Cart', (int) Context::getContext()->cart->id, true);

            return $this->displayError('order_create_err');
        }
    }

    public function displayError($message_index, $notIndex = false)
    {
        if (!$notIndex) {
            $message = $this->module->getErrorMessage($message_index);
        } else {
            $message = $message_index;
        }

        $this->context->smarty->assign('error', $message);
        if ($this->module->isPs17) {
            return $this->setTemplate('module:sumuppaymentgateway/views/templates/front/sumup_payment_error17.tpl');
        }

        return $this->setTemplate('sumup_payment_error.tpl');
    }
}
