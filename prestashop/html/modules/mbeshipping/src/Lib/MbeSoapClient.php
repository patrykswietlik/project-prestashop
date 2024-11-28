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

namespace PrestaShop\Module\Mbeshipping\Lib;

use PrestaShop\Module\Mbeshipping\Helper\LoggerHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MbeSoapClient extends \SoapClient
{
    public $isOnlinembe = false;

    public function __construct($wsdl, array $options = null, $username = null, $password = null)
    {
        try {
            if (strpos(\Tools::strtolower($wsdl), 'onlinembe') !== false) {
                $this->isOnlinembe = true;
                parent::__construct($wsdl, $options);
            } else {
                $opts = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    ),
                    'http' => array(
                        'protocol_version' => 1.0
                    )
                );
                $context = stream_context_create($opts);

                $soapClientOptions = array(
                    'trace' => 1,
                    'stream_context' => $context,
                    'login' => $username,
                    'password' => $password,
                    'location' => preg_replace('/(\/e-link\.wsdl)$/i', '', $wsdl),
                    'cache_wsdl' => WSDL_CACHE_NONE,
                    'exceptions' => true,
                );

                $isError = false;
                $restore = error_reporting(0);
                try {
                    new \SoapClient($wsdl, ['exceptions' => true]);
                } catch (\SoapFault $e) {
                    $isError = true;
                    trigger_error($e->getMessage()); // Overwrites E_ERROR with E_USER_NOTICE
                    $logger = new LoggerHelper();
                    $logger->logDebug("MbeSoapClient __construct #1 ERROR: " . $e->getMessage());
                } finally {
                    error_reporting($restore);
                }

                if (!$isError) {
                    parent::__construct($wsdl, $soapClientOptions);
                } else {
                    return null;
                }
            }
        } catch (\Throwable $e) {
            $logger = new LoggerHelper();
            $logger->logDebug("MbeSoapClient __construct #2 ERROR: " . $e->getMessage());
        }
    }

    #[\ReturnTypeWillChange]
    public function __soapCall(
        $function_name,
        $arguments,
        $options = null,
        $input_headers = null,
        &$output_headers = null
    ) {
        try {
            if (!$this->isOnlinembe) {
                $arguments[0]->RequestContainer->Credentials->Username = '';
                $arguments[0]->RequestContainer->Credentials->Passphrase = '';
            }

            $isError = false;
            $restore = error_reporting(0);
            $results = null;
            try {
                $results = parent::__soapCall($function_name, $arguments, $options, $input_headers, $output_headers);
            } catch (\SoapFault $e) {
                $isError = true;
                trigger_error($e->getMessage()); // Overwrites E_ERROR with E_USER_NOTICE
                $logger = new LoggerHelper();
                $logger->logDebug(__METHOD__ ." - #1 ERROR: {$e->getMessage()}");
            } finally {
                error_reporting($restore);
            }

            if (isset($results) && !$isError) {
                return $results;
            }

            return null;
        } catch (\Throwable $e) {
            $logger = new LoggerHelper();
            $logger->logDebug(__METHOD__ ." - #2 ERROR: {$e->getMessage()}");
        }

        return null;
    }
}
