<?php
/**
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Przelewy24TransactionSupport
{
    /**
     * Register transaction.
     *
     * @param Przelewy24PaymentData $paymentData
     * @param string $description
     * @param string $languageIsoCode
     * @param int|null $method
     *
     * @return Przelewy24TransactionRegistrationResult
     */
    public function registerTransaction(Przelewy24PaymentData $paymentData, $description, $languageIsoCode, $regulationAccept = false, $method = 0)
    {
        $currency = $paymentData->getCurrency();
        $suffix = ('PLN' === $currency->iso_code) ? '' : '_' . $currency->iso_code;
        $restApi = Przelewy24RestTransactionFactory::buildForSuffix($suffix);
        $payload = $this->getPayload($paymentData, $description, $languageIsoCode, $regulationAccept, $method);
        $token = $restApi->registerRawToken($payload);

        return new Przelewy24TransactionRegistrationResult($token, $payload->sign);
    }

    /**
     * Get plain payload to register transaction.
     *
     * @param Przelewy24PaymentData $paymentData
     * @param string $description
     * @param string $languageIsoCode
     * @param int|null mixed $method
     *
     * @return Przelewy24PayloadForRestTransaction
     */
    public function getPayload(Przelewy24PaymentData $paymentData, $description, $languageIsoCode, $regulationAccept = false, $method = 0)
    {
        $cart = $paymentData->getCart();
        $currency = $paymentData->getCurrency();
        $suffix = ('PLN' === $currency->iso_code) ? '' : '_' . $currency->iso_code;

        $amountFloat = $paymentData->getTotalAmountWithExtraCharge();
        $amount = $paymentData->formatAmount($amountFloat);

        $addressHelper = new Przelewy24AddressHelper($cart);
        $address = new Address((int) $addressHelper->getBillingAddress()['id_address']);

        $customer = new Customer((int) $cart->id_customer);

        $customerName = $customer->firstname . ' ' . $customer->lastname;

        $returnUrl = self::generateReturnUrl($paymentData);
        $statusUrl = Context::getContext()->link->getModuleLink(
            'przelewy24',
            'paymentStatus',
            ['status' => 'REST'],
            '1' === (string) Configuration::get('PS_SSL_ENABLED')
        );

        $payload = new Przelewy24PayloadForRestTransaction();
        $payload->merchantId = (int) Configuration::get('P24_MERCHANT_ID' . $suffix);
        $payload->posId = (int) Configuration::get('P24_SHOP_ID' . $suffix);
        $payload->sessionId = $cart->id . '|' . hash('sha224', rand());
        $payload->amount = (int) $amount;
        $payload->currency = $currency->iso_code;
        $payload->description = (string) $description;
        $payload->email = (string) $customer->email;
        $payload->client = $customerName;
        $payload->address = $address->address1 . ' ' . $address->address2;
        $payload->zip = (string) $address->postcode;
        $payload->city = (string) $address->city;
        $payload->country = Country::getIsoById((int) $address->id_country);
        $payload->language = $languageIsoCode;
        $payload->method = $method ? (int) $method : null;
        $payload->urlReturn = $returnUrl;
        $payload->urlStatus = $statusUrl;
        $payload->shipping = 0;
        $payload->waitForResult = (int) Configuration::get('P24_WAIT_FOR_RESULT' . $suffix);
        $payload->regulationAccept = (bool) $regulationAccept;
        $payload->encoding = 'UTF-8';

        return $payload;
    }

    /**
     * Generate return URL.
     *
     * @param Przelewy24PaymentData $paymentData
     * @return string
     */
    public static function generateReturnUrl(Przelewy24PaymentData $paymentData, $redirectToConfirmation = false)
    {
        $cart = $paymentData->getCart();

        if ($paymentData->orderExists()) {
            $currency = $paymentData->getCurrency();
            $suffix = ('PLN' === $currency->iso_code) ? '' : '_' . $currency->iso_code;
            $custom = '1' !== (string) Configuration::get('P24_USE_STANDARD_RETURN_PAGE' . $suffix);
        } else {
            $custom = true;
        }

        $link = Context::getContext()->link;
        $ssl = '1' === (string) Configuration::get('PS_SSL_ENABLED');
        if ($custom || $redirectToConfirmation) {
            $url = $link->getModuleLink(
                'przelewy24',
                'paymentFinished',
                ['id_cart' => $cart->id, 'confirmation' => (int) $redirectToConfirmation],
                $ssl
            );
        } else {
            $module = Module::getInstanceByName('przelewy24');
            $customer = new Customer((int) $cart->id_customer);
            $url = $link->getPageLink(
                'order-confirmation',
                $ssl,
                null,
                [
                    'id_cart' => $cart->id,
                    'id_module' => $module->id,
                    'id_order' => $paymentData->getFirstOrderId(),
                    'key' => $customer->secure_key,
                ]
            );
        }

        return $url;
    }

    /**
     * Generate return URL for BLIK.
     *
     * @param Przelewy24PaymentData $paymentData
     * @return string
     */
    public static function generateReturnUrlForBlik(Przelewy24PaymentData $paymentData)
    {
        $cart = $paymentData->getCart();

        $link = Context::getContext()->link;
        $ssl = '1' === (string) Configuration::get('PS_SSL_ENABLED');

        $url = $link->getModuleLink(
            'przelewy24',
            'paymentBlikPending',
            ['id_cart' => $cart->id],
            $ssl
        );

        return $url;
    }
}
