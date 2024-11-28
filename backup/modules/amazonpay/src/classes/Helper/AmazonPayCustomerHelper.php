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

class AmazonPayCustomerHelper
{

    /**
     * @return bool
     */
    public static function forceAccountCreation()
    {
        if (Configuration::get('AMAZONPAY_CREATE_AND_OVERRIDE_GUEST_CHECKOUT_SETTING') == '1') {
            return false;
        }
        return Configuration::get('PS_GUEST_CHECKOUT_ENABLED') ? true : false;
    }

    /**
     * @param $amazon_customer_id
     * @param bool $ignore_guest
     * @return bool|mixed
     */
    public static function findByAmazonCustomerId($amazon_customer_id, $ignore_guest = true)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
				SELECT c.*
				FROM `' . _DB_PREFIX_ . 'customer` c
                JOIN `' . _DB_PREFIX_ . 'amazonpay_customer` ac ON c.`id_customer` = ac.`id_customer`
				WHERE ac.`amazon_customer_id` = \'' . pSQL($amazon_customer_id) . '\'
				' . Shop::addSqlRestriction(Shop::SHARE_CUSTOMER) . '
				AND c.`deleted` = 0
				' . ($ignore_guest ? ' AND c.`is_guest` = 0' : ''));
        return $result['id_customer'] ? $result['id_customer'] : false;
    }

    /**
     * @param $email
     * @param bool $ignore_guest
     * @return bool|Customer
     */
    public static function findByEmailAddress($email, $ignore_guest = true)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
				SELECT *
				FROM `' . _DB_PREFIX_ . 'customer`
				WHERE `email` = \'' . pSQL($email) . '\'
				' . Shop::addSqlRestriction(Shop::SHARE_CUSTOMER) . '
				AND `deleted` = 0
				' . ($ignore_guest ? ' AND `is_guest` = 0' : ''));
        return $result['id_customer'] ? new Customer($result['id_customer']) : false;
    }

    /**
     * @param $id_customer
     * @param bool $ignore_guest
     * @param string $customer
     * @return bool|string
     */
    public static function getByCustomerID($id_customer, $ignore_guest = true, $customer = '')
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
				SELECT *
				FROM `' . _DB_PREFIX_ . 'customer`
				WHERE `id_customer` = \'' . pSQL($id_customer) . '\'
				' . Shop::addSqlRestriction(Shop::SHARE_CUSTOMER) . '
				AND `deleted` = 0
				' . ($ignore_guest ? ' AND `is_guest` = 0' : ''));

        if (! $result) {
            return false;
        }
        $customer->id = $result['id_customer'];
        foreach ($result as $key => $value) {
            if (array_key_exists($key, $customer)) {
                $customer->{$key} = $value;
            }
        }

        return $customer;
    }

    /**
     * @param Customer $customer
     * @param $amazon_customer_id
     * @throws PrestaShopDatabaseException
     */
    public static function saveCustomersAmazonReference(Customer $customer, $amazon_customer_id)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
            SELECT * FROM `' . _DB_PREFIX_ . 'amazonpay_customer` WHERE `id_customer` = \'' . (int)$customer->id . '\'
        ');

        if ($result) {
            Db::getInstance(_PS_USE_SQL_SLAVE_)->update('amazonpay_customer', array(
                'amazon_customer_id' => pSQL($amazon_customer_id)
            ), 'id_customer = \'' . (int) $customer->id . '\'');
        } else {
            Db::getInstance(_PS_USE_SQL_SLAVE_)->insert('amazonpay_customer', array(
                'id_customer' => pSQL((int)$customer->id),
                'amazon_customer_id' => pSQL($amazon_customer_id)
            ));
        }
    }

    /**
     * @param $id_customer
     * @return bool|mixed
     */
    public static function customerHasAmazonCustomerId($id_customer)
    {
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
				SELECT ac.*
				  FROM `' . _DB_PREFIX_ . 'customer` c
                  JOIN `' . _DB_PREFIX_ . 'amazonpay_customer` ac ON c.`id_customer` = ac.`id_customer`
				 WHERE c.`id_customer` = \'' . pSQL($id_customer) . '\'
				    ' . Shop::addSqlRestriction(Shop::SHARE_CUSTOMER) . '
				   AND c.`deleted` = 0');
        if (!$result) {
            return false;
        } else {
            return $result['amazon_customer_id'] ? $result['amazon_customer_id'] : false;
        }
    }
}
