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

class MbePickupBatchHelper extends ObjectModel
{
    public $id_mbeshipping_pickup_batch;
    public $pickup_batch_id;
    public $cutoff_period;
    public $cutoff_preferred_from;
    public $cutoff_preferred_to;
    public $cutoff_alternative_from;
    public $cutoff_alternative_to;
    public $notes;
    public $pickup_address_id;
    public $sender_name;
    public $sender_company;
    public $sender_address;
    public $sender_phone;
    public $sender_zip;
    public $sender_city;
    public $sender_state;
    public $sender_country;
    public $sender_email;
    public $date;
    public $status;

    public static $definition = [
        'table' => 'mbe_shipping_pickup_batch',
        'primary' => 'id_mbeshipping_pickup_batch',
        'fields' => [
            'id_mbeshipping_pickup_batch' => ["type" => self::TYPE_INT, "validate" => "isUnsignedInt"],
            'pickup_batch_id' => ["type" => self::TYPE_STRING, "required" => true, "validate" => "isString"],
            'cutoff_period' => ["type" => self::TYPE_STRING, "validate" => "isString"],
            'cutoff_preferred_from' => ["type" => self::TYPE_STRING, "validate" => "isString"],
            'cutoff_preferred_to' => ["type" => self::TYPE_STRING, "validate" => "isString"],
            'cutoff_alternative_from' => ["type" => self::TYPE_STRING,  "validate" => "isString"],
            'cutoff_alternative_to' => ["type" => self::TYPE_STRING,  "validate" => "isString"],
            'notes' => ["type" => self::TYPE_STRING,  "validate" => "isString"],
            'pickup_address_id' => ["type" => self::TYPE_INT,  "validate" => "isUnsignedInt"],
            'sender_name' => ["type" => self::TYPE_STRING,  "validate" => "isCustomerName"],
            'sender_company' => ["type" => self::TYPE_STRING,  "validate" => "isCustomerName"],
            'sender_address' => ["type" => self::TYPE_STRING,  "validate" => "isAddress"],
            'sender_phone' => ["type" => self::TYPE_STRING,  "validate" => "isPhoneNumber"],
            'sender_zip' => ["type" => self::TYPE_STRING,  "validate" => "isPostCode"],
            'sender_city' => ["type" => self::TYPE_STRING,  "validate" => "isCityName"],
            'sender_state' => ["type" => self::TYPE_STRING,  "validate" => "isCityName"],
            'sender_country' => ["type" => self::TYPE_STRING,  "validate" => "isCountryName"],
            'sender_email' => ["type" => self::TYPE_STRING,  "validate" => "isEmail"],
            'date' => ["type" => self::TYPE_STRING,  "validate" => "isDate"],
            'is_single_pickup' => ["type" => self::TYPE_BOOL, "required" => true, "validate" => "isBool"],
            'status' => ["type" => self::TYPE_STRING, "required" => true, "validate" => "isString"],
        ]
    ];

