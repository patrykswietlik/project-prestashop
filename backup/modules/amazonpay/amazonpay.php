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

require_once(dirname(__FILE__) . '/vendor/autoload.php');
require_once(dirname(__FILE__) . '/src/classes/AmazonPayLogger.php');
require_once(dirname(__FILE__) . '/src/classes/AmazonPayDefinitions.php');
require_once(dirname(__FILE__) . '/src/classes/AmazonPayAddress.php');
require_once(dirname(__FILE__) . '/src/classes/AmazonPayAddressLegacy.php');
require_once(dirname(__FILE__) . '/src/classes/AmazonPayCheckoutSession.php');
require_once(dirname(__FILE__) . '/src/classes/AmazonPayClient.php');
require_once(dirname(__FILE__) . '/src/classes/AmazonPayIPNHandler.php');
require_once(dirname(__FILE__) . '/src/classes/AmazonPayCronHandler.php');
require_once(dirname(__FILE__) . '/src/classes/AmazonPayKeyShareHandler.php');
require_once(dirname(__FILE__) . '/src/classes/AmazonPayTroubleshooter.php');
require_once(dirname(__FILE__) . '/src/traits/AmazonPayFrontendHooksTrait.php');
require_once(dirname(__FILE__) . '/src/traits/AmazonPayBackendHooksTrait.php');
require_once(dirname(__FILE__) . '/vendor/getOrigin.php');
require_once(dirname(__FILE__) . '/vendor/redirectionTrait.php');
require_once(dirname(__FILE__) . '/vendor/encodeDecode.php');
require_once(dirname(__FILE__) . '/src/classes/Entities/AmazonPayAddressReference.php');
require_once(dirname(__FILE__) . '/src/classes/Entities/AmazonPayCache.php');
require_once(dirname(__FILE__) . '/src/classes/Entities/AmazonPayCustomerReference.php');
require_once(dirname(__FILE__) . '/src/classes/Entities/AmazonPayOrder.php');
require_once(dirname(__FILE__) . '/src/classes/Entities/AmazonPayTransaction.php');
require_once(dirname(__FILE__) . '/src/classes/Entities/AmazonPayIPN.php');
require_once(dirname(__FILE__) . '/src/classes/Helper/AmazonPayAdminActionsHelper.php');
require_once(dirname(__FILE__) . '/src/classes/Helper/AmazonPayHelper.php');
require_once(dirname(__FILE__) . '/src/classes/Helper/AmazonPayAdminConfigFormHelper.php');
require_once(dirname(__FILE__) . '/src/classes/Helper/AmazonPayFormHelper.php');
require_once(dirname(__FILE__) . '/src/classes/Helper/AmazonPayPostalCodesHelper.php');
require_once(dirname(__FILE__) . '/src/classes/Helper/AmazonPayRestrictedProductsHelper.php');
require_once(dirname(__FILE__) . '/src/classes/Helper/AmazonPaySimplePathHelper.php');
require_once(dirname(__FILE__) . '/src/classes/Helper/AmazonPayCustomerHelper.php');
require_once(dirname(__FILE__) . '/src/classes/Helper/AmazonPayCV1Upgrade.php');

class Amazonpay extends PaymentModule
{
    use AmazonPayFrontendHooksTrait;
    use AmazonPayRedirectionTrait;
    use AmazonPayBackendHooksTrait;

    public static $plugin_version = '4.2.3';
    public static $pfid = 'A1AOZCKI9MBRZA';
    public static $pfid_uk = 'A3MHK0FZHD51XE';
    public static $pfid_us = 'A3E4E925IS54TU';

    protected $config_form = false;
    protected $this_file = __FILE__;

