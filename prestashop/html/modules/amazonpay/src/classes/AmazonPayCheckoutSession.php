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

class AmazonPayCheckoutSession
{

    protected $amazon_pay_checkout_session_id = false;
    protected $status = false;
    protected $rawClientResponse;
    protected $client;
    protected $last_message;

    /**
     * AmazonPayCheckoutSession constructor.
     * @param bool $create_if_not_valid
     * @param mixed $manual_id
     * @throws Exception
     */
    public function __construct($create_if_not_valid = true, $manual_id = false)
    {
        $this->client = AmazonPayHelper::getClient();
        if ($manual_id) {
            $this->amazon_pay_checkout_session_id = $manual_id;
        } else {
            if (isset(Context::getContext()->cookie->amazon_pay_checkout_session_id)) {
                if ($this->isValid(Context::getContext()->cookie->amazon_pay_checkout_session_id)) {
                    $this->amazon_pay_checkout_session_id = Context::getContext()->cookie->amazon_pay_checkout_session_id;
                    $this->status = true;
                }
            }
            if (!$this->amazon_pay_checkout_session_id && $create_if_not_valid) {
                $this->status = $this->createNewCheckoutSession();
            }
        }
    }

    /**
     * @return bool
     */
    public function checkStatus()
    {
        return $this->status;
    }

    /**
     * @return json
     */
    public function jsonReturn()
    {
        return $this->rawClientResponse;
    }

    /**
     * @return array
     */
    public function assocReturn()
    {
        return json_decode($this->rawClientResponse, true);
    }

    /**
     * @param bool $returnPayload
     * @param bool $apb
     * @return array|bool
     */
    public function createNewCheckoutSession($returnPayload = false, $apb = false)
    {
        $initParams = [];
        if ($returnPayload) {
            $initParams = [
                'decoupled' => '1'
            ];
        }
        $payload = array(
            'webCheckoutDetails' => array(
                'checkoutReviewReturnUrl' => Context::getContext()->link->getModuleLink(
                    'amazonpay',
                    'initcheckout',
                    $initParams
                ),
                'checkoutResultReturnUrl' => Context::getContext()->link->getModuleLink(
                    'amazonpay',
                    'validation',
                    $apb ? ['apb' => '1'] : []
                )
            ),
            'platformId' => Amazonpay::$pfid,
            'storeId' => Configuration::get('AMAZONPAY_STORE_ID'),
            'paymentDetails' => [
                'presentmentCurrency' => AmazonPayHelper::getCurrentCurrency(),
            ],
        );
        $allowedDestinations = $this->getAllowedDestinations();
        if (sizeof($allowedDestinations) > 0) {
            $allowedDestinations = $this->prepareAllowedDestinations($allowedDestinations);
            $payload['deliverySpecifications'] = [
                'addressRestrictions' => [
                    'type' => 'Allowed',
                    'restrictions' => $allowedDestinations
                ]
            ];
        }
        if ($apb) {
            unset($payload['webCheckoutDetails']['checkoutReviewReturnUrl']);
            unset($payload['deliverySpecifications']);
            $payload['scopes'] = ['name', 'email', 'phoneNumber', 'billingAddress'];
            $payload['paymentDetails']['paymentIntent'] = 'AuthorizeWithCapture';
            if (AmazonPayHelper::captureAtShipping()) {
                $payload['paymentDetails']['paymentIntent'] = 'Authorize';
            }
            $totalCartAmount = 0;
            if (isset(Context::getContext()->cart)) {
                $totalCartAmount = Context::getContext()->cart->getOrderTotal(true, Cart::BOTH);
            }
            $payload['paymentDetails']['chargeAmount'] = [
                'amount' => (string)Tools::ps_round($totalCartAmount, 2),
                'currencyCode' => AmazonPayHelper::getCurrentCurrency()
            ];
            $payload['merchantMetadata'] = array(
                'merchantStoreName' => Tools::substr(Configuration::get('PS_SHOP_NAME'), 0, 49),
                'customInformation' => 'created by patworx, PrestaShop ' . _PS_VERSION_ .',' . Amazonpay::$plugin_version
            );
            $payload['webCheckoutDetails']['checkoutMode'] = 'ProcessOrder';
            if (isset(Context::getContext()->cart)) {
                if (!Context::getContext()->cart->isVirtualCart()) {
                    $payload['addressDetails'] = AmazonPayAddress::dataForAbp();
                }
            }
        }
        if ($returnPayload) {
            return $payload;
        }
        $headers = array('x-amz-pay-Idempotency-Key' => uniqid());
        try {
            $result = $this->client->createCheckoutSession($payload, $headers);
            if ($result['status'] === 201) {
                $this->rawClientResponse = $result['response'];
                $response = json_decode($result['response'], true);
                $checkoutSessionId = $response['checkoutSessionId'];
                $this->amazon_pay_checkout_session_id = $checkoutSessionId;
                $this->saveSession();
                return true;
            } else {
                $this->logStatusError('createCheckoutSession', $result);
                return false;
            }
        } catch (\Exception $e) {
            $this->logCallException('captureCharge', $e, $result);
            return false;
        }
    }

