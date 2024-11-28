<?php
/**
 * 2017-2022 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    MBE Worldwide
 * @copyright 2017-2024 MBE Worldwide
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of MBE Worldwide
 */

use Firebase\JWT\JWT;
use PrestaShop\Module\Mbeshipping\Helper\DataHelper;
use PrestaShop\Module\Mbeshipping\Ws;

if (!defined('_PS_VERSION_')) {
    exit;
}

/*
 * FOR TESTING PURPOSES ONLY
 * */
const MBESHIPPING_DEBUG_MODE = false;
const MBESHIPPING_TEST_MODE = false;
const MBESHIPPING_TEST_ENDPOINT = 'https://api.dev.mbehub.cloud.mbeglobal.io';


const MBESHIPPING_USER_ROLES = ['DIRECT_CHANNEL_USER', 'ONLINEMBE_USER'];
const STANDARD_TAX_RATES = [
    'IT' => 22.000,
    'ES' => 21.000,
    'DE' => 19.000,
    'FR' => 20.000,
    'AT' => 20.000,
    'PL' => 23.000,
    'HR' => 25.000,
];

class AuthAPI
{
    public static $apiEndpoints = [
        'IT' => [
            'endpoint' => 'https://api.mbeonline.it',
            'webservice' => 'https://api.mbeonline.it/ws/e-link.wsdl'
        ],
        'ES' => [
            'endpoint' => 'https://api.mbeonline.es',
            'webservice' => 'https://api.mbeonline.es/ws/e-link.wsdl'
        ],
        'DE' => [
            'endpoint' => 'https://api.mbeonline.de',
            'webservice' => 'https://api.mbeonline.de/ws/e-link.wsdl'
        ],
        'FR' => [
            'endpoint' => 'https://api.mbeonline.fr',
            'webservice' => 'https://api.mbeonline.fr/ws/e-link.wsdl'
        ],
        'AT' => [
            'endpoint' => 'https://api.mbeonline.de',
            'webservice' => 'https://api.mbeonline.de/ws/e-link.wsdl'
        ],
        'PL' => [
            'endpoint' => 'https://api.mbeonline.pl',
            'webservice' => 'https://api.mbeonline.pl/ws/e-link.wsdl'
        ],
        'HR' => [
            'endpoint' => 'https://api.mbeonline.hr',
            'webservice' => 'https://api.mbeonline.hr/ws/e-link.wsdl'
        ],
        'UK' => [
            'endpoint' => 'https://api.mbeonline.uk',
            'webservice' => 'https://api.mbeonline.uk/ws/e-link.wsdl'
        ],
    ];

    protected $apiEndpoint;
    protected $username;
    protected $password;
    protected $mbe_country;
    protected $ws_url;

    public function __construct()
    {
        $mbe_credentials = json_decode(Configuration::get('MBESHIPPING_CREDENTIALS'));
        $this->apiEndpoint = MBESHIPPING_TEST_MODE ? MBESHIPPING_TEST_ENDPOINT : self::$apiEndpoints[$mbe_credentials->mbecountry]['endpoint'];
        $this->username = $mbe_credentials->mbe_user;
        $this->password = $mbe_credentials->mbe_pass;
        $this->mbe_country = $mbe_credentials->mbecountry;
        $this->ws_url = self::$apiEndpoints[$mbe_credentials->mbecountry]['webservice'];
    }

    public static function hasEnteredCredentials()
    {
        return !empty(Configuration::get('username')) && !empty(Configuration::get('password'));
    }

    public static function isAuthenticated()
    {
        $ws = new Ws();
        $customer = $ws->getCustomer(true);

        return !empty($customer);
    }

    public static function getAccessToken($refresh_token = null)
    {
        $authAPI = new self();

        $initial_token = $refresh_token ? $authAPI->refreshAccessToken($refresh_token) : $authAPI->step1();

        if (empty($initial_token)) {
            return false;
        }

        if (empty($customer_data = $authAPI->step2($initial_token))) {
            return false;
        }

        if (empty($access_token = $authAPI->step3($initial_token, $customer_data))) {
            return false;
        }

        return $access_token;
    }

