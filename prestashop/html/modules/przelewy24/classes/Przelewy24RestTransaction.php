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
 * Class Przelewy24RestTransaction
 */
class Przelewy24RestTransaction extends Przelewy24RestAbstract implements Przelewy24RestTransactionInterface
{
    /**
     * Register.
     *
     * @param Przelewy24PayloadForRestTransaction $payload
     *
     * @return array
     */
    public function register($payload)
    {
        $path = '/transaction/register';
        $this->signSha384ForRegister($payload);

        return $this->call($path, $payload, 'POST');
    }

    /**
     * Register raw token.
     *
     * @param Przelewy24PayloadForRestTransaction $payload
     *
     * @return string|null
     */
    public function registerRawToken($payload)
    {
        $res = $this->register($payload);
        if (isset($res['data']['token'])) {
            return $res['data']['token'];
        } else {
            return null;
        }
    }

    /**
     * Verify.
     *
     * @param Przelewy24PayloadForRestTransactionVerify $payload
     *
     * @return string
     */
    public function verify($payload)
    {
        $path = '/transaction/verify';
        $this->signSha384ForVerification($payload);
        $data = $this->call($path, $payload, 'PUT');

        if (isset($data['data']['status'])) {
            return $data['data']['status'] === 'success';
        } else {
            return false;
        }
    }

    /**
     * Sign sha384 for register.
     *
     * @param Przelewy24PayloadForRestTransaction $payload
     */
    public function signSha384ForRegister($payload)
    {
        $data = [
            'sessionId' => $payload->sessionId,
            'merchantId' => $payload->merchantId,
            'amount' => $payload->amount,
            'currency' => $payload->currency,
            'crc' => $this->salt,
        ];
        $string = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $sign = hash('sha384', $string);
        $payload->sign = $sign;
    }

    /**
     * Sign sha384 for verficication.
     *
     * @param Przelewy24PayloadForRestTransactionVerify $payload
     */
    private function signSha384ForVerification($payload)
    {
        $data = [
            'sessionId' => $payload->sessionId,
            'orderId' => $payload->orderId,
            'amount' => $payload->amount,
            'currency' => $payload->currency,
            'crc' => $this->salt,
        ];
        $string = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $sign = hash('sha384', $string);
        $payload->sign = $sign;
    }
}
