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
 * Class Przelewy24Order
 */
class Przelewy24Order extends ObjectModel
{
    /**
     * PrestaShop order id.
     *
     * @var int
     */
    public $pshop_order_id;

    /**
     * Session id.
     *
     * @var string
     */
    public $p24_session_id;

    /**
     * P24 order id.
     *
     * @var int
     */
    public $p24_order_id;

    /**
     * P24 order id.
     *
     * @var int
     */
    public $p24_full_order_id;

    /**
     * Amount in centiles.
     *
     * @var int
     */
    public $amount;

    /**
     * Moment the message was received.
     *
     * @var DateTime
     */
    public $received;

    const TABLE = 'przelewy24_order';
    const P24_ORDER_ID = 'p24_order_id';
    const PSHOP_ORDER_ID = 'pshop_order_id';
    const P24_SESSION_ID = 'p24_session_id';
    const P24_FULL_ORDER_ID = 'p24_full_order_id';
    const AMOUNT = 'amount';
    const RECEIVED = 'received';

    /**
     * Model definition.
     *
     * @var array
     */
    public static $definition = [
        'table' => self::TABLE,
        'primary' => self::P24_ORDER_ID,
        'fields' => [
            self::P24_ORDER_ID => ['type' => self::TYPE_INT, 'required' => true],
            self::PSHOP_ORDER_ID => ['type' => self::TYPE_INT, 'required' => true],
            self::P24_SESSION_ID => ['type' => self::TYPE_STRING, 'required' => true],
            self::P24_FULL_ORDER_ID => ['type' => self::TYPE_STRING, 'required' => false],
            self::AMOUNT => ['type' => self::TYPE_INT, 'required' => false],
            self::RECEIVED => ['type' => self::TYPE_DATE, 'required' => false],
        ],
    ];

    /**
     * Saves order.
     *
     * @param int $p24OrderId
     * @param int $pshopOrderId
     * @param string $p24SessionId
     */
    public static function saveOrder($p24OrderId, $pshopOrderId, $p24SessionId, $p24FullOrderId = null, $amount = null)
    {
        try {
            $now = new DateTime();

            $przelewy24Order = new Przelewy24Order();
            $przelewy24Order->p24_order_id = (int) $p24OrderId;
            $przelewy24Order->pshop_order_id = (int) $pshopOrderId;
            $przelewy24Order->p24_session_id = $p24SessionId;
            if ($p24FullOrderId) {
                $przelewy24Order->p24_full_order_id = $p24FullOrderId;
            }
            if ($amount) {
                $przelewy24Order->amount = $amount;
            }
            $przelewy24Order->received = $now->format('Y-m-d H:i:s');
            $przelewy24Order->add();
        } catch (PrestaShopException $exception) {
            PrestaShopLogger::addLog('Przelewy24Order -- savePrzelewy24Order ' . $exception->getMessage(), 3);
        }
    }

    /**
     * Gets P24 order by PrestaShop order id.
     *
     * @param int $id
     *
     * @return Przelewy24Order
     */
    public function getByPshopOrderId($id)
    {
        $id = (int) $id;

        $orders = new PrestaShopCollection('Przelewy24Order');
        $orders->where('pshop_order_id', '=', $id);

        return $orders->getFirst();
    }
}
