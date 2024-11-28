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

use PrestaShop\Module\Mbeshipping\Ws;
use PrestaShop\Module\Mbeshipping\Helper\LoggerHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class DeliveryPointHelper
{
    protected $logger = null;
    public static $wsItems = [
        'Items' => [
            [
                'weight' => [
                    'weight' => 0.3
                ],
                'dimensions' => [
                    'length' => 10,
                    'height' => 10,
                    'width' => 10
                ]
            ]
        ]
    ];

    public static $wsDestinations = [
        'IT' => [
            'ZipCode' => '20121',
            'City' => 'MI',
            'Country' => 'IT'
        ],
        'ES' => [
            'ZipCode' => '28001',
            'City' => 'MAD',
            'Country' => 'ES'
        ],
        'DE' => [
            'ZipCode' => '10176',
            'City' => 'BE',
            'Country' => 'DE'
        ],
        'FR' => [
            'ZipCode' => '70123',
            'City' => 'PAR',
            'County' => 'FR'
        ],
        'AT' => [
            'ZipCode' => '1010',
            'City' => 'W',
            'County' => 'AT'
        ],
        'PL' => [
            'ZipCode' => '00-001',
            'City' => 'WAW',
            'County' => 'PL'
        ],
        'HR' => [
            'ZipCode' => '10000',
            'City' => 'ZG',
            'County' => 'HR'
        ]
    ];

    const STANDARD_TAX_RATES = [
        'IT' => 22.000,
        'ES' => 21.000,
        'DE' => 19.000,
        'FR' => 20.000,
        'AT' => 20.000,
        'PL' => 23.000,
        'HR' => 25.000,
    ];

    public function __construct()
    {
        $this->helper = new DataHelper();
        $this->logger = new LoggerHelper();
    }


    public function installDeliveryPointTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mbe_shipping_dp` (
                `id_mbe_shipping_dp` int(11) NOT NULL AUTO_INCREMENT,
                `id_cart` INT(10) NOT NULL,
                `id_order` INT(10) DEFAULT NULL,
                `mbe_service_id` VARCHAR(255) NOT NULL,
                `network_name` VARCHAR(255) NOT NULL,
                `network_id` VARCHAR(255) NOT NULL,
                `pudo_id` VARCHAR(255) NOT NULL,
                `pudo_data` TEXT NOT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id_mbe_shipping_dp`),
                INDEX `id_order` (`id_order`),
                INDEX `id_cart` (`id_cart`))';

        $result = \Db::getInstance()->execute($sql);
        return $result;
    }

    public function uninstallDeliveryPointTable()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mbe_shipping_dp`';

        $result = \Db::getInstance()->execute($sql);
        return $result;
    }


    public function prepareShippingEstimates($destCountry)
    {
        if (array_key_exists($destCountry, self::$wsDestinations)) {
            $weight = self::$wsItems['Items'];
            $dimensions = self::$wsItems['Items'][0]['dimensions'];
            $destZipCode = self::$wsDestinations[$destCountry]['ZipCode'];
            $allowedShipmentServicesArray = [];

            $ws = new Ws();
            $shipments = $ws->estimateShipping($destCountry, null, $destZipCode, $weight, $dimensions, null, $allowedShipmentServicesArray, null, null);

            if (!$shipments) {
                $this->logger->logDebug('Call estimate Shipping is false');
            } else {
                $this->logger->logDebug('Call estimate Shipping correct ->' . print_r($shipments, true));

                $isServiceNumeric = false;
                foreach ($shipments as $shipment) {
                    if(is_numeric($shipment->Service)) {
                        $isServiceNumeric = true;
                    }
                    if ($shipment->Service == 'GPP') {
                        \Configuration::updateValue('MBE_GPP_NET_SHIPMENT_TOTAL_PRICE',  $shipment->NetShipmentTotalPrice);

                    } else if ($shipment->Service == 'NMDP'){
                        \Configuration::updateValue('MBE_NMDP_NET_SHIPMENT_TOTAL_PRICE', $shipment->NetShipmentTotalPrice);
                    }
                }
                \Configuration::updateValue('MBE_IS_SERVICE_NUMERIC', $isServiceNumeric);
            }
        }
    }

    public static function getDefaultEstimates()
    {
        $returns = array(
            'currencyCode' => \Context::getContext()->currency->iso_code,
            'prenegotiated' => (float)\Configuration::get('MBE_GPP_NET_SHIPMENT_TOTAL_PRICE'),
            'labeling' => (float)\Configuration::get('MBE_NMDP_NET_SHIPMENT_TOTAL_PRICE'),
        );

        if($returns['prenegotiated'] > 0 || $returns['labeling'] > 0) {

            $rates_helper = new RatesHelper();
            $default_rate = 0;
            $mbe_dp_carrier_id = \Configuration::get('MBE_SHIPPING_DP_CARRIER_ID');

            //Apply Currency
            $returns['prenegotiated'] *= \Context::getContext()->currency->conversion_rate;
            $returns['labeling'] *= \Context::getContext()->currency->conversion_rate;

            //Apply Tax
            $carrier_for_tax = new \Carrier($mbe_dp_carrier_id);
            if(\Validate::isLoadedObject($carrier_for_tax)) {
                $default_rate = $carrier_for_tax->getTaxesRate();
            }

            if($default_rate > 0) {
                $returns['prenegotiated'] = \Tools::ps_round($returns['prenegotiated'] * (1+($default_rate/100)), 2);
                $returns['labeling'] = \Tools::ps_round($returns['labeling'] * (1+($default_rate/100)), 2);
            }

            //Apply Markup
            $returns['prenegotiated'] = $rates_helper->applyFee($returns['prenegotiated']);
            $returns['labeling'] = $rates_helper->applyFee($returns['labeling']);

            //Manage Free Shipping for DP
            $helper = new DataHelper();
            $context = \Context::getContext();
            $isServiceNumeric = \Configuration::get('MBE_IS_SERVICE_NUMERIC');
            $gppServiceID = $isServiceNumeric ? '12' : 'GPP';
            $nmdpServiceID = $isServiceNumeric ? '11' : 'NMDP';
            $mergedServiceID = $isServiceNumeric ? '1112': 'NMDP-GPP';

            $serviceForMdpCarrier = \Configuration::get('carrier_'.$mbe_dp_carrier_id);
            $isMergegMdpCarrier = false;
            if($mergedServiceID == $serviceForMdpCarrier) {
                $isMergegMdpCarrier = true;
            }

            $baseSubtotalInclTax = $context->cart->getOrderTotal(true, \Cart::BOTH_WITHOUT_SHIPPING);
            if($isMergegMdpCarrier) {
                $freeMergedAmount = $helper->getThresholdByShippingServrice($mergedServiceID.(\Configuration::get('mbecountry') === $context->country->iso_code ? '' : '_ww'));
                if ($freeMergedAmount != null && $baseSubtotalInclTax >= $freeMergedAmount) {
                    $returns['prenegotiated'] = 0;
                    $returns['labeling'] = 0;
                }
            } else {
                $freeGppAmount = $helper->getThresholdByShippingServrice($gppServiceID.(\Configuration::get('mbecountry') === $context->country->iso_code ? '' : '_ww'));
                if ($freeGppAmount != null && $baseSubtotalInclTax >= $freeGppAmount) {
                    $returns['prenegotiated'] = 0;
                }

                $freeNmdpAmount = $helper->getThresholdByShippingServrice($nmdpServiceID.(\Configuration::get('mbecountry') === $context->country->iso_code ? '' : '_ww'));
                if ($freeNmdpAmount != null && $baseSubtotalInclTax >= $freeNmdpAmount) {
                    $returns['labeling'] = 0;
                }
            }
        }

        return $returns;
    }
}
