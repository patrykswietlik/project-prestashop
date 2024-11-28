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

class AmazonPayCronHandler
{

    private $transactions = [];
    private $refunded_transactions = [];

    /**
     * AmazonPayCronHandler constructor.
     */
    public function __construct()
    {
        $this->transactions = $this->findRefreshableTransactions();
        $this->refunded_transactions = $this->findRefreshableRefundTransactions();
    }

    public function refreshOrders()
    {
        foreach ($this->transactions as $transaction) {
            $amazonPayOrder = AmazonPayOrder::findByCheckoutSessionId($transaction['amazon_checkout_session']);
            $amazonPayCheckoutSession = new AmazonPayCheckoutSession(
                false,
                $transaction['amazon_checkout_session']
            );
            $chargeinfo = $amazonPayCheckoutSession->getCharge();

            if (isset($chargeinfo['info']['statusDetails']['state'])) {
                switch ($chargeinfo['info']['statusDetails']['state']) {
                    case 'Declined':
                        self::handleDeclinedTransaction(
                            $amazonPayOrder,
                            $transaction,
                            $chargeinfo
                        );
                        break;
                    case 'Authorized':
                        self::handleAuthorizedTransaction(
                            $amazonPayOrder,
                            $amazonPayCheckoutSession,
                            $chargeinfo
                        );
                        break;
                }
            }
        }
        foreach ($this->refunded_transactions as $transaction) {
            $amazonPayOrder = AmazonPayOrder::findByCheckoutSessionId($transaction['amazon_checkout_session']);
            $amazonPayCheckoutSession = new AmazonPayCheckoutSession(
                false,
                $transaction['amazon_checkout_session']
            );
            $refundinfo = $amazonPayCheckoutSession->getRefund($transaction['amazon_transaction']);
            if (isset($refundinfo['statusDetails']['state'])) {
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
     * @return array|false|mysqli_result|PDOStatement|resource|null
     * @throws PrestaShopDatabaseException
     */
    public function findRefreshableTransactions()
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'amazonpay_transactions` at1
                WHERE at1.`transaction_type` = \'chargePermission\'
                  AND NOT EXISTS (
                    SELECT * FROM `' . _DB_PREFIX_ . 'amazonpay_transactions` at2
                     WHERE at2.amazon_checkout_session = at1.amazon_checkout_session
                       AND (at2.transaction_type = \'capture\' OR at2.transaction_type = \'cancel\' OR at2.transaction_type = \'close\')
                  )
                '
        );
        if ($result && is_array($result) && sizeof($result) > 0) {
            return $result;
        }
        return [];
    }

    /**
     * @return array|false|mysqli_result|PDOStatement|resource|null
     * @throws PrestaShopDatabaseException
     */
    public function findRefreshableRefundTransactions()
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'amazonpay_transactions` at1
                WHERE at1.`transaction_type` = \'refund_pending\'
                  AND NOT EXISTS (
                    SELECT * FROM `' . _DB_PREFIX_ . 'amazonpay_transactions` at2
                     WHERE at2.amazon_checkout_session = at1.amazon_checkout_session
                       AND (at2.transaction_type = \'cancel\' OR at2.transaction_type = \'close\')
                  )
                '
        );
        if ($result && is_array($result) && sizeof($result) > 0) {
            return $result;
        }
        return [];
    }

    /**
     * @param AmazonPayOrder $amazonPayOrder
     * @param array $transaction
     * @param array $chargeinfo
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function handleDeclinedTransaction(AmazonPayOrder $amazonPayOrder, array $transaction, array $chargeinfo)
    {
        AmazonPayLogger::getInstance()->addLog(
            'Handling Declined Transaction ' . $transaction['amazon_checkout_session'],
            3,
            false,
            $chargeinfo
        );
        $amazonPayOrder->setOrderStatus(AmazonPayHelper::getStatus('decline'));
        AmazonPayTransaction::store(
            $transaction['amazon_checkout_session'],
            $chargeinfo['chargePermissionId'],
            'close',
            0
        );
    }

    /**
     * @param AmazonPayOrder $amazonPayOrder
     * @param AmazonPayCheckoutSession $amazonPayCheckoutSession
     * @param array $chargeinfo
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function handleAuthorizedTransaction(AmazonPayOrder $amazonPayOrder, AmazonPayCheckoutSession $amazonPayCheckoutSession, array $chargeinfo)
    {
        AmazonPayLogger::getInstance()->addLog(
            'Handling Authorized Transaction ' . $amazonPayCheckoutSession->getAmazonPayCheckoutSessionId(),
            3,
            false,
            $chargeinfo
        );
        if (AmazonPayHelper::captureDirectlyAfterCheckout()) {
            $order = new Order((int)$amazonPayOrder->id_order);
            $amazonPayCheckoutSession->capture(
                $chargeinfo['chargeId'],
                $chargeinfo['info']['chargeAmount']['amount'],
                $chargeinfo['info']['chargeAmount']['currencyCode'],
                $order->reference
            );
        }
    }

    /**
     * @param AmazonPayOrder $amazonPayOrder
     * @param AmazonPayCheckoutSession $amazonPayCheckoutSession
     * @param array $refundinfo
     */
    public static function handleDeclinedRefund(AmazonPayOrder $amazonPayOrder, AmazonPayCheckoutSession $amazonPayCheckoutSession, array $refundinfo)
    {
        AmazonPayLogger::getInstance()->addLog(
            'Handling Declined Refund ' . $amazonPayCheckoutSession->getAmazonPayCheckoutSessionId(),
            3,
            false,
            $refundinfo
        );
        $transaction = AmazonPayTransaction::getByRefundId($refundinfo['refundId']);
        if ($transaction) {
            $transaction->transaction_type = 'refund_declined';
            $transaction->save();
        }
    }

    /**
     * @param AmazonPayOrder $amazonPayOrder
     * @param AmazonPayCheckoutSession $amazonPayCheckoutSession
     * @param array $refundinfo
     */
    public static function handleRefundedRefund(AmazonPayOrder $amazonPayOrder, AmazonPayCheckoutSession $amazonPayCheckoutSession, array $refundinfo)
    {
        AmazonPayLogger::getInstance()->addLog(
            'Handling Refunded Refund ' . $amazonPayCheckoutSession->getAmazonPayCheckoutSessionId(),
            3,
            false,
            $refundinfo
        );
        $transaction = AmazonPayTransaction::getByRefundId($refundinfo['refundId']);
        if ($transaction) {
            $transaction->transaction_type = 'refund';
            $transaction->save();
        }
    }
}
