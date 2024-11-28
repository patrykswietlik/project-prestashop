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

namespace PrestaShop\Module\Mbeshipping;

use AuthAPI;
use PrestaShop\Module\Mbeshipping\Helper\DataHelper;
use PrestaShop\Module\Mbeshipping\Helper\LoggerHelper;
use PrestaShop\Module\Mbeshipping\Helper\MOrderHelper;
use PrestaShop\Module\Mbeshipping\Helper\OrderHelper;
use PrestaShop\Module\Mbeshipping\Lib\MbeWs;
use PrestaShop\Module\Mbeshipping\Helper\DeliveryPointHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ws
{
    private $helper;
    protected $logger = null;
    private $ws;

    public function __construct()
    {
        $this->helper = new DataHelper();
        $this->logger = new LoggerHelper();

        $wsLogPath = _PS_MODULE_DIR_ . 'mbeshipping' . DIRECTORY_SEPARATOR . 'log';
        $this->ws = new MbeWs($wsLogPath);
    }

    public function getCustomer($forceUpdate = false)
    {
        if ($forceUpdate || is_null(\Configuration::get('MBECustomer')) || !\Configuration::get('MBECustomer')) {
            $result = false;

            $wsUrl = $this->helper->getWsUrl();
            $wsUsername = $this->helper->getWsUsername();
            $wsPassword = $this->helper->getWsPassword();
            $system = $this->helper->getCountry();

            $this->logger->logDebug('Customer not found in cache');
            if ($wsUrl && $wsUsername && $wsPassword) {
                $result = $this->ws->getCustomer($wsUrl, $wsUsername, $wsPassword, $system);
                //$this->logger->logDebug($result, 'WS getCustomer');
            }

            \Configuration::updateValue('MBECustomer', json_encode($result));
            // To avoid Performance Problem save DP credentials in Configuration
            $this->storeDeliveryPointInformation($result);

            /* + Third party pickups  */
            AuthAPI::allowThirdPartyPickups($this->canUseThirdPartyPickups());
            if (AuthAPI::thirdPartyPickupsAllowed() && AuthAPI::isDirectChannelUser()) {
                AuthAPI::enableThirdPartyPickups(true);
            }
            /* - Third party pickups */

            return $result;
        }

        return json_decode(\Configuration::get('MBECustomer'));
    }

    /**
     * Get customer permission
     *
     * @param string $permissionName
     * @return false|mixed
     */
    public function getCustomerPermission(string $permissionName)
    {
        $customer = $this->getCustomer();

        if (!$customer ||
            !isset(
                $customer->Permissions,
                $customer->Permissions->$permissionName
            )
        ) {
            return false;
        }

        return $customer->Permissions->$permissionName;
    }

    public function getAvailableOptions()
    {
        $wsUrl = $this->helper->getWsUrl();
        $wsUsername = $this->helper->getWsUsername();
        $wsPassword = $this->helper->getWsPassword();
        $result = false;
        if ($wsUrl && $wsUsername && $wsPassword) {
            $result = $this->ws->getAvailableOptions($wsUrl, $wsUsername, $wsPassword);
        }

        return $result;
    }

    public function getAllowedShipmentServices()
    {
        $result = array();
        $customer = $this->getCustomer();
        if ($customer && $customer->Enabled) {
            if (isset($customer->Permissions->enabledServices)) {
                $enabledServices = $customer->Permissions->enabledServices;
                $enabledServicesDesc = $customer->Permissions->enabledServicesDesc;

                $enabledServicesArray = explode(",", $enabledServices);
                $enabledServicesDescArray = explode(",", $enabledServicesDesc);

                for ($i = 0; $i < count($enabledServicesArray); $i++) {
                    $service = $enabledServicesArray[$i];
                    $serviceDesc = $enabledServicesDescArray[$i];

                    $serviceDesc .= ' (' . $service . ')';

                    $currentShippingType = array(
                        'value' => $service,
                        'label' => $serviceDesc,
                    );

                    if (!in_array($currentShippingType, $result)) {
                        array_push($result, $currentShippingType);
                    }

                    //SHIPPING WITH INSURANCE
                    if (isset($customer->Permissions->canSpecifyInsurance) &&
                        $customer->Permissions->canSpecifyInsurance) {
                        $currentShippingWithInsuranceType = array(
                            'value' => $this->helper->convertShippingCodeWithInsurance($service),
                            'label' => $this->helper->convertShippingLabelWithInsurance($serviceDesc),
                        );
                        if (!in_array($currentShippingWithInsuranceType, $result)) {
                            array_push($result, $currentShippingWithInsuranceType);
                        }
                    }
                }
            }
        }
        return $result;
    }

    public function getLabelFromShipmentType($shipmentCode)
    {
        $result = $shipmentCode;
        $allowedShipmentServices = $this->getAllowedShipmentServices();
        foreach ($allowedShipmentServices as $allowedShipmentService) {
            if ($allowedShipmentService["value"] == $shipmentCode) {
                $result = $allowedShipmentService["label"];
                break;
            }
        }
        return $result;
    }

    private function convertInsuranceShipping($shippingList)
    {
        $result = false;
        if ($shippingList) {
            $newShippingList = array();
            foreach ($shippingList as $shipping) {
                if ($shipping->InsuranceAvailable) {
                    $newShipping = $shipping;
                    $newShipping->Service = $this->helper->convertShippingCodeWithInsurance($newShipping->Service);
                    $newShipping->ServiceDesc =
                        $this->helper->convertShippingLabelWithInsurance($newShipping->ServiceDesc);
                    $newShippingList[] = $newShipping;
                }
            }
            if (!empty($newShippingList)) {
                $result = $newShippingList;
            }
        }
        return $result;
    }

    public function estimateShipping(
        $country,
        $region,
        $postCode,
        $weight,
        $boxes,
        $products,
        $insuranceValue,
        $allowedShipmentServicesArray,
        $isPickup = false,
        $pickupDefaultAddress = [],
        $useTaxAndDutyService = false
    ) {
        $this->logger->logDebug(__METHOD__ . ' - ESTIMATE SHIPPING');
        $wsUrl = $this->helper->getWsUrl();
        $wsUsername = $this->helper->getWsUsername();
        $wsPassword = $this->helper->getWsPassword();
        $system = $this->helper->getCountry();

        if (!$wsUrl || !$wsUsername || !$wsPassword) {
            return false;
        }

        $result = false;

        //$length = $this->helper->getDefaultLength();
        //$width = $this->helper->getDefaultWidth();
        //$height = $this->helper->getDefaultHeight();

        $shipmentType = $this->helper->getDefaultShipmentType();

        $items = $this->setItems($weight);

        $this->logger->logDebug(__METHOD__ . ' - ESTIMATE SHIPPING ITEMS');
        $this->logger->logDebug($items);

        //Shipping without insurance
        $resultWithoutInsurance = $this->ws->estimateShipping(
            $wsUrl,
            $wsUsername,
            $wsPassword,
            $shipmentType,
            $system,
            $country,
            $region,
            $postCode,
            $items,
            $products,
            false,
            $insuranceValue,
            $isPickup,
            $pickupDefaultAddress,
            $useTaxAndDutyService
        );

        //Shipping with insurance
        if (preg_match("/INSURANCE/", var_export($allowedShipmentServicesArray, true))) {
            $resultWithInsurance = $this->ws->estimateShipping(
                $wsUrl,
                $wsUsername,
                $wsPassword,
                $shipmentType,
                $system,
                $country,
                $region,
                $postCode,
                $items,
                $products,
                true,
                $insuranceValue,
                $isPickup,
                [],
                $useTaxAndDutyService
            );
            $resultWithInsurance = $this->convertInsuranceShipping($resultWithInsurance);
        } else {
            $resultWithInsurance = null;
        }

        if ($resultWithInsurance && $resultWithoutInsurance) {
            $result = array_merge($resultWithInsurance, $resultWithoutInsurance);
        } else {
            if ($resultWithInsurance) {
                $result = $resultWithInsurance;
            }
            if ($resultWithoutInsurance) {
                $result = $resultWithoutInsurance;
            }
        }

        return $result;
    }


    public function createShipping(
        $country,
        $region,
        $postCode,
        $weight,
        $boxes,
        $products,
        $service,
        $notes,
        $firstName,
        $lastName,
        $companyName,
        $address,
        $phone,
        $city,
        $email,
        $goodsValue = 0.0,
        $reference = "",
        $isCod = false,
        $codValue = 0.0,
        $insurance = false,
        $insuranceValue = 0.0,
        $dp_data = false,
        $useTaxAndDutyService = false
    ) {
        $this->logger->logDebug(__METHOD__ . ' - CREATE SHIPPING');
        $wsUrl = $this->helper->getWsUrl();
        $wsUsername = $this->helper->getWsUsername();
        $wsPassword = $this->helper->getWsPassword();
        $system = $this->helper->getCountry();

        if (!$wsUrl || !$wsUsername || !$wsPassword) {
            return false;
        }

        //$length = $this->helper->getDefaultLength();
        //$width = $this->helper->getDefaultWidth();
        //$height = $this->helper->getDefaultHeight();

        $shipmentType = $this->helper->getDefaultShipmentType();

        $items = $this->setItems($weight);

        $this->logger->logDebug('CREATE SHIPPING ITEMS');
        $this->logger->logDebug($items);

        $shipperType = $this->getShipperType();
        $notes = mb_substr($notes, 0, 50, 'utf8');
        $result = $this->ws->createShipping(
            $wsUrl,
            $wsUsername,
            $wsPassword,
            $shipmentType,
            $service,
            $system,
            $notes,
            $firstName,
            $lastName,
            $companyName,
            $address,
            $phone,
            $city,
            $region,
            $country,
            $postCode,
            $email,
            $items,
            $products,
            $shipperType,
            $goodsValue,
            $reference,
            $isCod,
            $codValue,
            $insurance,
            $insuranceValue,
            $dp_data,
            $useTaxAndDutyService
        );

        return $result;
    }

    public function getShipperType()
    {
        //COURIERLDV or MBE
        $customer = $this->getCustomer();
        $shipperType = "MBE";
        if ($customer->Permissions->canCreateCourierWaybill) {
            $shipperType = "COURIERLDV";
        }

        return $shipperType;
    }

    public function mustCloseShipments()
    {
        $result = true;
        $customer = $this->getCustomer();
        if ($customer->Permissions->canCreateCourierWaybill) {
            $result = false;
        }
        return $result;
    }

    public function getCustomerMaxParcelWeight()
    {
        $customer = $this->getCustomer();
        return $customer->Permissions->maxParcelWeight;
    }

    public function getCustomerMaxShipmentWeight()
    {
        $customer = $this->getCustomer();
        return $customer->Permissions->maxShipmentWeight;
    }

    public function isCustomerActive()
    {
        $result = false;
        $customer = $this->getCustomer();
        if ($customer) {
            $result = $customer->Enabled;
        }
        return $result;
    }

    public function closeShipping(array $shipmentIds)
    {
        $orderHelper =  new OrderHelper();
        $trackingNumbers = [];

        foreach ($shipmentIds as $shipmentId) {
            $oc = $this->helper->getOrderCarrier((int)$shipmentId);

            $tracks = array_filter(
                explode(
                    DataHelper::MBE_SHIPPING_TRACKING_SEPARATOR,
                    $oc->tracking_number
                ),
                function ($value) {
                    return $value !== '';
                }
            );
            foreach ($tracks as $track) {
                if (strpos($track, 'RETURN') === false) {
                    $trackingNumbers[] = $track;
                }
            }
            $order = new \Order($shipmentId);
            $orderHelper->setOrderShipped($order);
        }
        $this->closeTrackingNumbers($trackingNumbers);
    }

    public function closeTrackingNumbers(array $trackingNumbers)
    {
        $this->logger->logDebug('CLOSE SHIPPING');

        $wsUrl = $this->helper->getWsUrl();
        $wsUsername = $this->helper->getWsUsername();
        $wsPassword = $this->helper->getWsPassword();
        $system = $this->helper->getCountry();

        $result = false;

        if ($wsUrl && $wsUsername && $wsPassword) {
            $result = $this->ws->closeShipping($wsUrl, $wsUsername, $wsPassword, $system, $trackingNumbers);

            if ($result) {
                foreach ($trackingNumbers as $trackingNumber) {
                    $filePath = $this->helper->getTrackingFilePath($trackingNumber);
                    if (base64_encode(base64_decode($result->Pdf, true)) === $result->Pdf) {
                        $fileStream = base64_decode($result->Pdf);
                    } else {
                        $fileStream = $result->Pdf;
                    }
                    file_put_contents($filePath, $fileStream);
                }
            }
        }

        return $result;
    }

    public function setItems($boxesWeight)
    {
        $items = [];
        foreach ($boxesWeight as $box) {
            foreach ($box['weight'] as $weight) {
                $item = new \stdClass;
                $item->Weight = $weight;
                $item->Dimensions = new \stdClass;
                $item->Dimensions->Lenght = $box['dimensions']['length'];
                $item->Dimensions->Height = $box['dimensions']['height'];
                $item->Dimensions->Width = $box['dimensions']['width'];
                $items[] = $item;
            }
        }
        return $items;
    }

    public function createReturnShipping($tracking)
    {
        $this->logger->logDebug('CREATE RETURN SHIPPING');

        $wsUrl = $this->helper->getWsUrl();
        $wsUsername = $this->helper->getWsUsername();
        $wsPassword = $this->helper->getWsPassword();
        $system = $this->helper->getCountry();
        $result = false;
        if ($wsUrl && $wsUsername && $wsPassword) {
            $result = $this->ws->createReturnShipping($wsUrl, $wsUsername, $wsPassword, $system, $tracking);
        }
        return $result;
    }

    /* + Third party pickups */
    public function getPickupAddresses()
    {
        if (!$this->canUseThirdPartyPickups()) {
            return false;
        }

        $wsUrl = $this->helper->getWsUrl();
        $wsUsername = $this->helper->getWsUsername();
        $wsPassword = $this->helper->getWsPassword();
        $system = $this->helper->getCountry();

        if (!$wsUrl || !$wsUsername || !$wsPassword) {
            return false;
        }

        return $this->ws->getPickupAddresses($wsUrl, $wsUsername, $wsPassword, $system);
    }

    public function savePickupAddress($pickup_container)
    {
        if (!$this->canUseThirdPartyPickups()) {
            return false;
        }

        $wsUrl = $this->helper->getWsUrl();
        $wsUsername = $this->helper->getWsUsername();
        $wsPassword = $this->helper->getWsPassword();
        $system = $this->helper->getCountry();

        if (!$wsUrl || !$wsUsername || !$wsPassword) {
            return false;
        }

        return $this->ws->savePickupAddress($wsUrl, $wsUsername, $wsPassword, $system, $pickup_container);
    }

    public function deletePickupAddress($pickup_address_id) {
        if (!$this->canUseThirdPartyPickups()) {
            return false;
        }

        $wsUrl = $this->helper->getWsUrl();
        $wsUsername = $this->helper->getWsUsername();
        $wsPassword = $this->helper->getWsPassword();
        $system = $this->helper->getCountry();

        if (!$wsUrl || !$wsUsername || !$wsPassword) {
            return false;
        }

        return $this->ws->deletePickupAddress($wsUrl, $wsUsername, $wsPassword, $system, $pickup_address_id);
    }

    public function canUseThirdPartyPickups()
    {
        $customer = $this->getCustomer();

        if (!$customer ||
            !$customer->Enabled ||
            !isset($customer->Permissions->enabledThirdPartyPickups) ||
            !$customer->Permissions->enabledThirdPartyPickups ||
            !isset($customer->Permissions->canCreateCourierWaybill) ||
            !$customer->Permissions->canCreateCourierWaybill
        ) {
            return false;
        }

        return true;
    }

    public function createPickupShipping(
        $country,
        $region,
        $postCode,
        $weight,
        $products,
        $service,
        $notes,
        $firstName,
        $lastName,
        $companyName,
        $address,
        $phone,
        $city,
        $email,
        $reference,
        $pickup_batch_id = '',
        $is_manual_mode = false,
        $pickup_address_id = null,
        $sender_data = null,
        $pickup_data = null,
        $dp_data = null,
        &$errors = null,
        $useTaxAndDutyService = false
    ) {
        $this->logger->logDebug(__METHOD__ .' - CREATE PICKUP SHIPPING');
        $wsUrl = $this->helper->getWsUrl();
        $wsUsername = $this->helper->getWsUsername();
        $wsPassword = $this->helper->getWsPassword();
        $system = $this->helper->getCountry();

        if (!$wsUrl || !$wsUsername || !$wsPassword) {
            return false;
        }

        $shipmentType = $this->helper->getDefaultShipmentType();
        $items = $this->setItems($weight);
        $this->logger->logDebug(__METHOD__ . ' - CREATE PICKUP SHIPPING ITEMS');
        $this->logger->logDebug($items);
        $shipperType = $this->getShipperType();

        $result = $this->ws->createPickupShipping(
            $wsUrl,
            $wsUsername,
            $wsPassword,
            $shipmentType,
            $service,
            $system,
            $notes,
            $firstName,
            $lastName,
            $companyName,
            $address,
            $phone,
            $city,
            $region,
            $country,
            $postCode,
            $email,
            $items,
            $products,
            $shipperType,
            $reference,
            $pickup_batch_id,
            $is_manual_mode,
            $pickup_address_id,
            $sender_data,
            $pickup_data,
            $dp_data,
            $errors,
            $useTaxAndDutyService
        );
        return $result;
    }

    public function closePickupShipping(string $pickup_batch_id, string $preferred_from, string $preferred_to, string $alternative_from, string $alternative_to, string $notes, string $date, array &$errors = null)
    {
        $orderHelper =  new OrderHelper();
        $mOrderHelper = new MOrderHelper();

        $this->logger->logDebug('CLOSE PICKUP SHIPPING');

        $wsUrl = $this->helper->getWsUrl();
        $wsUsername = $this->helper->getWsUsername();
        $wsPassword = $this->helper->getWsPassword();
        $system = $this->helper->getCountry();

        if (!$wsUrl || !$wsUsername || !$wsPassword) {
            return false;
        }

        $result = $this->ws->closePickupShipping($wsUrl, $wsUsername, $wsPassword, $system, $pickup_batch_id, $preferred_from, $preferred_to, $alternative_from, $alternative_to, $notes, $date, $errors);

        if (!$result) {
            return false;
        }

        $filePath = $this->helper->getTrackingFilePath($pickup_batch_id);
        if (base64_encode(base64_decode($result->Label->Stream, true)) === $result->Label->Stream) {
            $fileStream = base64_decode($result->Label->Stream);
        } else {
            $fileStream = $result->Label->Stream;
        }
        file_put_contents($filePath, $fileStream);

        $sql = new \DbQuery();
        $sql->select('o.id_order');
        $sql->from('mbe_shipping_order', 'o');
        $sql->innerJoin('mbe_shipping_pickup_batch', 'pb', 'o.id_mbeshipping_pickup_batch = pb.id_mbeshipping_pickup_batch');
        $sql->where('pb.pickup_batch_id = ' . $pickup_batch_id);
        $order_ids = array_column(\Db::getInstance()->getRow($sql), 'id_order');

        foreach ($order_ids as $order_id) {
            $order = new \Order($order_id);
            $mOrderHelper->setOrderPickupMode($order->id, true);
            $orderHelper->setOrderShipped($order);
        }

        return $result;
    }

    public function getPickupDefaultData() {
        $this->logger->logDebug('GET PICKUP DEFAULT DATA');
        $wsUrl = $this->helper->getWsUrl();
        $wsUsername = $this->helper->getWsUsername();
        $wsPassword = $this->helper->getWsPassword();
        $system = $this->helper->getCountry();

        if (!$wsUrl || !$wsUsername || !$wsPassword) {
            return false;
        }

        return $this->ws->getPickupDefaultData($wsUrl, $wsUsername, $wsPassword, $system);
    }

    public function setPickupDefaultData($cutoff, $preferred_from, $preferred_to, $alternative_from, $alternative_to, $notes = '') {
        $this->logger->logDebug('SET PICKUP DEFAULT DATA');
        $wsUrl = $this->helper->getWsUrl();
        $wsUsername = $this->helper->getWsUsername();
        $wsPassword = $this->helper->getWsPassword();
        $system = $this->helper->getCountry();

        if (!$wsUrl || !$wsUsername || !$wsPassword) {
            return false;
        }

        return $this->ws->setPickupDefaultData($wsUrl, $wsUsername, $wsPassword, $system, $cutoff, $preferred_from, $preferred_to, $alternative_from, $alternative_to, $notes);
    }

    public function getPickupManifestList(array $trackingNumbers, array &$errors)
    {
        $this->logger->logDebug('GET PICKUP MANIFEST LIST');

        $wsUrl = $this->helper->getWsUrl();
        $wsUsername = $this->helper->getWsUsername();
        $wsPassword = $this->helper->getWsPassword();
        $system = $this->helper->getCountry();

        $result = false;
        if ($wsUrl && $wsUsername && $wsPassword) {
            $result = $this->ws->getPickupManifestList($wsUrl, $wsUsername, $wsPassword, $system, $trackingNumbers, $errors);

            if (!$result) {
                return false;
            }

            $filePath = $this->helper->getManifestsFilePath($result->InternalReferenceID);
            if (base64_encode(base64_decode($result->Label->Stream, true)) === $result->Label->Stream) {
                $fileStream = base64_decode($result->Label->Stream);
            } else {
                $fileStream = $result->Label->Stream;
            }
            file_put_contents($filePath, $fileStream);
        }

        return $result;
    }
    /* - Third party pickups */

    /* - Delivery Point */
    public function shipmentDocumentRequest(int $id_order, string $trackingMBE, array &$errors)
    {
        $this->logger->logDebug('SHIPMENT DOCUMENT REQUEST');

        $wsUrl = $this->helper->getWsUrl();
        $wsUsername = $this->helper->getWsUsername();
        $wsPassword = $this->helper->getWsPassword();
        $system = $this->helper->getCountry();

        $result = false;
        if ($wsUrl && $wsUsername && $wsPassword) {
            $result = $this->ws->shipmentDocumentRequest($wsUrl, $wsUsername, $wsPassword, $system, $trackingMBE, $errors);

            if (!$result) {
                return false;
            }

            $isLdvFound = false;
            if(isset($result->ShipmentDocuments)) {
                foreach($result->ShipmentDocuments as $ShipmentDocument) {
                    if($ShipmentDocument->TrackingMBE == $trackingMBE) {

                        $urlDownload = $ShipmentDocument->CourierWaybill;
                        $headers = @get_headers($urlDownload);

                        $this->logger->logDebug("SHIPMENT DOCUMENT REQUEST - HEADERS  \n" . print_r($headers, true));

                        if($headers && strpos( $headers[0], '200')) {
                            $isLdvFound = true;
                            $filePath = $this->helper->getLdvFilePath($id_order, $trackingMBE);
                            file_put_contents($filePath, \Tools::file_get_contents($urlDownload));
                        }
                    }
                }
            }

            if(!$isLdvFound) {
                $this->logger->logDebug('SHIPMENT DOCUMENT REQUEST - LDV NOT FOUND');
                return false;
            }
        }

        return $result;
    }

    private function storeDeliveryPointInformation($customer)
    {
        \Configuration::updateValue('MBE_SHIPPING_DP_APIKEY', isset($customer->DeliveryPointApiKey) ? $customer->DeliveryPointApiKey : '');
        \Configuration::updateValue('MBE_SHIPPING_DP_MCODE', isset($customer->MerchantCode) ? $customer->MerchantCode : '');

        //Store Network Type
        $gel_network_codes = array();
        $mbe_network_codes = array();
        if(isset($customer->Permissions->enabledCouriersDesc) && isset($customer->Permissions->enabledCourierServices)) {
            $enabled_couriers_desc = explode(',', $customer->Permissions->enabledCouriersDesc);
            $enabled_courier_services = explode(',', $customer->Permissions->enabledCourierServices);
            if(sizeof($enabled_couriers_desc) && sizeof($enabled_courier_services)) {
                foreach($enabled_couriers_desc as $k => $v) {
                    if(substr($enabled_courier_services[$k], 0, 3) === "NET") {
                        if(strtolower($v) == 'gel') {
                            if(!in_array($enabled_courier_services[$k], $gel_network_codes)) {
                                $gel_network_codes[] = $enabled_courier_services[$k];
                            }
                        } else {
                            if(!in_array($enabled_courier_services[$k], $mbe_network_codes)) {
                                $mbe_network_codes[] = $enabled_courier_services[$k];
                            }
                        }
                    }
                }
            }
        }

        if(sizeof($gel_network_codes)) {
            \Configuration::updateValue('MBE_SHIPPING_DP_GNET', implode(',', $gel_network_codes));
        } else {
            \Configuration::updateValue('MBE_SHIPPING_DP_GNET', '');
        }

        if(sizeof($mbe_network_codes)) {
            \Configuration::updateValue('MBE_SHIPPING_DP_MNET', implode(',', $mbe_network_codes));
        } else {
            \Configuration::updateValue('MBE_SHIPPING_DP_MNET', '');
        }

        if(isset($customer->Country)) {
            //Prepare variable price shipping
            $helper = new DeliveryPointHelper();
            $helper->prepareShippingEstimates($customer->Country);
        }
    }
    /* - Delivery Point */
}
