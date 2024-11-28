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

class AmazonPayCV1Upgrade
{

    public static function migrate($amazonpay)
    {
        if (Module::isInstalled('amzpayments')) {
            AmazonPayLogger::getInstance()->addLog(
                'AmazonPay CV1 detected, checking for credentials',
                2
            );

            $legacy_merchant_id = trim(Configuration::get('AMZ_MERCHANT_ID'));
            $legacy_access_key = trim(Configuration::get('ACCESS_KEY'));
            $legacy_secret_key = trim(Configuration::get('SECRET_KEY'));

            if ($legacy_merchant_id == '' || $legacy_access_key == '' || $legacy_secret_key == '') {
                AmazonPayLogger::getInstance()->addLog(
                    'Credentials not complete, stopping upgrade.',
                    2
                );
                return;
            }

            AmazonPayLogger::getInstance()->addLog(
                'Credentials found, creating Key Pair',
                2
            );

            AmazonPayKeyShareHandler::createKeyPair();
            $publicKey = Configuration::get('AMAZONPAY_KEYEXCHANGE_PUBLIC_KEY');

            if ($publicKey != '') {
                AmazonPayLogger::getInstance()->addLog(
                    'publicKey created',
                    2
                );

                $region = AmazonPayHelper::getShopDefaultRegion();
                if ($region == 'US') {
                    $apiUrl = 'pay-api.amazon.com';
                } elseif ($region == 'JP') {
                    $apiUrl = 'pay-api.amazon.jp';
                } else {
                    $apiUrl = 'pay-api.amazon.eu';
                }
                $apiEndpoint = '/live/v2/publicKeyId';

                $getParams = [
                    'SellerId' => $legacy_merchant_id,
                    'AWSAccessKeyId' => $legacy_access_key,
                    'Action' => 'GetPublicKeyId',
                    'SignatureMethod' => 'HmacSHA256',
                    'SignatureVersion' => '2',
                    'Timestamp' => date(DATE_ISO8601, strtotime('now -2 hours')),
                ];

                foreach ($getParams as $gP => $gV) {
                    $getParams[$gP] = self::encode($gV);
                }

                $signatureString = self::calculateStringToSign(
                    $getParams,
                    $apiUrl,
                    $apiEndpoint
                );

                $signature = self::sign($signatureString, $legacy_secret_key);

                $getParams['PublicKey'] = self::encode(str_replace("\r", '', trim($publicKey)));
                $getParams['Signature'] = self::encode($signature);

                $callUrl = 'https://' . $apiUrl . $apiEndpoint . '?' . self::buildQuery(
                    $getParams
                );

                $callUrl = str_replace('SellerId', 'MerchantId', $callUrl);

                AmazonPayLogger::getInstance()->addLog(
                    'Requesting API at ' . $callUrl,
                    2
                );

                $response = Tools::file_get_contents(
                    $callUrl
                );

                try {
                    $response_json = json_decode($response, true);
                    AmazonPayLogger::getInstance()->addLog(
                        'API Response',
                        2,
                        false,
                        $response_json
                    );
                    if (isset($response_json['publicKeyId'])) {
                        Configuration::updateValue('AMAZONPAY_MERCHANT_ID', $legacy_merchant_id);
                        Configuration::updateValue('AMAZONPAY_PUBLIC_KEY_ID', $response_json['publicKeyId']);
                        $privateKeyStore = Configuration::updateValue('AMAZONPAY_PRIVATE_KEY', trim(Configuration::get('AMAZONPAY_KEYEXCHANGE_PRIVATE_KEY')));
                        $privateKeyStore = Configuration::updateValue('AMAZONPAY_PRIVATE_KEY_TMP', trim(Configuration::get('AMAZONPAY_KEYEXCHANGE_PRIVATE_KEY')));
                        Configuration::updateValue('AMAZONPAY_STORE_ID', Configuration::get('AMZ_CLIENT_ID'));

                        AmazonPayLogger::getInstance()->addLog(
                            'CV2 credentials set',
                            2,
                            false,
                            [
                                'Set merchant ID' => $legacy_merchant_id,
                                'Saved Pub Key Id' => $response_json['publicKeyId'],
                                'Key fetched from config' => Configuration::get('AMAZONPAY_KEYEXCHANGE_PRIVATE_KEY'),
                                'Store ID fetched' => Configuration::get('AMZ_CLIENT_ID'),
                                'privateKeyStore' => $privateKeyStore
                            ]
                        );
                        return true;
                    }
                } catch (\Exception $e) {
                    AmazonPayLogger::getInstance()->addLog(
                        'Invalid API Response',
                        2,
                        $e,
                        $response
                    );
                    return;
                }
            } else {
                AmazonPayLogger::getInstance()->addLog(
                    'publicKey could not be created, returning',
                    2
                );
            }
        }
    }

    /**
     * @param $string
     * @return array|string|string[]
     */
    protected static function encode($string)
    {
        return str_replace('%7E', '~', rawurlencode($string));
    }

    /**
     * @param $params
     * @return string
     */
    protected static function buildQuery($params)
    {
        foreach ($params as $pKey => $pVal) {
            $str.= $pKey . '=' . $pVal . '&';
        }
        $str = substr($str, 0, -1);
        return $str;
    }

    /**
     * @param $params
     * @return string
     */
    protected static function calculateStringToSign($params, $host, $endpoint)
    {
        $str = "GET" . "\n";
        $str.= $host . "\n";
        $str.= $endpoint . "\n";
        ksort($params);
        foreach ($params as $pKey => $pVal) {
            $str.= $pKey . '=' . $pVal . '&';
        }
        $str = substr($str, 0, -1);
        return $str;
    }

    /**
     * @param $str
     * @param $secret
     * @return false|string
     */
    protected static function sign($str, $secret)
    {
        return base64_encode(hash_hmac('sha256', $str, $secret, true));
    }

}