    public function retrieveAPIKeys()
    {
        self::doLog(__METHOD__ . ' - Processing STEP 1...');
        if (empty($bearer_token = $this->step1())) {
            self::doLog(__METHOD__ . ' - Error on step 1');
            return false;
        }
        self::doLog(__METHOD__ . ' - Step 1 OK');

        self::doLog(__METHOD__ . ' - Processing STEP 2...');
        if (empty($customer_data = $this->step2($bearer_token))) {
            self::doLog(__METHOD__ . ' - Error on step 2');
            return false;
        }
        self::doLog(__METHOD__ . ' - Step 2 OK');

        self::doLog(__METHOD__ . ' - Processing STEP 3...');
        if (empty($final_token = $this->step3($bearer_token, $customer_data))) {
            self::doLog(__METHOD__ . ' - Error on step 3');
            return false;
        }
        self::doLog(__METHOD__ . ' - Step 3 OK');

        $id_entity = json_decode($customer_data)->idEntity;
        if (empty($id_entity)) {
            return false;
        }

        $user_role = Configuration::get('MBESHIPPING_USER_ROLE');
        if (empty($user_role) || !in_array($user_role, MBESHIPPING_USER_ROLES)) {
            return false;
        }

        self::doLog(__METHOD__ . ' - Processing STEP 4...');
        if (empty($api_keys = $this->step4($final_token, $id_entity, $user_role))) {
            self::doLog(__METHOD__ . ' - STEP 4 failed!');
            return false;
        }
        self::doLog(__METHOD__ . ' - Step 4 OK');

        if (empty($api_keys->apiKey) || empty($api_keys->apiSecret)) {
            if ($api_keys->code !== 'APIKEY_ROLE_LEGAL_ENTITY_ALREADY_EXISTS') {
                return false;
            }

            if (empty($existing_api_key = $this->retrieveExistingApiKey($final_token, $id_entity))) {
                return false;
            }

            if ($this->deleteExistingAPIKey($final_token, $existing_api_key) == 200) {
                return $this->retrieveAPIKeys();
            }

            return false;
        }

        MBESHIPPING_TEST_MODE ? Configuration::updateValue('url', MBESHIPPING_TEST_ENDPOINT . '/ws/e-link.wsdl') : Configuration::updateValue('url', $this->ws_url);

        Configuration::updateValue('username', $api_keys->apiKey);
        Configuration::updateValue('password', $api_keys->apiSecret);
        Configuration::updateValue('mbecountry', $this->mbe_country);
        (new Ws())->getCustomer(true);

        return true;
    }