    public static $hooks = [
        'displayOverrideTemplate',
        'displayBackOfficeFooter',
        'displayExpressCheckout',
        'displayCustomerLoginFormAfter',
        'displayPersonalInformationTop',
        'displayProductActions',
        'displayProductAdditionalInfo',
        'displayProductButtons',
        'displayAdminOrderContentOrder',
        'displayAdminOrderTabContent',
        'displayShoppingCartFooter',
        'displayFooter',
        'displayBanner',
        'actionFrontControllerSetMedia',
        'header',
        'backOfficeHeader',
        'payment',
        'paymentReturn',
        'paymentOptions',
        'displayPaymentEU',
        'actionDispatcher',
        'actionAdminOrdersTrackingNumberUpdate',
        'actionAdminControllerSetMedia',
        'actionCustomerLogoutAfter',
        'adminOrder'
    ];

    /**
     * Amazonpay constructor.
     */
    public function __construct()
    {
        $this->name = 'amazonpay';
        $this->tab = 'payments_gateways';
        $this->author = 'patworx multimedia GmbH';
        $this->version = '4.2.3';
        $this->need_instance = 1;
        $this->module_key = '26d778fa5cb6735a816107ce4345b32d';
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Amazon Pay (Checkout v2)');
        $this->description = $this->l('Amazon Pay (Checkout v2) Integration for your PrestaShop');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * @return bool
     */
    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }

        include(dirname(__FILE__).'/sql/install.php');

        $install = parent::install();
        $hooks = $this->registerHooks();

        if (!$this->isPrestaShop16()) {
            $hookId = Hook::getIdByName('displayPaymentEU');
            if ($hookId) {
                $this->unregisterHook($hookId);
            }
        }
        $defaults = $this->setDefaults();

        try {
            Configuration::set('AMAZONPAY_LOGLEVEL', 2);
            AmazonPayCV1Upgrade::migrate($this);
            Configuration::set('AMAZONPAY_LOGLEVEL', 1);
        } catch (\Exception $e) {
            // silent
        }

