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

use phpseclib\Crypt\AES;

class AmazonPayKeyShareHandler
{
    private $jsonData;
    private $encrypted_data;
    private $is_valid = false;
    private $data;
    private $amazon_public_key;
    private $decryptedKey = null;

    /**
     * AmazonPayKeyShareHandler constructor.
     */
    public function __construct()
    {
        AmazonPayLogger::getInstance()->addLog('Submitted raw data: ' . Tools::file_get_contents('php://input'), 2);
        try {
            $inputData = array();
            parse_str(Tools::file_get_contents('php://input'), $inputData);
            AmazonPayLogger::getInstance()->addLog('Submitted array', 2, false, $inputData);
            $this->jsonData = $inputData['payload'];
            $this->encrypted_data = json_decode($this->jsonData, true);
            AmazonPayLogger::getInstance()->addLog('Submitted jsonData', 2, false, $this->encrypted_data);
            if ($this->hasKeyPair()) {
                $this->is_valid = true;
            }
        } catch (\Exception $e) {
            AmazonPayLogger::getInstance()->addLog(
                'Error Parsing jsonData',
                2,
                $e
            );
            return;
        }
    }

    /**
     * decrypt payload
     */
    public function decrypt()
    {
        $decodedPubKeyId = urldecode($this->encrypted_data['publicKeyId']);
        $decryptedKey = null;

        AmazonPayLogger::getInstance()->addLog(
            'Decrypting',
            2,
            false,
            [
                'decodedPubKeyId' => $decodedPubKeyId,
                'privKey' => $this->getPrivateKey()
            ]
        );

        $success = openssl_private_decrypt(
            base64_decode($decodedPubKeyId),
            $decryptedKey,
            $this->getPrivateKey()
        );

        if ($success) {
            $this->decryptedKey = $decryptedKey;
            return true;
        } else {
            return false;
        }
    }

    /*
     * store into database
     */
    public function store()
    {
        return
            Configuration::updateValue('AMAZONPAY_MERCHANT_ID', $this->encrypted_data['merchantId']) &&
            Configuration::updateValue('AMAZONPAY_STORE_ID', $this->encrypted_data['storeId']) &&
            Configuration::updateValue('AMAZONPAY_PRIVATE_KEY', $this->getPrivateKey()) &&
            Configuration::updateValue('AMAZONPAY_PUBLIC_KEY_ID', $this->decryptedKey);
    }

    /**
     * @return false|string
     */
    public function setAmazonPublicKey()
    {
        $url = AmazonPayHelper::getPublicKeyURL();
        $this->amazon_public_key = Tools::file_get_contents($url . '?sigkey_id=' . $this->encrypted_data['sigKeyID']);
        return $this->amazon_public_key;
    }

    protected function getPrivateKey()
    {
        return Configuration::get('AMAZONPAY_KEYEXCHANGE_PRIVATE_KEY');
    }

    /**
     * @param $key
     * @return string
     */
    protected function key2pem($key)
    {
        return "-----BEGIN PUBLIC KEY-----\n" .
            chunk_split($key, 64, "\n") .
            "-----END PUBLIC KEY-----\n";
    }

    /**
     * @return bool
     */
    public static function createKeyPair()
    {
        try {
            $config = array(
                "digest_alg" => "sha512",
                "private_key_bits" => 2048,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            );

            $res = openssl_pkey_new($config);

            openssl_pkey_export($res, $privKey);
            $pubKey = openssl_pkey_get_details($res);
            $pubKey = $pubKey["key"];

            if ($pubKey != '' && $privKey != '') {
                Configuration::updateValue('AMAZONPAY_KEYEXCHANGE_PUBLIC_KEY', $pubKey);
                Configuration::updateValue('AMAZONPAY_KEYEXCHANGE_PRIVATE_KEY', $privKey);
                return true;
            }
        } catch (Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function hasKeyPair()
    {
        return Configuration::get('AMAZONPAY_KEYEXCHANGE_PUBLIC_KEY') != '' &&
               Configuration::get('AMAZONPAY_KEYEXCHANGE_PRIVATE_KEY') != '';
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->is_valid;
    }
}
