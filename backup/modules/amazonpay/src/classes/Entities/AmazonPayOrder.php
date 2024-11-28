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

class AmazonPayOrder extends ObjectModel
{
    public $id_order;

    public $id_cart;

    public $amazon_checkout_session_id;

    public $amazon_charge_permission_id;

    public $amazon_charge_id;


    public static $definition = array(
        'table' => 'amazonpay_orders',
        'primary' => 'id',
        'fields' => array(
            'id_order' =>                    array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
            'id_cart' =>                     array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
            'amazon_checkout_session_id' =>  array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 255),
            'amazon_charge_permission_id' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 255),
            'amazon_charge_id' =>            array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 255),
        ),
    );

    /**
     * AmazonPayOrder constructor.
     * @param null $id
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    /**
     * @param $newStatus
     * @param bool $check
     * @param bool $sendmail
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function setOrderStatus($newStatus, $check = true, $sendmail = true)
    {
        if ((int)$this->id_order > 0) {
            if ($check) {
                $order = new Order((int)$this->id_order);
                $history = $order->getHistory(Context::getContext()->language->id, $newStatus);
                if (sizeof($history) > 0) {
                    return false;
                }
                if ($order->getCurrentState() == $newStatus) {
                    return false;
                }
            }
            $order_history = new OrderHistory();
            $order_history->id_order = (int)$this->id_order;
            $order_history->changeIdOrderState((int)$newStatus, (int)$this->id_order, true);
            if ($sendmail) {
                $order_history->addWithemail(true);
            } else {
                $order_history->add(true);
            }
        }
    }

    /**
     * @param $checkout_session_id
     * @return AmazonPayOrder
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function findByCheckoutSessionId($checkout_session_id)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT a.`id` FROM `' . _DB_PREFIX_ . 'amazonpay_orders` a
            WHERE a.`amazon_checkout_session_id` = "' . pSQL($checkout_session_id) . '"'
        );
        if (is_array($result) && $result['id']) {
            return new self($result['id']);
        } else {
            $obj = new self();
            $obj->amazon_checkout_session_id = $checkout_session_id;
            return $obj;
        }
    }

    /**
     * @param $charge_id
     * @return AmazonPayOrder
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function findByChargeId($charge_id)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT a.`id` FROM `' . _DB_PREFIX_ . 'amazonpay_orders` a
                WHERE a.`amazon_charge_id` = "' . pSQL($charge_id) . '"'
        );
        if (is_array($result) && $result['id']) {
            return new self($result['id']);
        } else {
            return false;
        }
    }

    /**
     * @param $cart_id
     * @return AmazonPayOrder|bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function findByCartId($cart_id)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT a.`id` FROM `' . _DB_PREFIX_ . 'amazonpay_orders` a
                WHERE a.`id_cart` = "' . pSQL($cart_id) . '" AND a.`id_order` > 0'
        );
        if (is_array($result) && $result['id']) {
            return new self($result['id']);
        } else {
            return false;
        }
    }

    /**
     * @param $id_order
     * @return AmazonPayOrder|bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function findByIdOrder($id_order)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            'SELECT a.`id` FROM `' . _DB_PREFIX_ . 'amazonpay_orders` a
            WHERE a.`id_order` = "' . pSQL($id_order) . '"'
        );
        if (is_array($result) && $result['id']) {
            return new self($result['id']);
        } else {
            return false;
        }
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
}
