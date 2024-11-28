<?php
/**
 * Class przelewy24ajaxPaymentCheckModuleFrontController
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24ajaxPaymentCheckModuleFrontController
 */
class Przelewy24ajaxPaymentCheckModuleFrontController extends ModuleFrontController
{
    /**
     * Init content.
     *
     * @return string $return
     */
    public function initContent()
    {
        parent::initContent();
        $cartId = (int) Tools::getValue('cartId');
        $order_id = Order::getOrderByCartId($cartId);
        $order = new Order($order_id);
        $return = 0;
        if ($order->hasInvoice()) {
            $return = 1;
        }

        return Przelewy24Helper::renderJson($return);
    }
}
