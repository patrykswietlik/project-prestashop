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
 * Class Przelewy24ReastCard
 */
class Przelewy24RestBlik extends Przelewy24RestAbstract implements Przelewy24RestBlikInterface
{
    /**
     * Execute payment by BLIK code.
     *
     * @param $token
     * @param $blikCode
     * @param $urlStatus
     *
     * @return array
     */
    public function executePaymentByBlikCode($token, $blikCode)
    {
        $path = '/paymentMethod/blik/chargeByCode';
        $payload = [
            'token' => $token,
            'blikCode' => $blikCode,
        ];

        return $this->call($path, $payload, 'POST');
    }
}
