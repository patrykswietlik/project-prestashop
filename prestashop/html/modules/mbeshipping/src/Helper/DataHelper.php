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
use Dompdf\Dompdf;

if (!defined('_PS_VERSION_')) {
    exit;
}

class DataHelper #extends Mage_Core_Helper_Abstract
{
    const MBE_SHIPPING_PREFIX = "mbe_shipping_";

    //MAIN
    const XML_PATH_ENABLED = 'mbe_active';
    const XML_PATH_COUNTRY = 'mbecountry';
    //WS
    const XML_PATH_WS_URL = 'url';
    const XML_PATH_WS_USERNAME = 'username';
    const XML_PATH_WS_PASSWORD = 'password';
    //OPTIONS
    const XML_PATH_DESCRIPTION = 'description';
    const XML_PATH_DEFAULT_SHIPMENT_TYPE = 'default_shipment_type';
    const XML_PATH_ALLOWED_SHIPMENT_SERVICES = 'allowed_shipment_services';
    const XML_PATH_SHIPMENT_CONFIGURATION_MODE = 'shipment_configuration_mode';
    const XML_PATH_DEFAULT_LENGTH = 'default_length';
    const XML_PATH_DEFAULT_WIDTH = 'default_width';
    const XML_PATH_DEFAULT_HEIGHT = 'default_height';
    const XML_PATH_MAX_PACKAGE_WEIGHT = 'max_package_weight';
    const XML_PATH_MAX_SHIPMENT_WEIGHT = 'max_shipment_weight';
    const XML_PATH_HANDLING_TYPE = 'handling_type';
    const XML_PATH_HANDLING_ACTION = 'handling_action';
    const XML_PATH_HANDLING_FEE = 'handling_fee';
    const XML_PATH_HANDLING_FEE_ROUNDING = 'handling_fee_rounding';
    const XML_PATH_HANDLING_FEE_ROUNDING_AMOUNT = 'handling_fee_rounding_amount';
    const XML_PATH_SALLOWSPECIFIC = 'sallowspecific';
    const XML_PATH_SPECIFICCOUNTRY = 'specificcountry';
    const XML_PATH_SORT_ORDER = 'sort_order';
    const XML_PATH_MAXIMUM_TIME_FOR_SHIPPING_BEFORE_THE_END_OF_THE_DAY =
        'maximum_time_for_shipping_before_the_end_of_the_day';
    const XML_PATH_SPECIFICERRMSG = 'specificerrmsg';
    const XML_PATH_WEIGHT_TYPE = 'weight_type';
    const XML_PATH_SHIPMENTS_CLOSURE_MODE = 'shipments_closure_mode';
    const XML_PATH_SHIPMENTS_CLOSURE_TIME = 'shipments_closure_time';

    const XML_PATH_THRESHOLD = 'mbelimit_';

    //const

    const MBE_SHIPMENT_STATUS_CLOSED = "Closed";
    const MBE_SHIPMENT_STATUS_OPEN = "Opened";

    const MBE_CLOSURE_MODE_AUTOMATICALLY = 'automatically';
    const MBE_CLOSURE_MODE_MANUALLY = 'manually';

    const MBE_CREATION_MODE_AUTOMATICALLY = 'automatically';
    const MBE_CREATION_MODE_MANUALLY = 'manually';

    const MBE_CSV_MODE_DISABLED = 'disabled';
    const MBE_CSV_MODE_TOTAL = 'total';
    const MBE_CSV_MODE_PARTIAL = 'partial';

    const MBE_INSURANCE_WITH_TAXES = 'insurance_with_taxes';
    const MBE_INSURANCE_WITHOUT_TAXES = 'insurance_without_taxes';


    const MBE_SHIPPING_WITH_INSURANCE_CODE_SUFFIX = '_INSURANCE';
    const MBE_SHIPPING_WITH_INSURANCE_LABEL_SUFFIX = ' + Insurance';

    const MBE_SHIPPING_TRACKING_SEPARATOR = ',';

    const XML_PATH_SHIPMENT_CUSTOM_LABEL = 'mbe_custom_label';
    const XML_PATH_SHIPMENT_CUSTOM_MAPPING = 'mbe_enable_custom_mapping';

