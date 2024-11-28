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

class PackageProductHelper
{
    protected $_cvs_package_product_table_name  = 'mbe_shipping_standard_package_product';

    public function installCsvPackageProductTable()
    {

        $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . bqSQL($this->_cvs_package_product_table_name) . "`(
                `id_mbeshippingpackageproduct` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `custom_package` tinyint(1) NULL,
                `single_parcel` tinyint(1) NULL,
                `product_sku` varchar(64) NOT NULL DEFAULT '',
                `package_code` varchar(50) NOT NULL DEFAULT '',                                
                UNIQUE KEY MBE_PKG_PROD_PACKAGE_PRODUCT_UNIQUE  (package_code, product_sku),
                UNIQUE KEY MBE_PKG_PROD_PRODUCT_SKU  (product_sku),
                KEY MBE_PKG_PROD_PACKAGE_CODE  (package_code))";
        $result = \Db::getInstance()->execute($sql);
        return $result;
    }

    public function uninstallCsvPackageProductTable()
    {
        $sql = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . bqSQL($this->_cvs_package_product_table_name) . "`";
        $result = \Db::getInstance()->execute($sql);
        return $result;
    }

    public function truncate()
    {
        $truncateSql = " TRUNCATE `" . _DB_PREFIX_ . bqSQL($this->_cvs_package_product_table_name) . "` ";
        $truncateResult = \Db::getInstance()->execute($truncateSql);
        return $truncateResult;
    }

    public function insertCsvPackageProduct($custom_package, $single_parcel, $product_sku, $package_code)
    {
        $sql = "INSERT INTO `" . _DB_PREFIX_ . bqSQL($this->_cvs_package_product_table_name) . "` (";
        $sql .= "`custom_package`,`single_parcel`,`product_sku`,`package_code`)" ;
        $sql .= "VALUES (";
        $sql .= (int)$custom_package . ",";
        $sql .= (int)$single_parcel . ",";
        $sql .= "'" . pSQL($product_sku) . "',";
        $sql .= "'" . pSQL($package_code) . "');";


        $insertResult = \Db::getInstance()->execute($sql);
        return $insertResult;
    }
}
