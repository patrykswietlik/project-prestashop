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
 * Class Przelewy24AddressType
 */
class Przelewy24AddressType extends ObjectModel
{
    const BILLING = 1;
    const DELIVERY = 0;

    /**
     * id.
     *
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $cart_id;

    /**
     * @var int
     */
    public $billing_address_id;

    /**
     * @var int
     */
    public $delivery_address_id;

    /**
     * @var int
     */
    public $client_id;

    const TABLE = 'przelewy24_address_type';

    const ID = 'id';
    const CART = 'cart_id';
    const BILLING_ADDRESS = 'billing_address_id';
    const DELIVERY_ADDRESS = 'delivery_address_id';
    const CLIENT = 'client_id';

    /**
     * Model definition.
     *
     * @var array
     */
    public static $definition = [
        'table' => self::TABLE,
        'primary' => self::ID,
        'fields' => [
            self::ID => ['type' => self::TYPE_INT],
            self::CART => ['type' => self::TYPE_INT],
            self::BILLING_ADDRESS => ['type' => self::TYPE_INT],
            self::DELIVERY_ADDRESS => ['type' => self::TYPE_INT],
            self::CLIENT => ['type' => self::TYPE_INT],
        ],
    ];

    /**
     * @param $cartId
     * @param $deliveryAddressId
     * @param $billingAddressId
     * @param $clientId
     *
     * @return Przelewy24AddressType
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function build($cartId, $deliveryAddressId, $billingAddressId, $clientId)
    {
        $model = new Przelewy24AddressType();

        $model->cart_id = $cartId;
        $model->delivery_address_id = $deliveryAddressId;
        $model->billing_address_id = $billingAddressId;
        $model->client_id = $clientId;

        return $model;
    }

    /**
     * @param $cartId
     *
     * @return ObjectModel
     *
     * @throws PrestaShopException
     */
    public static function getByCart($cartId)
    {
        $queryBuilder = new PrestaShopCollection(self::class);

        $addressType = $queryBuilder
            ->where('cart_id', '=', $cartId)
            ->setPageSize(1)
            ->getFirst();
        // todo : what if empty

        return $addressType;
    }
}
