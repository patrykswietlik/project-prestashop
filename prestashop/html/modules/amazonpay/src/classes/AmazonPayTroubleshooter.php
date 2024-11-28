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

class AmazonPayTroubleshooter
{

    protected static $orange_color_def = 'style="color:#FF9900;"';
    protected static $eye_icon_def = '<i class="fa fa-eye" aria-hidden="true"></i>&nbsp;';

    protected $troubleshooter;

    protected static $tests = array(
        array(
            'name' => 'Amazon pay activated',
            'method' => 'Activated',
            'quickcheck' => false
        ),
        array(
            'name' => 'Amazon keys provided',
            'method' => 'KeysProvided',
            'quickcheck' => true
        ),
        array(
            'name' => 'KYC passed',
            'method' => 'KYCPassed',
            'quickcheck' => true
        ),
        array(
            'name' => 'Module up to date',
            'method' => 'Versioncheck',
            'quickcheck' => true
        ),
        array(
            'name' => 'Amazon Pay hook integrity',
            'method' => 'HookIntegrity',
            'quickcheck' => false
        ),
        array(
            'name' => 'Amazon Pay module integrity',
            'method' => 'ModuleIntegrity',
            'quickcheck' => false
        ),
        array(
            'name' => 'SSL Enabled',
            'method' => 'SSLEnabled',
            'quickcheck' => true
        ),
        array(
            'name' => 'Currency Restrictions',
            'method' => 'CurrencyRestrictions',
            'quickcheck' => false
        ),
        array(
            'name' => 'Group Restrictions',
            'method' => 'GroupRestrictions',
            'quickcheck' => false
        ),
        array(
            'name' => 'Country Restrictions',
            'method' => 'CountryRestrictions',
            'quickcheck' => false
        ),
        array(
            'name' => 'Compulsory fields in datatable',
            'method' => 'CompulsoryFields',
            'quickcheck' => false
        ),
        array(
            'name' => 'Amazon Servers accessibility',
            'method' => 'ServerConnect',
            'quickcheck' => false
        ),
        /*
        array(
            'name' => 'Delivery Method module incompatibility',
            'method' => 'ModuleCheckDelivery',
        ),
        array(
            'name' => 'Express Checkout module incompatibility',
            'method' => 'ModuleCheckCheckout',
        ),
        */
    );

    protected $amazonpay;

    protected $test_details = array(
        '####MISSING_HOOKS####' => array(),
        '####MODULE_INTEGRITY####' => array(),
        '####COMPULSORY_FIELDS####' => array(),
        '####SERVER_ACCESSIBILITY####' => array(),
        '####DELIVERY_METHOD_INCOMPATIBILITY####' => array(),
        '####EXPRESS_CHECKOUT_INCOMPATIBILITY####' => array()
    );

    /**
     * AmazonPayTroubleshooter constructor.
     * @param $amazonpay
     */
    public function __construct($amazonpay)
    {
        $this->amazonpay = $amazonpay;
    }

    /**
     * @param $amazonpay
     * @throws SmartyException
     */
    public static function generateResults($amazonpay)
    {
        $troubleshooter = new self($amazonpay);
        $troubleshooter->troubleshoot();

        $url = $troubleshooter->getTroubleshooterJsonForLanguageCode();
        $json_data = $troubleshooter->requestJson($url);
        header('Content-Type: application/json');

        $results = array();
        if (isset($json_data['Troubleshooter'])) {
            foreach (self::$tests as $testnr => $test) {
                $results[] = array(
                    'title' => $json_data['Troubleshooter'][$testnr]['TestName'],
                    'status' => $test['status'],
                    'description' => $troubleshooter->prepareTroubleshooterDescription(
                        $json_data['Troubleshooter'][$testnr]['Description'],
                        $test
                    )
                );
            }
        }
        Context::getContext()->smarty->assign('troubleshooter_results', $results);
        $troubleshooter_html = Context::getContext()->smarty->fetch($amazonpay->getLocalPath() . 'views/templates/admin/troubleshooter.tpl');

        echo json_encode(array('troubleshooter' => $troubleshooter_html));
        die();
    }

    /**
     * @param $amazonpay
     * @return array
     */
    public static function generateQuickcheckResults($amazonpay)
    {
        $troubleshooter = new self($amazonpay);
        $troubleshooter->troubleshoot(true);
        $results = array();
        foreach (self::$tests as &$test) {
            if ($test['quickcheck']) {
                $results[$test['method']] = [
                    'method' => $test['method'],
                    'status' => $test['status']
                ];
            }
        }
        $results['sandbox'] = [
            'method' => 'sandbox',
            'status' => Configuration::get('AMAZONPAY_LIVEMODE') ? 0 : 1
        ];
        return $results;
    }

