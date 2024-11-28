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

class AmazonPayIPNHandler
{

    private $jsonData;
    private $data;
    private $is_valid = false;
    private $record;

    /**
     * AmazonPayIPNHandler constructor.
     */
    public function __construct()
    {
        try {
            $this->jsonData = Tools::file_get_contents('php://input');
            $this->data = json_decode($this->jsonData, true);
            if (isset($this->data['Message'])) {
                $this->data = json_decode($this->data['Message'], true);
            }

            AmazonPayLogger::getInstance()->addLog(
                'IPN Called',
                2,
                false,
                [
                    'jsonData' => $this->jsonData,
                    'data' => $this->data
                ]
            );

            if (isset($this->data[AmazonPayDefinitions::IPN_MERCHANT_ID]) &&
                isset($this->data[AmazonPayDefinitions::IPN_OBJECT_TYPE]) &&
                isset($this->data[AmazonPayDefinitions::IPN_OBJECT_ID]) &&
                isset($this->data[AmazonPayDefinitions::IPN_NOTIFICATION_TYPE]) &&
                isset($this->data[AmazonPayDefinitions::IPN_NOTIFICATION_ID]) &&
                isset($this->data[AmazonPayDefinitions::IPN_NOTIFICATION_VERSION])
            ) {
                if ($this->data[AmazonPayDefinitions::IPN_MERCHANT_ID] == Configuration::get('AMAZONPAY_MERCHANT_ID')) {
                    $this->is_valid = true;
                }
            }
        } catch (\Exception $e) {
            AmazonPayLogger::getInstance()->addLog(
                'IPN Called - Error',
                1,
                $e
            );
            echo $e->getMessage();
            die();
        }
    }

