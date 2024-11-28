<?php
/**
 * Class Przelewy24OrderDescriptionHelper
 *
 * @author    Przelewy24
 * @copyright Przelewy24
 * @license   https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24OrderDescriptionHelper
 */
class Przelewy24OrderDescriptionHelper
{
    /**
     * @param string $orderTranslated
     * @param string $suffix
     * @param Przelewy24PaymentData $paymentData
     * @param string $fallbackName fallback when order is made after payment
     *
     * @return string
     */
    public static function buildDescription($orderTranslated, $suffix, $paymentData, $fallbackName = '')
    {
        $orderReference = $paymentData->getOrderReference();
        $getFirstOrderId = $paymentData->getFirstOrderId();
        $orderNumberSuffix = ('1' === Configuration::get('P24_ORDER_TITLE_ID' . $suffix))
            ? $paymentData->getOrderReference()
            : $paymentData->getFirstOrderId();

        if (empty($orderReference) && empty($getFirstOrderId)) {
            return $fallbackName;
        }

        return $orderTranslated . ' ' . $orderNumberSuffix;
    }

    public static function buildDescriptionConfigured($orderTranslated, $cartTranslated, $suffix, $paymentData)
    {
        if ($paymentData->orderExists()) {
            $orderNumberSuffix = ('1' === Configuration::get('P24_ORDER_TITLE_ID' . $suffix))
                ? $paymentData->getOrderReference()
                : $paymentData->getFirstOrderId();

            return $orderTranslated . ' ' . $orderNumberSuffix;
        } else {
            return $cartTranslated . ' ' . $paymentData->getCart()->id;
        }
    }
}
