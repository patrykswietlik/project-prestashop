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

/**
 * Class AmazonPayClient, extended to implement log methods
 */
class AmazonPayClient extends Amazon\Pay\API\Client
{

    /**
     * @param $method
     * @param $urlFragment
     * @param $payload
     * @param null $headers
     * @param null $queryParams
     * @return array|bool|string
     * @throws Exception
     */
    public function apiCall($method, $urlFragment, $payload, $headers = null, $queryParams = null)
    {
        AmazonPayLogger::getInstance()->addLog(
            'Client apiCall REQUEST',
            3,
            false,
            [
                'params' => [
                    'method' =>  $method,
                    'urlFragment' => $urlFragment,
                    'payload' => $payload,
                    'headers' => $headers,
                    'queryParams' => $queryParams
                ]
            ]
        );

        $response = parent::apiCall($method, $urlFragment, $payload, $headers, $queryParams);

        AmazonPayLogger::getInstance()->addLog(
            'Client apiCall RESPONSE',
            3,
            false,
            $response
        );

        return $response;
    }
}
