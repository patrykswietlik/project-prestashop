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

class AmazonPayHelper
{

    /**
     * Fetch region specific checkout-JS URL, default EU
     *
     * @return string
     */
    public static function getCheckoutJSURL()
    {
        if (Configuration::get('AMAZONPAY_REGION') != '') {
            return AmazonPayDefinitions::$checkoutJs[Configuration::get('AMAZONPAY_REGION')];
        }
        return AmazonPayDefinitions::$checkoutJs['EU'];
    }

    /**
     * Fetch region specific keyexchange URL, default EU
     *
     * @return string
     */
    public static function getPublicKeyURL()
    {
        if (Configuration::get('AMAZONPAY_REGION') != '') {
            return AmazonPayDefinitions::$getPublicKeyURL[Configuration::get('AMAZONPAY_REGION')];
        }
        return AmazonPayDefinitions::$getPublicKeyURL['EU'];
    }

    /**
     * @return bool
     */
    public static function isAmazonPayCheckout()
    {
        $amazonPayCheckoutSession = new AmazonPayCheckoutSession(false);
        if ($amazonPayCheckoutSession->checkStatus()) {
            return true;
        }
        return false;
    }

    /**
     * @param false $specific
     * @param false $params
     * @return bool
     * @throws PrestaShopException
     */
    public static function orderAllowed($specific = false, $params = false)
    {
        $minimalPurchase = Tools::convertPrice((float) Configuration::get('PS_PURCHASE_MINIMUM'));
        Hook::exec('overrideMinimalPurchasePrice', [
            'minimalPurchase' => &$minimalPurchase,
        ]);
        if ($specific == 'product') {
            if (is_array($params)) {
                $pPrice = Product::getPriceStatic((int)$params['id_product'], false);
            } else {
                if (!isset($params->id_product)) {
                    return false;
                }
                $pPrice = Product::getPriceStatic((int)$params->id_product, false);
            }
            if ((Context::getContext()->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS) + $pPrice) < $minimalPurchase) {
                return false;
            }
        } else {
            if (Context::getContext()->cart->getOrderTotal(false, Cart::ONLY_PRODUCTS) < $minimalPurchase) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return mixed
     */
    public static function showInCheckoutSection()
    {
        return Configuration::get('AMAZONPAY_PLACEMENT_CHECKOUT');
    }

    /**
     * @return mixed
     */
    public static function showInLoginSection()
    {
        return Configuration::get('AMAZONPAY_PLACEMENT_LOGIN');
    }

    /**
     * @return bool
     */
    public static function showStandardCheckoutPaymentOption()
    {
        return Configuration::get('AMAZONPAY_SHOW_STANDARD_PAYMENT_OPTION');
    }

    /**
     * @return bool
     */
    public static function showStandardCheckoutLogo()
    {
        return Configuration::get('AMAZONPAY_SHOW_LOGO');
    }

    /**
     * @return bool
     */
    public static function hideAmazonPayButtonInRegularCheckoutStep()
    {
        return Configuration::get('AMAZONPAY_HIDE_BUTTON_IN_REGULAR_CHECKOUT_STEP');
    }

    /**
     * @return bool
     */
    public static function isButtonHiddenMode()
    {
        return Configuration::get('AMAZONPAY_BUTTONS_HIDDEN_MODE');
    }

    /**
     * @return array
     */
    public static function getAmazonPayConfig()
    {
        $amazonpay_config = array(
            'public_key_id'         => Configuration::get('AMAZONPAY_PUBLIC_KEY_ID'),
            'private_key'           => Configuration::get('AMAZONPAY_PRIVATE_KEY'),
            'region'                => Configuration::get('AMAZONPAY_REGION'),
            'sandbox'               => Configuration::get('AMAZONPAY_LIVEMODE') ? false : true,
            'integrator_id'         => Amazonpay::$pfid,
            'integrator_version'    => Amazonpay::$plugin_version,
            'platform_version'      => _PS_VERSION_
        );
        return $amazonpay_config;
    }

    /**
     * @return AmazonPayClient
     * @throws Exception
     */
    public static function getClient()
    {
        return new AmazonPayClient(self::getAmazonPayConfig());
    }

    /**
     * May capture directly after checkout?
     *
     * @return bool
     */
    public static function captureDirectlyAfterCheckout()
    {
        return Configuration::get('AMAZONPAY_CAPTURE_MODE') == 'on_order';
    }

    /**
     * Capture at shipping?
     *
     * @return bool
     */
    public static function captureAtShipping()
    {
        return Configuration::get('AMAZONPAY_CAPTURE_MODE') == 'on_shipment';
    }

    /**
     * Return configured payment intent
     *
     * @return string
     */
    public static function getPaymentIntent()
    {
        // UPDATE 04.07.2020 - To fix bug at Amazon Pay for Optimized auth mode
        // Always return 'Authorize' if Optimized Sync mode!
        // To be removed after Amazon Pay has fixed it on their side.
        if (self::canHandlePendingAuthorization()) {
            return 'Authorize';
        }

        // Regular handling
        if (Configuration::get('AMAZONPAY_AUTH_MODE') == 'confirm') {
            return 'Confirm';
        } else {
            return 'Authorize';
        }
    }

    /**
     * @return string
     */
    public static function getMerchantReferenceId()
    {
        return 'AP' . Context::getContext()->cart->id . '-' . Tools::passwdGen(8);
    }

    /**
     * Return specific configured Order-Status-ID
     *
     * @param $type
     * @return int
     */
    public static function getStatus($type)
    {
        $status = 0;
        switch ($type) {
            case 'decline':
                $status = (int)Configuration::get('AMAZONPAY_DECLINE_STATUS_ID');
                if ($status == 0) {
                    $status = (int)Configuration::get('_PS_OS_ERROR_');
                }
                break;
            case 'authorized':
                $status = (int)Configuration::get('AMAZONPAY_AUTHORIZED_STATUS_ID');
                if ($status == 0) {
                    $status = (int)Configuration::get('PS_OS_PAYMENT');
                }
                break;
            case 'captured':
                $status = (int)Configuration::get('AMAZONPAY_CAPTURED_STATUS_ID');
                if ($status == 0) {
                    $status = (int)Configuration::get('_PS_OS_WS_PAYMENT_');
                }
                break;
        }
        return $status;
    }

    /**
     * Returns if can handle pending auth
     *
     * @return bool
     */
    public static function canHandlePendingAuthorization()
    {
        return Configuration::get('AMAZONPAY_SYNC_MODE') == '0';
    }

    /**
     * Returns currency currently set by user in frontend
     *
     * @return string
     */
    public static function getCurrentCurrency()
    {
        try {
            if (isset(Context::getContext()->currency) && isset(Context::getContext()->currency->id)) {
                $id_currency = Context::getContext()->currency->id;
                $currency = new Currency((int)$id_currency);
                if ($currency->iso_code != '') {
                    return $currency->iso_code;
                } else {
                    return 'EUR';
                }
            }
        } catch (\Exception $e) {
            return 'EUR';
        }
        return 'EUR';
    }

    /**
     * Returns currency
     *
     * @return string
     */
    public static function getLedgerCurrency()
    {
        switch (Configuration::get('AMAZONPAY_REGION')) {
            case 'UK':
                return 'GBP';
            case 'US':
                return 'USD';
            case 'JP':
                return 'JPY';
        }
        return 'EUR';
    }

    /**
     * @param $type
     * @return string
     */
    public static function getButtonColor($type)
    {
        $color = 'Gold';
        switch (Tools::strtolower($type)) {
            case 'checkout':
                $color = Configuration::get('AMAZONPAY_BUTTON_COLOR_CHECKOUT');
                break;
            case 'cart':
                $color = Configuration::get('AMAZONPAY_BUTTON_COLOR_CART');
                break;
            case 'product':
                $color = Configuration::get('AMAZONPAY_BUTTON_COLOR_PRODUCT');
                break;
        }
        return $color;
    }

    /**
     * @return bool
     */
    public static function jumpInvoiceAddress()
    {
        return Configuration::get('AMAZONPAY_JUMP_INVOICE_ADDRESS') == 1;
    }

    /**
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function getShopDefaultRegion()
    {
        $shop_default_country_id = (int)Configuration::get('PS_COUNTRY_DEFAULT');
        $country = new Country($shop_default_country_id);
        switch ($country->iso_code) {
            case 'GB':
                return 'UK';
            case 'US':
                return 'US';
            case 'JP':
                return 'JP';
        }
        return 'EU';
    }

    /**
     * Returns specific checkout Language
     *
     * @return string
     */
    public static function getCheckoutLanguage()
    {
        if (Configuration::get('AMAZONPAY_REGION') == 'UK') {
            return 'en_GB';
        } elseif (Configuration::get('AMAZONPAY_REGION') == 'US') {
            return 'en_US';
        } elseif (Configuration::get('AMAZONPAY_REGION') == 'JP') {
            return 'ja_JP';
        }
        if (in_array(Context::getContext()->language->iso_code, ['de', 'fr', 'it', 'es'])) {
            return Context::getContext()->language->iso_code . '_' . Tools::strtoupper(Context::getContext()->language->iso_code);
        }
        return 'en_GB';
    }

    /**
     * @return array
     */
    public static function getBannersForLanguageCode()
    {
        $banners =  AmazonPayDefinitions::$banners;
        switch (Context::getContext()->language->iso_code) {
            case 'de':
                return $banners['de'];
            case 'at':
                return $banners['at'];
            case 'us':
            case 'en':
                return $banners['uk'];
            case 'fr':
                return $banners['fr'];
            case 'it':
                return $banners['it'];
            case 'es':
                return $banners['es'];
            default:
                return $banners['uk'];
        }
    }

    /**
     * @param $apb
     * @return array
     * @throws Exception
     */
    public static function getButtonPayloadAndSignature($apb = false)
    {
        $session = new AmazonPayCheckoutSession(false);
        $client = self::getClient();
        $payload = json_encode($session->createNewCheckoutSession(true, $apb), JSON_UNESCAPED_UNICODE);
        return [
            'payload' => $payload,
            'signature' => $client->generateButtonSignature($payload)
        ];
    }

    /**
     * @param bool $formatted
     * @return mixed|string|void
     */
    public static function getPaymentDescriptor($formatted = true)
    {
        $session = new AmazonPayCheckoutSession(false);
        if ($session->checkStatus()) {
            $info = $session->getInformation();
            if (isset($info['paymentPreferences']) && is_array($info['paymentPreferences'])) {
                if (isset($info['paymentPreferences'][0]['paymentDescriptor'])) {
                    if ($formatted) {
                        return ' (' . trim(str_replace('(Amazon Pay)', '', $info['paymentPreferences'][0]['paymentDescriptor'])) . ')';
                    }
                    return $info['paymentPreferences'][0]['paymentDescriptor'];
                }
            }
        }
    }

    /**
     * @param false $toCheckout
     * @return array
     * @throws Exception
     */
    public static function getLoginOnlyButtonPayloadAndSignature($toCheckout = false)
    {
        if ($toCheckout) {
            $signInReturnUrl = Context::getContext()->link->getModuleLink('amazonpay', 'processlogin', array('toCheckout' => '1'));
        } else {
            $signInReturnUrl = Context::getContext()->link->getModuleLink('amazonpay', 'processlogin');
        }
        $client = self::getClient();
        $payload = json_encode(
            array(
                'signInReturnUrl' => $signInReturnUrl,
                'storeId' => Configuration::get('AMAZONPAY_STORE_ID'),
                'signInScopes' => array(
                    "name", "email", "postalCode"
                )
            )
        );
        return [
            'payload' => $payload,
            'signature' => $client->generateButtonSignature($payload)
        ];
    }

    /**
     * @param $iso_code
     * @return bool
     */
    public static function isValidAmazonDeliveryCountry($iso_code)
    {
        return in_array(Tools::strtoupper($iso_code), AmazonPayDefinitions::$valid_country_codes);
    }

    /**
     * @param $country
     * @return bool
     */
    public static function hasAtLeastOneCarrier($country)
    {
        $cache_id = 'AmazonPayHelper::hasAtLeastOneCarrier' . (int) $country['id_country'];
        if (!Cache::isStored($cache_id)) {
            $countries = Carrier::getDeliveredCountries(Context::getContext()->language->id, true, true);
            if (isset($countries[$country['id_country']])) {
                Cache::store($cache_id, '1');
                return true;
            }
            Cache::store($cache_id, '0');
            return false;
        }
        return Cache::retrieve($cache_id) == '1';
    }

    /**
     * @return bool
     */
    public static function useCache()
    {
        return Configuration::get('AMAZONPAY_USE_OWN_CACHE') == '1';
    }

    /**
     * @param $number
     * @return string
     */
    public static function formatNumberForAmazon($number)
    {
        return number_format($number, 2, '.', '');
    }
}