    /**
     * @param $allowedDestinations
     * @return mixed
     */
    protected function prepareAllowedDestinations($allowedDestinations)
    {
        foreach ($allowedDestinations as $destKey => $dest) {
            if (is_array($dest)) {
                $allowedDestinations[$destKey] = (object)$dest;
            }
        }
        return $allowedDestinations;
    }

    /**
     * @return array
     */
    protected function getAllowedDestinations()
    {
        $cache_id_return = 'AmazonPayCheckoutSession::getAllowedDestinationsReturn' . (int)Context::getContext()->language->id;
        if (AmazonPayHelper::useCache()) {
            $allowedDestinationsCache = AmazonPayCache::getByKey($cache_id_return, 259200, true, (int)Context::getContext()->language->id);
            if ((int)$allowedDestinationsCache->id > 0) {
                return json_decode($allowedDestinationsCache->cache_value, true);
            }
        } else {
            if (Cache::isStored($cache_id_return)) {
                return Cache::retrieve($cache_id_return);
            }
        }
        $ret = [];
        $cache_id = 'AmazonPayCheckoutSession::getAllowedDestinations' . (int)Context::getContext()->language->id;
        if (!Cache::isStored($cache_id)) {
            $countries = Country::getCountries(
                (int)Context::getContext()->language->id,
                true,
                false,
                false
            );
            Cache::store($cache_id, $countries);
        } else {
            $countries = Cache::retrieve($cache_id);
        }
        foreach ($countries as $country) {
            if (AmazonPayHelper::isValidAmazonDeliveryCountry($country['iso_code'])) {
                if (AmazonPayHelper::hasAtLeastOneCarrier($country)) {
                    $ret[$country['iso_code']] = new ArrayObject();
                }
            }
        }
        if (AmazonPayHelper::useCache()) {
            $allowedDestinationsCache->cache_value = json_encode($ret);
            $allowedDestinationsCache->save();
        } else {
            Cache::store($cache_id_return, $ret);
        }
        return $ret;
    }

    /**
     * @return bool
     */
    public function isCompleted()
    {
        $info = $this->getInformation();
        return $info['statusDetails']['state'] == 'Completed';
    }

