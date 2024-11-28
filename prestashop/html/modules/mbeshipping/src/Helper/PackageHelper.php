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

class PackageHelper
{
    protected $_cvs_package_table_name  = 'mbe_shipping_standard_packages';
    protected $_cvs_package_product_table_name  = 'mbe_shipping_standard_package_product';

    public function installCsvPackageTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . bqSQL($this->_cvs_package_table_name) . "`(
                `id_mbeshippingpackage` int(10) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `max_weight` decimal(12,4) default 0 not null,
                `length` decimal(12,4) default 0 not null,
                `width` decimal(12,4) default 0 not null,
                `height` decimal(12,4) default 0 not null,
                `package_label` varchar(255) not null,
                `package_code` varchar(55) not null,                
                 UNIQUE KEY MBE_PKG_PROD_UNIQUE (package_code))";
        $result = \Db::getInstance()->execute($sql);
        return $result;
    }

    public function uninstallCsvPackageTable()
    {
        $sql = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . bqSQL($this->_cvs_package_table_name) . "`";
        $result = \Db::getInstance()->execute($sql);
        return $result;
    }

    public function truncate()
    {
        $truncateSql = " TRUNCATE `" . _DB_PREFIX_ . bqSQL($this->_cvs_package_table_name) . "` ";
        $truncateResult = \Db::getInstance()->execute($truncateSql);
        return $truncateResult;
    }

    public function insertCsvPackage($max_weight, $length, $width, $height, $package_label, $package_code)
    {
        $sql = "INSERT INTO `" . _DB_PREFIX_ . bqSQL($this->_cvs_package_table_name) . "` ( ";
        $sql .= "`max_weight`,`length`,`width`,`height`,`package_label`,`package_code`";
        $sql .= ") VALUES (";
        $sql .= (float)$max_weight . ",";
        $sql .= (float)$length . ",";
        $sql .= (float)$width . ",";
        $sql .= (float)$height . ",";
        $sql .= "'" . pSQL($package_label) . "',";
        $sql .= "'" . pSQL($package_code) . "');";


        $insertResult = \Db::getInstance()->execute($sql);
        return $insertResult;
    }

	public function getPackageInfobyProduct( $productSku ) {
		$main_table      = _DB_PREFIX_ . bqSQL($this->_cvs_package_table_name);
		$packagesProduct = _DB_PREFIX_ . bqSQL($this->_cvs_package_product_table_name);

		$sql = "SELECT " . bqSQL($main_table) . ".*, " .
			bqSQL($packagesProduct) . ".id_mbeshippingpackageproduct as id_product, " . bqSQL($packagesProduct) .".single_parcel, " . bqSQL($packagesProduct) . ".custom_package " .
			" FROM " . bqSQL($main_table) .
			" LEFT JOIN " . bqSQL($packagesProduct) . " ON " .
			bqSQL($main_table) . ".package_code = " . bqSQL($packagesProduct) . ".package_code " .
			" WHERE " . bqSQL($packagesProduct) . ".product_sku = '" . pSQL($productSku) . "'";

		return \Db::getInstance()->executeS($sql);

	}

	public function getStandardPackages() {
        $main_table = _DB_PREFIX_ . bqSQL($this->_cvs_package_table_name);
		$join_table = _DB_PREFIX_ . bqSQL($this->_cvs_package_product_table_name);

		$sql = "SELECT " . bqSQL($main_table) . ".package_label, " . bqSQL($main_table) . ".id  FROM " . bqSQL($main_table) .
			" LEFT JOIN " . bqSQL($join_table) . " ON " .
			bqSQL($main_table) . ".package_code = " . bqSQL($join_table) . ".package_code " .
			" WHERE " . bqSQL($join_table) . ".custom_package <> true OR ". bqSQL($join_table) . ".custom_package is null";

        return \Db::getInstance()->executeS($sql);
	}

	public function getCsvPackages() {
		$main_table = _DB_PREFIX_ . bqSQL($this->_cvs_package_table_name);

        return \Db::getInstance()->executeS("SELECT *  FROM ". bqSQL($main_table));
	}

	public function getPackageInfobyId( $packageId ) {
        $main_table = _DB_PREFIX_ . bqSQL($this->_cvs_package_table_name);

        $sql = "SELECT *, 0 as single_parcel, 0 as custom_package FROM " . bqSQL($main_table)  . " WHERE id = ".  (int)$packageId;

		return \Db::getInstance()->executeS($sql);
	}
}