        return $install && $hooks && $defaults && $this->moveToFirst();
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function moveToFirst()
    {
        $hooks = [
            'payment', 'paymentOptions', 'paymentReturn', 'displayExpressCheckout'
        ];
        if ($this->isPrestaShop16()) {
            $hooks[] = 'displayPaymentEU';
        }
        foreach ($hooks as $hook) {
            $hookId = Hook::getIdByName($hook);
            if ($hookId) {
                $this->updatePosition(
                    (int)$hookId,
                    0,
                    1
                );
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');
        return parent::uninstall();
    }

    /**
     * @param $var
     * @return |null
     */
    public function getVar($var)
    {
        if (isset($this->$var)) {
            return $this->$var;
        }
        return null;
    }

    /**
     * @return bool
     */
    public function registerHooks()
    {
        $return = true;
        foreach (self::$hooks as $hook) {
            $registerHook = $this->registerHook($hook);
            if (!$registerHook) {
                $return = false;
            }
        }
        return $return;
    }

    /**
     * sets default config values
     */
    public function setDefaults()
    {
        Configuration::updateValue('AMAZONPAY_LIVEMODE', false);
        Configuration::updateValue('AMAZONPAY_PRIVATE_KEY', false);
        Configuration::updateValue('AMAZONPAY_PUBLIC_KEY', false);
        Configuration::updateValue('AMAZONPAY_AUTH_MODE', 'authorize');
        Configuration::updateValue('AMAZONPAY_CAPTURE_MODE', 'on_order');
        Configuration::updateValue('AMAZONPAY_SYNC_MODE', '1');
        Configuration::updateValue('AMAZONPAY_LOGLEVEL', 1);
        Configuration::updateValue('AMAZONPAY_BUTTONS_HIDDEN_MODE', 0);
        Configuration::updateValue('AMAZONPAY_PLACEMENT_MINICART', 1);
        Configuration::updateValue('AMAZONPAY_PLACEMENT_CHECKOUT', 1);
        Configuration::updateValue('AMAZONPAY_PLACEMENT_PRODUCT', 1);
        Configuration::updateValue('AMAZONPAY_PLACEMENT_LOGIN', 1);
        Configuration::updateValue('AMAZONPAY_SHOW_STANDARD_PAYMENT_OPTION', 1);
        Configuration::updateValue('AMAZONPAY_SHOW_LOGO', 1);
        Configuration::updateValue('AMAZONPAY_RESTRICTED_CATEGORIES', '');
        Configuration::updateValue('AMAZONPAY_CARRIERS_MAPPING', '');
        Configuration::updateValue('AMAZONPAY_PROMO_HEADER', false);
        Configuration::updateValue('AMAZONPAY_PROMO_PRODUCT', true);
        Configuration::updateValue('AMAZONPAY_PROMO_FOOTER', true);
        Configuration::updateValue('AMAZONPAY_PROMO_HEADER_STYLE', false);
        Configuration::updateValue('AMAZONPAY_PROMO_PRODUCT_STYLE', false);
        Configuration::updateValue('AMAZONPAY_PROMO_FOOTER_STYLE', false);
        Configuration::updateValue('AMAZONPAY_ALEXA_DELIVERY_NOTIFICATIONS', false);
        Configuration::updateValue('AMAZONPAY_HIDE_BUTTON_IN_REGULAR_CHECKOUT_STEP', 1);
        Configuration::updateValue('AMAZONPAY_REGION', AmazonPayHelper::getShopDefaultRegion());
        return true;
    }

    /**
     * @return string
     * @throws SmartyException
     */
    public function getContent()
    {
        if (Tools::getValue('getPublicKey') == 'true') {
            $public_key = Configuration::get('AMAZONPAY_PUBLIC_KEY');
            header('Content-Type: application/download');
            header('Content-Disposition: attachment; filename="pubkey.key"');
            header("Content-Length: " . Tools::strlen($public_key));
            header("Pragma: no-cache");
            header("Expires: 0");
            echo $public_key;
            exit();
        }
        if (Tools::getValue('getLogFile') == 'true') {
            $logContent = AmazonPayLogger::getInstance()->getLogContent();
            header('Content-Type: application/download');
            header('Content-Disposition: attachment; filename="amazonpay_log.txt"');
            header("Content-Length: " . Tools::strlen($logContent));
            header("Pragma: no-cache");
            header("Expires: 0");
            echo $logContent;
            exit();
        }
        if (Tools::getValue('troubleshooter') == 'true') {
            AmazonPayTroubleshooter::generateResults($this);
        }
        if (Tools::getValue('createKeypair') == 'true') {
            if ($this->generateKeypair()) {
                $this->context->smarty->assign('keygen_success', true);
            } else {
                $this->context->smarty->assign('keygen_error', true);
            }
        }
        if (Configuration::get('AMAZONPAY_PRIVATE_KEY_TMP') != '') {
            Configuration::updateValue('AMAZONPAY_PRIVATE_KEY', trim(Configuration::get('AMAZONPAY_PRIVATE_KEY_TMP')));
            Configuration::deleteByName('AMAZONPAY_PRIVATE_KEY_TMP');
        }

        if (((bool)Tools::isSubmit('submitAmazonpayModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign(
            'troubleshooter',
            $this->getAdminLink(
                'AdminModules',
                true,
                array(),
                array(
                    'configure' => $this->name,
                    'tab_module' => $this->tab,
                    'module_name' => $this->name,
                    'troubleshooter' => 'true'
                )
            )
        );
        $this->context->smarty->assign(
            'log_url',
            $this->getAdminLink(
                'AdminModules',
                true,
                array(),
                array(
                    'configure' => $this->name,
                    'tab_module' => $this->tab,
                    'module_name' => $this->name,
                    'getLogFile' => 'true'
                )
            )
        );
        if (Configuration::get('AMAZONPAY_PUBLIC_KEY') != '' && self::$plugin_version == '1.0.0') {
            $this->context->smarty->assign(
                'create_keypair_action',
                $this->getAdminLink(
                    'AdminModules',
                    true,
                    array(),
                    array(
                        'configure' => $this->name,
                        'createKeypair' => 'true'
                    )
                )
            );
            $this->context->smarty->assign(
                'getPublicKeyLink',
                $this->getAdminLink(
                    'AdminModules',
                    true,
                    array(),
                    array(
                        'configure' => $this->name,
                        'tab_module' => $this->tab,
                        'module_name' => $this->name,
                        'getPublicKey' => 'true'
                    )
                )
            );
        }

        switch ($this->context->language->iso_code) {
            case 'de':
                $videoamazonyoutube = 'https://www.youtube.com/embed/KjMYIXMETc0?rel=0&showinfo=0';
                $youtube_video_embed_link = 'https://www.youtube.com/embed/CkJ4bs9_8xY?rel=0&showinfo=0';
                $blog_link = 'https://view.highspot.com/viewer/5dcdbd4734d6be7a55b36e42';
                $help_page_link = 'https://pay.amazon.de/help/202137070';
                $ref_link = 'https://pay.amazon.de/help/6023';
                break;
            case 'en':
                $videoamazonyoutube = 'https://www.youtube.com/embed/KjMYIXMETc0?rel=0&showinfo=0';
                $youtube_video_embed_link = 'https://www.youtube.com/embed/Xc-81od6zn8?rel=0&showinfo=0';
                $blog_link = 'https://view.highspot.com/viewer/5dcdbdf26a3b11141aad5eb5';
                $help_page_link = 'https://pay.amazon.co.uk/help/202137070';
                $ref_link = 'https://pay.amazon.co.uk/help/6023';
                break;
            case 'fr':
                $videoamazonyoutube = 'https://www.youtube.com/embed/KjMYIXMETc0?rel=0&showinfo=0';
                $youtube_video_embed_link = 'https://www.youtube.com/embed/KjMYIXMETc0?rel=0&showinfo=0';
                $blog_link = 'https://view.highspot.com/viewer/5dcdbd29f7794d243b160ef5';
                $help_page_link = 'https://pay.amazon.fr/help/202137070';
                $ref_link = 'https://pay.amazon.fr/help/6023';
                break;
            case 'it':
                $videoamazonyoutube = 'https://www.youtube.com/embed/KjMYIXMETc0?rel=0&showinfo=0';
                $youtube_video_embed_link = 'https://www.youtube.com/embed/EscvWeQbBtM?rel=0&showinfo=0';
                $blog_link = 'https://view.highspot.com/viewer/5dcdbd79b7b73943847b3d44';
                $help_page_link = 'https://pay.amazon.it/help/202137070';
                $ref_link = 'https://pay.amazon.it/help/6023';
                break;
            case 'es':
                $videoamazonyoutube = 'https://www.youtube.com/embed/KjMYIXMETc0?rel=0&showinfo=0';
                $youtube_video_embed_link = 'https://www.youtube.com/embed/Xc-81od6zn8?rel=0&showinfo=0';
                $blog_link = 'https://view.highspot.com/viewer/5dcdbd92c247914302bebfc9';
                $help_page_link = 'https://pay.amazon.es/help/202137070';
                $ref_link = 'https://pay.amazon.es/help/6023';
                break;
            default:
                $videoamazonyoutube = 'https://www.youtube.com/embed/KjMYIXMETc0?rel=0&showinfo=0';
                $youtube_video_embed_link = 'https://www.youtube.com/embed/Xc-81od6zn8?rel=0&showinfo=0';
                $blog_link = 'https://view.highspot.com/viewer/5dcdbdf26a3b11141aad5eb5';
                $help_page_link = 'https://pay.amazon.co.uk/help/202137070';
                $ref_link = 'https://pay.amazon.co.uk/help/6023';
                break;
        }
        $ref_link = '(' . $this->l('Reference:') . ' <a href="' . $ref_link . '" target="_blank">' . $this->l('Acceptable Use Policy') . '</a>' . ')';

        $this->context->smarty->assign('videoamazonyoutube', $videoamazonyoutube);
        $this->context->smarty->assign('blog_link', $blog_link);
        $this->context->smarty->assign('help_page_link', $help_page_link);
        $this->context->smarty->assign('youtube_video_embed_link', $youtube_video_embed_link);
        $this->context->smarty->assign('ref_link', $ref_link);
        $this->context->smarty->assign('language_code', $this->context->language->iso_code);
        $this->context->smarty->assign('ipn_url', $this->getIPNControllerUrl());
        $this->context->smarty->assign('cron_url', $this->getCronControllerUrl());
        $this->context->smarty->assign('pubkey_link', Configuration::get('AMAZONPAY_PUBLIC_KEY') != '' ? true : false);
        $this->context->smarty->assign('alexa_region', Configuration::get('AMAZONPAY_REGION'));
        $this->context->smarty->assign('alexa_public_key', $this->getPublicKeyString());
        $this->context->smarty->assign('amz_carrier_options', $this->getCarrierOptions());
        $this->context->smarty->assign('mapped_carriers', $this->getMappedCarriers());
        $this->context->smarty->assign('amazon_carriers', $this->getAmazonCarriers());
        $this->context->smarty->assign('simple_path', $this->getSimplePathVars());
        $this->context->smarty->assign('simplepath_form_url', 'https://payments-eu.amazon.com/register');
        if (AmazonPayHelper::getCheckoutLanguage() == 'en_US') {
            $this->context->smarty->assign('simplepath_form_url', 'https://payments.amazon.com/register');
        }

        $forms = AmazonPayAdminConfigFormHelper::renderForm($this);
        $this->context->smarty->assign('authform', $forms['auth']);
        $this->context->smarty->assign('configform', $forms['config']);
        $this->context->smarty->assign('expertform', $forms['expert']);
        $this->context->smarty->assign('alexaform', $forms['alexa']);

        $this->context->smarty->assign('quickcheck', AmazonPayTroubleshooter::generateQuickcheckResults($this));

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/amazonpay_admin.tpl');

        return $output;
    }

    /**
     * @param $pkey
     * @return bool|string
     */
    public function getPublicKeyString()
    {
        return Configuration::get('AMAZONPAY_PUBLIC_KEY');
    }

    /**
     * @return string
     */
    public function getIPNControllerUrl()
    {
        return $this->context->link->getModuleLink($this->name, 'ipn');
    }

    /**
     * @return string
     */
    public function getCronControllerUrl()
    {
        return $this->context->link->getModuleLink($this->name, 'cron');
    }

    /**
     * @return array
     */
    protected function getSimplePathVars()
    {
        $simplePathHelper = new AmazonPaySimplePathHelper($this);
        return $simplePathHelper->getSimplePathVars();
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = AmazonPayAdminConfigFormHelper::getConfigFormValues();

        if (isset($_FILES['AMAZONPAY_PRIVATE_KEY_UPLOAD']['name']) &&
            !empty($_FILES['AMAZONPAY_PRIVATE_KEY_UPLOAD']['name']) &&
            !empty($_FILES['AMAZONPAY_PRIVATE_KEY_UPLOAD']['tmp_name'])) {
            $keyContent = Tools::file_get_contents(
                $_FILES['AMAZONPAY_PRIVATE_KEY_UPLOAD']['tmp_name']
            );
            if (trim($keyContent) != '') {
                Configuration::updateValue('AMAZONPAY_PRIVATE_KEY', trim($keyContent));
            }
        }

        foreach (array_keys($form_values) as $key) {
            if (!is_null(Tools::getValue($key, null))) {
                if ($key == 'AMAZONPAY_CARRIERS_MAPPING') {
                    Configuration::updateValue($key, trim(json_encode(Tools::getValue($key))));
                } elseif ($key == 'AMAZONPAY_RESTRICTED_CATEGORIES') {
                    Configuration::updateValue($key, join(',', Tools::getValue($key)));
                } elseif ($key == 'AMAZONPAY_PRIVATE_KEY') {
                    if (trim(Tools::getValue($key)) != '[Secret key]' &&
                        trim(Tools::getValue($key)) != '') {
                        Configuration::updateValue($key, trim(Tools::getValue($key)));
                    }
                } else {
                    Configuration::updateValue($key, trim(Tools::getValue($key)));
                }
            } elseif ($key == 'AMAZONPAY_RESTRICTED_CATEGORIES' && is_null(Tools::getValue($key, null))) {
                Configuration::updateValue($key, join(',', []));
            }
        }
    }

    /**
     * @return bool
     */
    protected function generateKeypair()
    {
        try {
            $config = array(
                "digest_alg" => "sha512",
                "private_key_bits" => 4096,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            );

            $res = openssl_pkey_new($config);

            openssl_pkey_export($res, $privKey);
            $pubKey = openssl_pkey_get_details($res);
            $pubKey = $pubKey["key"];

            if ($pubKey != '' && $privKey != '') {
                Configuration::updateValue('AMAZONPAY_PUBLIC_KEY', $pubKey);
                Configuration::updateValue('AMAZONPAY_PRIVATE_KEY', $privKey);
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * Add JS Defs
     */
    protected function addJsDef()
    {
        if ($this->isReadyForFrontend()) {
            $payloadAndSignature = AmazonPayHelper::getButtonPayloadAndSignature();
            $payloadAndSignatureApb = AmazonPayHelper::getButtonPayloadAndSignature(true);
            $loginOnlyPayloadAndSignature = AmazonPayHelper::getLoginOnlyButtonPayloadAndSignature();
            $loginOnlyToCheckoutPayloadAndSignature = AmazonPayHelper::getLoginOnlyButtonPayloadAndSignature(true);
        } else {
            $payloadAndSignature = ['payload' => '', 'signature' => ''];
            $payloadAndSignatureApb = ['payload' => '', 'signature' => ''];
            $loginOnlyPayloadAndSignature = ['payload' => '', 'signature' => ''];
            $loginOnlyToCheckoutPayloadAndSignature = ['payload' => '', 'signature' => ''];
        }

        Media::addJsDef(
            [
                'amazonpay' => [
                    'is_prestashop16' => $this->isPrestaShop16() ? true : false,
                    'merchant_id' => Configuration::get('AMAZONPAY_MERCHANT_ID'),
                    'public_key_id' => Configuration::get('AMAZONPAY_PUBLIC_KEY_ID'),
                    'amazonCheckoutSessionId' => isset(Context::getContext()->cookie->amazon_pay_checkout_session_id) ? Context::getContext()->cookie->amazon_pay_checkout_session_id : false,
                    'isInAmazonPayCheckout' => AmazonPayHelper::isAmazonPayCheckout() ? 'true' : 'false',
                    'loginButtonCode' => $this->getLoginButtonCode(),
                    'showInCheckoutSection' => AmazonPayHelper::showInCheckoutSection() ? 'true' : 'false',
                    'showInLoginSection' => AmazonPayHelper::showInLoginSection() ? 'true' : 'false',
                    'amazonPayCheckoutSessionURL' => $this->context->link->getModuleLink($this->name, 'createcheckoutsession'),
                    'amazonPayCheckoutSetDeliveryOptionURL' => $this->context->link->getModuleLink($this->name, 'checkout', ['ajax' => 1, 'action' => 'selectDeliveryOption']),
                    'amazonPayCheckoutAddressFormAction' => $this->context->link->getModuleLink($this->name, 'checkout'),
                    'amazonPayCheckoutRefreshAddressFormURL' => $this->context->link->getModuleLink($this->name, 'checkout', ['ajax' => 1, 'action' => 'addressForm']),
                    'sandbox' => Configuration::get('AMAZONPAY_LIVEMODE') ? false : true,
                    'customerCurrencyCode' => AmazonPayHelper::getCurrentCurrency(),
                    'estimatedOrderAmount' => AmazonPayHelper::formatNumberForAmazon(Context::getContext()->cart->getOrderTotal()),
                    'ledgerCurrency' => AmazonPayHelper::getLedgerCurrency(),
                    'checkoutType' => $this->context->cart->isVirtualCart() ? 'PayOnly' : 'PayAndShip',
                    'checkoutLanguage' => AmazonPayHelper::getCheckoutLanguage(),
                    'button_payload' => AmazonPayVendorEncodeDecode::stripslashes($payloadAndSignature['payload']),
                    'button_signature' => $payloadAndSignature['signature'],
                    'button_payload_apb' => AmazonPayVendorEncodeDecode::stripslashes($payloadAndSignatureApb['payload']),
                    'button_signature_apb' => $payloadAndSignatureApb['signature'],
                    'login_button_payload' => AmazonPayVendorEncodeDecode::stripslashes($loginOnlyPayloadAndSignature['payload']),
                    'login_button_signature' => $loginOnlyPayloadAndSignature['signature'],
                    'login_to_checkout_button_payload' => AmazonPayVendorEncodeDecode::stripslashes($loginOnlyToCheckoutPayloadAndSignature['payload']),
                    'login_to_checkout_button_signature' => $loginOnlyToCheckoutPayloadAndSignature['signature'],
                    'legacy_address_form_action' =>  $this->context->link->getModuleLink($this->name, 'setaddresslegacy', ['amazonCheckoutSessionId' => Context::getContext()->cookie->amazon_pay_checkout_session_id])
                ]
            ]
        );
    }

    /**
     * @return bool
     */
    protected function isReadyForFrontend()
    {
        if (Configuration::get('AMAZONPAY_MERCHANT_ID') == '') {
            return false;
        }
        if (Configuration::get('AMAZONPAY_PUBLIC_KEY_ID') == '') {
            return false;
        }
        if (Configuration::get('AMAZONPAY_STORE_ID') == '') {
            return false;
        }
        $config = AmazonPayHelper::getAmazonPayConfig();
        $key_spec = $config['private_key'];
        if ((strpos($key_spec, 'BEGIN RSA PRIVATE KEY') === false) && (strpos($key_spec, 'BEGIN PRIVATE KEY') === false)) {
            if (!file_exists($key_spec)) {
                return false;
            }
            $contents = Tools::file_get_contents($key_spec);
            if ($contents === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $cart
     * @return bool
     */
    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function getMappedCarriers()
    {
        $mapped_carriers_config = Configuration::get('AMAZONPAY_CARRIERS_MAPPING');
        try {
            $mapped_carriers = json_decode($mapped_carriers_config, true);
        } catch (Exception $e) {
            $mapped_carriers = array();
        }
        return $mapped_carriers;
    }

    /**
     * @return array
     */
    protected function getAmazonCarriers()
    {
        $csv = array_map(
            'str_getcsv',
            file(dirname(__FILE__) . '/vendor/amazon-pay-delivery-tracker-supported-carriers.csv')
        );
        if (isset($csv[0]) && $csv[0][0] == 'carrierName') {
            unset($csv[0]);
        }
        return $csv;
    }

    /**
     * @return array
     */
    public function getCarrierOptions()
    {
        $ret = array();
        $carriers = Carrier::getCarriers(
            Configuration::get('PS_LANG_DEFAULT'),
            false,
            false,
            false,
            null,
            Carrier::ALL_CARRIERS
        );
        foreach ($carriers as $carrier) {
            $ret[] = array('id' => 'carrier_' . $carrier['id_carrier'] . '_on',
                'value' => $carrier['id_carrier'],
                'val' => $carrier['id_carrier'],
                'label' => $carrier['name']
            );
        }
        return $ret;
    }

    /**
     * @param $carrier_id
     * @param bool $parent_carrier_id
     * @return bool
     */
    public function getMappedCarrier($carrier_id, $parent_carrier_id = false)
    {
        $mapped_carriers = $this->getMappedCarriers();
        foreach ($mapped_carriers as $mapped_carrier_id => $mapped_amazon_carrier) {
            if ($mapped_carrier_id == $carrier_id || $mapped_carrier_id == $parent_carrier_id) {
                return $mapped_amazon_carrier;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function getViewPath()
    {
        return dirname(__FILE__) . '/';
    }

    /**
     * @return bool
     */
    public static function isPrestaShop16Static()
    {
        return (version_compare(_PS_VERSION_, '1.7.0', '<') || Tools::substr(_PS_VERSION_, 0, 3) == '1.6');
    }

    /**
     * @return bool
     */
    public static function isPrestaShop176Static()
    {
        return version_compare(_PS_VERSION_, '1.7.6', '>=');
    }

    /**
     * @return bool
     */
    public static function isPrestaShop177OrHigherStatic()
    {
        return version_compare(_PS_VERSION_, '1.7.7', '>=');
    }

    /**
     * @return bool
     */
    public function isPrestaShop16()
    {
        return self::isPrestaShop16Static();
    }

    /**
     * @return bool
     */
    public function isPrestaShop176()
    {
        return self::isPrestaShop176Static();
    }

    /**
     * @return bool
     */
    public function isPrestaShop177OrHigher()
    {
        return self::isPrestaShop177OrHigherStatic();
    }

    /**
     * @return string
     */
    public function getLoginButtonCode()
    {
        $this->context->smarty->assign('loginButtonId', 'AMAZONPAYLOGIN_' . time() . rand(100, 100000));
        return $this->display($this->this_file, 'views/templates/hook/loginButton.tpl');
    }

    /**
     * @return bool
     */
    private function amazonPayV2SupportsProductLevel()
    {
        return true;
    }

    /**
     * @param $controller
     * @param bool $withToken
     * @param array $sfRouteParams
     * @param array $params
     * @return string
     */
    protected function getAdminLink($controller, $withToken = true, $sfRouteParams = array(), $params = array())
    {
        if ($this->isPrestaShop16()) {
            $param_string = '';
            foreach ($params as $pK => $pV) {
                $param_string.= '&' . $pK . '=' . $pV;
            }
            return Context::getContext()->link->getAdminLink(
                $controller,
                $withToken
            ) . $param_string;
        } else {
            return Context::getContext()->link->getAdminLink(
                $controller,
                $withToken,
                $sfRouteParams,
                $params
            );
        }
    }

    /**
     * Check if module onepagecheckoutps from PresTeamShop is installed and activated.
     *
     * @return bool
     */
    public function isOnePageCheckoutPSInstalled()
    {
        if (Module::isInstalled('onepagecheckoutps')) {
            $onepagecheckoutps = Module::getInstanceByName('onepagecheckoutps');
            if (Validate::isLoadedObject($onepagecheckoutps) && $onepagecheckoutps->active) {
                if ($onepagecheckoutps->core->isVisible()) {
                    if (method_exists($onepagecheckoutps, 'isCheckoutBetaEnabled') && $onepagecheckoutps->isCheckoutBetaEnabled()) {
                        return false;
                    }
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if new version V5 of module onepagecheckoutps from PresTeamShop is installed and activated.
     *
     * @return bool
     */
    public function isOnePageCheckoutPSV5Installed()
    {
        if (Module::isInstalled('onepagecheckoutps')) {
            $onepagecheckoutps = Module::getInstanceByName('onepagecheckoutps');
            if (Validate::isLoadedObject($onepagecheckoutps) && $onepagecheckoutps->active) {
                if ($onepagecheckoutps->core->isVisible()) {
                    if (method_exists($onepagecheckoutps, 'isCheckoutBetaEnabled') && $onepagecheckoutps->isCheckoutBetaEnabled()) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Check if module thecheckout from Zelarg is installed and activated.
     *
     * @return bool
     */
    public function isTheCheckoutInstalled()
    {
        if (Module::isInstalled('thecheckout')) {
            $thecheckout = Module::getInstanceByName('thecheckout');
            if (Validate::isLoadedObject($thecheckout) && $thecheckout->active) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if module supercheckout from Knowband is installed and activated.
     *
     * @return bool
     */
    public function isSupercheckoutInstalled()
    {
        if (Module::isInstalled('supercheckout')) {
            $thecheckout = Module::getInstanceByName('supercheckout');
            if (Validate::isLoadedObject($thecheckout) && $thecheckout->active) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $addressdata
     * @return mixed
     *
     * empty method to be used in individual overrides, to manipulate address datasets if needed
     *
     */
    public static function parseAddressdata($addressdata)
    {
        return $addressdata;
    }

}
