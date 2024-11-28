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
 * Class Przelewy24RestStatusSupport
 */
class Przelewy24RestStatusSupport implements Przelewy24StatusSupportInterface
{
    /**
     * Status payload.
     *
     * @var array
     */
    private $statusPayload;

    /**
     * Raw payload.
     *
     * @var string
     */
    private $rawPayload;

    /**
     * Przelewy24RestStatusSupport constructor.
     */
    public function __construct()
    {
        $this->rawPayload = (string) Tools::file_get_contents('php://input');
        $json = json_decode($this->rawPayload, true);
        if (!is_array($json)) {
            $json = (array) $json;
        }

        $this->statusPayload = $json;
    }

    /**
     * Get payload for log.
     *
     * @return string
     */
    public function getPayloadForLog()
    {
        return $this->rawPayload;
    }

    /**
     * Get session id.
     *
     * @return string
     */
    public function getSessionId()
    {
        if ($this->statusPayload && isset($this->statusPayload['sessionId'])) {
            return $this->statusPayload['sessionId'];
        } else {
            return '';
        }
    }

    /**
     * Get P24 order id.
     *
     * @return string
     */
    public function getP24OrderId()
    {
        if (isset($this->statusPayload['orderId'])) {
            return (string) $this->statusPayload['orderId'];
        } else {
            return '';
        }
    }

    /**
     * Get P24 order id.
     *
     * @return string
     */
    public function getP24Number()
    {
        /* The response do not contain this number. */
        return '';
    }

    /**
     * Possible card to save.
     *
     * @return bool
     */
    public function possibleCardToSave()
    {
        return true;
    }

    /**
     * Verify payload.
     *
     * @param string $totalAmount;
     * @param Currency $currency
     * @param string $suffix
     *
     * @return bool
     */
    public function verify($totalAmount, $currency, $suffix)
    {
        $statusPayload = $this->statusPayload;
        if ($statusPayload['merchantId'] !== (int) Configuration::get('P24_MERCHANT_ID' . $suffix)) {
            return false;
        } elseif ($statusPayload['posId'] !== (int) Configuration::get('P24_SHOP_ID' . $suffix)) {
            return false;
        } elseif ((string) $statusPayload['amount'] !== (string) $totalAmount) {
            return false;
        } elseif ($statusPayload['currency'] !== $currency->iso_code) {
            return false;
        } elseif ($this->sign($statusPayload, $suffix) !== $statusPayload['sign']) {
            return false;
        }

        $transactionService = Przelewy24RestTransactionInterfaceFactory::buildForSuffix($suffix);
        $payload = new Przelewy24PayloadForRestTransactionVerify();
        $payload->merchantId = Configuration::get('P24_MERCHANT_ID' . $suffix);
        $payload->posId = Configuration::get('P24_SHOP_ID' . $suffix);
        $payload->sessionId = $statusPayload['sessionId'];
        $payload->amount = (int) $totalAmount;
        $payload->currency = $currency->iso_code;
        $payload->orderId = $statusPayload['orderId'];
        $verified = $transactionService->verify($payload);

        return $verified;
    }

    /**
     * Sign.
     *
     * @param $payload
     * @param $suffix
     *
     * @return string
     */
    private function sign($payload, $suffix)
    {
        unset($payload['sign']);
        $payload['crc'] = Configuration::get('P24_SALT' . $suffix);
        $string = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $sign = hash('sha384', $string);

        return $sign;
    }
}