    /**
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function notHandledYet()
    {
        if (isset($this->data[AmazonPayDefinitions::IPN_NOTIFICATION_ID])) {
            if (AmazonPayIPN::findByNotificationId($this->data[AmazonPayDefinitions::IPN_NOTIFICATION_ID])) {
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function createIPNRecord()
    {
        $this->record = new AmazonPayIPN();
        $this->record->notification_id = $this->data[AmazonPayDefinitions::IPN_NOTIFICATION_ID];
        $this->record->message_payload = $this->jsonData;
        $this->record->save();
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function handleCharge()
    {
        AmazonPayLogger::getInstance()->addLog(
            'Try to find AmazonPayOrder by ChargeId',
            3
        );
        $amazonPayOrder = AmazonPayOrder::findByChargeId($this->data[AmazonPayDefinitions::IPN_OBJECT_ID]);
        if (!$amazonPayOrder) {
            AmazonPayLogger::getInstance()->addLog(
                'Not found, try to find AmazonPayOrder by Merchant Reference',
                3
            );
            $amazonPayOrder = self::findByMerchantReference($this->data[AmazonPayDefinitions::IPN_OBJECT_ID]);
        }
        if ($amazonPayOrder) {
            $amazonPayCheckoutSession = new AmazonPayCheckoutSession(
                false,
                $amazonPayOrder->amazon_checkout_session_id
            );
            AmazonPayLogger::getInstance()->addLog(
                'IPN: Checkoutsession created ' . $amazonPayOrder->amazon_checkout_session_id,
                3
            );
            $chargeinfo = $amazonPayCheckoutSession->getChargeInfo($this->data[AmazonPayDefinitions::IPN_OBJECT_ID]);
            $charge_amount = $chargeinfo['chargeAmount']['amount'];

            AmazonPayLogger::getInstance()->addLog(
                'IPN: Fetched charge info',
                3,
                false,
                [ $chargeinfo ]
            );

            if ($amazonPayOrder->amazon_charge_permission_id == '') {
                $amazonPayOrder->amazon_charge_permission_id = $this->data[AmazonPayDefinitions::IPN_CHARGE_ID];
            }
            if ($amazonPayOrder->amazon_charge_id == '') {
                $amazonPayOrder->amazon_charge_id = $this->data[AmazonPayDefinitions::IPN_OBJECT_ID];
            }
            $amazonPayOrder->save();

            if (!AmazonPayTransaction::findByChargeId($this->data[AmazonPayDefinitions::IPN_OBJECT_ID])) {
                AmazonPayLogger::getInstance()->addLog(
                    'No Transaction found, try to create new',
                    3,
                    false,
                    [
                        $chargeinfo['statusDetails']['state'],
                        $amazonPayOrder->amazon_checkout_session_id,
                        $this->data[AmazonPayDefinitions::IPN_OBJECT_ID],
                        $charge_amount
                    ]
                );

                if ($chargeinfo['statusDetails']['state'] == 'Authorized') {
                    AmazonPayTransaction::store(
                        $amazonPayOrder->amazon_checkout_session_id,
                        $this->data[AmazonPayDefinitions::IPN_CHARGE_ID],
                        'chargePermission',
                        $charge_amount
                    );
                    if ($amazonPayCheckoutSession->isOpenAndInAmazonPayBugWorkarround()) {
                        if (AmazonPayHelper::captureDirectlyAfterCheckout()) {
                            $order = new Order((int)$amazonPayOrder->id_order);
                            try {
                                $amazonPayCheckoutSession->capture(
                                    $this->data[AmazonPayDefinitions::IPN_OBJECT_ID],
                                    $charge_amount,
                                    $chargeinfo['chargeAmount']['currencyCode'],
                                    $order->reference
                                );
                                $amazonPayOrder->save();
                                $amazonPayOrder->setOrderStatus(AmazonPayHelper::getStatus('captured'));
                                AmazonPayLogger::getInstance()->addLog(
                                    'Order successfully captured, Charge-ID: ' . $chargeinfo['chargeId'],
                                    3
                                );
                            } catch (Exception $e) {
                                AmazonPayLogger::getInstance()->addLog('Order could not be captured', 1, $e);
                            }
                        }
                    }
                } elseif ($chargeinfo['statusDetails']['state'] == 'Declined') {
                    $amazonPayOrder->setOrderStatus(AmazonPayHelper::getStatus('decline'));
                    AmazonPayLogger::getInstance()->addLog(
                        'Payment declined, Charge-ID: ' . $chargeinfo['chargeId'],
                        3
                    );
                } else {
                    AmazonPayTransaction::store(
                        $this->amazon_pay_checkout_session_id,
                        $this->data[AmazonPayDefinitions::IPN_OBJECT_ID],
                        'capture',
                        $charge_amount
                    );
                }
            }
        }
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function handleRefund()
    {
        $amazonPayOrder = AmazonPayOrder::findByChargeId($this->data[AmazonPayDefinitions::IPN_OBJECT_ID]);
        if ($amazonPayOrder) {
            $amazonPayCheckoutSession = new AmazonPayCheckoutSession(
                false,
                $amazonPayOrder->amazon_checkout_session_id
            );
            $refundinfo = $amazonPayCheckoutSession->getRefund($this->data[AmazonPayDefinitions::IPN_OBJECT_ID]);
            $transaction = AmazonPayTransaction::getByRefundId($this->data[AmazonPayDefinitions::IPN_OBJECT_ID]);
            if ($transaction && isset($refundinfo['statusDetails']['state'])) {
                switch ($refundinfo['statusDetails']['state']) {
                    case 'Declined':
                        self::handleDeclinedRefund(
                            $amazonPayOrder,
                            $transaction,
                            $refundinfo
                        );
                        break;
                    case 'Refunded':
                        self::handleRefundedRefund(
                            $amazonPayOrder,
                            $amazonPayCheckoutSession,
                            $refundinfo
                        );
                        break;
                }
            }
        }
    }

    /**
     * @param $objectId
     * @return AmazonPayOrder|bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function findByMerchantReference($objectId)
    {
        $amzSess = new AmazonPayCheckoutSession(false, false);
        $chargeInfo = $amzSess->getChargeInfo($objectId);
        if (isset($chargeInfo['merchantMetadata']['merchantReferenceId'])) {
            $localCartId = str_replace(
                'AP',
                '',
                Tools::substr(
                    $chargeInfo['merchantMetadata']['merchantReferenceId'],
                    0,
                    strpos(
                        $chargeInfo['merchantMetadata']['merchantReferenceId'],
                        '-'
                    )
                )
            );
            return AmazonPayOrder::findByCartId((int)$localCartId);
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->is_valid;
    }

    /**
     * @return string|array
     */
    public function getData($what = false)
    {
        if ($what) {
            if (isset($this->data[$what])) {
                return $this->data[$what];
            }
            return false;
        }
        return $this->data;
    }
}
