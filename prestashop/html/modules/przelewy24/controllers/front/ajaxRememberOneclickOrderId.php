<?php
/**
 * Class przelewy24ajaxRememberOneclickOrderIdModuleFrontController
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/gpl.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24ajaxRememberOneclickOrderIdModuleFrontController
 */
class Przelewy24ajaxRememberOneclickOrderIdModuleFrontController extends ModuleFrontController
{
    /**
     * Init content.
     */
    public function initContent()
    {
        $data = [];
        try {
            $orderId = $this->sanitizeOrderId(Tools::getValue('orderId'));
            $sessionId = $this->sanitizeSessionId(Tools::getValue('sessionId'));
            $sign = $this->sanitizeSign(Tools::getValue('sign'));
            $signValidation = $this->checkSign($sign, $sessionId);
            if ($orderId && $sessionId && $sign && $signValidation) {
                $data['success'] = 1;
                $data['error'] = '';
            } else {
                $data['success'] = 0;
                $data['error'] = 'wrong data';
            }
        } catch (Exception $e) {
            $data['success'] = 0;
            $data['error'] = 'Data not added';
        }
        Przelewy24Helper::renderJson($data);
    }

    /**
     * Sanitize order id.
     *
     * @param int|string $orderId
     *
     * @return int|bool
     */
    private function sanitizeOrderId($orderId)
    {
        if (9 === Tools::strlen($orderId) || 0 === (int) $orderId) {
            return (int) $orderId;
        } else {
            return false;
        }
    }

    /**
     * Sanitize session id.
     *
     * @param string $sessionId
     *
     * @return string|bool
     */
    private function sanitizeSessionId($sessionId)
    {
        if (Tools::strlen($sessionId) > 0 && filter_var($sessionId, FILTER_SANITIZE_STRING)) {
            return filter_var($sessionId, FILTER_SANITIZE_STRING);
        } else {
            return false;
        }
    }

    /**
     * Sanitizes sign parameter.
     *
     * @param string $sign
     *
     * @return string|bool
     */
    private function sanitizeSign($sign)
    {
        if (Tools::strlen($sign) > 0 && Tools::strlen($sign) <= 100 && filter_var($sign, FILTER_SANITIZE_STRING)) {
            return filter_var($sign, FILTER_SANITIZE_STRING);
        } else {
            return false;
        }
    }

    /**
     * Checks sign correctness.
     *
     * @param string $sign
     * @param string $sessionId
     *
     * @return bool
     */
    private function checkSign($sign, $sessionId)
    {
        list($cartId) = explode('|', $sessionId, 1);

        $cartId = (int) $cartId;

        $cart = new Cart($cartId);
        $currency = new Currency($cart->id_currency);
        $suffix = Przelewy24Helper::getSuffix($currency->iso_code);

        $amount = Przelewy24Helper::p24AmountFormat($cart->getOrderTotal(true, Cart::BOTH));
        $extrachargeFloat = Przelewy24ServicePaymentOptions::getExtrachargeStatic($amount / 100, $suffix);
        $extracharge = (int) round($extrachargeFloat * 100);
        $amount += $extracharge;
        $currency = new Currency($cart->id_currency);
        $suffix = Przelewy24Helper::getSuffix($currency->iso_code);
        $merchantId = Configuration::get('P24_MERCHANT_ID' . $suffix);
        $salt = Configuration::get('P24_SALT' . $suffix);
        $countedSign = md5($sessionId . '|' . $merchantId . '|' . $amount . '|' . $currency->iso_code . '|' . $salt);

        if ($sign === $countedSign) {
            return true;
        }

        return false;
    }
}