    public static function insert(
        $pickup_batch_id,
        $cutoff_period,
        $cutoff_preferred_from,
        $cutoff_preferred_to,
        $cutoff_alternative_from = null,
        $cutoff_alternative_to = null,
        $notes = null,
        $date = null,
        $pickup_address_id = null,
        $sender_name = null,
        $sender_company = null,
        $sender_address = null,
        $sender_phone = null,
        $sender_zip = null,
        $sender_city = null,
        $sender_state = null,
        $sender_country = null,
        $sender_email = null,
        $is_single_pickup = false,
        $status = 'READY'
    )
    {
        if (!in_array($cutoff_period, ['MORNING', 'AFTERNOON'])) {
            return false;
        }

        if (!in_array($status, ['READY', 'SENT', 'CONFIRMED'])) {
            return false;
        }

        try {
            $result = \Db::getInstance()->insert(
                self::$definition['table'],
                [
                    'pickup_batch_id' => pSQL($pickup_batch_id),
                    'cutoff_period' => pSQL($cutoff_period),
                    'cutoff_preferred_from' => pSQL($cutoff_preferred_from),
                    'cutoff_preferred_to' => pSQL($cutoff_preferred_to),
                    'cutoff_alternative_from' => pSQL($cutoff_alternative_from),
                    'cutoff_alternative_to' => pSQL($cutoff_alternative_to),
                    'notes' => pSQL($notes),
                    'date' => pSQL($date),
                    'pickup_address_id' => (int)$pickup_address_id,
                    'sender_name' => pSQL($sender_name),
                    'sender_company' => pSQL($sender_company),
                    'sender_address' => pSQL($sender_address),
                    'sender_phone' => pSQL($sender_phone),
                    'sender_zip' => pSQL($sender_zip),
                    'sender_city' => pSQL($sender_city),
                    'sender_state' => pSQL($sender_state),
                    'sender_country' => pSQL($sender_country),
                    'sender_email' => pSQL($sender_email),
                    'is_single_pickup' => (int)$is_single_pickup,
                    'status' => pSQL($status),
                ]
            );

            if (!$result) {
                return false;
            }

            return \Db::getInstance()->Insert_ID();
        } catch (\PrestaShopDatabaseException $e) {
            return false;
        }
    }

    public static function edit(
        $pickup_batch_id,
        $cutoff_period,
        $cutoff_preferred_from,
        $cutoff_preferred_to,
        $cutoff_alternative_from = null,
        $cutoff_alternative_to = null,
        $notes = null,
        $date = null,
        $pickup_address_id = null,
        $sender_name = null,
        $sender_company = null,
        $sender_address = null,
        $sender_phone = null,
        $sender_zip = null,
        $sender_city = null,
        $sender_state = null,
        $sender_country = null,
        $sender_email = null,
        $is_single_pickup = false,
        $status = 'READY'
    )
    {
        if (!in_array($cutoff_period, ['MORNING', 'AFTERNOON'])) {
            return false;
        }

        if (!in_array($status, ['READY', 'SENT', 'CONFIRMED'])) {
            return false;
        }

        try {
            return \Db::getInstance()->update(
                self::$definition['table'],
                [
                    'cutoff_period' => pSQL($cutoff_period),
                    'cutoff_preferred_from' => pSQL($cutoff_preferred_from),
                    'cutoff_preferred_to' => pSQL($cutoff_preferred_to),
                    'cutoff_alternative_from' => pSQL($cutoff_alternative_from),
                    'cutoff_alternative_to' => pSQL($cutoff_alternative_to),
                    'notes' => pSQL($notes),
                    'date' => pSQL($date),
                    'pickup_address_id' => (int)$pickup_address_id,
                    'sender_name' => pSQL($sender_name),
                    'sender_company' => pSQL($sender_company),
                    'sender_address' => pSQL($sender_address),
                    'sender_phone' => pSQL($sender_phone),
                    'sender_zip' => pSQL($sender_zip),
                    'sender_city' => pSQL($sender_city),
                    'sender_state' => pSQL($sender_state),
                    'sender_country' => pSQL($sender_country),
                    'sender_email' => pSQL($sender_email),
                    'is_single_pickup' => (int)$is_single_pickup,
                    'status' => pSQL($status),
                ],
                'pickup_batch_id = ' . '"' . pSQL($pickup_batch_id) . '"'
            );
        } catch (\PrestaShopDatabaseException $e) {
            return false;
        }
    }

    public static function setPickupDefaultData(
        $pickup_batch_id,
        $cutoff_period,
        $cutoff_preferred_from,
        $cutoff_preferred_to,
        $cutoff_alternative_from = null,
        $cutoff_alternative_to = null,
        $notes = null
    ) {
        if (empty($pickup_batch_id)) {
            return false;
        }

        return \Db::getInstance()->update(
            self::$definition['table'],
            [
                'cutoff_period' => pSQL($cutoff_period),
                'cutoff_preferred_from' => pSQL($cutoff_preferred_from),
                'cutoff_preferred_to' => pSQL($cutoff_preferred_to),
                'cutoff_alternative_from' => pSQL($cutoff_alternative_from),
                'cutoff_alternative_to' => pSQL($cutoff_alternative_to),
                'notes' => pSQL($notes),
            ],
            'pickup_batch_id = ' .  '"' . pSQL($pickup_batch_id) . '"'
        );
    }

