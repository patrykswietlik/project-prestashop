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

use PrestaShop\Module\FacetedSearch\Hook\Configuration;
use PrestaShop\Module\Mbeshipping\Ws;

require_once(dirname(__FILE__) . '/../../classes/custom/models/MbeRatesCacheHelper.php');
require_once(dirname(__FILE__) . '/../../classes/custom/models/MbeShippingDPHelper.php');


if (!defined('MBE_UAP_WEIGHT_LIMIT_20_KG')) {
    define('MBE_UAP_WEIGHT_LIMIT_20_KG', 20);
}

if (!defined('MBE_UAP_LONGEST_LIMIT_97_CM')) {
    define('MBE_UAP_LONGEST_LIMIT_97_CM', 97);
}

if (!defined('MBE_UAP_TOTAL_SIZE_LIMIT_300_CM')) {
    define('MBE_UAP_TOTAL_SIZE_LIMIT_300_CM', 300);
}

if (!defined('MBE_UAP_SERVICE')) {
    define('MBE_UAP_SERVICE', 'MDP');
}

if (!defined('MBE_UAP_ALLOWED_COUNTRIES_LIST')) {
    define('MBE_UAP_ALLOWED_COUNTRIES_LIST', array('IT','FR','GB','ES','DE','PL'));
}

if (!defined('_PS_VERSION_')) {
    exit;
}

class RatesHelper
{
    protected $rate_table_name = 'mbeshippingrate';

    const SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_ITEM = 1;
    const SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_SHOPPING_CART_SINGLE_PARCEL = 2;
    const SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_SHOPPING_CART_MULTI_PARCEL = 3;
    const HANDLING_TYPE_PER_SHIPMENT = "S";
    const HANDLING_TYPE_PER_PARCEL = "P";
    const HANDLING_TYPE_FIXED = "F";
    const ITALIAN_URL = "https://www.mbe.it/it/tracking?c=@";
    const SPAIN_URL = "https://www.mbe.es/es/tracking?c=@";
    const GERMANY_URL = "https://www.mbe.de/de/tracking?c=@";
    const AUSTRIA_URL = "https://www.mbe.at/at/tracking?c=@";
    const FRANCE_URL = "https://www.mbefrance.fr/fr/suivi?c=@";
	const POLAND_URL = "https://www.mbe.pl/pl/tracking?c=@";
    const CROATIA_URL = "https://www.mbe.hr/hr/tracking?c=@";
    const UNITEDKINGDOM_URL = "https://www.mbe.co.uk/track?tracking=@";

