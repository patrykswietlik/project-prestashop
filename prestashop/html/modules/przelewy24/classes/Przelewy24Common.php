<?php
/**
 * Class Przelewy24ServicePaymentReturn
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24Common
 */
class Przelewy24Common extends Przelewy24Service
{
    public function validateOrderIfNot1(Przelewy24PaymentData $paymentData)
    {
        if (!$paymentData->orderExists()) {
            $currencySuffix = $this->getSuffix($paymentData);
            if ('1' !== Configuration::get('P24_VERIFYORDER' . $currencySuffix)) {
                $cart = $paymentData->getCart();
                $customer = new Customer((int) $cart->id_customer);
                /* This amount is without extracharge. */
                $prestaAmount = $paymentData->getTotalAmountWithoutExtraCharge();
                $prestaAmount = $paymentData->formatAmount($prestaAmount);
                $orderBeginningState = Configuration::get('P24_ORDER_STATE_1');
                $this->getPrzelewy24()->validateOrder(
                    $cart->id,
                    (int) $orderBeginningState,
                    (float) ($prestaAmount / 100),
                    'Przelewy24',
                    null,
                    [],
                    null,
                    false,
                    $customer->secure_key
                );
            }
        }
    }

    public function getSuffix(Przelewy24PaymentData $paymentData)
    {
        $currency = $paymentData->getCurrency();
        $currencySuffix = ('PLN' === $currency->iso_code) ? '' : '_' . $currency->iso_code;

        return $currencySuffix;
    }
}
