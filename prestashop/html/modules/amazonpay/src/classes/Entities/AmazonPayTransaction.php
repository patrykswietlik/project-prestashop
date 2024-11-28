<?php
/**
 * 2007-2023 patworx.de
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade AmazonPay to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    patworx multimedia GmbH <service@patworx.de>
 *  @copyright 2007-2023 patworx multimedia GmbH
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class AmazonPayTransaction extends ObjectModel
{
    public $amazon_checkout_session;

    public $amazon_transaction;

    public $transaction_type;

    public $transaction_amount;

    public $transaction_reference;

    public $date_add;

    public $date_upd;

    public static $definition = array(
        'table' => 'amazonpay_transactions',
        'primary' => 'amazon_transaction_id',
        'fields' => array(
            'amazon_checkout_session' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 255),
            'amazon_transaction' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 255),
            'transaction_type' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 16),
            'transaction_amount' => array('type' => self::TYPE_FLOAT, 'required' => false),
            'transaction_reference' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 255),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        ),
    );

    /**
     * AmazonPayTransaction constructor.
     * @param null $id
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    /**
     * @param bool $autodate
     * @param bool $null_values
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function add($autodate = true, $null_values = false)
    {
        if (!parent::add($autodate, $null_values)) {
            return false;
        }
        return true;
    }

    /**
     * @param bool $null_values
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function update($null_values = false)
    {
        return parent::update($null_values);
    }

    /**
     * @return bool
     * @throws PrestaShopException
     */
    public function delete()
    {
        return parent::delete();
    }

    /**
     * @param $amazon_checkout_session
     * @param $amazon_transaction
     * @param $transaction_type
     * @param $transaction_amount
     * @return AmazonPayTransaction
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function store($amazon_checkout_session, $amazon_transaction, $transaction_type, $transaction_amount)
    {
        $tx = new self();
        $tx->amazon_checkout_session = $amazon_checkout_session;
        $tx->amazon_transaction = $amazon_transaction;
        $tx->transaction_type = $transaction_type;
        $tx->transaction_amount = $transaction_amount;
        $tx->add();
        return $tx;
    }

    /**
     * @param $charge_id
     * @return AmazonPayTransaction|bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function findByChargeId($charge_id)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT a.`amazon_transaction_id` FROM `' . _DB_PREFIX_ . 'amazonpay_transactions` a
                WHERE a.`amazon_transaction` = "' . pSQL($charge_id) . '"'
        );
        if (is_array($result) && $result['amazon_transaction_id']) {
            return new self($result['amazon_transaction_id']);
        } else {
            return false;
        }
    }

    /**
     * @param $refund_id
     * @return AmazonPayTransaction|bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function getByRefundId($refund_id)
    {
        return self::findByChargeId($refund_id);
    }

    /**
     * @param $transaction_reference
     * @return $this
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function updateReference($transaction_reference)
    {
        $this->transaction_reference = $transaction_reference;
        $this->update();
        return $this;
    }

    public static function getByCheckoutSession($amazon_checkout_session)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'amazonpay_transactions` a
                WHERE a.`amazon_checkout_session` = "' . pSQL($amazon_checkout_session) . '"'
        );
        if ($result && is_array($result) && sizeof($result) > 0) {
            return $result;
        }
        return [];
    }
}
