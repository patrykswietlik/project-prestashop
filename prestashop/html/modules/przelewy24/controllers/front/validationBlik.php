<?php
/**
 * Class przelewy24validationBlikModuleFrontController
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24validationBlikModuleFrontController
 */
class Przelewy24validationBlikModuleFrontController extends ModuleFrontController
{
    /**
     * Post process.
     */
    public function postProcess()
    {
        if (preg_match('/[^a-z_\-0-9 ]/i', Tools::getValue('p24_blik_code'))
            || preg_match('/[^a-z_\-0-9 ]/i', Tools::getValue('type'))) {
            // zwrócić odpowiedź z httpStatus zamiast die
            exit('Invalid string');
        }

        $cart = $this->context->cart;

        $currency = $this->context->currency;
        if ((0 === (int) $cart->id_customer)
            || (0 === (int) $cart->id_address_delivery)
            || (0 === (int) $cart->id_address_invoice)
            || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);
        $idOrderState = Configuration::get('P24_ORDER_STATE_1');
        $cartId = (int) $cart->id;
        $this->module->validateOrder(
            $cartId,
            $idOrderState,
            $total,
            $this->module->displayName,
            null,
            [],
            (int) $currency->id,
            false,
            $customer->secure_key
        );

        $paymentMethod = '&blik_type=UID'; // default is UID
        $toolsType = Tools::getValue('type');
        if (!empty($toolsType)) {
            $paymentMethod = '&blik_type=' . Tools::getValue('type');
        }

        if (Tools::getValue('p24_blik_code')) {
            $blikCode = '&blik_code=' . Tools::getValue('p24_blik_code');
        }

        /* PrestaShop require us to clear the cart for the next step. */
        unset($this->context->cookie->id_cart);

        Tools::redirect(
            'index.php?controller=order-confirmation&id_cart=' . $cartId .
            '&id_module=' . (int) $this->module->id .
            '&id_order=' . $this->module->currentOrder .
            '&key=' . $customer->secure_key .
            $paymentMethod . (isset($blikCode) ? $blikCode : '')
        );
    }
}
