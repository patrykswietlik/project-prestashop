<?php
/**
 * Class Przelewy24PaymentBlikPendingModuleFrontController
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24PaymentBlikPendingModuleFrontController
 */
class Przelewy24PaymentBlikPendingModuleFrontController extends ModuleFrontController
{
    const TIME_TO_WAIT_FOR_PAYMENT_STATUS = 15;
    const MAXIMUM_TRY = 8;

    /**
     * Redirect to finished or failed payment.
     */
    public function initContent()
    {
        parent::initContent();
        $idCart = Tools::getValue('id_cart');

        $sleep = Tools::getValue('sleep') ? Tools::getValue('sleep') : 0;

        sleep(self::TIME_TO_WAIT_FOR_PAYMENT_STATUS); // wait for payment status

        $cardOrderId = Order::getIdByCartId($idCart);
        if ($cardOrderId) {
            $order = new Order($cardOrderId);
        } else {
            $order = null;
        }

        $finalRedirect = false;
        if ($order && ((int) $order->current_state === (int) Configuration::get('P24_ORDER_STATE_2'))) {
            $finalRedirect = true;
        } else {
            if (($sleep < self::MAXIMUM_TRY)
                && !($order && ((int) $order->current_state === (int) Configuration::get('PS_OS_ERROR')))) {
                // paymentStatus not yet processed
                ++$sleep;

                $returnParamArray = ['sleep' => $sleep];
                $returnParamArray['id_cart'] = $idCart;

                Tools::redirect(
                    $this->context->link->getModuleLink(
                        'przelewy24',
                        'paymentBlikPending',
                        $returnParamArray,
                        '1' === (string) Configuration::get('PS_SSL_ENABLED')
                    )
                );
            } else {
                $finalRedirect = true;
            }
        }

        if ($finalRedirect) {
            $cart = new Cart($idCart);
            $paymentData = new Przelewy24PaymentData($cart);
            $url = Przelewy24TransactionSupport::generateReturnUrl($paymentData);
            Tools::redirect($url);
        }
    }
}
