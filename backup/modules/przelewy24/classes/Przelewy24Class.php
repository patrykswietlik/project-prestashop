<?php
/**
 * Przelewy24 comunication class
 * Communication protol version
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

define('P24_VERSION', '3.2');
if (!class_exists('Przelewy24Class', false)) {
    class Przelewy24Class implements Przelewy24ClassInterface, Przelewy24ClassStaticInterface
    {
        /**
         * Live system URL address.
         *
         * @var string
         */
        private static $hostLive = 'https://secure.przelewy24.pl/';

        /**
         * Sandbox system URL address.
         *
         * @var string
         */
        private static $hostSandbox = 'https://sandbox.przelewy24.pl/';

        /**
         * Use Live (false) or Sandbox (true) environment.
         *
         * @var bool
         */
        private $testMode = false;

        /**
         * Merchant Id.
         *
         * @var int
         */
        private $merchantId = 0;

        /**
         * Merchant posId.
         *
         * @var int
         */
        private $posId = 0;

        /**
         * Salt to create a control sum (from P24 panel).
         *
         * @var string
         */
        private $salt = '';

        /**
         * Array of POST data.
         *
         * @var array
         */
        private $postData = [];

        /**
         * Obcject constructor. Set initial parameters.
         *
         * @param int $merchantId
         * @param int $posId
         * @param string $salt
         * @param bool $testMode
         */
        public function __construct($merchantId, $posId, $salt, $testMode = false)
        {
            $this->posId = (int) trim($posId);
            $this->merchantId = (int) trim($merchantId);
            if (0 === $this->merchantId) {
                $this->merchantId = $this->posId;
            }
            $this->salt = trim($salt);
            $this->testMode = $testMode;

            $this->addValue('p24_merchant_id', $this->merchantId);
            $this->addValue('p24_pos_id', $this->posId);
            $this->addValue('p24_api_version', P24_VERSION);
        }

        /**
         * Returns host URL.
         *
         * @return string
         */
        public function getHost()
        {
            return self::getHostForEnvironment($this->testMode);
        }

        /**
         * Returns host URL For Environmen
         *
         * @param bool $isTestMode
         *
         * @return string
         */
        public static function getHostForEnvironment($isTestMode = false)
        {
            return $isTestMode ? self::$hostSandbox : self::$hostLive;
        }

        /**
         * Returns URL for direct request (trnDirect).
         *
         * @return string
         */
        public function trnDirectUrl()
        {
            return $this->getHost() . 'trnDirect';
        }

        /**
         * Adds value do post request.
         *
         * @param string $name argument name
         * @param int|string|bool $value argument value
         */
        public function addValue($name, $value)
        {
            if ($this->validateField($name, $value)) {
                $this->postData[$name] = $value;
            }
        }

        /**
         * Redirects or returns URL to a P24 payment screen.
         *
         * @param string $token Token
         * @param bool $redirect If set to true redirects to P24 payment screen.
         *                       If set to false function returns URL to redirect to P24 payment screen.
         *
         * @return string URL to P24 payment screen
         */
        public function trnRequest($token, $redirect = true)
        {
            $token = Tools::substr($token, 0, 100);
            $url = $this->getHost() . 'trnRequest/' . $token;
            if ($redirect) {
                Tools::redirect($url);

                return '';
            }

            return $url;
        }

        /**
         * Validate api version.
         *
         * @param string $version
         *
         * @return bool
         */
        private function validateVersion(&$version)
        {
            if (preg_match('/^[0-9]+(?:\.[0-9]+)*(?:[\.\-][0-9a-z]+)?$/', $version)) {
                return true;
            }
            $version = '';

            return false;
        }

        /**
         * Validate email.
         *
         * @param string $email
         *
         * @return bool
         */
        private function validateEmail(&$email)
        {
            if ($email = filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return true;
            }
            $email = '';

            return false;
        }

        /**
         * Validate number.
         *
         * @param string|float|int $value
         * @param bool $min
         * @param bool $max
         *
         * @return bool
         */
        private function validateNumber(&$value, $min = false, $max = false)
        {
            if (is_numeric($value)) {
                $value = (int) $value;
                if ((false !== $min && $value < $min) || (false !== $max && $value > $max)) {
                    return false;
                }

                return true;
            }
            $value = (false !== $min ? $min : 0);

            return false;
        }

        /**
         * Validate string.
         *
         * @param string $value
         * @param int $len
         *
         * @return bool
         */
        private function validateString(&$value, $len = 0)
        {
            $len = (int) $len;
            if (preg_match('/<[^<]+>/', $value, $m) > 0) {
                return false;
            }

            if (0 === $len ^ Tools::strlen($value) <= $len) {
                return true;
            }
            $value = '';

            return false;
        }

        private function validateUrl(&$url, $len = 0)
        {
            $len = (int) $len;
            if (0 === $len ^ Tools::strlen($url) <= $len) {
                if (preg_match('@^https?://[^\s/$.?#].[^\s]*$@iS', $url)) {
                    return true;
                }
            }
            $url = '';

            return false;
        }

        /**
         * Validate enum.
         *
         * @param string $value provided value
         * @param string[] $haystack array of valid values
         *
         * @return bool
         */
        private function validateEnum(&$value, $haystack)
        {
            if (in_array(Tools::strtolower($value), $haystack)) {
                return true;
            }
            $value = $haystack[0];

            return false;
        }

        /**
         * Validate field.
         *
         * @param string $field
         * @param mixed &$value
         *
         * @return bool
         */
        public function validateField($field, &$value)
        {
            $ret = false;
            switch ($field) {
                case 'p24_session_id':
                    $ret = $this->validateString($value, 100);
                    break;
                case 'p24_description':
                    $ret = $this->validateString($value, 1024);
                    break;
                case 'p24_address':
                    $ret = $this->validateString($value, 80);
                    break;
                case 'p24_country':
                case 'p24_language':
                    $ret = $this->validateString($value, 2);
                    break;
                case 'p24_client':
                case 'p24_city':
                    $ret = $this->validateString($value, 50);
                    break;
                case 'p24_merchant_id':
                case 'p24_pos_id':
                case 'p24_order_id':
                case 'p24_amount':
                case 'p24_method':
                case 'p24_time_limit':
                case 'p24_channel':
                case 'p24_shipping':
                    $ret = $this->validateNumber($value);
                    break;
                case 'p24_wait_for_result':
                    $ret = $this->validateNumber($value, 0, 1);
                    break;
                case 'p24_api_version':
                    $ret = $this->validateVersion($value);
                    break;
                case 'p24_sign':
                    if ((32 === Tools::strlen($value)) && ctype_xdigit($value)) {
                        $ret = true;
                    } else {
                        $value = '';
                    }
                    break;
                case 'p24_url_return':
                case 'p24_url_status':
                    $ret = $this->validateUrl($value, 250);
                    break;
                case 'p24_currency':
                    $ret = preg_match('/^[A-Z]{3}$/', $value);
                    if (!$ret) {
                        $value = '';
                    }
                    break;
                case 'p24_email':
                    $ret = $this->validateEmail($value);
                    break;
                case 'p24_encoding':
                    $ret = $this->validateEnum($value, ['iso-8859-2', 'windows-1250', 'utf-8', 'utf8']);
                    break;
                case 'p24_transfer_label':
                    $ret = $this->validateString($value, 20);
                    break;
                case 'p24_phone':
                    $ret = $this->validateString($value, 12);
                    break;
                case 'p24_zip':
                    $ret = $this->validateString($value, 10);
                    break;
                default:
                    if ((0 === strpos($field, 'p24_quantity_'))
                        || (0 === strpos($field, 'p24_price_'))
                        || (0 === strpos($field, 'p24_number_'))
                    ) {
                        $ret = $this->validateNumber($value);
                    } elseif ((0 === strpos($field, 'p24_name_'))
                        || (0 === strpos($field, 'p24_description_'))) {
                        $ret = $this->validateString($value, 127);
                    } else {
                        $value = '';
                    }
                    break;
            }

            return $ret;
        }
    }
}