    public function installRatesTable()
    {

        $sql = "
            CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . bqSQL($this->rate_table_name) . "`(
                `id_mbeshippingrate` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `country` varchar(4) NOT NULL DEFAULT '',
                `region` varchar(10) NOT NULL DEFAULT '',
                `city` varchar(30) NOT NULL DEFAULT '',
                `zip` varchar(10) NOT NULL DEFAULT '',
                `zip_to` varchar(10) NOT NULL DEFAULT '',
                `weight_from` decimal(12,4) NOT NULL DEFAULT '0.0000',
                `weight_to` decimal(12,4) NOT NULL DEFAULT '0.0000',
                `price` decimal(12,4) DEFAULT '0.0000',
                `delivery_type` varchar(255) DEFAULT '',
            PRIMARY KEY (`id_mbeshippingrate`))
        ";
        $result = \Db::getInstance()->execute($sql);
        return $result;
    }

    public function uninstallRatesTable()
    {
        $sql = "DROP TABLE IF EXISTS `" . _DB_PREFIX_ . bqSQL($this->rate_table_name) . "`";
        $result = \Db::getInstance()->execute($sql);
        return $result;
    }

    public function truncate()
    {
        $truncateSql = " TRUNCATE `" . _DB_PREFIX_ . bqSQL($this->rate_table_name) . "` ";
        $truncateResult = \Db::getInstance()->execute($truncateSql);
        return $truncateResult;
    }

    public function insertRate($country, $region, $city, $zip, $zipTo, $weightFrom, $weightTo, $price, $deliveryType)
    {
        $sql = "
                INSERT INTO `" . _DB_PREFIX_ . bqSQL($this->rate_table_name) . "` (
                    `country`,`region`,`city`,`zip`,`zip_to`,`weight_from`,`weight_to`,`price`,`delivery_type`
                ) 
                VALUES (
                    '" . pSQL($country) . "',
                    '" . pSQL($region) . "',
                    '" . pSQL($city) . "',
                    '" . pSQL($zip) . "',
                    '" . pSQL($zipTo) . "',
                    " . (float)$weightFrom . ",
                    " . (float)$weightTo . ",
                    " . (float)$price . ",
                    '" . pSQL($deliveryType) . "'
                );
            ";


        $insertResult = \Db::getInstance()->execute($sql);
        return $insertResult;
    }

    public function useCustomRates($country)
    {
        $result = false;
        if (\Configuration::get('shipments_csv_mode') === DataHelper::MBE_CSV_MODE_TOTAL) {
            $result = true;
        } elseif (\Configuration::get('shipments_csv_mode') === DataHelper::MBE_CSV_MODE_PARTIAL) {
            $sql = "SELECT * FROM `" . _DB_PREFIX_ . bqSQL($this->rate_table_name) . "` WHERE `country` = '" . pSQL($country) . "'";
            $rates = \Db::getInstance()->executeS($sql);
            if (is_array($rates) && count($rates) > 0) {
                $result = true;
            }
        }
        return $result;
    }

    public function getCountryRates($country)
    {
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . bqSQL($this->rate_table_name) . "` WHERE `country` = '" . pSQL($country) . "'";

        $result = \Db::getInstance()->executeS($sql);
        return $result;
    }

    public function applyInsuranceToRate($rate, $insuranceValue)
    {
        $result = $rate;

        $percentageValue = \Configuration::get("mbe_shipments_csv_insurance_per") / 100 * $insuranceValue;
        $fixedValue = \Configuration::get("mbe_shipments_csv_insurance_min");

        if ($percentageValue < $fixedValue) {
            $result += $fixedValue;
        } else {
            $result += $percentageValue;
        }
        return $result;
    }

    public function getCustomRates($country, $region, $city, $postCode, $weight, $insuranceValue)
    {
        $result = array();
        $newdata = array();
        $zipSql = " '" . pSQL($postCode) . "' BETWEEN zip AND zip_to";

		$country = str_replace("'","''", $country);
        $region = str_replace("'","''", $region);
        $city = str_replace("'","''", $city);

        $helper = new DataHelper();
        $services = $helper->getAllowedShipmentServicesArray();

        foreach ($services as $service) {
            for ($j = 0; $j <= 7; $j++) {
                $sql = "SELECT * FROM `" . _DB_PREFIX_ . bqSQL($this->rate_table_name) . "` ";

                switch ($j) {
                    case 0:
                        $sql .= "WHERE ";
                        $sql .= "`country` = '" . pSQL($country) . "'";
                        $sql .= " AND ";
                        $sql .= "`region` = '" . pSQL($region) . "'";
                        $sql .= " AND ";
                        $sql .= " STRCMP(LOWER(city),LOWER('" . pSQL($city) . "')) = 0";
                        $sql .= " AND ";
                        $sql .= $zipSql;

                        break;

                    case 1:
                        $sql .= "WHERE ";
                        $sql .= "`country` = '" . pSQL($country) . "'";
                        $sql .= " AND ";
                        $sql .= "`region` = '" . pSQL($region) . "'";
                        $sql .= " AND ";
                        $sql .= "`city` = ''";
                        $sql .= " AND ";
                        $sql .= $zipSql;

                        break;
                    case 2:
                        $sql .= "WHERE ";
                        $sql .= "`country` = '" . pSQL($country) . "'";
                        $sql .= " AND ";
                        $sql .= "`region` = '" . pSQL($region) . "'";
                        $sql .= " AND ";
                        $sql .= "STRCMP(LOWER(city),LOWER('" . pSQL($city) . "')) = 0";
                        $sql .= " AND ";
                        $sql .= " zip = '' ";

                        break;
                    case 3:
                        $sql .= "WHERE ";
                        $sql .= "`country` = '" . pSQL($country) . "'";
                        $sql .= " AND ";
                        //$sql .= "`region` = '" . $region . "'";
                        $sql .= "`region` = ''";
                        $sql .= " AND ";
                        $sql .= "STRCMP(LOWER(city),LOWER('" . pSQL($city) . "')) = 0";
                        $sql .= " AND ";
                        $sql .= $zipSql;

                        break;
                    case 4:
                        $sql .= "WHERE ";
                        $sql .= "`country` = '" . pSQL($country) . "'";
                        $sql .= " AND ";
                        //$sql .= "`region` = '" . $region . "'";
                        $sql .= "`region` = ''";
                        $sql .= " AND ";
                        $sql .= "STRCMP(LOWER(city),LOWER('" . pSQL($city) . "')) = 0";
                        $sql .= " AND ";
                        $sql .= "zip = ''";

                        break;
                    case 5:
                        $sql .= "WHERE ";
                        $sql .= "`country` = '" . pSQL($country) . "'";
                        $sql .= " AND ";
                        //$sql .= "`region` = '" . $region . "'";
                        $sql .= "`region` = ''";
                        $sql .= " AND ";
                        $sql .= "city = ''";
                        $sql .= " AND ";
                        $sql .= $zipSql;

                        break;
                    case 6:
                        $sql .= "WHERE ";
                        $sql .= "`country` = '" . pSQL($country) . "'";
                        $sql .= " AND ";
                        $sql .= "`region` = '" . pSQL($region) . "'";
                        $sql .= " AND ";
                        $sql .= "city = ''";
                        $sql .= " AND ";
                        $sql .= "zip = ''";
                        break;
                    case 7:
                        $sql .= "WHERE ";
                        $sql .= "`country` = '" . pSQL($country) . "'";
                        $sql .= " AND ";
                        $sql .= "`region` = ''";
                        $sql .= " AND ";
                        $sql .= "city = ''";
                        $sql .= " AND ";
                        $sql .= "zip = ''";
                        break;
                }
                $sql .= " AND weight_from <= " . (float)$weight . " AND weight_to >=" . (float)$weight;
                $sql .= " AND delivery_type = '" . pSQL($service) . "'";

                $sql .= " ORDER BY country DESC, region DESC, zip DESC";

                $rows = \Db::getInstance()->executeS($sql);


                //$row = $read->fetchAll($select);
                if (!empty($rows)) {
                    // have found a result or found nothing and at end of list!
                    foreach ($rows as $data) {
                        $newdata[$data["delivery_type"]] = $data;
                    }
                    break;
                }
            }
        }

        $ws = new Ws();

        foreach ($newdata as $data) {
            $rate = new \stdClass;
            $rate->Service = $data["delivery_type"];
            $rate->ServiceDesc = $ws->getLabelFromShipmentType($data["delivery_type"]);
            $rate->SubzoneDesc = '';
            $rate->IdSubzone = '';

            $rate->NetShipmentTotalPrice = $data["price"];
            $result[] = $rate;

            //rate with insurance
            $rateWithInsurance = new \stdClass;
            $rateWithInsurance->Service = $helper->convertShippingCodeWithInsurance($data["delivery_type"]);
            $label = $ws->getLabelFromShipmentType($data["delivery_type"]);
            $rateWithInsurance->ServiceDesc = $helper->convertShippingLabelWithInsurance($label);
            $rateWithInsurance->SubzoneDesc = '';
            $rateWithInsurance->IdSubzone = '';

            $rateWithInsurance->NetShipmentTotalPrice = $this->applyInsuranceToRate($data["price"], $insuranceValue);
            $result[] = $rateWithInsurance;
        }

        return $result;
    }


    /**
     * @param $destCountry
     * @param $destRegion
     * @param $city
     * @param $destPostCode
     * @param $baseSubtotalInclTax
     * @param $weight
     * @param $boxes
     * @param $products
     * @param $insuranceValue
     * @param $oldResults
     * @param $iteration
     * @return array
     */
    public function getRates($destCountry, $destRegion, $city, $destPostCode, $baseSubtotalInclTax, $weight, $boxes, $products, $insuranceValue, $oldResults = array(), $iteration = 1)
    {
        $logger = new LoggerHelper();
        $helper = new DataHelper();
        if ($helper->isEnabledCustomMapping()) {
            // If custom mapping is used no MBE methods will be available
            return [];
        }

        $ws = new Ws();
        $result = [];
        $newResults = [];

        $allowedShipmentServicesArray = $helper->getAllowedShipmentServicesArray();
        $tax_duty_active = $this->useTaxAndDutyService();

        if (is_array($weight)) {
            $weightString = json_encode($weight);
        } else {
            $weightString = $weight;
        }

        if ($this->useCustomRates($destCountry)) {
            $totalWeight = 0;
            if (is_array($weight)) {
                foreach ($weight as $boxType) {
                    $totalWeight += array_sum($boxType['weight']);
                }
            }

            $values = json_encode([$destCountry, $destRegion, $destPostCode, $weightString, $boxes, $insuranceValue, $tax_duty_active]);
            $id_cache = md5($values);
            $id_cart = \Context::getContext()->cart->id;

            if ($cached_response = \MbeRatesCacheHelper::get($id_cache, $id_cart)) {
                $logger->logDebug(__METHOD__ . ' - Rates cache found for ' . $id_cache . ' and cart ' . $id_cart);
                $logger->logDebug(__METHOD__ . ' - Rates cache: ' . print_r($cached_response, true));
                $shipments = $cached_response;
            } else {
                $logger->logDebug(__METHOD__ . ' - No rates cache found for ' . $id_cache . ' and cart ' . $id_cart);
            }

            if (!isset($shipments) || $shipments === false) {
                $shipments = $this->getCustomRates($destCountry, $destRegion, $city, $destPostCode, $totalWeight, $insuranceValue);

                if ($tax_duty_active && !empty($shipments)) {
                    $shipments2 = $ws->estimateShipping(
                        $destCountry,
                        $destRegion,
                        $destPostCode,
                        $weight,
                        $boxes,
                        $products,
                        $insuranceValue,
                        $allowedShipmentServicesArray,
                        false,
                        [],
                        true);

                    if (!empty($shipments2)) {
                        $this->addTaxAndDutyService($shipments, $shipments2);
                    }
                }

                $logger->logDebug(__METHOD__ . ' - Storing rates cache for ' . $id_cache . ' and cart ' . $id_cart);
                $logger->logDebug(__METHOD__ . ' - Rates cache: ' . print_r($shipments, true));
                \MbeRatesCacheHelper::store($id_cache, $id_cart, $shipments);
            }
        } else {
            // Get Pickup State
            $pickup_address_default = null;
            $pickup_active = (bool)\Configuration::get('MBESHIPPING_PICKUP_MODE');
            if($pickup_active && \Configuration::get('MBESHIPPING_PICKUP_REQUEST_MODE') === 'automatically') {
                $pickup_address_default = \MbePickupAddressHelper::getDefaultPickupAddress();
                if(empty($pickup_address_default)) {
                    $pickup_active = false;
                }
            }

            $values = json_encode([$destCountry, $destRegion, $destPostCode, $weightString, $boxes, $insuranceValue, $pickup_active, $pickup_address_default, $tax_duty_active]);
            $id_cache = md5($values);
            $id_cart = \Context::getContext()->cart->id;

            if ($cached_response = \MbeRatesCacheHelper::get($id_cache, $id_cart)) {
                $logger->logDebug(__METHOD__ . ' - Rates cache found for ' . $id_cache . ' and cart ' . $id_cart);
                $logger->logDebug(__METHOD__ . ' - Rates cache: ' . print_r($cached_response, true));
                $shipments = $cached_response;
            } else {
                $logger->logDebug(__METHOD__ . ' - No rates cache found for ' . $id_cache . ' and cart ' . $id_cart);
            }

            if (!isset($shipments) || $shipments === false) {
                $shipments = $ws->estimateShipping(
                    $destCountry,
                    $destRegion,
                    $destPostCode,
                    $weight,
                    $boxes,
                    $products,
                    $insuranceValue,
                    $allowedShipmentServicesArray,
                    $pickup_active,
                    $pickup_address_default,
                    $tax_duty_active
                );

                $logger->logDebug(__METHOD__ . ' - Storing rates cache for ' . $id_cache . ' and cart ' . $id_cart);
                $logger->logDebug(__METHOD__ . ' - Rates cache: ' . print_r($shipments, true));
                \MbeRatesCacheHelper::store($id_cache, $id_cart, $shipments);
            }
        }

        if (!$shipments) {
            return [];
        }

        $allowedShipmentServicesArray = $helper->getAllowedShipmentServicesArray();
        $shipments = $this->selectShipments($shipments);
        foreach ($shipments as $shipment) {
            $shipmentMethod = $shipment->Service;
            $shipmentMethodKey = $shipment->Service;
            if (in_array($shipmentMethod, $allowedShipmentServicesArray, true)) {
                $shipmentTitle = $shipment->ServiceDesc;
                $shipmentTitle .= ' - ' . $shipment->SubzoneDesc;
                $shipmentPrice = $shipment->NetShipmentTotalPrice;
                $shipmentPrice = $this->applyFee($shipmentPrice, $boxes);

                $shippingThreshold = $helper->getThresholdByShippingServrice($shipmentMethod . (\Configuration::get('mbecountry') === $destCountry ? '' : '_ww'));

                        if ($shippingThreshold != null && $baseSubtotalInclTax >= $shippingThreshold) {
                            $shipmentPrice = 0;
                        }

                $customLabel = $helper->getShippingMethodCustomLabel($shipment->Service);

                $current = new \stdClass();
                $current->title = $customLabel ?: $shipment->ServiceDesc;
                $current->mbetitle = $shipment->ServiceDesc;
                $current->title_full = $shipmentTitle;
                $current->method = $shipmentMethod;
                $current->price = $shipmentPrice;
                $current->subzone = $shipment->SubzoneDesc;
                $current->subzone_id = $shipment->IdSubzone;
                $current->shipment_code = $shipmentMethodKey;
                $newResults[$shipmentMethodKey] = $current;
            }
        }

        if ($iteration == 1) {
            $result = $newResults;
        } else {
            foreach ($newResults as $newResultKey => $newResult) {
                if (array_key_exists($newResultKey, $oldResults)) {
                    $newResult->price += $oldResults[$newResultKey]->price;
                    $result[$newResultKey] = $newResult;
                }
            }
        }

        return $result;
    }


    /**
     * Collect rates
     *
     * @param $cart \Cart
     * @param $weight
     * @return array
     */
    public function collectRates(\Cart $cart)
    {
        //INSERT HERE CHECK FOR CSV
        $helper = new DataHelper();
        $logger = new LoggerHelper();
        $id_address = $cart->id_address_delivery;
        $address = new \Address($id_address);
        $country = new \Country((int)($address->id_country));

        $city = $address->city;

        $region = new \State($address->id_state);
        $destRegion = $region->iso_code;
        $shipmentConfigurationMode = $helper->getShipmentConfigurationMode();
        $shipments = [];
        $baseSubtotalInclTax = $cart->getOrderTotal(true, \Cart::BOTH_WITHOUT_SHIPPING);

        $boxesDimensionWeight             = [];
        $boxesSingleParcelDimensionWeight = [];

        $products = [];
        foreach ($cart->getProducts() as $item) {
            $product = new \stdClass();
            $product->SKUCode = $item['reference'];
            $product->Description = $item['name'];
            $product->Quantity = $item['cart_quantity'];
            $product->Price = $item['price'];
            $product->Weight = $helper->convertWeight($item['weight']);

            $category = new \Category((int)$item['id_category_default']);
            $product->Category = $category->getName();
            $products[] = $product;
        }

        if ($shipmentConfigurationMode == self::SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_ITEM) {
            $iteration = 1;
            foreach ($cart->getProducts() as $item) {

                $weight = $item['weight'];
                $weight = $helper->convertWeight($weight);

                $boxesDimensionWeight = [];
                $boxesSingleParcelDimensionWeight = [];

                // Retrieve the product info using the new box structure
                $helper->getBoxesArray(
                    $boxesDimensionWeight,
                    $boxesSingleParcelDimensionWeight,
                    $weight,
                    $helper->getPackageInfo($item['reference'])
                );

                if (\Configuration::get('mbe_shipments_ins_mode') === DataHelper::MBE_INSURANCE_WITH_TAXES) {
                    $insuranceValue = $item['price_wt'];
                } else {
                    $insuranceValue = $item['price'];
                }

                for ($i = 1; $i <= $item['cart_quantity']; $i++) {
                    $logger->logDebug("Product Iteration: $iteration");
                    $shipments = $this->getRates($country->iso_code, $destRegion, $city, $address->postcode, $baseSubtotalInclTax, $boxesDimensionWeight, 1, $products, $insuranceValue, $shipments, $iteration);
                    $iteration++;
                }
            }
        } elseif ($shipmentConfigurationMode == self::SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_SHOPPING_CART_SINGLE_PARCEL) {

            $insuranceValue = 0;

            foreach ($cart->getProducts() as $item) {
                $itemQty = $item['cart_quantity'];
                for ($i = 1; $i <= $itemQty; $i++) {
                    $packageInfo =  $helper->getPackageInfo($item['reference']);
                    $boxesDimensionWeight = $helper->getBoxesArray(
                        $boxesDimensionWeight,
                        $boxesSingleParcelDimensionWeight,
                        $helper->convertWeight($item['weight']),
                        $packageInfo
                    );
                }
                $insuranceValue += $this->getSubtotalForInsurance($item);
            }

            $boxesDimensionWeight = $helper->mergeBoxesArray(
                $boxesDimensionWeight,
                $boxesSingleParcelDimensionWeight
            );

            $numBoxes = $helper->countBoxesArray($boxesDimensionWeight);

            $logger->logDebug(__METHOD__ . " - Num Boxes: $numBoxes");
            $shipments = $this->getRates($country->iso_code, $destRegion, $city, $address->postcode, $baseSubtotalInclTax, $boxesDimensionWeight, $numBoxes, $products, $insuranceValue, array(), 1);
        } elseif ($shipmentConfigurationMode == self::SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_SHOPPING_CART_MULTI_PARCEL) {
            $numBoxes = 0;
            $insuranceValue = 0;

            foreach ($cart->getProducts() as $item) {
                $insuranceValue += $this->getSubtotalForInsurance($item);
                $numBoxes += $item['cart_quantity'];
                for ($i = 1; $i <= $item['cart_quantity']; $i++) {
                    $helper->getBoxesArray(
                        $boxesDimensionWeight,
                        $boxesSingleParcelDimensionWeight,
                        $helper->convertWeight($item['weight']),
                        $helper->getPackageInfo($item['reference'], true)
                    );
                }
            }

            $logger->logDebug(__METHOD__ . " - Num Boxes: $numBoxes");
            $shipments = $this->getRates($country->iso_code, $destRegion, $city, $address->postcode, $baseSubtotalInclTax, $boxesSingleParcelDimensionWeight, $numBoxes, $products, $insuranceValue, array(), 1);
        }

        if (empty($shipments)) {
            return [];
        }

        $result = [];
        $shipments = $this->mergeShipments($shipments, $cart->id, $country, $baseSubtotalInclTax);
        foreach ($shipments as $key => $shipment) {
            if ($shipment instanceof \stdClass) {
                $carrier = $this->createOrFindCarrier($shipment->title, $shipment->mbetitle, $shipment->shipment_code);
                \Configuration::updateValue('carrier_' . $carrier->id, $shipment->shipment_code);

                $result[$key] = array('carrier' => $carrier, 'price' => $shipment->price);
            } else {
                foreach ($shipment as $k => $obj) {
                    if (($obj instanceof \stdClass) && !isset($result[$k])) {

                            $carrier = $this->createOrFindCarrier($obj->title, $obj->mbetitle, $obj->shipment_code);
                            \Configuration::updateValue('carrier_' . $carrier->id, $obj->shipment_code);
                            $price = $obj->price;

                            $shippingThreshold = $helper->getThresholdByShippingServrice($obj->shipment_code . (\Configuration::get('mbecountry') === $country ? '' : '_ww'));

                            if ($shippingThreshold != null && $baseSubtotalInclTax >= $shippingThreshold) {
                                $price = 0;
                            }
                            $result[$k] = array('carrier' => $carrier, 'price' => $price);
                    }
                }
            }
        }

        return $result;
    }

    private function mergeShipments($shipments, $cart_id, $country, $baseSubtotalInclTax)
    {

        // ritorna il tipo di punto selezionato
        $typeOfPoint = \MbeShippingDPHelper::getTypeByPudo($cart_id);


        if (!empty($shipments['NMDP']) && !empty($shipments['GPP'])) {

            $price = 0;
            if (empty($typeOfPoint)) {
                $price = min($shipments['NMDP']->price, $shipments['GPP']->price);
            } else {
                $price = $shipments[$typeOfPoint]->price;
            }

            $shipments = $this->addDPShipment($shipments, 'NMDP-GPP', $price, $shipments['NMDP']->subzone,
                $shipments['NMDP']->subzone_id, $country, $baseSubtotalInclTax);

            unset($shipments['NMDP']);
            unset($shipments['GPP']);

        } else {
            if (!empty($shipments['11']) && !empty($shipments['12'])) {

                $price = 0;
                if (empty($typeOfPoint)) {
                    $price = min($shipments['11']->price, $shipments['12']->price);
                } else {

                    if ($typeOfPoint == "NMDP") {
                        $typeOfPoint = '11';
                    } else {
                        if ($typeOfPoint == "GPP") {
                            $typeOfPoint = '12';
                        }
                    }

                    $price = $shipments[$typeOfPoint]->price;
                }

                $shipments = $this->addDPShipment($shipments, '1112', $price, $shipments['11']->subzone,
                    $shipments['11']->subzone_id, $country, $baseSubtotalInclTax);

                unset($shipments['11']);
                unset($shipments['12']);
            }
        }

        return $shipments;
    }

    public function addDPShipment($shipments, $id, $price, $subzone, $id_subzone, $destCountry, $baseSubtotalInclTax)
    {
        $isCustomLabel = \Configuration::get('mbe_custom_label_' . \Tools::strtolower($id));
        $shipments[$id] = (object)array(
            "title" => !empty($isCustomLabel) ? $isCustomLabel : "Delivery Point",
            "mbetitle" => "Delivery Point",
            "title_full" => "Delivery Point",
            "method" => $id,
            "price" => $price,
            "subzone" => $subzone,
            "subzone_id" => $id_subzone,
            "shipment_code" => $id
        );

        $helper = new DataHelper();
        $shippingThreshold = $helper->getThresholdByShippingServrice($id . (\Configuration::get('mbecountry') === $destCountry ? '' : '_ww'));

        if ($shippingThreshold != null && $baseSubtotalInclTax >= $shippingThreshold) {
            $shipments[$id]->price = 0;
        }

        return $shipments;
    }


    public function selectShipments($shipments)
    {
        $result = array();
        foreach ($shipments as $s) {
            $values = array();
            foreach ($result as $r) {
                $values[] = $r->Service;
            }
            if (!in_array($s->Service, $values)) {
                $result[] = $s;
            }
            else {
                $r = null;
                $k = null;
                foreach ($result as $t => $struct) {
                    if ($s->Service == $struct->Service) {
                        $r = $struct;
                        $k = $t;
                        break;
                    }
                }
                if ($s->NetShipmentTotalPrice > $r->NetShipmentTotalPrice) {
                    $result[$k] = $s;
                }
            }
        }
        return $result;
    }


    public function applyFee($value, $packages = 1)
    {
        $handlingType = \Configuration::get('handling_type');
        $handlingAction = \Configuration::get('handling_action');
        $handlingFee = \Configuration::get('handling_fee');
        if ($handlingAction === self::HANDLING_TYPE_PER_SHIPMENT) {
            $packages = 1;
        }
        if (self::HANDLING_TYPE_FIXED === $handlingType) {
            $result = $value + $handlingFee * $packages;
        } else {
            $result = $value * (100 + $handlingFee) / 100;
        }
        return $result;
    }

    public function createOrFindCarrier($key, $mbekey, $key2)
    {
        $logger = new LoggerHelper();
        $carrier = new \Carrier();

        if (!isset($mbekey) || $mbekey === '' || $key === $mbekey) {
            // add service code to service title and check if there is insurance present
            $key2_firstchars = \Tools::substr($key2, 0, 3);
            if (\strpos($key, $key2_firstchars) === false) {
                if (\strpos($key2, 'INSURANCE') == true) {
                    $keys = explode('+', $key);
                    $keys2 = explode('_', $key2);
                    $key = $keys[0] . '(' . $keys2[0] . ') +' . $keys[1];
                } else {
                    $key .= ' (' . $key2 . ')';
                }
            }
        }

        $carrier->name = $key;
        $carrier->active = true;
        $carrier->deleted = 0;
        $carrier->shipping_handling = false;
        $carrier->range_behavior = 0;
        $carrier->url = $this->getTrackingUrlBySystem(\Configuration::get('mbecountry'));

        if (isset($mbekey) && $mbekey !== '' && $key !== $mbekey) {
            $key = $mbekey;
            $key2_firstchars = \Tools::substr($key2, 0, 3);
            if (\strpos($key, $key2_firstchars) === false) {
                if (\strpos($key2, 'INSURANCE') == true) {
                    $keys = explode('+', $key);
                    $keys2 = explode('_', $key2);
                    $key = $keys[0] . '(' . $keys2[0] . ') +' . $keys[1];
                } else {
                    $key .= ' (' . $key2 . ')';
                }
            }
        }

        foreach (\Language::getLanguages() as $lang) {
            $carrier->delay[$lang['id_lang']] = \Tools::substr(\Configuration::get('mbeshippingdelay_' . \Tools::substr(md5($key . '_' . $lang['iso_code']), 0, 15)), 0, 128);
			if(isset($carrier->delay[$lang['id_lang']])) {
                $carrier->delay[$lang['id_lang']] = ' ';
            }
        }

        $id_tax_rules_group = (int)\Configuration::get('mbe_tax_rule_' . \Tools::strtolower($key2));
        $carrier->setTaxRulesGroup($id_tax_rules_group);
        $carrier->shipping_external = true;
        $carrier->is_module = true;
        $carrier->external_module_name = 'mbeshipping';
        $carrier->need_range = true;
        $id_carrier = \Db::getInstance()->getValue('SELECT `id_carrier` FROM `' . _DB_PREFIX_ . 'carrier` where name = "' . pSQL($carrier->name) . '" AND deleted="0"');
        $logger->logDebug(__METHOD__ . " - id_carrier: $id_carrier");
        try {
            if (!$id_carrier) {
                if ($carrier->add()) {
                    $logger->logDebug(__METHOD__ . " - carrier added: $carrier->id");

                    $groups = \Group::getGroups(true);

                    $logger->logDebug(__METHOD__ . ' - groups');
                    foreach ($groups as $group) {
                        \Db::getInstance()->insert('carrier_group', [
                            'id_carrier' => (int)$carrier->id,
                            'id_group'   => (int)$group['id_group'],
                        ]);

                        $logger->logDebug(__METHOD__ . " - added group: {$group['id_group']}");
                    }

                    \Db::getInstance()->insert('carrier_tax_rules_group_shop', [
                        'id_carrier'         => (int)$carrier->id,
                        'id_tax_rules_group' => (int)$id_tax_rules_group,
                        'id_shop'            => (int)\Context::getContext()->shop->id,
                    ]);

                    $rangePrice = new \RangePrice();
                    $rangePrice->id_carrier = $carrier->id;
                    $rangePrice->delimiter1 = '0';
                    $rangePrice->delimiter2 = '1000000';
                    $rangePrice->add();
                    $rangeWeight = new \RangeWeight();
                    $rangeWeight->id_carrier = $carrier->id;
                    $rangeWeight->delimiter1 = '0';
                    $rangeWeight->delimiter2 = '1000000';
                    $rangeWeight->add();
                    $zones = \Zone::getZones(true);
                    foreach ($zones as $z) {
                        \Db::getInstance()->insert(
                            'carrier_zone',
                            ['id_carrier' => (int)$carrier->id, 'id_zone' => (int)$z['id_zone']]);
                        \Db::getInstance()->insert(
                            'delivery',
                            ['id_carrier' => (int)$carrier->id, 'id_range_price' => (int)$rangePrice->id, 'id_range_weight' => null, 'id_zone' => (int)$z['id_zone'], 'price' => '0'],
                            true,
                            0,
                            \Db::INSERT_IGNORE);
                        \Db::getInstance()->insert(
                            'delivery',
                            ['id_carrier' => (int)$carrier->id, 'id_range_price' => null, 'id_range_weight' => (int)$rangeWeight->id, 'id_zone' => (int)$z['id_zone'], 'price' => '0'],
                            true,
                            0,
                            \Db::INSERT_IGNORE);
                    }

                    if (_PS_VERSION_ >= 1.7) {
                        $logoPath = realpath(_PS_MODULE_DIR_ . 'mbeshipping' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'logo1-7.jpg');
                    }
                    else {
                        $logoPath = realpath(_PS_MODULE_DIR_ . 'mbeshipping' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'logo.jpg');
                    }

                    $logoPathDestination = _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 's' . DIRECTORY_SEPARATOR . $carrier->id . '.jpg';
                    copy($logoPath, $logoPathDestination);

                    $this->setCarrierIfDelilveryPoint($key2 , $id_carrier);

                    return $carrier;
                }
            }
            else {
                foreach (\Language::getLanguages() as $lang) {
                    $v = \Tools::substr(\Configuration::get('mbeshippingdelay_' . \Tools::substr(md5($key . '_' . $lang['iso_code']), 0, 15)), 0, 128);
                    \Db::getInstance()->update('carrier_lang', ['delay' => pSQL($v)], 'id_carrier = ' . (int)$id_carrier . ' and id_lang = ' . (int)$lang['id_lang'], \Db::INSERT_IGNORE);
                }

                $this->setCarrierIfDelilveryPoint($key2, $id_carrier);

                return new \Carrier($id_carrier);
            }
        }
        catch (\Exception $e) {
            $logger->logDebug(__METHOD__ . " - Exception: {$e->getMessage()}");
            $logger->logDebug(print_r($e->getTrace(), true));
        }
    }

    public function setCarrierIfDelilveryPoint($shipment_code, $id)
    {
        $gel_code = ['GPP', '12'];
        $mbe_code = ['NMDP', '11'];
        // If Delivery Point
        if(\MbeShippingDPHelper::isDeliveryPointByShipmentCode($shipment_code)){
            $dp_type = 'UNIFIED';
            \Configuration::updateValue('MBE_SHIPPING_DP_CARRIER_ID', $id);
            if(in_array($shipment_code, $gel_code)) {
                $dp_type = 'GEL';
            }
            if(in_array($shipment_code, $mbe_code)) {
                $dp_type = 'MBE';
            }
            \Configuration::updateValue('MBE_SHIPPING_DP_TYPE', $dp_type);
        }

    }

    public function getTrackingUrlBySystem($system): string
    {
        $result = '';
        if ($system === 'IT') {
            $result = self::ITALIAN_URL;
        } elseif ($system === 'ES') {
            $result = self::SPAIN_URL;
        } elseif ($system === 'DE') {
            $result = self::GERMANY_URL;
        } elseif ($system === 'AT') {
            $result = self::AUSTRIA_URL;
        } elseif ($system === 'FR') {
            $result = self::FRANCE_URL;
        } elseif ($system === 'PL') {
            $result = self::POLAND_URL;
        } elseif ($system === 'HR') {
            $result = self::CROATIA_URL;
        } elseif ($system === 'UK') {
            $result = self::UNITEDKINGDOM_URL;
        }

        return $result;
    }

    public function enabledCountry($id_address)
    {
        $address = new \Address($id_address);
        $country = new \Country($address->id_country);

        if ((bool)\Configuration::get('sallowspecific') === true) {
            return in_array($country->iso_code, explode('-', \Configuration::get('specificcountry')), true);
        }

        return true;
    }

    public function getTotalNumOfBoxes($weight)
    {
        $helper = new DataHelper();
        $numBoxes = 1;
        $maxPackageWeight = $helper->getMaxPackageWeight();
        if ($weight > $maxPackageWeight && $maxPackageWeight != 0) {
            $numBoxes = ceil($weight / $maxPackageWeight);
        }

        return $numBoxes;
    }

    protected function getSubtotalForInsurance($item) {
        $insuranceValue = 0;
        if (\Configuration::get('mbe_shipments_ins_mode') === DataHelper::MBE_INSURANCE_WITH_TAXES) {
            $insuranceValue += $item['price_wt'] * $item['cart_quantity'];
        } else {
            $insuranceValue += $item['price'] * $item['cart_quantity'];
        }
        return $insuranceValue;
    }

    /**
     * @param $boxes
     * If shipping mode is SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_ITEM $boxes is the last one used for getRates, it's always a "settings" box as CSV it's not used for this mode,
     * so it must be used for dimensions check only since it contains only 1 item
     * In the other cases it's the proper list of boxes for the shipment
     * @param $items
     *
     * @return bool
     */
    private function isUapEnabled( $boxes, $items )
    {
        $helper = new DataHelper();
        $oneParcel = (
            ( $helper->getShipmentConfigurationMode() == self::SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_ITEM ) ||
            ( $helper->countBoxesArray( $boxes ) === 1 &&
                in_array( $helper->getShipmentConfigurationMode(), [
                    self::SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_SHOPPING_CART_SINGLE_PARCEL,
                    self::SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_SHOPPING_CART_MULTI_PARCEL
                ] )
            )
        );

        // Check longest size of the last box used for getRates. This must be modified if CSV will be enabled for SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_ITEM shipping mode
        // Nested if to avoid useless check
        if ( $oneParcel ) {
            $longestSize = $helper->longestSizeBoxesArray( $boxes );
            $box         = $boxes[ $helper->arrayKeyFirst( $boxes ) ]; // since it's one parcel we can check only the first element of the array
            if ( $longestSize <= MBE_UAP_LONGEST_LIMIT_97_CM ) {
//				Longest Size Ok
                if ( ( $longestSize + ( 2 * $box['dimensions']['width'] ) + ( 2 * $box['dimensions']['height'] ) ) <= MBE_UAP_TOTAL_SIZE_LIMIT_300_CM ) {
//					Total Size Ok
                    $weightOk = true;
                    if ( $helper->getShipmentConfigurationMode() == self::SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_ITEM ) {
                        foreach ( $items as $item ) {
                            if ( $helper->convertWeight( $item['weight'] ) > MBE_UAP_WEIGHT_LIMIT_20_KG ) {
                                $weightOk = false;
                                break;
                            }
                        }
                    } else {
                        $weightOk = $helper->convertWeight( $helper->totalWeightBoxesArray( $boxes ) ) <= MBE_UAP_WEIGHT_LIMIT_20_KG;
                    }
                    if ( $weightOk ) {
                        // All the checks are OK
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if "Tax and Duty" service should be used
     *
     * @return bool
     */
    private function useTaxAndDutyService(): bool
    {
        $ws = new Ws();
        return (bool)$ws->getCustomerPermission('enabledTaxAndDuties') === true &&
                (bool)\Configuration::get('MBESHIPPING_TAX_DUTY_SERVICE') === true;
    }

    /**
     * Add "Tax and Duty" service to custom rates
     *
     * @param array $custom_rates
     * @param array $mbe_rates
     * @return void
     */
    private function addTaxAndDutyService(array &$custom_rates, array $mbe_rates): void
    {
        foreach ($custom_rates as &$custom_rate) {
            $service_name = str_replace(DataHelper::MBE_SHIPPING_WITH_INSURANCE_CODE_SUFFIX, '', $custom_rate->Service);
            if (!$mbe_rate = $this->findRateByService($mbe_rates, $service_name)) {
                continue;
            }

            if (!isset($mbe_rate->NetTaxAndDutyTotalPrice)) {
                continue;
            }

            $updated_rate = clone $custom_rate;
            $updated_rate->NetTaxAndDutyTotalPrice = $mbe_rate->NetTaxAndDutyTotalPrice;

            if (isset($mbe_rate->CustomDutiesGuaranteed)) {
                $updated_rate->CustomDutiesGuaranteed = $mbe_rate->CustomDutiesGuaranteed;
            }

            $custom_rate = $updated_rate;
        }
    }

    /**
     * @param array $rates
     * @param string $service
     * @return false|object
     */
    private function findRateByService(array $rates, string $service)
    {
        foreach ($rates as $rate) {
            if ((string)$rate->Service === $service) {
                return $rate;
            }
        }

        return false;
    }
}
