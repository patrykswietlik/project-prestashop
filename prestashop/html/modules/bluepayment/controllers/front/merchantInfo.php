<?php
/**
 * NOTICE OF LICENSE
 * This source file is subject to the GNU Lesser General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/lgpl-3.0.en.html
 *
 * @author     Autopay S.A.
 * @copyright  Since 2015 Autopay S.A.
 * @license    https://www.gnu.org/licenses/lgpl-3.0.en.html GNU Lesser General Public License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

use BluePayment\Config\Config;
use BluePayment\Until\Helper;

class BluePaymentMerchantInfoModuleFrontController extends ModuleFrontController
{
    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Exception
     */
    public function initContent()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            echo json_encode('Invalid method');
            exit;
        }

        echo json_encode($this->getMerchantInfo());
        exit;
    }

    /**
     * @return array
     */
    private function getMerchantInfo()
    {
        $result = $this->sendRequest();

        if (!$result) {
            return [];
        }

        $merchantData = json_decode($result, true);
        $price = $this->getTotalAmount();

        return [
            'apiVersion' => 2,
            'apiVersionMinor' => 0,
            'merchantInfo' => [
                'authJwt' => $merchantData['authJwt'],
                'merchantName' => $merchantData['merchantName'],
                'merchantOrigin' => $merchantData['merchantOrigin'],
                'merchantId' => $merchantData['merchantId'],
            ],
            'allowedPaymentMethods' => [
                [
                    'type' => 'CARD',
                    'parameters' => [
                        'allowedAuthMethods' => ['PAN_ONLY', 'CRYPTOGRAM_3DS'],
                        'allowedCardNetworks' => ['MASTERCARD', 'VISA'],
                        'billingAddressRequired' => false,
                    ],
                    'tokenizationSpecification' => [
                        'type' => 'PAYMENT_GATEWAY',
                        'parameters' => [
                            'gateway' => 'bluemedia',
                            'gatewayMerchantId' => (string) $merchantData['acceptorId'],
                        ],
                    ],
                ],
            ],
            'transactionInfo' => [
                'currencyCode' => $this->context->currency->iso_code,
                'totalPriceStatus' => 'FINAL',
                'totalPrice' => (string) $price,
            ],
        ];
    }

    private function getTotalAmount()
    {
        if (Validate::isLoadedObject($this->context->cart) && (bool) !$this->context->cart->OrderExists()) {
            $cart = $this->context->cart;
        } else {
            $postOrderId = pSQL(Tools::getValue('postOrderId'));

            if (empty($postOrderId)) {
                $cart = $this->context->cart;
            } else {
                $orderIdItem = explode('-', $postOrderId);
                $orderIdItem = empty($orderIdItem[0]) ? 0 : $orderIdItem[0];
                $cart = Cart::getCartByOrderId($orderIdItem);

                if (!Validate::isLoadedObject($cart) || (int) $this->context->cart->id != $cart->id) {
                    throw new OrderException('Invalid cart provided.');
                }
            }
        }

        $totalPaid = (float) $cart->getOrderTotal(true, Cart::BOTH);

        return number_format(round($totalPaid, 2), 2, '.', '');
    }

    /**
     * @return bool|string
     */
    private function sendRequest()
    {
        $currency = $this->context->currency->iso_code;
        $serviceId = Helper::parseConfigByCurrency($this->module->name_upper . Config::SERVICE_PARTNER_ID, $currency);
        $sharedKey = Helper::parseConfigByCurrency($this->module->name_upper . Config::SHARED_KEY, $currency);

        $test_mode = Configuration::get($this->module->name_upper . '_TEST_ENV');
        $gateway_mode = $test_mode
            ? \BlueMedia\OnlinePayments\Gateway::MODE_SANDBOX
            : \BlueMedia\OnlinePayments\Gateway::MODE_LIVE;

        $gateway = new \BlueMedia\OnlinePayments\Gateway($serviceId, $sharedKey, $gateway_mode);

        /**
         * string MerchantDomain for BM should be different localhost
         */
        $data = [
            'ServiceID' => $serviceId,
            'MerchantDomain' => Tools::getHttpHost(false),
        ];

        $hash = array_merge($data, [$sharedKey]);
        $hash = Helper::generateAndReturnHash($hash);
        $data['Hash'] = $hash;
        $fields = is_array($data) ? http_build_query($data) : $data;

        try {
            $curl = curl_init($gateway::getActionUrl($gateway::GET_MERCHANT_INFO));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
            curl_setopt(
                $curl,
                CURLOPT_HTTPHEADER,
                ['BmHeader: pay-bm', 'Content-Type: application/x-www-form-urlencoded']
            );
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            $curlResponse = curl_exec($curl);
            curl_close($curl);

            if ($curlResponse === 'ERROR' || empty($curlResponse)) {
                Tools::error_log(
                    'Invalid response from BlueMedia API during get merchant info for G-pay. Dta: '
                    . print_r($data, 1)
                    . "\nResponse:\n" . print_r($curlResponse, 1)
                );

                return false;
            }

            return $curlResponse;
        } catch (Exception $e) {
            Tools::error_log($e);

            return false;
        }
    }
}
