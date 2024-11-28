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

class MbePickupAddressHelper extends ObjectModel
{
    public $id_mbe_shipping_pickup_address;
    public $pickup_address_id;
    public $trade_name;
    public $address1;
    public $address2;
    public $address3;
    public $zip_code;
    public $city;
    public $province;
    public $country;
    public $reference;
    public $phone1;
    public $phone2;
    public $email1;
    public $email2;
    public $fax;
    public $res;
    public $mmr;
    public $ltz;
    public $is_default;
    public $deleted;

    public static $definition = [
        'table' => 'mbe_shipping_pickup_address',
        'primary' => 'id_mbe_shipping_pickup_address',
        'fields' => [
            'id_mbe_shipping_pickup_address' => ["type" => self::TYPE_INT, "validate" => "isUnsignedInt"],
            'pickup_address_id' => ["type" => self::TYPE_INT, "validate" => "isUnsignedInt"],
            'trade_name' => ["type" => self::TYPE_STRING, "required" => true, "validate" => "isString"],
            'address1' => ["type" => self::TYPE_STRING, "required" => true, "validate" => "isAddress"],
            'address2' => ["type" => self::TYPE_STRING, "validate" => "isAddress"],
            'address3' => ["type" => self::TYPE_STRING, "validate" => "isAddress"],
            'zip_code' => ["type" => self::TYPE_STRING, "required" => true, "validate" => "isPostCode"],
            'city' => ["type" => self::TYPE_STRING, "required" => true, "validate" => "isCityName"],
            'province' => ["type" => self::TYPE_STRING, "required" => true, "validate" => "isCityName"],
            'country' => ["type" => self::TYPE_STRING, "required" => true, "validate" => "isCountryName"],
            'reference' => ["type" => self::TYPE_STRING, "required" => true, "validate" => "isString"],
            'phone1' => ["type" => self::TYPE_STRING, "required" => true, "validate" => "isPhoneNumber"],
            'phone2' => ["type" => self::TYPE_STRING, "validate" => "isPhoneNumber"],
            'email1' => ["type" => self::TYPE_STRING, "required" => true, "validate" => "isEmail"],
            'email2' => ["type" => self::TYPE_STRING, "validate" => "isEmail"],
            'fax' => ["type" => self::TYPE_STRING, "validate" => "isPhoneNumber"],
            'res' => ["type" => self::TYPE_BOOL, "validate" => "isBool"],
            'mmr' => ["type" => self::TYPE_BOOL, "validate" => "isBool"],
            'ltz' => ["type" => self::TYPE_BOOL, "validate" => "isBool"],
            'is_default' => ["type" => self::TYPE_BOOL, "validate" => "isBool"],
            'deleted' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
        ]
    ];

    public static function getPickupAddresses($only_active = false, $only_count = false)
    {
        $sql = new DbQuery();
        if($only_count) {
            $sql->select('COUNT(*)');
        } else {
            $sql->select('*');
        }
        $sql->from(self::$definition['table']);
        if ($only_active) {
            $sql->where('deleted = 0');
        }
        try {
            if($only_count) {
                return Db::getInstance()->getValue($sql);
            }
            return Db::getInstance()->executeS($sql);
        } catch (PrestaShopDatabaseException $e) {
            return [];
        }
    }

    public static function getByPickupAddressId($pickup_address_id)
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where('pickup_address_id = ' . (int) $pickup_address_id);
        return Db::getInstance()->getRow($sql);
    }

    public static function softDeleteById($pickup_address_id)
    {
        return Db::getInstance()->update(self::$definition['table'], ['deleted' => 1], 'pickup_address_id = ' . (int) $pickup_address_id);
    }

    public static function softDeleteAll()
    {
        return Db::getInstance()->update(self::$definition['table'], ['deleted' => 1]);
    }

    public static function createPickupAddress($pickup_address)
    {
		//Sanitize Slashes
        foreach($pickup_address as $k => $row) {
            $pickup_address[$k] = addslashes($row);
        }
		
        try {
            return Db::getInstance()->insert(self::$definition['table'], $pickup_address);
        } catch (PrestaShopDatabaseException $e) {
            return false;
        }
    }

    public static function updatePickupAddress($pickup_address)
    {
		//Sanitize Slashes
        foreach($pickup_address as $k => $row) {
            $pickup_address[$k] = addslashes($row);
        }
		
        return Db::getInstance()->update(self::$definition['table'], $pickup_address, 'pickup_address_id = ' . (int) $pickup_address['pickup_address_id']);
    }

    public static function getDefaultPickupAddress()
    {
        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where('deleted = 0 AND is_default = 1');
        try {
            return Db::getInstance()->getRow($sql);
        } catch (PrestaShopDatabaseException $e) {
            return [];
        }
    }
}
