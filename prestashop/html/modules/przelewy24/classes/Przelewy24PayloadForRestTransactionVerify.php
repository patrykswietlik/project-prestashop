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
 * Class Przelewy24PayloadForRestTransactionVerify
 */
class Przelewy24PayloadForRestTransactionVerify
{
    /**
     * Merchant id.
     *
     * @var int|null
     */
    public $merchantId;

    /**
     * Pos id.
     *
     * @var int
     */
    public $posId;

    /**
     * Session id.
     *
     * @var string|null
     */
    public $sessionId;

    /**
     * Amount.
     *
     * @var int|null
     */
    public $amount;

    /**
     * Currency.
     *
     * @var string|null
     */
    public $currency;

    /**
     * Order id.
     *
     * @var int|null
     */
    public $orderId;

    /**
     * Sign.
     *
     * @var string|null
     */
    public $sign;
}
