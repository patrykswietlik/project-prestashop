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

use PrestaShop\Module\Mbeshipping\Lib\MbeWs;
use PrestaShop\Module\Mbeshipping\Helper\DataHelper;
use PrestaShop\Module\Mbeshipping\Helper\RatesHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Carrier extends CarrierCore
{
    const TESTTEST = 'Dont remove';

    public $mbe_module = true;

    public static function getCarriers($id_lang, $active = false, $delete = false, $id_zone = false, $ids_group = null,
                                       $modules_filters = self::PS_CARRIERS_ONLY, $cart_par = null)
    {
        if (!Module::isEnabled('mbeshipping')) {
            return parent::getCarriers($id_lang, $active, $delete, $id_zone, $ids_group, $modules_filters);
        }

		if (!class_exists('RatesHelper')) {
			$mbe_module = Module::getInstanceByName('mbeshipping');
		}
        $ratesHelper = new RatesHelper();
        $dataHelper = new DataHelper();
        if ($ids_group && (!is_array($ids_group) || !count($ids_group))) {
            return array();
        }
        $sql = '
		SELECT c.*, cl.delay
		FROM `' . _DB_PREFIX_ . 'carrier` c';

        $sql .= ' LEFT JOIN `' . _DB_PREFIX_ . 'carrier_lang` cl ON (c.`id_carrier` = cl.`id_carrier` AND 
        cl.`id_lang` = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('cl') . ')';
        $sql .= ' LEFT JOIN `' . _DB_PREFIX_ . 'carrier_zone` cz ON (cz.`id_carrier` = c.`id_carrier`)' .
            ($id_zone ? ' LEFT JOIN `' . _DB_PREFIX_ . 'zone` z ON (z.`id_zone` = ' . (int)$id_zone . ')' : '') . '
		' . Shop::addSqlAssociation('carrier', 'c') . '
		WHERE c.`deleted` = ' . ($delete ? '1' : '0');

        if ($active) {
            $sql .= ' AND c.`active` = 1 ';
        }
        if ($id_zone) {
            $sql .= ' AND cz.`id_zone` = ' . (int)$id_zone . ' AND z.`active` = 1 ';
        }
        if ($ids_group) {
            $sql .= ' AND EXISTS (SELECT 1 FROM ' . _DB_PREFIX_ . 'carrier_group 
            WHERE ' . _DB_PREFIX_ . 'carrier_group.id_carrier = c.id_carrier 
            AND id_group IN (' . pSQL(implode(',', array_map('intval', $ids_group))) . ')) ';
        }
        switch ($modules_filters) {
            case 1:
                $sql .= ' AND c.is_module = 0 ';
                break;
            case 2:
                $sql .= ' AND c.is_module = 1 ';
                break;
            case 3:
                $sql .= ' AND c.is_module = 1 AND c.need_range = 1 ';
                break;
            case 4:
                $sql .= ' AND (c.is_module = 0 OR c.need_range = 1) ';
                break;
        }
        if (class_exists('Context')) {
            $context = Context::getContext();
            if (!($context->controller instanceof AdminController)) {
                $sql .= ' AND c.external_module_name <> "mbeshipping" ';
            }
        }
        $sql2 = '';
        if (Configuration::get('mbe_active') == '1') {
            if (!isset($cart)) {
                $cart = $context->cart;
            }
            if (!isset($cart)) {
                $cart = $cart_par;
            }
            if (is_a($cart, 'Cart')) {
                $id_address_delivery = $cart->id_address_delivery;
                if ($ratesHelper->enabledCountry($id_address_delivery)) {
                    $myCarriers = $ratesHelper->collectRates($cart);
                    if (is_array($myCarriers) && !empty($myCarriers)) {
                        $names = array();
                        foreach ($myCarriers as $obj) {
                            $names[] = '"' . pSQL($obj['carrier']->name) . '"';
                        }
                        $sql2 .= ' UNION ';
                        $sql2 .= '
                    SELECT c.*, cl.delay
                    FROM `' . _DB_PREFIX_ . 'carrier` c';

                        $sql2 .= ' LEFT JOIN `' . _DB_PREFIX_ . 'carrier_lang` cl ON (c.`id_carrier` = cl.`id_carrier` 
                        AND cl.`id_lang` = ' . (int)$id_lang . Shop::addSqlRestrictionOnLang('cl') . ')';
                        $sql2 .= ' LEFT JOIN `' . _DB_PREFIX_ . 'carrier_zone` cz ON (cz.`id_carrier` = c.`id_carrier`)'
                            . ($id_zone ? 'LEFT JOIN `' . _DB_PREFIX_ . 'zone` z ON (z.`id_zone` = ' .
                                (int)$id_zone . ')' : '') . '
                    ' . Shop::addSqlAssociation('carrier', 'c') . '
                    WHERE c.`deleted` = ' . ($delete ? '1' : '0');

                        if ($active) {
                            $sql2 .= ' AND c.`active` = 1 ';
                        }
                        if ($id_zone) {
                            $sql2 .= ' AND cz.`id_zone` = ' . (int)$id_zone . ' AND z.`active` = 1 ';
                        }
                        if ($ids_group) {
                            $sql2 .= ' AND EXISTS (SELECT 1 FROM ' . _DB_PREFIX_ . 'carrier_group 
                            WHERE ' . _DB_PREFIX_ . 'carrier_group.id_carrier = c.id_carrier 
                            AND id_group IN (' . pSQL(implode(',', array_map('intval', $ids_group))) . ')) ';
                        }
                        $sql2 .= '  AND c.external_module_name = "mbeshipping" 
                        AND c.name IN (' . join(',', array_unique($names)) . ') ';
                    }
                }
            }
        } else {
            $sql .= ' AND c.external_module_name <> "mbeshipping" ';
        }
        if ($sql != '') {
            $sql .= $sql2;
        }
        $sql .= ' GROUP BY c.`id_carrier`';
        $cache_id = 'Carrier::getCarriers_' . md5($sql);

        if (!Cache::isStored($cache_id)) {
            $carriers = Db::getInstance()->executeS($sql);
            Cache::store($cache_id, $carriers);
        } else {
            $carriers = Cache::retrieve($cache_id);
        }

        foreach ($carriers as $key => $carrier) {
            if (isset($myCarriers)) {
                foreach ($myCarriers as $t => $m) {
                    if (($m['carrier']->id ==  $carrier['id_carrier'])) {
                        $carriers[$key]['price'] = $myCarriers[$t]['price'];
                    }
                }
            }
        }
        return $carriers;
    }
}
