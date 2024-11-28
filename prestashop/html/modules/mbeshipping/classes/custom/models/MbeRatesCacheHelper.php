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

if (!defined('_PS_VERSION_')) {
    exit;
}

class MbeRatesCacheHelper extends ObjectModel
{
    public $id_mbe_shipping_rates_cache;
    public $id_cache;
    public $id_cart;
    public $response;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'mbe_shipping_rates_cache',
        'primary' => 'id_mbe_shipping_rates_cache',
        'fields' => [
            'id_mbe_shipping_rates_cache' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'id_cache' => ['type' => self::TYPE_STRING, 'required' => true, 'validate' => 'isMd5'],
            'id_cart' => ['type' => self::TYPE_INT, 'required' => true, 'validate' => 'isUnsignedInt'],
            'response' => ['type' => self::TYPE_STRING, 'required' => true, 'validate' => 'isString'],
            'date_add' => ['type' => self::TYPE_DATE, 'required' => true, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ]
    ];

    public static function get($id_cache, $id_cart) {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where('id_cache = "' . pSQL($id_cache) . '" AND id_cart = ' . (int)$id_cart);

        $result = Db::getInstance()->getRow($sql);
        if (empty($result)) {
            return false;
        }

        return unserialize($result['response']);
    }

    public static function getByCartId($id_cart) {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where('id_cart = ' . (int)$id_cart);
        $sql->orderBy('date_upd DESC');

        $result = Db::getInstance()->getRow($sql);
        if (empty($result)) {
            return false;
        }

        return unserialize($result['response']);
    }

    public static function exists($id_cache, $id_cart) {
        $sql = new DbQuery();
        $sql->select('COUNT(*)');
        $sql->from(self::$definition['table']);
        $sql->where('id_cache = "' . pSQL($id_cache) . '" AND id_cart = ' . (int)$id_cart);

        return (int)Db::getInstance()->getValue($sql);
    }

    public static function store($id_cache, $id_cart, $response) {
        $response = self::escapeString($response);
        if (self::exists($id_cache, $id_cart)) {
            $data = [
                'response' => serialize($response),
                'date_upd' => date('Y-m-d H:i:s'),
            ];

            return Db::getInstance()->update(self::$definition['table'], $data, 'id_cache = "' . pSQL($id_cache) . '" AND id_cart = ' . (int)$id_cart);
        }

        $data = [
            'id_cache' => $id_cache,
            'id_cart' => $id_cart,
            'response' => serialize($response),
            'date_add' => date('Y-m-d H:i:s'),
            'date_upd' => date('Y-m-d H:i:s'),
        ];

        return Db::getInstance()->insert(self::$definition['table'], $data);
    }

    public static function clearOlderThanMonths($months = 1) {
        return Db::getInstance()->delete(self::$definition['table'], 'date_upd < DATE_SUB(NOW(), INTERVAL ' . (int)$months . ' MONTH)');
    }

    public static function clearAll() {
        return Db::getInstance()->delete(self::$definition['table']);
    }

    public static function clearByCartId($id_cart) {
        return Db::getInstance()->delete(self::$definition['table'], 'id_cart = ' . (int)$id_cart);
    }

    public static function escapeString($response) {
        if(!is_array($response)) {
            return array();
        }
        foreach($response as $k => $row) {
            if(isset($row->SubzoneDesc)) {
                $response[$k]->SubzoneDesc = str_replace("'", ' ', $row->SubzoneDesc);
            }
        }

        return $response;
    }
}