    private function step1()
    {
        $data = [
            'grant_type' => 'password',
            'username' => $this->username,
            'password' => $this->password
        ];

        $headers = [
            'Accept: application/json, text/plain, */*',
            'Accept-Language: it,it-IT;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
            'Authorization: Basic dGVsZXBvcnQtZmU6',
            'Connection: keep-alive',
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.53 Safari/537.36 Edg/103.0.1264.37',
            'cache-control: no-cache'
        ];

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            if ($curl === false) {
                return false;
            }

            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($curl, CURLOPT_URL, $this->apiEndpoint . '/oauth/token');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($curl);
            $info = curl_getinfo($curl);

            self::doLog(print_r($info, true));
            self::doLog(print_r($result, true));

            if ($result === false || curl_errno($curl) != 0) {
                return false;
            }

            curl_close($curl);

            if (!isset(json_decode($result)->access_token)) {
                return false;
            }

            return json_decode($result)->access_token;
        } catch (Exception $e) {
            return false;
        }
    }

    private function step2($bearer_token)
    {
        $headers = [
            'Accept: application/json, text/plain, */*',
            'Accept-Language: it,it-IT;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
            'Authorization: Bearer ' . $bearer_token,
            'Connection: keep-alive',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.53 Safari/537.36 Edg/103.0.1264.37',
            'cache-control: no-cache'
        ];

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            if ($curl === false) {
                return false;
            }

            curl_setopt($curl, CURLOPT_URL, $this->apiEndpoint . '/oauth/mine');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($curl);

            //self::doLog(__METHOD__ . ' - RESULT: ' . print_r($result, true));

            if ($result === false || curl_errno($curl) != 0) {
                return false;
            }

            curl_close($curl);
            // remove []
            return Tools::substr($result, 1, -1);
        } catch (Exception $e) {
            return false;
        }
    }

    private function step3($bearer_token, $customer_data)
    {
        $headers = [
            'Accept: application/json, text/plain, */*',
            'Accept-Language: it,it-IT;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
            'Authorization: Bearer ' . $bearer_token,
            'Connection: keep-alive',
            'Content-Type: application/json',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.53 Safari/537.36 Edg/103.0.1264.37',
            'cache-control: no-cache'
        ];

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            if ($curl === false) {
                return false;
            }

            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $customer_data);
            curl_setopt($curl, CURLOPT_URL, $this->apiEndpoint . '/oauth/select-store');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($curl);

            if ($result === false || curl_errno($curl) != 0) {
                return false;
            }

            curl_close($curl);

            $decoded = json_decode($result);
            if (!isset($decoded->access_token)) {
                $this->saveTokens(0, 0);
                return false;
            }

            $this->saveTokens($decoded->access_token, $decoded->refresh_token);
            return $decoded->access_token;
        } catch (Exception $e) {
            return false;
        }
    }

    private function step4($final_token, $id_entity, $user_role)
    {
        $data = [
            'legalEntityType' => 'CUSTOMER',
            'roleName' => $user_role,
            'idEntity' => $id_entity,
            'username' => Tools::substr($this->username, 0, strpos($this->username, '@'))
        ];

        $headers = [
            'Accept: application/json, text/plain, */*',
            'Accept-Language: it,it-IT;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
            'Authorization: Bearer ' . $final_token,
            'Connection: keep-alive',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.53 Safari/537.36 Edg/103.0.1264.37',
            'cache-control: no-cache',
            'Content-Length: 0'
        ];

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            if ($curl === false) {
                return false;
            }

            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_URL, $this->apiEndpoint . '/auth-registry/apikey?' . http_build_query($data));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_ENCODING, '');

            $result = curl_exec($curl);

            self::doLog(print_r($result, true));

            if ($result === false || curl_errno($curl) != 0) {
                return false;
            }

            curl_close($curl);
            return json_decode($result);
        } catch (Exception $e) {
            return false;
        }
    }

    public static function doLog($message)
    {
        if(!MBESHIPPING_DEBUG_MODE) {
            return;
        }

        $module = Module::getInstanceByName('mbeshipping');
        if (!$module) {
            return;
        }

        $logDir = $module->getLocalPath() . 'log';
        if (!file_exists($logDir)) mkdir($logDir);
        $logger = new FileLogger(FileLogger::DEBUG);
        $logger->setFilename($logDir . DIRECTORY_SEPARATOR . 'auth.log');
        $logger->logDebug($message);
    }

    private function retrieveExistingApiKey($final_token, $id_entity)
    {
        $data = [
            'legalEntityType' => 'CUSTOMER',
            'idEntity' => $id_entity
        ];

        $headers = [
            'Accept: application/json, text/plain, */*',
            'Accept-Language: it,it-IT;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
            'Authorization: Bearer ' . $final_token,
            'Connection: keep-alive',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.53 Safari/537.36 Edg/103.0.1264.37',
            'cache-control: no-cache'
        ];

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            if ($curl === false) {
                return false;
            }

            curl_setopt($curl, CURLOPT_URL, $this->apiEndpoint . '/auth-registry/apikey?' . http_build_query($data));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($curl);

            if ($result === false || curl_errno($curl) != 0) {
                return false;
            }

            curl_close($curl);

            return json_decode($result, true)['content'][0]['apiKey'];
        } catch (Exception $e) {
            return false;
        }
    }

    private function deleteExistingAPIKey($final_token, $existing_api_key)
    {
        $headers = [
            'Accept: */*',
            'Accept-Encoding: gzip, deflate, br',
            'Authorization: Bearer ' . $final_token,
            'Connection: keep-alive',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.53 Safari/537.36 Edg/103.0.1264.37',
            'cache-control: no-cache'
        ];

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            if ($curl === false) {
                return false;
            }

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($curl, CURLOPT_URL, $this->apiEndpoint . '/auth-registry/apikey?apikey=' . $existing_api_key);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($curl);

            if ($result === false || curl_errno($curl) != 0) {
                return false;
            }

            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);
            return json_decode($http_code);
        } catch (Exception $e) {
            return false;
        }
    }

    public function refreshAccessToken($refresh_token)
    {
        $data = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token,
        ];

        $headers = [
            'Accept: application/json, text/plain, */*',
            'Accept-Language: it,it-IT;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
            'Authorization: Basic dGVsZXBvcnQtZmU6',
            'Connection: keep-alive',
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.53 Safari/537.36 Edg/103.0.1264.37',
            'cache-control: no-cache'
        ];

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            if ($curl === false) {
                return false;
            }

            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($curl, CURLOPT_URL, $this->apiEndpoint . '/oauth/token');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($curl);

            if ($result === false || curl_errno($curl) != 0) {
                return false;
            }

            curl_close($curl);

            if (!isset(json_decode($result)->access_token)) {
                return false;
            }

            return json_decode($result)->access_token;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function isDelinquentUser()
    {
        $user_role = Configuration::get('MBESHIPPING_USER_ROLE');
        if ($user_role !== 'DIRECT_CHANNEL_USER') {
            return false;
        }

        $headers = [
            'Accept: application/json, text/plain, */*',
            'Accept-Language: it,it-IT;q=0.9,en;q=0.8,en-GB;q=0.7,en-US;q=0.6',
            'Authorization: Bearer ' . Configuration::get('MBESHIPPING_ACCESS_TOKEN'),
            'Connection: keep-alive',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.5060.53 Safari/537.36 Edg/103.0.1264.37',
            'cache-control: no-cache'
        ];

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            if ($curl === false) {
                return false;
            }

            curl_setopt($curl, CURLOPT_URL, MBESHIPPING_TEST_MODE ? MBESHIPPING_TEST_ENDPOINT . '/eship/customer/delinquent-check' : (new self())->apiEndpoint . '/eship/customer/delinquent-check');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($curl);

            if ($result === false || curl_errno($curl) != 0) {
                return false;
            }

            curl_close($curl);

            $decoded = json_decode($result);
            if (!isset($decoded->isDelinquent)) {
                return false;
            }

            return $decoded->isDelinquent;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function isJwtExpired($token)
    {
        $expiration = self::getJwtExpiration($token);

        if ($expiration === false) {
            return true;
        }

        return $expiration < time();
    }

    public static function getJwtExpiration($jwt)
    {
        $payload = self::parseJwtPayload($jwt);

        return $payload->exp ?? false;
    }

    public static function getJwtUserRole($jwt)
    {
        $payload = self::parseJwtPayload($jwt);

        return $payload->role ?? false;
    }

    public static function parseJwtPayload($jwt)
    {
        if (empty($jwt)) {
            return false;
        }

        [, $payload_b64] = explode('.', $jwt);

        if (empty($payload_b64)) {
            return false;
        }

        return JWT::jsonDecode(JWT::urlsafeB64Decode($payload_b64));
    }

    public static function clearAuth()
    {
        Configuration::deleteByName('MBECustomer');
        Configuration::deleteByName('username');
        Configuration::deleteByName('password');
        Configuration::deleteByName('url');
        Configuration::deleteByName('mbecountry');
        Configuration::deleteByName('MBESHIPPING_ADVANCED_AUTH_CONF');
        Configuration::deleteByName('MBESHIPPING_USER_ROLE');
        Configuration::deleteByName('MBESHIPPING_ACCESS_TOKEN');
        Configuration::deleteByName('MBESHIPPING_REFRESH_TOKEN');
        Configuration::deleteByName('MBESHIPPING_THIRD_PARTY_PICKUPS');
        Configuration::deleteByName('MBESHIPPING_PICKUP_MODE');
        self::togglePrivateArea(0);
    }

    private static function togglePrivateArea($active)
    {
        $tab = new Tab((int)Tab::getIdFromClassName('AdminMbePrivateArea'));
        if (Validate::isLoadedObject($tab)) {
            $tab->active = $active;
            $tab->save();
        }
    }

    public static function presetDirectChannelUserServices() {
        $available_services = (new Ws())->getAllowedShipmentServices();

        self::doLog(__METHOD__ . ' - Available services: ' . print_r($available_services, true));

        if (empty($available_services)) {
            return;
        }

        $preset_services = [];
        foreach ($available_services as $service) {
            if (!in_array($service['value'], ['MDP', 'SEE', 'SSE'])) {
                continue;
            }

            $preset_services[] = $service['value'];
        }

        self::doLog(__METHOD__ . ' - Preset services: ' . print_r($preset_services, true));

        if (empty($preset_services)) {
            return;
        }

        Configuration::updateValue(DataHelper::XML_PATH_ALLOWED_SHIPMENT_SERVICES, implode('-', $preset_services));

        // Preset carrier taxes
        $country_iso = Configuration::get('mbecountry');
        $id_country = Country::getByIso($country_iso);

        if (!$id_country) {
            return;
        }

        // Build query
        $query = new DbQuery();
        $query->select('g.id_tax_rules_group');
        $query->from('tax_rules_group', 'g');
        $query->innerJoin('tax_rule', 'tr', 'tr.id_tax_rules_group = g.id_tax_rules_group');
        $query->innerJoin('tax', 't', '(t.id_tax = tr.id_tax AND t.active = 1)');
        $query->innerJoin('tax_rules_group_shop', 'gs', 'gs.id_tax_rules_group = g.id_tax_rules_group');
        $query->where('gs.id_shop = ' . (int)Context::getContext()->shop->id);
        $query->where('g.deleted = 0 AND g.active = 1');
        $query->where('tr.id_state = 0 AND tr.zipcode_from = 0 AND tr.zipcode_to = 0 AND tr.id_country = ' . (int)$id_country);
        $query->where('ABS(t.rate - ' . STANDARD_TAX_RATES[$country_iso] . ') < 0.001');

        self::doLog(__METHOD__ . ' - Query:' . PHP_EOL . $query->build());

        $id_tax_rules_group = Db::getInstance()->getValue($query);

        self::doLog(__METHOD__ . ' - id_tax_rules_group: ' . print_r($id_tax_rules_group, true));

        if (!$id_tax_rules_group) {
            return;
        }

        foreach ($preset_services as $service) {
            $id_service = Tools::strtolower(str_replace('+', 'p', $service));
            Configuration::updateValue('mbe_tax_rule_' . $id_service, $id_tax_rules_group);
        }
    }

    private function saveTokens($access_token, $refresh_token)
    {
        Configuration::updateValue('MBESHIPPING_ACCESS_TOKEN', $access_token);
        Configuration::updateValue('MBESHIPPING_REFRESH_TOKEN', $refresh_token);
        $userRole = self::getJwtUserRole($access_token);
        self::doLog('User role: ' . $userRole);
        $this->setUserRole(self::getJwtUserRole($access_token));
    }

    private function setUserRole($role)
    {
        if (!in_array($role, MBESHIPPING_USER_ROLES)) {
            Configuration::updateValue('MBESHIPPING_USER_ROLE', 0);
            self::togglePrivateArea(0);
            return;
        }

        Configuration::updateValue('MBESHIPPING_USER_ROLE', $role);

        if ($role !== 'DIRECT_CHANNEL_USER') {
            self::togglePrivateArea(0);
            return;
        }

        self::presetDirectChannelUserConfigs();
    }

    public static function presetDirectChannelUserConfigs() {
        // Force "MBE services recovery" for DIRECT_CHANNEL_USER
        Configuration::updateValue('MBESHIPPING_COURIERS_SERVICES_CONF_MODE', 3);
        // Force allow all shipping countries for DIRECT_CHANNEL_USER
        Configuration::updateValue(DataHelper::XML_PATH_SALLOWSPECIFIC, 0);
        // Force default shipment type to "GENERIC" for DIRECT_CHANNEL_USER
        Configuration::updateValue(DataHelper::XML_PATH_DEFAULT_SHIPMENT_TYPE, 'GENERIC');
        // Force shipments closure mode to "manually" for DIRECT_CHANNEL_USER
        Configuration::updateValue(DataHelper::XML_PATH_SHIPMENTS_CLOSURE_MODE, DataHelper::MBE_CLOSURE_MODE_MANUALLY);
        // Force handling fee to round up for DIRECT_CHANNEL_USER
        Configuration::updateValue(DataHelper::XML_PATH_HANDLING_FEE_ROUNDING, 4);
        // Force handling fee to round up amount by 1 for DIRECT_CHANNEL_USER
        Configuration::updateValue(DataHelper::XML_PATH_HANDLING_FEE_ROUNDING_AMOUNT, 1);
        // Force shipments creation mode to "automatic" for DIRECT_CHANNEL_USER
        Configuration::updateValue('shipments_creation_mode', DataHelper::MBE_CREATION_MODE_AUTOMATICALLY);
        // Force auto change order status to "no" for DIRECT_CHANNEL_USER
        Configuration::updateValue(DataHelper::XML_PATH_AUTO_CHANGE_ORDER_STATUS, 0);
        // Force shipments csv mode to "disabled" for DIRECT_CHANNEL_USER
        Configuration::updateValue('shipments_csv_mode', DataHelper::MBE_CSV_MODE_DISABLED);
        // Force disable custom mapping for DIRECT_CHANNEL_USER
        Configuration::updateValue(DataHelper::XML_PATH_SHIPMENT_CUSTOM_MAPPING, 0);
        // Force one shipment per shopping cart for DIRECT_CHANNEL_USER
        Configuration::updateValue(DataHelper::XML_PATH_SHIPMENT_CONFIGURATION_MODE, 2);
        // Force pickup request mode to "automatically" for DIRECT_CHANNEL_USER
        Configuration::updateValue('MBESHIPPING_PICKUP_REQUEST_MODE', 'automatically');
        // Force max package weight to 30 for DIRECT_CHANNEL_USER
        Configuration::updateValue('max_package_weight', 30);
        // Force max shipment weight to 30 for DIRECT_CHANNEL_USER
        Configuration::updateValue('max_shipment_weight', 30);

        self::togglePrivateArea(1);
    }

    // Verify is the customer logged is a Direct User
    public static function isDirectChannelUser()
    {
        $user_role = Configuration::get('MBESHIPPING_USER_ROLE');
        return $user_role === 'DIRECT_CHANNEL_USER';
    }

    public static function thirdPartyPickupsAllowed()
    {
        return (bool)Configuration::get('MBESHIPPING_THIRD_PARTY_PICKUPS');
    }

    public static function thirdPartyPickupsEnabled()
    {
        return (bool)Configuration::get('MBESHIPPING_PICKUP_MODE');
    }

    public static function allowThirdPartyPickups($allowed)
    {
        Configuration::updateValue('MBESHIPPING_THIRD_PARTY_PICKUPS', $allowed);
        $tab = new Tab((int) Tab::getIdFromClassName('AdminMbePickupAddressHelper'));
        if (Validate::isLoadedObject($tab)) {
            $tab->active = $allowed;
            $tab->save();
        }
    }

    public static function enableThirdPartyPickups($enabled)
    {
        Configuration::updateValue('MBESHIPPING_PICKUP_MODE', $enabled);
    }

    public static function existsPayment() {
        $authApi = new self();
        $url = "$authApi->apiEndpoint/payments/customer/payment-check";
        $bearer_token = Configuration::get('MBESHIPPING_ACCESS_TOKEN');

        $headers = [
            'Content-type: application/json',
            'Accept: application/json, text/plain, */*',
            "Authorization: Bearer $bearer_token",
        ];

        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            if ($curl === false) {
                return false;
            }

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($curl);

            if ($result === false || curl_errno($curl) != 0) {
                return false;
            }

            curl_close($curl);

            $decoded = json_decode($result, true);

            if (!isset($decoded['existsPayment'])) {
                return false;
            }

            return $decoded['existsPayment'];
        } catch (Exception $e) {
            return false;
        }
    }
}
