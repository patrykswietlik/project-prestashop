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
 * Class Przelewy24BlikAlias
 */
class Przelewy24BlikAlias extends ObjectModel
{
    /**
     * Customer id.
     *
     * @var int
     */
    public $customer_id;

    /**
     * Alias (max length 100)
     *
     * @var string
     */
    public $alias;

    /**
     * Last order id.
     *
     * @var int
     */
    public $last_order_id;

    const TABLE = 'przelewy24_blik_alias';

    const CUSTOMER_ID = 'customer_id';
    const ALIAS = 'alias';
    const LAST_ORDER_ID = 'last_order_id';

    /**
     * Model definition.
     *
     * @var array
     */
    public static $definition = [
        'table' => self::TABLE,
        'primary' => self::CUSTOMER_ID,
        'fields' => [
            self::CUSTOMER_ID => ['type' => self::TYPE_INT],
            self::ALIAS => ['type' => self::TYPE_STRING],
            self::LAST_ORDER_ID => ['type' => self::TYPE_INT],
        ],
    ];

    /**
     * Creates new entity or returns existing one with cleared alias
     *
     * @param int $customerId
     *
     * @return Przelewy24BlikAlias
     */
    public static function prepareEmptyModel($customerId)
    {
        $model = new Przelewy24BlikAlias($customerId);
        $model->customer_id = $customerId;
        $model->alias = null;
        if (!Validate::isLoadedObject($model)) {
            $model->save();
        }

        return $model;
    }
}