    public static function setPickupStatus($pickup_batch_id, $status)
    {
        if (empty($pickup_batch_id)) {
            return false;
        }

        if (!in_array($status, ['READY', 'SENT', 'CONFIRMED'])) {
            return false;
        }

        return \Db::getInstance()->update(
            self::$definition['table'],
            [
                'status' => pSQL($status),
            ],
            'pickup_batch_id = ' .  '"' . pSQL($pickup_batch_id) . '"'
        );
    }

    public static function setPickupSenderData(
        $pickup_batch_id,
        $sender_name,
        $sender_company,
        $sender_address,
        $sender_phone,
        $sender_zip,
        $sender_city,
        $sender_state,
        $sender_country,
        $sender_email
    ) {
        if (empty($pickup_batch_id)) {
            return false;
        }

        return \Db::getInstance()->update(
            self::$definition['table'],
            [
                'sender_name' => pSQL($sender_name),
                'sender_company' => pSQL($sender_company),
                'sender_address' => pSQL($sender_address),
                'sender_phone' => pSQL($sender_phone),
                'sender_zip' => pSQL($sender_zip),
                'sender_city' => pSQL($sender_city),
                'sender_state' => pSQL($sender_state),
                'sender_country' => pSQL($sender_country),
                'sender_email' => pSQL($sender_email),
            ],
            'pickup_batch_id = ' .  '"' . pSQL($pickup_batch_id) . '"'
        );
    }

    public function setPickupAddressId(
        $pickup_batch_id,
        $pickup_address_id
    ) {
        if (empty($pickup_batch_id)) {
            return false;
        }

        return \Db::getInstance()->update(
            self::$definition['table'],
            [
                'pickup_address_id' => (int)$pickup_address_id,
            ],
            'pickup_batch_id = ' .  '"' . pSQL($pickup_batch_id) . '"'
        );
    }

    public static function getById($id)
    {
        if (empty($id)) {
            return false;
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where(self::$definition['primary'] . ' = ' . (int)$id);

        return \Db::getInstance()->getRow($sql);
    }

    public static function getPickupBatchIdById($id)
    {
        if (empty($id)) {
            return false;
        }

        $result = self::getById($id);

        if (!$result) {
            return false;
        }

        return $result['pickup_batch_id'];
    }

    public static function getByPickupBatchId($pickup_batch_id)
    {
        if (empty($pickup_batch_id)) {
            return false;
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(self::$definition['table']);
        $sql->where('pickup_batch_id = ' . '"' . pSQL($pickup_batch_id) . '"');
        return \Db::getInstance()->getRow($sql);
    }

    public static function getOrderIdsByPickupBatchId($pickup_batch_id)
    {
        if (empty($pickup_batch_id)) {
            return false;
        }

        $sql = new DbQuery();
        $sql->select('id_order');
        $sql->from('mbe_shipping_order', 'mso');
        $sql->innerJoin(self::$definition['table'], 'mpb', 'mso.`id_mbeshipping_pickup_batch` = mpb.`id_mbeshipping_pickup_batch`');
        $sql->where('mpb.`pickup_batch_id` = ' . '"' . pSQL($pickup_batch_id) . '"');
        return array_column(\Db::getInstance()->executeS($sql), 'id_order');
    }

    public function delete()
    {
        if (parent::delete()) {
            $res = Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'mbe_shipping_order` SET `is_pickup_mode` = 0, `id_mbeshipping_pickup_batch` = NULL WHERE `id_mbeshipping_pickup_batch` = ' . (int) $this->id);
            return $res;
        }

        return false;
    }
}