    public function isOpenAndInAmazonPayBugWorkarround()
    {
        if (AmazonPayHelper::canHandlePendingAuthorization()) {
            if (AmazonPayHelper::getPaymentIntent() == 'Authorize') {
                return $this->isOpen();
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isOpen()
    {
        $info = $this->getInformation();
        return $info['statusDetails']['state'] == 'Open';
    }

    /**
     * @return array
     */
    public function getCharge()
    {
        $info = $this->getInformation();
        $chargeInfo = array(
            'chargeId' => $info['chargeId'],
            'chargePermissionId' => $info['chargePermissionId'],
            'info' => $this->getChargeInfo($info['chargeId'])
        );
        return $chargeInfo;
    }

    /**
     * @param $refund_id
     * @return array|bool|string
     */
    public function getRefund($refund_id)
    {
        try {
            $result = $this->client->getRefund($refund_id);
            if ($result['status'] === 200) {
                $this->rawClientResponse = $result['response'];
                $response = json_decode($result['response'], true);
                return $response;
            } else {
                $this->logStatusError('getRefund', $result);
                return false;
            }
        } catch (\Exception $e) {
            $this->logCallException('getRefund', $e, $result);
            return false;
        }
    }

    /**
     * @param $charge_id
     * @return bool|mixed
     */
    public function getChargeInfo($charge_id)
    {
        try {
            $result = $this->client->getCharge($charge_id);
            if ($result['status'] === 200) {
                $this->rawClientResponse = $result['response'];
                $response = json_decode($result['response'], true);
                return $response;
            } else {
                $this->logStatusError('getCharge', $result);
                return false;
            }
        } catch (\Exception $e) {
            $this->logCallException('captureCharge', $e, $result);
            return false;
        }
    }

    /**
     * @return bool|mixed
     */
    public function getInformation()
    {
        try {
            $result = $this->client->getCheckoutSession($this->amazon_pay_checkout_session_id);
            if ($result['status'] === 200) {
                $this->rawClientResponse = $result['response'];
                $response = json_decode($result['response'], true);
                return $response;
            } else {
                $this->logStatusError('getCheckoutSession', $result);
                return false;
            }
        } catch (\Exception $e) {
            $this->logCallException('captureCharge', $e, $result);
            return false;
        }
    }

    /**
     * @param Cart $cart
     * @return bool
     * @throws Exception
     */
    public function setPaymentInfo(Cart $cart)
    {
        $payload = array(
            'paymentDetails' => array(
                'paymentIntent' => AmazonPayHelper::getPaymentIntent(),
                'canHandlePendingAuthorization' => AmazonPayHelper::canHandlePendingAuthorization(),
                'chargeAmount' => array(
                    'amount' => (string)Tools::ps_round($cart->getOrderTotal(true, Cart::BOTH), 2),
                    'currencyCode' => AmazonPayHelper::getCurrentCurrency()
                )
            ),
            'merchantMetadata' => array(
                'merchantStoreName' => Tools::substr(Configuration::get('PS_SHOP_NAME'), 0, 49),
                'merchantReferenceId' => AmazonPayHelper::getMerchantReferenceId(),
                'customInformation' => 'created by patworx, PrestaShop ' . _PS_VERSION_ .',' . Amazonpay::$plugin_version
            )
        );
        try {
            $result = $this->client->updateCheckoutSession($this->amazon_pay_checkout_session_id, $payload);
            if ($result['status'] === 200) {
                $this->rawClientResponse = $result['response'];
                $response = json_decode($result['response'], true);
                $amazonPayRedirectUrl = $response['webCheckoutDetails']['amazonPayRedirectUrl'];
                return $amazonPayRedirectUrl;
            } else {
                $this->logStatusError('updateCheckoutSession', $result);
                return false;
            }
        } catch (\Exception $e) {
            $this->logCallException('captureCharge', $e, $result);
            return false;
        }
    }

    /**
     * @param $chargeId
     * @param $amount
     * @param $currency_code
     * @param string $order_ref
     * @return bool
     */
    public function createCharge($chargePermissionId, $amount, $currency_code, $order_ref = '')
    {
        $payload = array(
            'chargePermissionId' => $chargePermissionId,
            'chargeAmount' => array(
                'amount' => (string)$amount,
                'currencyCode' => $currency_code
            ),
            'captureNow' => true,
            'softDescriptor' => $order_ref
        );
        try {
            $headers = array('x-amz-pay-Idempotency-Key' => uniqid());
            $result = $this->client->createCharge($payload, $headers);
            if ($result['status'] === 201) {
                $this->rawClientResponse = $result['response'];
                $response = json_decode($result['response'], true);
                $state = $response['statusDetails']['state'];
                AmazonPayTransaction::store(
                    $this->amazon_pay_checkout_session_id,
                    $response['chargeId'],
                    'capture',
                    $amount
                );
                return $state;
            } else {
                $this->logStatusError('createCharge', $result);
                return false;
            }
        } catch (\Exception $e) {
            $this->logCallException('createCharge', $e, $result);
            return false;
        }
    }

    /**
     * @param $chargeId
     * @param $order_ref
     * @return bool
     */
    public function updateOrderReference($chargeId, $order_ref)
    {
        $payload = array(
            'merchantMetadata' => array(
                'merchantReferenceId' => $order_ref
            )
        );
        try {
            $headers = array('x-amz-pay-Idempotency-Key' => uniqid());
            $result = $this->client->updateChargePermission($chargeId, $payload, $headers);
            if ($result['status'] === 200) {
                return true;
            } else {
                $this->logStatusError('updateChargePermission', $result);
                return false;
            }
        } catch (\Exception $e) {
            $this->logCallException('updateChargePermission', $e, $result);
            return false;
        }
    }

    /**
     * @param $chargeId
     * @param $amount
     * @param $currency_code
     * @param string $order_ref
     * @return bool
     */
    public function capture($chargeId, $amount, $currency_code, $order_ref = '')
    {
        $payload = array(
            'captureAmount' => array(
                'amount' => (string)$amount,
                'currencyCode' => $currency_code
            ),
            'softDescriptor' => $order_ref
        );
        try {
            $headers = array('x-amz-pay-Idempotency-Key' => uniqid());
            $result = $this->client->captureCharge($chargeId, $payload, $headers);
            if ($result['status'] === 200) {
                $this->rawClientResponse = $result['response'];
                $response = json_decode($result['response'], true);
                $state = $response['statusDetails']['state'];
                AmazonPayTransaction::store(
                    $this->amazon_pay_checkout_session_id,
                    $chargeId,
                    'capture',
                    $amount
                );
                return $state;
            } else {
                $this->logStatusError('captureCharge', $result);
                return false;
            }
        } catch (\Exception $e) {
            $this->logCallException('captureCharge', $e, $result);
            return false;
        }
    }

    /**
     * @param $chargeId
     * @param $amount
     * @param $currency_code
     * @param string $order_ref
     * @return bool
     */
    public function refund($chargeId, $amount, $currency_code, $order_ref = '')
    {
        $payload = array(
            'chargeId' => $chargeId,
            'refundAmount' => array(
                'amount' => (string)$amount,
                'currencyCode' => $currency_code
            ),
            'softDescriptor' => $order_ref
        );
        try {
            $headers = array('x-amz-pay-Idempotency-Key' => uniqid());
            $result = $this->client->createRefund($payload, $headers);
            if ($result['status'] === 201) {
                $this->rawClientResponse = $result['response'];
                $response = json_decode($result['response'], true);
                $state = $response['statusDetails']['state'];
                AmazonPayTransaction::store(
                    $this->amazon_pay_checkout_session_id,
                    $response['refundId'],
                    'refund_pending',
                    $amount * -1
                );
                return $state;
            } else {
                $this->logStatusError('createRefund', $result);
                return false;
            }
        } catch (\Exception $e) {
            $this->logCallException('createRefund', $e, $result);
            return false;
        }
    }

    /**
     * @param string $chargeId
     * @param string $reason
     * @return bool
     */
    public function cancel($chargeId, $reason)
    {
        $payload = array(
            'cancellationReason' => $reason
        );
        try {
            $headers = array('x-amz-pay-Idempotency-Key' => uniqid());
            $result = $this->client->cancelCharge($chargeId, $payload, $headers);
            if ($result['status'] === 200) {
                $this->rawClientResponse = $result['response'];
                $response = json_decode($result['response'], true);
                $state = $response['statusDetails']['state'];
                AmazonPayTransaction::store(
                    $this->amazon_pay_checkout_session_id,
                    $response['chargeId'],
                    'cancel',
                    0
                );
                return $state;
            } else {
                $this->logStatusError('cancelCharge', $result);
                return false;
            }
        } catch (\Exception $e) {
            $this->logCallException('cancelCharge', $e, $result);
            return false;
        }
    }

    /**
     * @param string $chargeId
     * @param string $reason
     * @return bool
     */
    public function close($chargeId, $reason)
    {
        $payload = array(
            'closureReason' => $reason
        );
        try {
            $headers = array('x-amz-pay-Idempotency-Key' => uniqid());
            $result = $this->client->closeChargePermission($chargeId, $payload, $headers);
            if ($result['status'] === 200) {
                $this->rawClientResponse = $result['response'];
                $response = json_decode($result['response'], true);
                $state = $response['statusDetails']['state'];
                AmazonPayTransaction::store(
                    $this->amazon_pay_checkout_session_id,
                    $response['chargePermissionId'],
                    'close',
                    0
                );
                return $state;
            } else {
                $this->logStatusError('closeChargePermission', $result);
                return false;
            }
        } catch (\Exception $e) {
            $this->logCallException('closeChargePermission', $e, $result);
            return false;
        }
    }

    /**
     * @param $call
     * @param $exception
     * @param $result
     */
    protected function logCallException($call, $exception, $result)
    {
        AmazonPayLogger::getInstance()->addLog(
            'Error API-Call ' . $call,
            3,
            $exception,
            $result
        );
    }

    /**
     * @param $call
     * @param $result
     */
    protected function logStatusError($call, $result)
    {
        $response = json_decode($result['response'], true);
        if (is_array($response) && isset($response['message'])) {
            $this->last_message = $response['message'];
        }
        AmazonPayLogger::getInstance()->addLog(
            'Error API-Call ' . $call,
            3,
            false,
            $result
        );
    }

    public function getLastMessage()
    {
        return $this->last_message;
    }

    /**
     * @param $amazon_pay_checkout_session_id
     * @return bool
     */
    public function isValid($amazon_pay_checkout_session_id)
    {
        $result = $this->client->getCheckoutSession(
            $amazon_pay_checkout_session_id
        );
        if ($result['status'] === 200) {
            $this->rawClientResponse = $result['response'];
            return true;
        }
        return false;
    }

    /**
     * @param $amazon_pay_checkout_session_id
     * @return bool
     */
    public function complete($amazon_pay_checkout_session_id, $amount, $currency_code)
    {
        $payload = array(
            'chargeAmount' => array(
                'amount' => (string)$amount,
                'currencyCode' => $currency_code
            )
        );
        try {
            $headers = array('x-amz-pay-Idempotency-Key' => uniqid());
            $result = $this->client->completeCheckoutSession($amazon_pay_checkout_session_id, $payload, $headers);
            if ($result['status'] === 200 || $result['status'] === 202) {
                $this->rawClientResponse = $result['response'];
                $response = json_decode($result['response'], true);
                $state = $response['statusDetails']['state'];
                return $state;
            } else {
                $this->logStatusError('completeCheckoutSession', $result);
                return false;
            }
        } catch (\Exception $e) {
            $this->logCallException('completeCheckoutSession', $e, $result);
            return false;
        }
    }

    /**
     * @param $json
     * @return bool
     */
    public function isInCanceledState($json)
    {
        $response = json_decode($json, true);
        if (isset($response['statusDetails']['state'])) {
            return $response['statusDetails']['state'] == 'Canceled';
        }
        return false;
    }

    /**
     * put session into cookie storage
     */
    public function saveSession()
    {
        Context::getContext()->cookie->amazon_pay_checkout_session_id = $this->amazon_pay_checkout_session_id;
    }

    /**
     * @return bool|string
     */
    public function getAmazonPayCheckoutSessionId()
    {
        return $this->amazon_pay_checkout_session_id;
    }

    /**
     * reset session data in prestashop
     */
    public function reset()
    {
        $this->amazon_pay_checkout_session_id = false;
        unset(Context::getContext()->cookie->amazon_pay_checkout_session_id);
    }
}
