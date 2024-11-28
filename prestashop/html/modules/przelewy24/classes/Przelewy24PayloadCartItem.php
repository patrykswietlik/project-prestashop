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
 * Class Przelewy24PayloadCartItem
 */
class Przelewy24PayloadCartItem
{
    /**
     * Seller id.
     *
     * @var string|null
     */
    public $sellerId;

    /**
     * Seller category.
     *
     * @var string|null
     */
    public $sellerCategory;

    /**
     * Name.
     *
     * @var string|null
     */
    public $name;

    /**
     * Description.
     *
     * @var string|null
     */
    public $description;

    /**
     * Quantity.
     *
     * @var int|null
     */
    public $quantity;

    /**
     * Price.
     *
     * @var int|null
     */
    public $price;

    /**
     * Number.
     *
     * @var string|null
     */
    public $number;
}