    const XML_PATH_AUTO_CHANGE_ORDER_STATUS = 'mbe_auto_change_order_status';

    const XML_PATH_CSV_STANDARD_PACKAGE_USE_CSV = 'mbe_shipping_use_packages_csv';

    public function checkMbeDir()
    {
        $mbeDir = $this->getMediaPath();
        if (!file_exists($mbeDir)) {
            mkdir($mbeDir, 0777, true);
        }
        $manifestsDir = $this->getMediaPath() . 'manifests/';
        if (!file_exists($manifestsDir)) {
            mkdir($manifestsDir, 0777, true);
        }
    }

    public function isEnabled($storeId = null)
    {
        $ws = new Ws();
        $result = $ws->isCustomerActive() && \Configuration::get(self::XML_PATH_ENABLED, $storeId);
        return $result;
    }

    public function getCountry($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_COUNTRY, $storeId);
    }


    public function getWsUrl($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_WS_URL, $storeId);
    }

    public function getWsUsername($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_WS_USERNAME, $storeId);
    }

    public function getWsPassword($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_WS_PASSWORD, $storeId);
    }

    public function getDescription($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_DESCRIPTION, $storeId);
    }

    public function getDefaultShipmentType($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_DEFAULT_SHIPMENT_TYPE, $storeId);
    }

    public function getAllowedShipmentServices($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_ALLOWED_SHIPMENT_SERVICES, $storeId);
    }

    public function convertShippingCodeWithInsurance($code)
    {
        return $code . self::MBE_SHIPPING_WITH_INSURANCE_CODE_SUFFIX;
    }

    public function convertShippingLabelWithInsurance($label)
    {
        return $label . self::MBE_SHIPPING_WITH_INSURANCE_LABEL_SUFFIX;
    }

    public function convertShippingCodeWithoutInsurance($code)
    {
        return str_replace(self::MBE_SHIPPING_WITH_INSURANCE_CODE_SUFFIX, "", $code);
    }

    public function isShippingWithInsurance($code)
    {
        $result = false;
        $shippingSuffix = \Tools::substr($code, - \Tools::strlen(self::MBE_SHIPPING_WITH_INSURANCE_CODE_SUFFIX));
        if ($shippingSuffix == self::MBE_SHIPPING_WITH_INSURANCE_CODE_SUFFIX) {
            $result = true;
        }
        return $result;
    }

    public function getAllowedShipmentServicesArray($storeId = null)
    {
        $ws = new Ws();
        $allowedShipmentServices = $this->getAllowedShipmentServices($storeId);

        $result = array();
        if ($allowedShipmentServices != "") {
            $allowedShipmentServicesArray = explode("-", $allowedShipmentServices);
        }
        $canSpecifyInsurance = $ws->getCustomerPermission('canSpecifyInsurance');

        if(!empty($allowedShipmentServices)){
            foreach ($allowedShipmentServicesArray as $item) {
                $canAdd = true;
                if (!$canSpecifyInsurance) {
                    if (strpos($item, self::MBE_SHIPPING_WITH_INSURANCE_CODE_SUFFIX) !== false) {
                        $canAdd = false;
                    }
                }
                if ($canAdd) {
                    array_push($result, $item);
                }
            }
        }

        return $result;
    }

    public function getShipmentConfigurationMode($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_SHIPMENT_CONFIGURATION_MODE, $storeId);
    }

    public function getDefaultLength($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_DEFAULT_LENGTH, $storeId);
    }

    public function getDefaultWidth($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_DEFAULT_WIDTH, $storeId);
    }

    public function getDefaultHeight($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_DEFAULT_HEIGHT, $storeId);
    }

    public function getMaxPackageWeight($storeId = null)
    {
        $result = \Configuration::get(self::XML_PATH_MAX_PACKAGE_WEIGHT, $storeId);

        $ws = new Ws();

        if ($this->getDefaultShipmentType() == "ENVELOPE") {
            $maxParcelWeight = 0.5;
        } else {
            $maxParcelWeight = $ws->getCustomerPermission("maxParcelWeight");
        }

        if ($maxParcelWeight > 0 && $maxParcelWeight < $result) {
            $result = $maxParcelWeight;
        }
        return $result;
    }

    public function checkMaxWeight($baseWeight, $storeId = null)
    {
        $ws = new Ws();

        if ($this->getDefaultShipmentType() == "ENVELOPE") {
            $maxParcelWeight = 0.5;
        } else {
            $maxParcelWeight = $ws->getCustomerPermission("maxParcelWeight");
        }

        if ($maxParcelWeight > 0 && $maxParcelWeight < $baseWeight) {
            $baseWeight = $maxParcelWeight;
        }
        return $baseWeight;
    }

    public function getHandlingType($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_HANDLING_TYPE, $storeId);
    }

    public function getHandlingAction($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_HANDLING_ACTION, $storeId);
    }

    public function getHandlingFee($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_HANDLING_FEE, $storeId);
    }

    public function getHandlingFeeRounding($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_HANDLING_FEE_ROUNDING, $storeId);
    }

    public function getHandlingFeeRoundingAmount($storeId = null)
    {
        $result = 1;
        if (\Configuration::get(self::XML_PATH_HANDLING_FEE_ROUNDING_AMOUNT, $storeId) == 2) {
            $result = 0.5;
        }
        return $result;
    }

    public function getSallowspecific($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_SALLOWSPECIFIC, $storeId);
    }

    public function getSpecificcountry($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_SPECIFICCOUNTRY, $storeId);
    }

    public function getSortOrder($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_SORT_ORDER, $storeId);
    }

    public function getMaximumTimeForShippingBeforeTheEndOfTheDay($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_MAXIMUM_TIME_FOR_SHIPPING_BEFORE_THE_END_OF_THE_DAY, $storeId);
    }

    public function getSpecificerrmsg($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_SPECIFICERRMSG, $storeId);
    }

    public function getWeightType($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_WEIGHT_TYPE, $storeId);
    }

    public function getShipmentsClosureMode($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_SHIPMENTS_CLOSURE_MODE, $storeId);
    }

    public function getShipmentsClosureTime($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_SHIPMENTS_CLOSURE_TIME, $storeId);
    }

    public function getNameFromLabel($label)
    {
        $name = $label;
        $name = \Tools::strtolower($name);
        $name = str_replace(" ", "_", $name);
        $name = str_replace(" ", "_", $name);
        return $name;
    }

    public function getThresholdByShippingServrice($shippingService, $storeId = null)
    {
        $shippingService = \Tools::strtolower($shippingService);
        $val = str_replace('+', 'plus', $shippingService);
        return \Configuration::get(self::XML_PATH_THRESHOLD . $val, $storeId);
    }

    public function getTrackingStatus($trackingNumber)
    {
        $result = self::MBE_SHIPMENT_STATUS_OPEN;

        $mbeDir = $this->getMediaPath();
        $filePath = $mbeDir . 'MBE_' . $trackingNumber . "_closed.pdf";

        if (file_exists($filePath)) {
            $result = self::MBE_SHIPMENT_STATUS_CLOSED;
        }
        return $result;
    }

    public function getOrderCarrier($shipmentid)
    {
        $id_order_carrier = \Db::getInstance()->getValue('
			SELECT `id_order_carrier`
			FROM `' . _DB_PREFIX_ . 'order_carrier`
			WHERE `id_order` = ' . (int)$shipmentid);
        return new \OrderCarrier($id_order_carrier);
    }

    public function getTrackings($shipmentId)
    {
        $oc = $this->getOrderCarrier((int)$shipmentId);
        $tracks = array_filter(explode(DataHelper::MBE_SHIPPING_TRACKING_SEPARATOR,
            $oc->tracking_number), function ($value) {
            return $value !== '';
        });
        return $tracks;
    }

    public function isShippingOpen($shipmentId)
    {
        $result = true;
        $tracks = $this->getTrackings($shipmentId);
        foreach ($tracks as $track) {
            if(strpos($track, 'RETURN') == false) {
                $result = $result && $this->isTrackingOpen($track);
            }
        }
        return $result;
    }

    public function hasTracking($shipmentId)
    {
        $oc = $this->getOrderCarrier((int)$shipmentId);

        $tracks = array_filter(explode(DataHelper::MBE_SHIPPING_TRACKING_SEPARATOR,
            $oc->tracking_number), function ($value) {
            return $value !== '';
        });
        return !empty($tracks);
    }

    public function isTrackingOpen($trackingNumber)
    {
        return $this->getTrackingStatus($trackingNumber) == self::MBE_SHIPMENT_STATUS_OPEN;
    }


    public function getShipmentFilePath($shipmentIncrementId, $ext)
    {
        $this->checkMbeDir();
        $filePath = $this->getMediaPath() . $shipmentIncrementId . "." . $ext;
        return $filePath;
    }

    public function getTrackingFilePath($trackingNumber)
    {
        $this->checkMbeDir();
        $mbeDir = $this->getMediaPath();
        return $mbeDir . 'MBE_' . $trackingNumber . '_closed.pdf';
    }

    public function getManifestsFilePath($trackingNumber, $excludeExtension = false)
    {
        $this->checkMbeDir();
        $manifestsDir = $this->getMediaPath() . 'manifests/';
        return $manifestsDir . 'MBE_' . $trackingNumber . ($excludeExtension ? '' : '.pdf');
    }

    public function getLdvFilePath($id_order, $trackingNumber, $excludeExtension = false)
    {
        $this->checkMbeDir();
        $mbeDir = $this->getMediaPath();
        return $mbeDir . 'MBE_' . $id_order . '_' . $trackingNumber. ($excludeExtension ? '' : '.pdf');
    }

    public function isMbeShipping($order)
    {
        $carrier = new \Carrier($order->id_carrier);

        if ($carrier->external_module_name == 'mbeshipping'
            || $this->isMbeShippingCustomMapping($carrier->id)) {
            return true;
        }
        return false;
    }

    public function isMbeShippingCustomMapping($shippingMethod)
    {
        if ($this->isEnabledCustomMapping()) {

            $defaultMethods = \Carrier::getCarriers(\Context::getContext()->language->id);
            $customMapping = [];

            foreach ( $defaultMethods as $default_method ) {

                $mapping = \Configuration::get('mbe_custom_mapping_' . (int)$default_method['id_carrier']);
                if (empty($mapping)) {
                    continue;
                }

                $customMapping[$default_method['id_carrier']] = $mapping;
            }

            if (array_key_exists($shippingMethod, $customMapping)) {
                return true;
            }
        }
        return false;
    }

    /* FIXME: Ticket 106383 */
    public function getCustomMappingService($id_carrier)
    {
        if ($this->isEnabledCustomMapping()) {
            $carriers = \Carrier::getCarriers(\Context::getContext()->language->id);

            $customMapping = [];

            foreach ($carriers as $carrier) {
                $mapping = \Configuration::get('mbe_custom_mapping_' . (int)$carrier['id_carrier']);
                if (empty($mapping)) {
                    continue;
                }

                $customMapping[$carrier['id_carrier']] = $mapping;
            }

            if (array_key_exists($id_carrier, $customMapping)) {
                return $customMapping[$id_carrier];
            }
        }

        return '';
    }

    public function getCustomMappingCarriers()
    {
        $customMapping = [];

        if ($this->isEnabledCustomMapping()) {
            $defaultMethods = \Carrier::getCarriers(\Context::getContext()->language->id);

            foreach ( $defaultMethods as $default_method ) {
                $mapping = \Configuration::get( 'mbe_custom_mapping_' .
                    \Tools::strtolower( $default_method['id_carrier'] ) );
                if (!empty($mapping) && trim($mapping)!='') {
                    $customMapping[] = $default_method['id_carrier'];
                }
            }

        }
        return $customMapping;
    }

    public function getMediaPath()
    {
        return _PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'mbe' . DIRECTORY_SEPARATOR;
    }

    public function getPaymentTypes()
    {
        $result = [];

        $modules_list = \Module::getPaymentModules();

        foreach ($modules_list as $module) {
            $module_obj = \Module::getInstanceById($module['id_module']);
            $item = [
                "code" => $module["name"],
                "name" => $module_obj->displayName,
            ];
            $result[] = $item;
        }

        return $result;
    }

    public function generateDelivery($filenames)
    {
        $result = [];
        foreach ($filenames as $filename) {
            if (file_exists($filename . '.html')) {
                $path = $filename . '.html';
                $result[] = $path;
            } elseif (file_exists($filename . '.pdf')) {
                $path = $filename . '.pdf';
                $result[] = $path;
            } elseif (file_exists($filename . '.gif')) {
                $path = $filename . '.gif';
                $result[] = $path;
            } else {
                $foundFiles = true;
                $i = 1;
                do {
                    $progressiveFilename = $filename . '_' . $i;

                    if (file_exists($progressiveFilename . '.html')) {
                        $path = $progressiveFilename . '.html';
                        $result[] = $path;
                    }
                    elseif (file_exists($progressiveFilename . '.pdf')) {
                        $path = $progressiveFilename . '.pdf';
                        $result[] = $path;
                    }
                    elseif (file_exists($progressiveFilename . '.gif')) {
                        $path = $progressiveFilename . '.gif';
                        $result[] = $path;
                    }
                    else {
                        $foundFiles = false;
                    }
                    $i++;
                } while ($foundFiles);
            }
        }

        $url = \Context::getContext()->link->getModuleLink('mbeshipping', 'mbeaction', ['files' => $result]);

        \Tools::redirect($url);
    }

    public function generateDeliveryForBulk($filenames)
    {
        $result = [];
        foreach ($filenames as $filename) {
            if (file_exists($filename . '.html')) {
                $path = $filename . '.html';
                $result[] = $this->convertShippingLabelToPdf('html', \Tools::file_get_contents($path));
            } elseif (file_exists($filename . '.pdf')) {
                $path = $filename . '.pdf';
                $result[] = $this->convertShippingLabelToPdf('pdf', \Tools::file_get_contents($path));
            } elseif (file_exists($filename . '.gif')) {
                $path = $filename . '.gif';
                $result[] = $this->convertShippingLabelToPdf('gif', \Tools::file_get_contents($path));
            } else {
                $foundFiles = true;
                $i = 1;
                do {
                    $progressiveFilename = $filename . '_' . $i;

                    if (file_exists($progressiveFilename . '.html')) {
                        $path = $progressiveFilename . '.html';
                        $result[] = $this->convertShippingLabelToPdf('html', \Tools::file_get_contents($path));
                    }
                    elseif (file_exists($progressiveFilename . '.pdf')) {
                        $path = $progressiveFilename . '.pdf';
                        $result[] = $this->convertShippingLabelToPdf('pdf', \Tools::file_get_contents($path));
                    }
                    elseif (file_exists($progressiveFilename . '.gif')) {
                        $path = $progressiveFilename . '.gif';
                        $result[] = $this->convertShippingLabelToPdf('gif', \Tools::file_get_contents($path));
                    }
                    else {
                        $foundFiles = false;
                    }
                    $i++;

                } while ($foundFiles);
            }

        }

      return $result;

    }

    public function convertShippingLabelToPdf(string $labelType, $label)
    {
        $dompdf = new Dompdf();

        switch ($labelType) {
            case 'html':
                $domOptions = $dompdf->getOptions();
                $domOptions->setIsHtml5ParserEnabled(true);
                $domOptions->setIsRemoteEnabled(true);
                $domOptions->setDefaultMediaType('print');
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->setOptions($domOptions);
                $dompdf->loadHtml($label);
                $dompdf->render();
                return $dompdf->output();
            case 'gif':
                $gifHtml = '<img style="min-width:90%; max-height:90%" src="data:image/gif;base64,' .
                    base64_encode($label) . '">';
                $dompdf->loadHtml($gifHtml);
                $dompdf->render();
                return $dompdf->output();
            default: //pdf
                return $label;
        }
    }

    public function getCashOnDeliveryModuleName()
    {
        $result = "cashondelivery";
        if (_PS_VERSION_ >= 1.7) {
            $result = "ps_cashondelivery";
        }
        return $result;
    }

    public function getShippingMethodCustomLabel($shippingService, $storeId = null)
    {
        $shippingService = \Tools::strtolower($shippingService);
        $val = str_replace('+', 'plus', $shippingService);
        return \Configuration::get(self::XML_PATH_SHIPMENT_CUSTOM_LABEL. '_' .
            str_replace('+','p',$val), $storeId);
    }

    public function isMBEStandard($customLabel, $storeId = null)
    {
        $service = \Configuration::get(self::XML_PATH_SHIPMENT_CUSTOM_LABEL. '_' . 'sse', $storeId);
        if ($service === $customLabel){
            return true;
        } else{
            return false;
        }
    }

    public function isMBEDeliveryPoint($customLabel, $storeId = null)
    {
        $service = \Configuration::get(self::XML_PATH_SHIPMENT_CUSTOM_LABEL. '_' . 'mdp', $storeId);
        if ($service === $customLabel){
            return true;
        } else{
            return false;
        }
    }

    public function isEnabledCustomMapping($storeId = null)
    {
        return \Configuration::get(self::XML_PATH_SHIPMENT_CUSTOM_MAPPING, $storeId);
    }

    public function convertWeight($weight)
    {
        $weight_unit = \Tools::strtolower(\Configuration::get('PS_WEIGHT_UNIT'));
        switch ($weight_unit) {
            case 'mg':
                $weight = $weight * 0.000001;
                break;
            case 'cg':
                $weight = $weight * 0.00001;
                break;
            case 'dg':
                $weight = $weight * 0.0001;
                break;
            case 'gr':
                $weight = $weight * 0.001;
                break;
            case 'dag':
                $weight = $weight * 0.01;
                break;
            case 'hg':
                $weight = $weight * 0.1;
                break;
            case 'kg':
                $weight = $weight * 1;
                break;
        }
        return $weight;
    }

    public function getAutoChangeOrderStatus($storeId = null)
    {
        return (int)\Configuration::get(self::XML_PATH_AUTO_CHANGE_ORDER_STATUS, $storeId);
    }

    /**
     * @param $productSku
     * @param false $singleParcel // All the packages based on settings (not CSV) must be set as "single parcels"
     * @return array|null
     */
    public function getPackageInfo($productSku, $singleParcel = false)
    {
        if ($this->useCsvStandardPackages()) {
            return $this->getCsvPackageInfo($productSku, $singleParcel);
        } else {
            return $this->getSettingsPackageInfo($singleParcel);
        }
    }

    public function useCsvStandardPackages()
    {
        $csv_package_model = new PackageHelper();
        $standardPackagesCount = count( $csv_package_model->getCsvPackages() );
        return \Configuration::get(self::XML_PATH_CSV_STANDARD_PACKAGE_USE_CSV)
            && $standardPackagesCount > 0
            && RatesHelper::SHIPMENT_CONFIGURATION_MODE_ONE_SHIPMENT_PER_SHOPPING_CART_SINGLE_PARCEL ==
            $this->getShipmentConfigurationMode();
    }

    /**
     * Return the CSV package information related to a specific product, if any
     * Otherwise it returns the CSV standard package selected as the default one
     *
     * @param null $productSku
     * @return array|null
     *
     */
    protected function getCsvPackageInfo($productSku, $singleParcel)
    {
        $csv_package_model = new PackageHelper();

        $packagesInfo = $csv_package_model->getPackageInfobyProduct( $productSku );
        if (count($packagesInfo) <= 0) {
            //$packagesInfo = $csv_package_model->getPackageInfobyId( $this->getCsvStandardPackageDefault() );
            $packageInfoResult = $this->getSettingsPackageInfo($singleParcel);
        } else
        {
            $packageInfoResult = $packagesInfo[0];
        }

        $packageInfoResult['max_weight'] = $this->checkMaxWeight($packageInfoResult['max_weight']);

        return $packageInfoResult;
    }

    protected function getSettingsPackageInfo($singleParcel)
    {
        return [
            'id' => null,
            'package_code' => 'settings',
            'package_label' => 'Package from settings',
            'height' => $this->getDefaultHeight(),
            'width' => $this->getDefaultWidth(),
            'length' => $this->getDefaultLength(),
            'max_weight' => $this->getMaxPackageWeight(),
            'single_parcel' => $singleParcel,
            'custom_package' => false
        ];
    }

    public function getStandardPackagesForSelect()
    {
        return $this->toSelectArray( $this->csv_package_model->getStandardPackages(), 'id', 'package_label' );
    }

    public function getBoxesArray(&$boxesArray, &$boxesSingleParcelArray, $itemWeight, $packageInfo)
    {
        if ($packageInfo['single_parcel'] || $packageInfo['custom_package']) {
            if (!isset($boxesSingleParcelArray[$packageInfo['package_code']])) {
                $boxesSingleParcelArray[$packageInfo['package_code']] = $this->addEmptyBoxType($packageInfo);
            }
            $boxesSingleParcelArray[$packageInfo['package_code']]['weight'][] = $itemWeight;
        } else {
            $canAddToExistingBox = false;
            if (!isset($boxesArray[$packageInfo['package_code']])) {
                $boxesArray[$packageInfo['package_code']] = $this->addEmptyBoxType($packageInfo);
            }
            $boxesPackage = &$boxesArray[$packageInfo['package_code']]; // by ref to simplify the code
            $boxesCount = count($boxesPackage['weight']);
            for ($j = 0; $j < $boxesCount; $j++) {
                $newWeight = $boxesPackage['weight'][$j] + $itemWeight;
                if ($newWeight <= $packageInfo['max_weight']) {
                    $canAddToExistingBox = true;
                    $boxesPackage['weight'][$j] = $newWeight;
                    break;
                }
            }
            if (!$canAddToExistingBox) {
                $boxesPackage['weight'][] = $itemWeight;
            }
        }
        return $boxesArray;
    }

    public function addEmptyBoxType($packageInfo)
    {
        return [
            'maxweight' => $packageInfo['max_weight'],
            'dimensions' => [
                'length' => $packageInfo['length'],
                'width' => $packageInfo['width'],
                'height' => $packageInfo['height']
            ],
            'weight' => []
        ];
    }

    public function mergeBoxesArray($boxes, $boxesSingleParcel)
    {
        foreach ($boxes as $key => $value) {
            if (isset($boxesSingleParcel[$key])) {
                foreach ($boxesSingleParcel[$key]['weight'] as $item) {
                    $boxes[$key]['weight'][] = $item;
                }
                // remove the merged package
                unset($boxesSingleParcel[$key]);
            }
        }
        //  append all the remaining packages
        return array_merge($boxes, $boxesSingleParcel);
    }

    public function countBoxesArray($boxesArray)
    {
        $count = 0;
        $countArray = array_column($boxesArray, 'weight');
        foreach ($countArray as $box) {
            $count += count($box);
        }
        return $count;
    }

    /**
     * Compare all the dimensions for all the boxes and returns the biggest one
     * @param $boxesArray
     *
     * @return mixed
     */
    public function longestSizeBoxesArray($boxesArray)
    {
        $sortArray = [];
        $dimensionsArray = array_column($boxesArray, 'dimensions');
        foreach ( $dimensionsArray as $item ) {
            rsort($item);
            $sortArray[] = $item[0];
        }
        rsort($sortArray);
        return $sortArray[0];
    }

    public function arrayKeyFirst($array) {
        if (!function_exists('array_key_first')) {
            function array_key_first(array $array) {
                foreach($array as $key => $unused) {
                    return $key;
                }
                return NULL;
            }
        } else {
            return array_key_first($array);
        }
    }

    public function totalWeightBoxesArray($boxesArray) {
        $totalWeight = 0;
        if ( is_array( $boxesArray ) ) {
            foreach ( $boxesArray as $box ) {
                $totalWeight += array_sum( $box['weight'] );
            }
        }
        return $totalWeight;
    }

    public function doSerialize($obj) {
        return @json_encode(
            $obj,
            JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE
        );
    }

    public function doUnserialize($strobj, $assoc = true) {
        return @json_decode($strobj, (bool)$assoc);
    }
}
