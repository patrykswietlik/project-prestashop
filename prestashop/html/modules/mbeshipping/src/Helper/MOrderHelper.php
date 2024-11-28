<?php
/**
 * 2017-2022 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    MBE Worldwide
 * @copyright 2017-2024 MBE Worldwide
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of MBE Worldwide
 */

namespace PrestaShop\Module\Mbeshipping\Helper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MOrderHelper
{
    protected $_morder_table_name = 'mbe_shipping_order';

    public function installMOrderTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . bqSQL($this->_morder_table_name) . "`(
                `id_mbeshipping_order` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `id_order` int(10) default 0 not null,
                `is_download_available` int(10) default 0 not null,
                `is_pickup_mode` tinyint(1) default 0 not null,
                `is_dp` tinyint(1) default 0 not null,
                `service_dp` VARCHAR(255) NOT NULL,
                `id_mbeshipping_pickup_batch` int(10) default 0 not null,
                `net_tax_duty_total_price` decimal(20,6) DEFAULT NULL,
                `custom_duties_guaranteed` tinyint(1) DEFAULT NULL,
                UNIQUE KEY MBE_ORDER_MO_UNIQUE (id_order))";
        $result = \Db::getInstance()->execute($sql);
        return $result;
    }

    public function uninstallMOrderTable()
    {
        $sql = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . bqSQL($this->_morder_table_name) . "`";
        $result = \Db::getInstance()->execute($sql);
        return $result;
    }

    public function getDownloadAvailableByOrderId($id_order)
    {
        $main_table = _DB_PREFIX_ . bqSQL($this->_morder_table_name);

        $sql = "
        SELECT `is_download_available`
        FROM " . $main_table . "
        WHERE `id_order` = ".(int)$id_order;

        return \Db::getInstance()->getValue($sql);
    }

    /**
     * @param int $id_order
     * @param bool $is_download_available
     * @param bool $is_pickup_mode
     * @param int $id_mbeshipping_pickup_batch
     * @param float|null $net_tax_duty_total_price
     * @param bool|null $custom_duties_guaranteed
     * @return bool
     */
    public function insertOrder(
        int   $id_order,
        bool  $is_download_available = false,
        bool  $is_pickup_mode = false,
        int   $id_mbeshipping_pickup_batch = 0,
        float $net_tax_duty_total_price = null,
        bool $custom_duties_guaranteed = null
    )
    {
        if (!$id_order) {
            return false;
        }

        $data = [
            'id_order' => (int)$id_order,
            'is_download_available' => (bool)$is_download_available,
            'is_pickup_mode' => (bool)$is_pickup_mode,
            'id_mbeshipping_pickup_batch' => (int)$id_mbeshipping_pickup_batch,
        ];

        if ($net_tax_duty_total_price !== null) {
            $data['net_tax_duty_total_price'] = (float)$net_tax_duty_total_price;
        }

        if ($custom_duties_guaranteed !== null) {
            $data['custom_duties_guaranteed'] = (bool)$custom_duties_guaranteed;
        }

        try {
            return \Db::getInstance()->insert(
                bqSQL($this->_morder_table_name),
                $data
            );
        } catch (\PrestaShopDatabaseException $e) {
            return false;
        }
    }

    public function setOrderDownloadAvailable($id_order, $is_download_available = 0)
    {
        if (!isset($id_order)) {
            return false;
        }

        return \Db::getInstance()->update(
            bqSQL($this->_morder_table_name),
            [
                'is_download_available' => (int)$is_download_available
            ],
            'id_order = ' . (int)$id_order
        );
    }

    public function setOrderPickupMode($id_order, $is_pickup_mode = 0)
    {
        if (!isset($id_order)) {
            return false;
        }

        return \Db::getInstance()->update(
            bqSQL($this->_morder_table_name),
            [
                'is_pickup_mode' => (int)$is_pickup_mode
            ],
            'id_order = ' . (int)$id_order
        );
    }

    public function setOrderPickupBatch($id_order, $id_mbeshipping_pickup_batch = null)
    {
        if (!isset($id_order)) {
            return false;
        }

        if (!isset($id_mbeshipping_pickup_batch)) {
            return false;
        }

        return \Db::getInstance()->update(
            bqSQL($this->_morder_table_name),
            [
                'id_mbeshipping_pickup_batch' => (int)$id_mbeshipping_pickup_batch
            ],
            'id_order = ' . (int)$id_order
        );
    }

    /**
     * @param $id_order
     * @return false|array
     */
    public function getByOrderId($id_order)
    {
        $query = new \DbQuery();
        $query->select('*');
        $query->from(bqSQL($this->_morder_table_name));
        $query->where('id_order = ' . (int)$id_order);

        $result = \Db::getInstance()->getRow($query);
        if (empty($result)) {
            return false;
        }

        return $result;
    }

    public static function hasPickupBatchByOrderId($id_order)
    {
        $query = new \DbQuery();
        $query->select('id_mbeshipping_pickup_batch');
        $query->from(bqSQL('mbe_shipping_order'));
        $query->where('id_order = ' . (int)$id_order);

        $result = \Db::getInstance()->getValue($query);
        return $result && $result > 0;
    }

    public static function detachOrderIdFromPickupBatch($id_order)
    {
        if (!isset($id_order)) {
            return false;
        }

        return \Db::getInstance()->update(
            bqSQL('mbe_shipping_order'),
            [
                'id_mbeshipping_pickup_batch' => 0,
                'is_pickup_mode' => 0
            ],
            'id_order = ' . (int)$id_order
        );
    }

    public static function getShippingNumber($id_order)
    {
        $idOrderCarrier = (int)\Db::getInstance()->getValue('
                SELECT `id_order_carrier`
                FROM `' . _DB_PREFIX_ . 'order_carrier`
                WHERE `id_order` = ' . (int) $id_order);
        if (!$idOrderCarrier) {
            return null;
        }

        $orderCarrier = new \OrderCarrier($idOrderCarrier);

        return $orderCarrier->tracking_number;
    }

    public function isDeliveryPointByIdOrder($id_order){
        if (!isset($id_order)) {
            return false;
        }

        $is_dp = (int)\Db::getInstance()->getValue('
                SELECT `is_dp`
                FROM `' . _DB_PREFIX_ . 'mbe_shipping_order`
                WHERE `id_order` = ' . (int) $id_order);
        if (!$is_dp) {
            return false;
        }

        return true;
    }

    public function setDeliveryPointByIdOrder($id_order, $type)
    {
        \Db::getInstance()->execute('
        UPDATE `' . _DB_PREFIX_ . 'mbe_shipping_order`
        SET `is_dp` = 1, `service_dp` = "'. $type .'" 
        WHERE `id_order` = ' . (int) $id_order);
    }

    public function setIsDownloadAvailableByIdOrder($id_order){
        \Db::getInstance()->execute('
        UPDATE `' . _DB_PREFIX_ . 'mbe_shipping_order`
        SET `is_download_available` = 1
        WHERE `id_order` = ' . (int) $id_order);
    }
}