    /**
     * Runs troubleshooter
     * @param $quickheck
     */
    public function troubleshoot($quickheck = false)
    {
        foreach (self::$tests as &$test) {
            $method = 'ts' . $test['method'];
            $check = true;
            if ($quickheck) {
                if (!$test['quickcheck']) {
                    $check = false;
                }
            }
            if ($check) {
                if ($this->{$method}()) {
                    $test['status'] = 1;
                } else {
                    $test['status'] = 0;
                }
            }
        }
    }

    /**
     * @return bool|mixed
     */
    public function tsActivated()
    {
        return Module::isEnabled('amazonpay');
    }

    /**
     * @return bool
     */
    public function tsKeysProvided()
    {
        $keysToCheck = array(
            'merchant_id' => 'AMAZONPAY_MERCHANT_ID',
            'public_key_id' => 'AMAZONPAY_PUBLIC_KEY_ID',
            'client_id' => 'AMAZONPAY_STORE_ID',
        );
        foreach ($keysToCheck as $k) {
            if (trim(Configuration::get($k)) == '') {
                return false;
            }
        }
        /**
         * Basic check OK, now checking validity of Keys
         */
        return $this->tsKeysValid();
    }

    /**
     * @return bool
     */
    public function tsKYCPassed()
    {
        if (Configuration::get('AMAZONPAY_MERCHANT_ID') != '') {
            $checkUrl = 'https://payments-eu.amazon.com/merchantAccount/{Merchant_Id}/accountStatus?ledgerCurrency=EUR';
            if (Tools::strtolower(Configuration::get('REGION')) == 'uk') {
                $checkUrl = 'https://payments-eu.amazon.com/merchantAccount/{Merchant_Id}/accountStatus?ledgerCurrency=GBP';
            } elseif (Tools::strtolower(Configuration::get('REGION')) == 'us') {
                $checkUrl = 'https://payments.amazon.com/merchantAccount/{Merchant_Id}/accountStatus?ledgerCurrency=USD';
            } elseif (Tools::strtolower(Configuration::get('REGION')) == 'jp') {
                $checkUrl = 'https://payments-jp.amazon.com/merchantAccount/{Merchant_Id}/accountStatus?ledgerCurrency=YEN';
            }
            $checkUrl = str_replace('{Merchant_Id}', Configuration::get('AMAZONPAY_MERCHANT_ID'), $checkUrl);
            $kycData = $this->requestJson($checkUrl);
            if (isset($kycData['merchantAccountStatus'])) {
                if ($kycData['merchantAccountStatus'] == 'ACTIVE') {
                    return true;
                }
            }
            /*
            $button_url = 'https://payments.amazon.de/gp/widgets/button';
            if (Tools::strtolower(Configuration::get('REGION')) == 'uk') {
                $button_url = 'https://payments.amazon.co.uk/gp/widgets/button';
            } elseif (Tools::strtolower(Configuration::get('REGION')) == 'us') {
                $button_url = 'https://payments.amazon.com/gp/widgets/button';
            } elseif (Tools::strtolower(Configuration::get('REGION')) == 'jp') {
                $button_url = 'https://payments.amazon.co.jp/gp/widgets/button';
            }
            $check = getimagesize($button_url . "?sellerId=" . Configuration::get('AMAZONPAY_MERCHANT_ID'));
            if ($check[0] > 1) {
                return true;
            } else {
                return false;
            }
            */
        }
        return false;
    }

