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
 * Class Przelewy24Recurring
 */
class Przelewy24Recurring extends ObjectModel
{
    /**
     * Model id.
     *
     * @var int
     */
    public $id;

    /**
     * Website id.
     *
     * @var int
     */
    public $website_id;

    /**
     * Customer id.
     *
     * @var int
     */
    public $customer_id;

    /**
     * Reference id - max 35 characters.
     *
     * @var int
     */
    public $reference_id;

    /**
     * Expiration data (month and year in format mmyy) - max 4 characters.
     *
     * @var string
     */
    public $expires;

    /** @var string - max 32 characters */

    /**
     * Card number mask - max 32 characters.
     *
     * @var string
     */
    public $mask;

    /**
     * Type of card (max 20 characters).
     *
     * @var string
     */
    public $card_type;

    /**
     * Created at.
     *
     * @var DateTime
     */
    public $timestamp;

    const TABLE = 'przelewy24_recuring';

    const ID = 'id';
    const WEBSITE_ID = 'website_id';
    const CUSTOMER_ID = 'customer_id';
    const REFERENCE_ID = 'reference_id';
    const EXPIRES = 'expires';
    const MASK = 'mask';
    const CARD_TYPE = 'card_type';
    const TIMESTAMP = 'timestamp';

    const MAX_NUMBER_OF_CREDIT_CARDS_ALLOWED_PER_CUSTOMER = 999;

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
            self::WEBSITE_ID => ['type' => self::TYPE_INT, 'required' => true],
            self::CUSTOMER_ID => ['type' => self::TYPE_INT, 'required' => true],
            self::REFERENCE_ID => ['type' => self::TYPE_STRING, 'required' => true, 'size' => 35],
            self::EXPIRES => ['type' => self::TYPE_STRING, 'required' => true, 'size' => 4],
            self::MASK => ['type' => self::TYPE_STRING, 'required' => true, 'size' => 32],
            self::CARD_TYPE => ['type' => self::TYPE_DATE, 'required' => true, 'size' => 20],
            self::TIMESTAMP => ['type' => self::TYPE_BOOL],
        ],
    ];

    /**
     * Remembers card data, when it does not exist in database yet.
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function replace()
    {
        return Db::getInstance()->insert(
            self::TABLE,
            $this->getFields(),
            false,
            true,
            Db::REPLACE
        );
    }

    /**
     * Saves card data in database.
     *
     * @param int $customerId
     * @param string $referenceId
     * @param string $expirationYear
     * @param string $mask
     * @param string $cardType
     *
     * @return Przelewy24Recurring|null
     *
     * @throws PrestaShopDatabaseException
     */
    public static function remember($customerId, $referenceId, $expirationYear, $mask, $cardType)
    {
        $cardRemembered = new Przelewy24Recurring();
        $cardRemembered->website_id = 1;
        $cardRemembered->customer_id = $customerId;
        $cardRemembered->reference_id = $referenceId;
        $cardRemembered->expires = $expirationYear;
        $cardRemembered->mask = $mask;
        $cardRemembered->card_type = $cardType;

        return $cardRemembered->replace() ? $cardRemembered : null;
    }

    /**
     * Find credit cards by customer
     *
     * @param int $customerId
     * @param int $limit Only limited number of cards assigned to customer should be displayed (because of performance
     *                   reasons). It is unlikely for anyone to own more than 99999 (value configured in const
     *                   self::MAX_NUMBER_OF_CREDIT_CARDS_ALLOWED_PER_CUSTOMER) different credit cards anyway. But you
     *                   can still use limit = 0 to load all entries (default PrestaShopCollection behaviour) if you
     *                   need to.
     *
     * @return Przelewy24Recurring[]
     */
    public static function findByCustomerId($customerId, $limit = self::MAX_NUMBER_OF_CREDIT_CARDS_ALLOWED_PER_CUSTOMER)
    {
        $queryBuilder = new PrestaShopCollection(self::class);

        return $queryBuilder
            ->where('customer_id', '=', $customerId)
            ->setPageSize($limit)
            ->getResults();
    }

    /**
     * Gets result of findByCustomerId and parses each of them into array.
     *
     * @param int $customerId
     * @param int $limit for null value default limit from findByCustomerId will be applied
     *
     * @return array
     */
    public static function findArrayByCustomerId($customerId, $limit = null)
    {
        $result = [];
        foreach (Przelewy24Recurring::findByCustomerId($customerId, $limit) as $card) {
            $result[] = array_merge(
                $card->getFields(),
                [
                    'year' => Tools::substr($card->getFields()['expires'], 0, 2),
                    'month' => Tools::substr($card->getFields()['expires'], 2, 2),
                    'mask_substr' => Tools::substr($card->getFields()['mask'], -9),
                ]
            );
        }

        return $result;
    }

    /**
     * Returns true if this entry can be removed by certain user (using his id) and false if operation is not allowed).
     *
     * @param int $customerId
     *
     * @return bool
     */
    public function canBeRemovedByUser($customerId)
    {
        return $customerId && ((int) $this->customer_id === (int) $customerId);
    }
}
