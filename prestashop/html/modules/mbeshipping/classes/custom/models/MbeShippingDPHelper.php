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

use PrestaShop\Module\Mbeshipping\Helper\MOrderHelper;


class MbeShippingDPHelper extends ObjectModel
{
    public const MBE_GELPRX_API_URL_SERVER_SANDBOX = 'https://test-platform.gelproximity.com';
    public const MBE_GELPRX_API_URL_SERVER_PRODUCTION = 'https://platform.gelproximity.com';
    public const MBE_GELPRX_API_REDIRECT_MODE = 'POST';
    public const MBE_GELPRX_CONFIG_AJAX_TOKEN = 'do1fGus173v153gP9';
    public const MBE_GELPRX_CONFIG_API_REFERENCE_PREFIX = 'CartId-';
    public const MBE_GELPRX_CONFIG_DEFAULT_LOCALE = 'it_IT';
    public const MBE_GELPRX_CONFIG_CARRIER_ID = 'GELPRX_CARRIER_ID';
    public $id_mbe_shipping_dp;
    public $id_cart;
    public $id_order;
    public $mbe_service_id;
    public $network_name;
    public $network_id;
    public $pudo_id;
    public $pudo_data;
    public $date_add;
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'mbe_shipping_dp',
        'primary' => 'id_mbe_shipping_dp',
        'fields' => [
            'id_mbe_shipping_dp' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'id_cart' => ['type' => self::TYPE_INT, 'required' => true],
            'id_order' => ['type' => self::TYPE_INT],
            'mbe_service_id' => ['type' => self::TYPE_STRING, 'required' => true],
            'network_name' => ['type' => self::TYPE_STRING, 'required' => true],
            'network_id' => ['type' => self::TYPE_STRING, 'required' => true],
            'pudo_id' => ['type' => self::TYPE_STRING, 'required' => true],
            'pudo_data' => ['type' => self::TYPE_STRING, 'required' => false, 'size' => 65000],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
        ]
    ];

    // metodo che verifica se esiste già un id_cart uguale
    public static function getIdMbeShippingDp($id_cart)
    {
        $sql = 'SELECT id_mbe_shipping_dp FROM ' . _DB_PREFIX_ . 'mbe_shipping_dp WHERE id_cart = ' . (int)$id_cart;
        return (int)Db::getInstance()->getValue($sql);
    }

    public static function getAllMbeShippingDpByIdOrder($id_order)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'mbe_shipping_dp WHERE id_order = ' . (int)$id_order;
        return Db::getInstance()->getRow($sql);
    }

    public static function getTypeByPudo($id_cart)
    {
        $sql = 'SELECT pudo_data FROM ' . _DB_PREFIX_ . 'mbe_shipping_dp WHERE id_cart = ' . (int)$id_cart;

        $result = Db::getInstance()->getValue($sql);

        if (!empty($result)) {
            $result_obj = json_decode($result);
            if ($result_obj->serviceType == 'LABELING') {
                return "NMDP";
            } else if ($result_obj->serviceType == 'PRENEGOTIATED') {
                return "GPP";
            }
        }

        return null;
    }

    public static function isDeliveryPointByShipmentCode($shipment_code)
    {
        $allowedStringCodes = ['NMDP', 'GPP', 'NMDP-GPP'];
        $allowedNumericCodes = ['11' , '12', '1112'];
        if(is_numeric($shipment_code)) {
            return in_array($shipment_code, $allowedNumericCodes);
        }
        return in_array($shipment_code, $allowedStringCodes);
    }

    public static function isDeliveryPointByIdOrder($id_order)
    {
        $order = new Order($id_order);
        $cart = new Cart($order->id_cart);

        return self::isDeliveryPointByIdCarrier($cart->id_carrier);
    }

    public static function isDeliveryPointByIdCarrier($id_carrier)
    {
        return $id_carrier == Configuration::get('MBE_SHIPPING_DP_CARRIER_ID');
    }

    public static function doUnserialize($strobj, $type = 'json', $params = '')
    {
        if ($type === 'json') {
            return json_decode($strobj, isset($params['assoc']) ? $params['assoc'] : false);
        }

        return false;
    }

    public static function downloadLDV($file_url, $tracking_number, $id_order)
    {
        $helper = new PrestaShop\Module\Mbeshipping\Helper\DataHelper();
        $newName = 'MBE_' . $id_order . '_' . $tracking_number;
        $destination = $helper->getMediaPath() . $newName . '.pdf';

        if ($file_content = Tools::file_get_contents($file_url)) {
            if (file_put_contents($destination, $file_content) !== false) {
                $mOrderHelper = new MOrderHelper();
                $mOrderHelper->setIsDownloadAvailableByIdOrder($id_order);

                return $newName;
            }
        }

        return false;
    }

    public static function storageData($id_cart, $payload)
    {
        if (empty($id_cart) || empty($payload)) {
            return false;
        }

        $id_cart_exists = self::getIdMbeShippingDp($id_cart);
        $id_mbe_shipping_dp = $id_cart_exists ? new self($id_cart_exists) : new self();
        $id_mbe_shipping_dp->id_cart = (int)$id_cart;
        $id_mbe_shipping_dp->mbe_service_id = (string)$payload['pickupPointId'];
        $id_mbe_shipping_dp->network_name = (string)$payload['networkName'];
        $id_mbe_shipping_dp->network_id = (string)$payload['networkCode'];
        $id_mbe_shipping_dp->pudo_id = (string)$payload['code'];
        $id_mbe_shipping_dp->pudo_data = json_encode($payload);
        $id_cart_exists ? $id_mbe_shipping_dp->update() : $id_mbe_shipping_dp->save();
    }

    public static function formatEurPrice($price)
    {
        return str_replace('.', ',', $price) . ' €';
    }

    public static function getPayloadByPsIdCart($id_cart)
    {
        $sql = 'SELECT `pudo_data`
        FROM `' . _DB_PREFIX_ . 'mbe_shipping_dp`
        WHERE `id_cart` = ' . (int)$id_cart;
        $result = Db::getInstance()->getValue($sql, true);

        if (is_null($result)) {
            return false;
        }

        return $result;
    }
}
