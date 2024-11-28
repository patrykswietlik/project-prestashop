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

class PsPackageProductHelper extends ObjectModel
{
    public $id_mbeshippingpackageproduct;
    public $custom_package;
    public $single_parcel;
    public $product_sku;
    public $package_code;

    public static $definition = [
        'table' => 'mbe_shipping_standard_package_product',
        'primary' => 'id_mbeshippingpackageproduct',
        'fields' => [
            'id_mbeshippingpackageproduct' => ["type" => self::TYPE_INT, "validate" => "isUnsignedInt"],
            'custom_package' => ["type" => self::TYPE_BOOL, "required" => true],
            'single_parcel' => ["type" => self::TYPE_BOOL, "required" => true],
            'product_sku' => ["type" => self::TYPE_STRING, "required" => true],
            'package_code' => ["type" => self::TYPE_STRING, "required" => true],
        ]
    ];
}