    /**
     * @return bool
     */
    public function tsVersioncheck()
    {
        try {
            $postData = http_build_query([
                'action' => 'native',
                'iso_code' => 'all',
                'method' => 'listing',
                'version' => _PS_VERSION_
            ]);

            $opts = [
                'http' => [
                    'method' => 'POST',
                    'content' => $postData,
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'timeout' => 3,
                ], ];
            $context = stream_context_create($opts);

            $xml_string = Tools::file_get_contents('https://api.addons.prestashop.com', false, $context);

            $module_list_xml = simplexml_load_string($xml_string);
            foreach ($module_list_xml as $module) {
                if ($module->name == 'amazonpay') {
                    $addons_version = $module->version;
                    if (version_compare($module->version, $this->amazonpay->version, '>')) {
                        return false;
                    }
                }
            }
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function tsHookIntegrity()
    {
        $have_all_hooks = true;
        foreach (Amazonpay::$hooks as $hook) {
            if (!Amazonpay::isPrestaShop16Static() && $hook == 'displayPaymentEU') {
                continue;
            }
            $id_hook = Hook::getIdByName($hook, false);
            $id_hook_alternate = Hook::getIdByName($hook, true);
            if ((int)$id_hook > 0) {
                $sql = 'SELECT `id_hook` FROM `'._DB_PREFIX_.'hook_module` WHERE `id_module` = '.(int)$this->amazonpay->id . ' AND `id_hook` IN (' . (int)$id_hook . ', ' . (int)$id_hook_alternate . ')';
                $sql_exec = Db::getInstance()->executeS($sql);
                if (!$sql_exec) {
                    $this->test_details['####MISSING_HOOKS####'][] = $hook;
                    $have_all_hooks = false;
                }
            }
        }
        return $have_all_hooks;
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function tsModuleIntegrity()
    {
        $have_integrity = true;
        if (is_dir(_PS_OVERRIDE_DIR_ . 'modules/amazonpay')) {
            $this->test_details['####MODULE_INTEGRITY####'][] = 'Overrides for module exist';
            $have_integrity = false;
        }
        if (is_dir(_PS_THEME_DIR_ . 'modules/amazonpay/views/templates')) {
            $this->test_details['####MODULE_INTEGRITY####'][] = 'Possible overrides for theme-files exist';
            $have_integrity = false;
        }
        if (!$this->checkDbForTableExists('amazonpay_transactions')) {
            $this->test_details['####MODULE_INTEGRITY####'][] = 'Missing DB table for transactions';
            $have_integrity = false;
        }
        if (!$this->checkDbForTableExists('amazonpay_orders')) {
            $this->test_details['####MODULE_INTEGRITY####'][] = 'Missing DB table for orders';
            $have_integrity = false;
        }
        if (!$this->checkDbForTableExists('amazonpay_address_reference')) {
            $this->test_details['####MODULE_INTEGRITY####'][] = 'Missing DB table for addresses';
            $have_integrity = false;
        }
        if (!$this->checkDbForTableExists('amazonpay_customer_reference')) {
            $this->test_details['####MODULE_INTEGRITY####'][] = 'Missing DB table for customers';
            $have_integrity = false;
        }
        if (!$this->checkDbForTableExists('amazonpay_ipn')) {
            $this->test_details['####MODULE_INTEGRITY####'][] = 'Missing DB table for IPN';
            $have_integrity = false;
        }

        return $have_integrity;

        /*-
         - overrides (there should be no php overrides on the amazon payments files!)
         - specific files in theme-folder that are blocking the default theme-files of the module
         - database tables correct*/
    }

    /**
     * @return string
     */
    public function tsSSLEnabled()
    {
        return Configuration::get('PS_SSL_ENABLED');
    }

    /**
     * @return array|false|mysqli_result|PDOStatement|resource|null
     * @throws PrestaShopDatabaseException
     */
    public function tsCurrencyRestrictions()
    {
        return $this->checkDbModuleRestriction('currency');
    }

    /**
     * @return array|false|mysqli_result|PDOStatement|resource|null
     * @throws PrestaShopDatabaseException
     */
    public function tsGroupRestrictions()
    {
        return $this->checkDbModuleRestriction('group');
    }

    /**
     * @return array|false|mysqli_result|PDOStatement|resource|null
     * @throws PrestaShopDatabaseException
     */
    public function tsCountryRestrictions()
    {
        return $this->checkDbModuleRestriction('country');
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    public function tsCompulsoryFields()
    {
        $return = Db::getInstance()->executeS('
		SELECT id_required_field, object_name, field_name
		FROM '._DB_PREFIX_.'required_field');
        if ($return) {
            foreach ($return as $r) {
                if ($r['object_name'] == 'CustomerAddress' && $r['field_name'] == 'phone') {
                    /**
                     * Skip, as CustomerAddress::phone is covered since Amazon Pay CV2
                     */
                    continue;
                }
                $this->test_details['####COMPULSORY_FIELDS####'][] = $r['object_name'] . ': ' . $r['field_name'];
            }
        }
        return !sizeof($this->test_details['####COMPULSORY_FIELDS####']);
    }

    /**
     * @return bool
     */
    public function tsServerConnect()
    {
        $all_available = true;
        $urls = array('https://api.amazon.com/user/profile', 'https://eu.account.amazon.com/ap/oa', 'https://mws.amazonservices.com');
        foreach ($urls as $url) {
            try {
                if (!$this->urlIsReachable($url)) {
                    $this->test_details['####SERVER_ACCESSIBILITY####'][] = $url;
                    $all_available = false;
                }
            } catch (Exception $e) {
                $all_available = false;
            }
        }
        return $all_available;
    }

    /**
     * @return bool
     */
    public function tsModuleCheckDelivery()
    {
        $return = true;
        $delivery_modules = array('cubyn', 'relaiscolis', 'relaiscolisplus', 'colissimo');
        foreach ($delivery_modules as $m) {
            if ($this->checkForModule($m)) {
                $this->test_details['####DELIVERY_METHOD_INCOMPATIBILITY####'][] = $m;
                $return = false;
            }
        }
        return $return;
    }

    /**
     * @return bool
     */
    public function tsModuleCheckCheckout()
    {
        $return = true;
        $delivery_modules = array('onepagecheckout', 'onepagecheckoutps', 'supercheckout');
        foreach ($delivery_modules as $m) {
            if ($this->checkForModule($m)) {
                $this->test_details['####EXPRESS_CHECKOUT_INCOMPATIBILITY####'][] = $m;
                $return = false;
            }
        }
        return $return;
    }

    /**
     * @return false
     */
    public function tsKeysValid()
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
        $amazonPayCheckoutSession = new AmazonPayCheckoutSession();
        if ($amazonPayCheckoutSession->checkStatus()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $module
     * @return bool
     */
    protected function checkForModule($module)
    {
        return Module::isInstalled($module);
    }

    /**
     * @param $url
     * @return bool
     */
    protected function urlIsReachable($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        $result = curl_exec($curl);
        if ($result !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $table
     * @return array|false|mysqli_result|PDOStatement|resource|null
     * @throws PrestaShopDatabaseException
     */
    protected function checkDbModuleRestriction($table)
    {
        return Db::getInstance()->executeS('
		SELECT *
		FROM '._DB_PREFIX_.'module_' . pSQL($table) . '
        WHERE id_module = \'' . (int)$this->amazonpay->id . '\'');
    }

    /**
     * @param $table
     * @return array|false|mysqli_result|PDOStatement|resource|null
     * @throws PrestaShopDatabaseException
     */
    protected function checkDbForTableExists($table)
    {
        return Db::getInstance()->executeS("SHOW TABLES LIKE '". _DB_PREFIX_ . pSQL($table) ."'");
    }

    /**
     * @param $desc
     * @param $test
     * @return string|string[]|void
     */
    protected function prepareTroubleshooterDescription($desc, $test)
    {
        if ($test['status'] == '1') {
            return;
        }
        $details = $this->test_details;
        if (is_array($details)) {
            foreach ($details as $detKey => &$detail) {
                if (sizeof($detail) > 0) {
                    $details[$detKey] = join(", ", $detail);
                } elseif (is_array($detail) && sizeof($detail) == 0) {
                    $details[$detKey] = '';
                }
            }
            $desc = str_replace(array_keys($details), array_values($details), $desc);
        }
        $desc = str_replace('<a ', ' <a ' . self::$orange_color_def . ' ', $desc);
        $desc = str_replace('target="_blank">', 'target="_blank">' . self::$eye_icon_def, $desc);
        return $desc;
    }

    /**
     * @param $url
     * @param bool $utf8_encode
     * @return array
     */
    protected function requestJson($url, $utf8_encode = false)
    {
        $c = curl_init($url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        if ($utf8_encode) {
            $r = utf8_encode(curl_exec($c));
        } else {
            $r = curl_exec($c);
        }
        if (curl_error($c)) {
            $this->amazonpay->exceptionLog(curl_error($c));
        }
        curl_close($c);
        $d = json_decode($r, true);
        return $d;
    }

    /**
     * @return string|string[]
     */
    public function getTroubleshooterJsonForLanguageCode()
    {
        $lang_code = 'uk';
        $lang_id = false;
        if (isset(Context::getContext()->employee->id_lang)) {
            $lang_id = Context::getContext()->employee->id_lang;
        }
        if ($lang_id) {
            $language = new Language((int)$lang_id);
            if (isset($language->iso_code)) {
                switch ($language->iso_code) {
                    case 'de':
                    case 'at':
                        $lang_code = 'de';
                        break;
                    case 'us':
                        $lang_code = 'us';
                        break;
                    case 'fr':
                        $lang_code = 'fr';
                        break;
                    case 'it':
                        $lang_code = 'it';
                        break;
                    case 'es':
                        $lang_code = 'es';
                        break;
                }
            }
        }
        return str_replace('%%%LANG%%%', $lang_code, AmazonPayDefinitions::$troubleshooterJson);
    }
}
