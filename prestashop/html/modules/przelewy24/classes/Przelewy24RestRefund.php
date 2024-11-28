<?php
/**
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24RestRefund
 */
class Przelewy24RestRefund extends Przelewy24RestAbstract
{
    public function refundByOrderId($orderId)
    {
        $orderId = urlencode($orderId);
        $path = '/refund/by/orderId/' . $orderId;
        $ret = $this->call($path, null, 'GET');

        return $ret;
    }

    public function transactionBySessionId($sessionId)
    {
        $sessionId = urlencode($sessionId);
        $path = '/transaction/by/sessionId/' . $sessionId;
        $ret = $this->call($path, null, 'GET');

        return $ret;
    }

    public function transactionRefund($p24OrderId, $sessionId, $amount)
    {
        /* We are limited to 35 characters. */
        $xId = Tools::substr(hash('sha224', rand()), 0, 35);
        /* The refunds field is an array of arrays. */
        $payload = [
            'requestId' => $xId,
            'refunds' => [
                [
                    'orderId' => (int) $p24OrderId,
                    'sessionId' => (string) $sessionId,
                    'amount' => (int) $amount,
                ],
            ],
            'refundsUuid' => $xId,
        ];
        $path = '/transaction/refund';
        $ret = $this->call($path, $payload, 'POST');

        return $ret;
    }
}
