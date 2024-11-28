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
 * Class Przelewy24PayloadForRestTransaction
 */
class Przelewy24PayloadForRestTransaction
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
     * Description.
     *
     * @var string|null
     */
    public $description;

    /**
     * Email.
     *
     * @var string|null
     */
    public $email;

    /**
     * Client.
     *
     * @var string|null
     */
    public $client;

    /**
     * Address.
     *
     * @var string|null
     */
    public $address;

    /**
     * Zip.
     *
     * @var string|null
     */
    public $zip;

    /**
     * City.
     *
     * @var string|null
     */
    public $city;

    /**
     * Country.
     *
     * @var string|null
     */
    public $country;

    /**
     * Language.
     *
     * @var string|null
     */
    public $language;

    /**
     * Method id.
     *
     * @var int|null
     */
    public $method;

    /**
     * Url return.
     *
     * @var string|null
     */
    public $urlReturn;

    /**
     * Url status.
     *
     * @var string|null
     */
    public $urlStatus;

    /**
     * Wait for transaction result.
     *
     * @var bool|null
     */
    public $waitForResult;

    /**
     * Regulation accept.
     *
     * @var bool|null
     */
    public $regulationAccept;

    /**
     * Shipping.
     *
     * @var int|null
     */
    public $shipping;

    /**
     * Sign.
     *
     * @var string|null
     */
    public $sign;

    /**
     * Encoding.
     *
     * @var string|null
     */
    public $encoding;

    /**
     * Method ref id.
     *
     * @var string|null
     */
    public $methodRefId;

    /**
     * Cart.
     *
     * @var array|null
     */
    public $cart;

    /**
     * Additional.
     *
     * @var stdClass|null
     */
    public $additional;
}
