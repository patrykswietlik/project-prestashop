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
 * Class Przelewy24CustomerSetting
 */
class Przelewy24CustomerSetting extends ObjectModel
{
    /**
     * Customer id.
     *
     * @var int
     */
    public $customer_id;

    /**
     * Should credit card be remembered for this user.
     *
     * @var bool
     */
    public $card_remember;

    const TABLE = 'przelewy24_customersettings';

    const CUSTOMER_ID = 'customer_id';
    const CARD_REMEMBER = 'card_remember';

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
            self::CARD_REMEMBER => ['type' => self::TYPE_BOOL],
        ],
    ];

    /**
     * Przelewy24CustomerSetting constructor.
     *
     * @param int $customerId
     */
    public function __construct($customerId)
    {
        parent::__construct($customerId);
        $this->customer_id = $customerId;
        if (null === $this->card_remember) {
            $this->card_remember = false;
        }
    }

    /**
     * Updates value of card_remember field.
     *
     * @param bool $isCardRemembered
     *
     * @return Przelewy24CustomerSetting
     */
    public function setIsCardRemembered($isCardRemembered)
    {
        $this->card_remember = (bool) $isCardRemembered;

        return $this;
    }

    /**
     * Saves data stored in this model (information whether card should be remembered for customer).
     *
     * @param bool $nullValues
     * @param bool $autoDate
     *
     * @return bool
     */
    public function save($nullValues = false, $autoDate = true)
    {
        return Validate::isLoadedObject($this) ? $this->update($nullValues) : $this->add($autoDate, $nullValues);
    }

    /**
     * Prepares new model of Customer Setting.
     *
     * @param int $customerId
     *
     * @return Przelewy24CustomerSetting
     */
    public static function initialize($customerId)
    {
        return new Przelewy24CustomerSetting($customerId);
    }
}
