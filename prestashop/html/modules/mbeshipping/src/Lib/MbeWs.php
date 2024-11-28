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

namespace PrestaShop\Module\Mbeshipping\Lib;

use PrestaShop\Module\Mbeshipping\Helper\LoggerHelper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MbeWs
{

//    private $_logPath;
//    private $_logFileName = 'mbe_ws.log';
    private $_log = true;

    public function __construct()
    {
        $this->logger = new LoggerHelper();
    }

    public function getCustomer($ws, $username, $password, $system)
    {
        $this->log(__METHOD__ .' - GET CUSTOMER');
        $result = false;

        try {
            $soapClient = new MbeSoapClient($ws, ['encoding' => 'utf-8', 'trace' => 1], $username, $password);

            $internalReferenceID = $this->generateRandomString();

            //WS ARGS
            $args = new \stdClass;
            $args->RequestContainer = new \stdClass;

            $args->RequestContainer->Action = 'GET';

            $args->RequestContainer->SystemType = $system;

            $args->RequestContainer->Customer = new \stdClass;
            $args->RequestContainer->Customer->Login = '';

            $args->RequestContainer->Credentials = new \stdClass;
            $args->RequestContainer->Credentials->Username = $username;
            $args->RequestContainer->Credentials->Passphrase = $password;

            $args->RequestContainer->InternalReferenceID = $internalReferenceID;

            $this->logVar(__METHOD__ . ' - GET CUSTOMER ARGS');
            $this->logVar($args);
            $soapResult = $soapClient->__soapCall('ManageCustomerRequest', [$args]);

            $lastResponse = $soapClient->__getLastResponse();
            $this->logVar(__METHOD__ . ' - GET CUSTOMER RESPONSE');
            $this->logVar($lastResponse);

            if (isset($soapResult->RequestContainer->Errors)) {
                $this->logVar(__METHOD__ . ' - GET CUSTOMER ERRORS');
                $this->logVar($soapResult->RequestContainer->Errors);
            }

            if (isset($soapResult->RequestContainer->Status) && $soapResult->RequestContainer->Status === 'OK') {
                //if (isset($soapResult->RequestContainer->InternalReferenceID) && $soapResult->RequestContainer->InternalReferenceID == $internalReferenceID) {
                $result = $soapResult->RequestContainer->Customer;
                //}
            }
        } catch (\Exception $e) {
            $this->log(__METHOD__ . ' - GET CUSTOMER EXCEPTION');
            $this->log($e->getMessage());
        }

        $this->logVar(__METHOD__ . ' - GET CUSTOMER RESULT');
        $this->logVar($result);

        return $result;
    }

    private function log($message)
    {
        if ($this->_log) {
            $row = date_format(new \DateTime(), 'Y-m-d\TH:i:s\Z');
            $row .= ' - ';
            $row .= $message . "\n";
            $this->logger->logDebug($row);
        }
    }

    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = \Tools::strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function logVar($var, $message = null)
    {
        if ($this->_log) {
            if ($message) {
                $this->log($message);
            }
            $this->log(print_r($var, true));
        }
    }

    public function estimateShipping($ws, $username, $password, $shipmentType, $system, $country, $region, $postCode, $items, $products, $insurance = false, $insuranceValue = 0.00, $isPickup = false, $pickuPdefaultAddress = [], $useTaxAndDutyService = false)
    {
        $this->log(__METHOD__ . ' - ESTIMATE SHIPPING');
        $result = false;

        try {
            $soapClient = new MbeSoapClient($ws, ['encoding' => 'utf-8', 'trace' => 1], $username, $password);
            $internalReferenceID = $this->generateRandomString();

            //WS ARGS
            $args = new \stdClass();
            $args->RequestContainer = new \stdClass();
            $args->RequestContainer->System = $system;

            $args->RequestContainer->Credentials = new \stdClass();
            $args->RequestContainer->Credentials->Username = $username;
            $args->RequestContainer->Credentials->Passphrase = $password;

            $args->RequestContainer->InternalReferenceID = $internalReferenceID;

            $args->RequestContainer->ShippingParameters = new \stdClass();

            if($isPickup && !empty($pickuPdefaultAddress)) {
                $args->RequestContainer->ShippingParameters->SenderInfo = new \stdClass();
                $args->RequestContainer->ShippingParameters->SenderInfo->ZipCode = $pickuPdefaultAddress['zip_code'];
                $args->RequestContainer->ShippingParameters->SenderInfo->City = $pickuPdefaultAddress['city'];
                $args->RequestContainer->ShippingParameters->SenderInfo->Country = $pickuPdefaultAddress['country'];
            }

            $args->RequestContainer->ShippingParameters->DestinationInfo = new \stdClass();
            $args->RequestContainer->ShippingParameters->DestinationInfo->ZipCode = $postCode;
            $args->RequestContainer->ShippingParameters->DestinationInfo->City = $region;
            //$args->RequestContainer->ShippingParameters->DestinationInfo->State = $region;
            $args->RequestContainer->ShippingParameters->DestinationInfo->Country = $country;
            //$args->RequestContainer->ShippingParameters->DestinationInfo->idSubzone = "";

            $args->RequestContainer->ShippingParameters->ShipType = 'EXPORT';
            $args->RequestContainer->ShippingParameters->PackageType = $shipmentType;
            $args->RequestContainer->ShippingParameters->Items = $items;
            $args->RequestContainer->ShippingParameters->Insurance = $insurance;

            if ($insurance) {
                $args->RequestContainer->ShippingParameters->InsuranceValue = $insuranceValue;
            }

            if ($useTaxAndDutyService) {
                $request_proforma = [];
                foreach ($products as $item) {
                    $proforma = new \stdClass();
                    $proforma->Amount = $item->Quantity;
                    $proforma->Currency = 'EUR';
                    $proforma->Value = $item->Price;
                    $proforma->Unit = 'PCS';
                    $proforma->Description = $item->Description;
                    //$proforma->HsCode = '';
                    $proforma->Category = $item->Category;
                    //$proforma->Gender = '';
                    //$proforma->Material = '';
                    $proforma->Weight = $item->Weight;
                    $request_proforma[] = $proforma;
                }

                $args->RequestContainer->ShippingParameters->ProformaInvoice = new \stdClass();
                $args->RequestContainer->ShippingParameters->ProformaInvoice->ProformaDetail = $request_proforma;
                $args->RequestContainer->ShippingParameters->LanguageCode = \Context::getContext()->language->iso_code;
                $args->RequestContainer->ShippingParameters->TaxAndDutyPluginAct = true;


                // + MBE CR DDP/DAP
                $proformaIncoterms = \Configuration::get('MBESHIPPING_TAX_DUTY_INTERCOM');
                $args->RequestContainer->ShippingParameters->ProformaIncoterms = $proformaIncoterms != null ? $proformaIncoterms : 'DAP';
                // - MBE CR DDP/DAP
            }

            $this->logVar(__METHOD__ . ' - ESTIMATE SHIPPING ARGS');
            $this->logVar($args);

            $soapResult = $soapClient->__soapCall('ShippingOptionsRequest', [$args]);

            $lastResponse = $soapClient->__getLastResponse();
            $this->logVar(__METHOD__ . ' - ESTIMATE SHIPPING RESPONSE');
            $this->logVar($lastResponse);

            if (isset($soapResult->RequestContainer->Errors)) {
                $this->logVar(__METHOD__ . ' - ESTIMATE SHIPPING ERRORS');
                $this->logVar($soapResult->RequestContainer->Errors);
            }

            if (
                isset(
                    $soapResult->RequestContainer->Status,
                    $soapResult->RequestContainer->InternalReferenceID,
                    $soapResult->RequestContainer->ShippingOptions->ShippingOption
                ) &&
                $soapResult->RequestContainer->Status === 'OK' &&
                $soapResult->RequestContainer->InternalReferenceID === $internalReferenceID
            ) {
                if (is_array($soapResult->RequestContainer->ShippingOptions->ShippingOption)) {
                    $result = $soapResult->RequestContainer->ShippingOptions->ShippingOption;
                } else {
                    $result = [$soapResult->RequestContainer->ShippingOptions->ShippingOption];
                }
            }
        } catch (\Exception $e) {
            $this->log(__METHOD__ . ' - ESTIMATE SHIPPING EXCEPTION');
            $this->log($e->getMessage());
        }
        $this->logVar(__METHOD__ . ' - ESTIMATE SHIPPING RESULT');
        $this->logVar($result);
        return $result;
    }

    public function createShipping($ws, $username, $password, $shipmentType, $service, $system, $notes, $firstName, $lastName, $companyName, $address, $phone, $city, $state, $country, $postCode, $email, $items, $products, $shipperType = 'MBE', $goodsValue = 0.0, $reference = '', $isCod = false, $codValue = 0.0, $insurance = false, $insuranceValue = 0.0, $dp_data = false, $useTaxAndDutyService = false)
    {
        $this->logVar(__METHOD__ . ' - CREATE SHIPPING');
        $this->logVar(func_get_args());

        $result = false;

        try {
            $soapClient = new MbeSoapClient($ws, ['encoding' => 'utf-8', 'trace' => 1], $username, $password);
            $internalReferenceID = $this->generateRandomString();

            //WS ARGS
            $args = new \stdClass;
            $args->RequestContainer = new \stdClass;
            $args->RequestContainer->System = $system;
            //$args->RequestContainer->Customer = new stdClass;
            //$args->RequestContainer->Customer->Login = "";

            $args->RequestContainer->Credentials = new \stdClass;
            $args->RequestContainer->Credentials->Username = $username;
            $args->RequestContainer->Credentials->Passphrase = $password;

            $args->RequestContainer->InternalReferenceID = $internalReferenceID;

            //RequestContainer -> Recipient
            $args->RequestContainer->Recipient = new \stdClass;

            $recipientName = $firstName . ' ' . $lastName;
            $RecipientCompanyName = $companyName;
            $recipientName = mb_substr($recipientName, 0, 35, 'utf8');
            $RecipientCompanyName = mb_substr($RecipientCompanyName, 0, 35, 'utf8');
            if (empty($RecipientCompanyName)) {
                $RecipientCompanyName = $recipientName;
            }
            $args->RequestContainer->Recipient->Name = $recipientName;
            //$args->RequestContainer->Recipient->LastName = $lastName;
            $args->RequestContainer->Recipient->CompanyName = $RecipientCompanyName;
            $args->RequestContainer->Recipient->Address = $address;
            $args->RequestContainer->Recipient->Phone = $phone;
            $args->RequestContainer->Recipient->ZipCode = $postCode;
            $args->RequestContainer->Recipient->City = $city;
            $args->RequestContainer->Recipient->State = $state;
            $args->RequestContainer->Recipient->Country = $country;
            $args->RequestContainer->Recipient->Email = $email;
            //$args->RequestContainer->Recipient->SubzoneId = $subZone;

            //RequestContainer -> Shipment
            $args->RequestContainer->Shipment = new \stdClass;

            $args->RequestContainer->Shipment->ShipperType = $shipperType;//"MBE";//COURIERLDV - MBE
            $args->RequestContainer->Shipment->Description = 'ECOMMERCE SHOP PURCHASE';
            $args->RequestContainer->Shipment->COD = $isCod;
            //$args->RequestContainer->Shipment->CODValue =0;
            //$args->RequestContainer->Shipment->MethodPayment ="CASH";//CASH - CHECK
            if ($isCod) {
                $args->RequestContainer->Shipment->CODValue = $codValue;
                $args->RequestContainer->Shipment->MethodPayment = 'CASH';//CASH - CHECK
            }

            $args->RequestContainer->Shipment->Insurance = $insurance;
            //$args->RequestContainer->Shipment->InsuranceValue = 0;
            if ($insurance) {
                $args->RequestContainer->Shipment->InsuranceValue = $insuranceValue;
            }
            $args->RequestContainer->Shipment->Service = $service;//SEE /SSE

            //$args->RequestContainer->Shipment->Courier = "";
            //$args->RequestContainer->Shipment->CourierService = "SEE";
            //$args->RequestContainer->Shipment->CourierAccount = "";
            $args->RequestContainer->Shipment->PackageType = $shipmentType;
            //$args->RequestContainer->Shipment->Value = 0;
            $args->RequestContainer->Shipment->Referring = $reference;

            $args->RequestContainer->Shipment->Items = $items;

            $request_products = [];
            $request_proforma = [];
            foreach ($products as $item) {
                $product = new \stdClass;
                $product->SKUCode = $item->SKUCode;
                $product->Description = $item->Description;
                $product->Quantity = $item->Quantity;
                $request_products[] = $product;

                $proforma = new \stdClass;
                $proforma->Amount = $item->Quantity;
                $proforma->Currency = 'EUR';
                $proforma->Value = $item->Price;
                $proforma->Unit = 'PCS';
                $proforma->Description = $item->Description;
                $request_proforma[] = $proforma;
            }

            $args->RequestContainer->Shipment->Products = $request_products;
            $args->RequestContainer->Shipment->ProformaInvoice = new \stdClass;
            $args->RequestContainer->Shipment->ProformaInvoice->ProformaDetail = $request_proforma;
            $args->RequestContainer->Shipment->LanguageCode = \Context::getContext()->language->iso_code;
            $args->RequestContainer->Shipment->TaxAndDutyPluginAct = $useTaxAndDutyService;

            // + MBE CR DDP/DAP
            if($useTaxAndDutyService){
                $proformaIncoterms = \Configuration::get('MBESHIPPING_TAX_DUTY_INTERCOM');
                $args->RequestContainer->Shipment->ProformaIncoterms = $proformaIncoterms != null ? $proformaIncoterms : 'DAP';
            }
            // - MBE CR DDP/DAP

            $args->RequestContainer->Shipment->Value = $goodsValue;

            // new data to request
            if($dp_data){
                $args->RequestContainer->RecipientDeliveryPoint = clone $args->RequestContainer->Recipient;
                $args->RequestContainer->RecipientDeliveryPoint->DeliveryPointId = $dp_data['deliveryPointID'];
                $args->RequestContainer->Shipment->CourierService = $dp_data['networkCode'];
                $args->RequestContainer->Shipment->Courier = $dp_data['networkName'];
                $args->RequestContainer->Shipment->Service = $dp_data['service'];
            }

            $args->RequestContainer->Shipment->Notes = mb_substr($notes, 0, 50, 'utf8');

            $args->RequestContainer->Shipment->ShipmentOrigin = "MBE eShip Prestashop 2.2.2";

            $this->logVar(__METHOD__ . ' - CREATE SHIPPING ARGS');
            $this->logVar($args);

            $soapResult = $soapClient->__soapCall('ShipmentRequest', [$args]);


            $lastResponse = $soapClient->__getLastResponse();
            $this->logVar(__METHOD__ .' - CREATE SHIPPING RESPONSE');
            $this->logVar($lastResponse);

            if (isset($soapResult->RequestContainer->Errors)) {
                $this->logVar(__METHOD__ . ' - CREATE SHIPPING ERRORS');
                $this->logVar($soapResult->RequestContainer->Errors);
                $this->updateShippingErrors($reference, $soapResult->RequestContainer->Errors);
            }
            if (isset($soapResult->RequestContainer->Status, $soapResult->RequestContainer->InternalReferenceID) && $soapResult->RequestContainer->Status === 'OK' && $soapResult->RequestContainer->InternalReferenceID === $internalReferenceID) {
                $result = $soapResult->RequestContainer;
            }

        } catch (\Exception $e) {
            $this->log(__METHOD__ . ' - CREATE SHIPPING EXCEPTION');
            $this->log($e->getMessage());
        }
        $this->logVar(__METHOD__ . ' - CREATE SHIPPING RESULT');
        $this->logVar($result);
        return $result;
    }

    private function updateShippingErrors($reference, $errors)
    {
        $conf = \Configuration::get('MBESHIPPING_CREATE_SHIPPING_ERRORS');

        if (empty($conf)) {
            $error_data = [];

            if (!empty($errors) && $errors instanceof \stdClass) {
                $error_data[$reference] = [
                    'code' => $errors->Error->ErrorCode,
                    'description' => $errors->Error->Description
                ];
                \Configuration::updateValue('MBESHIPPING_CREATE_SHIPPING_ERRORS', json_encode($error_data));
            }
            return;
        }

        if (!empty($errors) && $errors instanceof \stdClass) {
            $errors_arr = json_decode($conf, true);
            $errors_arr[$reference] = [
                'code' => $errors->Error->ErrorCode,
                'description' => $errors->Error->Description
            ];
            \Configuration::updateValue('MBESHIPPING_CREATE_SHIPPING_ERRORS', json_encode($errors_arr));
        }
    }

    public function closeShipping($ws, $username, $password, $system, $trackings)
    {
        $this->log('CLOSE SHIPPING');

        $result = false;

        try {
            $soapClient = new MbeSoapClient($ws, array('encoding' => 'utf-8', 'trace' => 1), $username, $password);
            $internalReferenceID = $this->generateRandomString();

            //WS ARGS
            $args = new \stdClass;
            $args->RequestContainer = new \stdClass;
            $args->RequestContainer->SystemType = $system;

            $args->RequestContainer->Credentials = new \stdClass;
            $args->RequestContainer->Credentials->Username = $username;
            $args->RequestContainer->Credentials->Passphrase = $password;

            $args->RequestContainer->InternalReferenceID = $internalReferenceID;

            $masterTrackingsMBE = array();
            foreach ($trackings as $track) {
                $masterTrackingsMBE[] = $track;
            }

            $args->RequestContainer->MasterTrackingsMBE = $masterTrackingsMBE;

            $this->logVar('CLOSE SHIPPING ARGS');
            $this->logVar($args);

            $soapResult = $soapClient->__soapCall("CloseShipmentsRequest", array($args));

            $lastResponse = $soapClient->__getLastResponse();
            $this->logVar('CLOSE SHIPPING RESPONSE');
            $this->logVar($lastResponse);

            if (isset($soapResult->RequestContainer->Errors)) {
                $this->logVar('CLOSE SHIPPING ERRORS');
                $this->logVar($soapResult->RequestContainer->Errors);
            }

            if (isset($soapResult->RequestContainer->Status) && $soapResult->RequestContainer->Status == "OK") {
                $result = $soapResult->RequestContainer;
            }

        } catch (\Exception $e) {
            $this->log('CLOSE SHIPPING EXCEPTION');
            $this->log($e->getMessage());
        }
        $this->logVar('CLOSE SHIPPING RESULT');
        $this->logVar($result);
        return $result;
    }

    public function createReturnShipping($ws, $username, $password, $system, $tracking)
    {
        $this->log('CREATE RETURN SHIPPING');

        $this->logVar('CREATE RETURN SHIPPING');
        $this->logVar(func_get_args());

        $result = false;

        try {
            $soapClient = new MbeSoapClient($ws, array('encoding' => 'utf-8', 'trace' => 1), $username, $password);
            $internalReferenceID = $this->generateRandomString();

            //WS ARGS
            $args = new \stdClass;
            $args->RequestContainer = new \stdClass;
            $args->RequestContainer->System = $system;

            $args->RequestContainer->Credentials = new \stdClass;
            $args->RequestContainer->Credentials->Username = $username;
            $args->RequestContainer->Credentials->Passphrase = $password;

            $args->RequestContainer->InternalReferenceID = $internalReferenceID;

            $args->RequestContainer->MbeTracking = $tracking;
            $args->RequestContainer->CustomerAsReceiver = true;
            $args->RequestContainer->ShipmentOrigin = "MBE eShip Prestashop 2.2.2";
            $args->RequestContainer->Referring = '';

            $this->logVar($args, 'RETURN SHIPPING ARGS');

            $soapResult = $soapClient->__soapCall("ShipmentReturnRequest", array($args));

            $lastResponse = $soapClient->__getLastResponse();
            $this->logVar('CREATE SHIPPING RESPONSE');
            $this->logVar($lastResponse);

            if (isset($soapResult->RequestContainer->Errors)) {
                $this->logVar('RETURN SHIPPING ERRORS');
                $this->logVar($soapResult->RequestContainer->Errors);
            }
            if (isset($soapResult->RequestContainer->Status) && $soapResult->RequestContainer->Status == "OK") {
                if (isset($soapResult->RequestContainer->InternalReferenceID) && $soapResult->RequestContainer->InternalReferenceID == $internalReferenceID) {
                    $result = $soapResult->RequestContainer;
                }
            }

        } catch (\Exception $e) {
            $this->log('RETURN SHIPPING EXCEPTION');
            $this->log($e->getMessage());
        }
        $this->logVar('RETURN SHIPPING RESULT');
        $this->logVar($result);
        return $result;
    }

    /* + Third party pickups */
    public function getPickupAddresses($ws, $username, $password, $system)
    {
        $this->log(__METHOD__ . " - GET PICKUP ADDRESSES");
        $result = [];

        try {
            $soapClient = new MbeSoapClient($ws, ['encoding' => 'utf-8', 'trace' => 1], $username, $password);

            //WS ARGS
            $args = new \stdClass;
            $args->RequestContainer = new \stdClass;
            $args->RequestContainer->System = $system;
            $args->RequestContainer->Credentials = new \stdClass;
            $args->RequestContainer->Credentials->Username = $username;
            $args->RequestContainer->Credentials->Passphrase = $password;
            $args->RequestContainer->InternalReferenceID = $this->generateRandomString();

            $this->log(__METHOD__ . " - PAYLOAD: \n" . print_r($args, true));

            $soapResult = $soapClient->__soapCall("GetPickupAddressesRequest", [$args]);
            $this->log(__METHOD__ . " - SOAP RESULT: \n" . print_r($soapResult, true));

            if (isset($soapResult->RequestContainer->Errors)) {
                $this->log(__METHOD__ . " - ERRORS: \n" . print_r($soapResult->RequestContainer->Errors, true));
                return false;
            }

            if (!isset($soapResult->RequestContainer->Status) || !$soapResult->RequestContainer->Status == "OK") {
                $this->log(__METHOD__ . " - STATUS NOT OK");
                return false;
            }

            $response = $soapResult->RequestContainer->PickupAddress;

            if (is_array($response)) {
                $result = array_map(function ($address) {
                    return get_object_vars($address->PickupContainer);
                }, $response);
            } else {
                $result[] = get_object_vars($response->PickupContainer);
            }
        } catch (\Exception $e) {
            $this->log(__METHOD__ . " - EXCEPTION: {$e->getMessage()}");
        }

        $this->log(__METHOD__ . " - RESULT: \n" . print_r($result, true));

        return $result;
    }

    public function savePickupAddress($ws, $username, $password, $system, $pickup_container)
    {
        $this->log(__METHOD__ . " - SAVE PICKUP ADDRESS");
        $result = false;

        try {
            $soapClient = new MbeSoapClient($ws, ['encoding' => 'utf-8', 'trace' => 1], $username, $password);

            //WS ARGS
            $args = new \stdClass;
            $args->RequestContainer = new \stdClass;
            $args->RequestContainer->System = $system;
            $args->RequestContainer->Credentials = new \stdClass;
            $args->RequestContainer->Credentials->Username = $username;
            $args->RequestContainer->Credentials->Passphrase = $password;
            $args->RequestContainer->InternalReferenceID = $this->generateRandomString();
            $args->RequestContainer->PickupContainer = $pickup_container;

            $this->log(__METHOD__ . " - PAYLOAD: \n" . var_export($args, true));

            $soapResult = $soapClient->__soapCall("CreatePickupAddressRequest", [$args]);
            $this->log(__METHOD__ . " - SOAP RESULT: \n" . var_export($soapResult, true));

            if (isset($soapResult->RequestContainer->Errors)) {
                $this->log(__METHOD__ . " - ERRORS: \n" . print_r($soapResult->RequestContainer->Errors, true));
                return false;
            }

            if (!isset($soapResult->RequestContainer->Status) || !$soapResult->RequestContainer->Status == "OK") {
                $this->log(__METHOD__ . " - STATUS NOT OK");
                return false;
            }

            $result = $soapResult->RequestContainer->PickupAddressId;
        } catch (\Exception $e) {
            $this->log(__METHOD__ . " - EXCEPTION: {$e->getMessage()}");
        }

        $this->log(__METHOD__ . " - RESULT: \n" . print_r($result, true));
        return $result;
    }

    public function deletePickupAddress($ws, $username, $password, $system, $pickup_address_id)
    {
        $this->log(__METHOD__ . " - DELETE PICKUP ADDRESS");
        $result = false;

        try {
            $soapClient = new MbeSoapClient($ws, ['encoding' => 'utf-8', 'trace' => 1], $username, $password);

            //WS ARGS
            $args = new \stdClass;
            $args->RequestContainer = new \stdClass;
            $args->RequestContainer->System = $system;
            $args->RequestContainer->Credentials = new \stdClass;
            $args->RequestContainer->Credentials->Username = $username;
            $args->RequestContainer->Credentials->Passphrase = $password;
            $args->RequestContainer->InternalReferenceID = $this->generateRandomString();
            $args->RequestContainer->PickupAddressId = $pickup_address_id;

            $this->log(__METHOD__ . " - PAYLOAD: \n" . print_r($args, true));

            $soapResult = $soapClient->__soapCall("DeletePickupAddressRequest", [$args]);
            $this->log(__METHOD__ . " - SOAP RESULT: \n" . print_r($soapResult, true));

            if (isset($soapResult->RequestContainer->Errors)) {
                $this->log(__METHOD__ . " - ERRORS: \n" . print_r($soapResult->RequestContainer->Errors, true));
                return false;
            }

            if (!isset($soapResult->RequestContainer->Status) || !$soapResult->RequestContainer->Status == "OK") {
                $this->log(__METHOD__ . " - STATUS NOT OK");
                return false;
            }

            $result = $soapResult->RequestContainer->PickupAddressId;
        } catch (\Exception $e) {
            $this->log(__METHOD__ . " - EXCEPTION: {$e->getMessage()}");
        }

        $this->log(__METHOD__ . " - RESULT: \n" . print_r($result, true));
        return $result;
    }

    public function createPickupShipping(
        $ws,
        $username,
        $password,
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
        $state,
        $country,
        $postCode,
        $email,
        $items,
        $products,
        $shipperType,
        $reference,
        $pickup_batch_id = '',
        $is_manual_mode = false,
        $pickup_address_id = null,
        $sender_data = null,
        $pickup_data = null,
        $dp_data = null,
        &$errors = null,
        $useTaxAndDutyService = false
    )
    {
        $this->log(__METHOD__ . ' - CREATE PICKUP SHIPPING');
        $this->logVar(func_get_args());

        $result = false;

        try {
            $soapClient = new MbeSoapClient($ws, ['encoding' => 'utf-8', 'trace' => 1], $username, $password);

            //WS ARGS
            $args = new \stdClass;
            $args->RequestContainer = new \stdClass;
            $args->RequestContainer->System = $system;
            $args->RequestContainer->Credentials = new \stdClass;
            $args->RequestContainer->Credentials->Username = $username;
            $args->RequestContainer->Credentials->Passphrase = $password;
            $args->RequestContainer->InternalReferenceID = $this->generateRandomString();

            $args->RequestContainer->Recipient = new \stdClass;

            $recipientName = $firstName . ' ' . $lastName;
            $RecipientCompanyName = $companyName;
            $recipientName = mb_substr($recipientName, 0, 35, 'utf8');
            $RecipientCompanyName = mb_substr($RecipientCompanyName, 0, 35, 'utf8');
            if (empty($RecipientCompanyName)) {
                $RecipientCompanyName = $recipientName;
            }
            $args->RequestContainer->Recipient->Name = $recipientName;
            $args->RequestContainer->Recipient->CompanyName = $RecipientCompanyName;
            $args->RequestContainer->Recipient->Address = $address;
            $args->RequestContainer->Recipient->Phone = $phone;
            $args->RequestContainer->Recipient->ZipCode = $postCode;
            $args->RequestContainer->Recipient->City = $city;
            $args->RequestContainer->Recipient->State = $state;
            $args->RequestContainer->Recipient->Country = $country;
            $args->RequestContainer->Recipient->Email = $email;

            $args->RequestContainer->Shipment = new \stdClass;
            $args->RequestContainer->Shipment->ShipperType = $shipperType;
            $args->RequestContainer->Shipment->Description = 'ECOMMERCE SHOP PURCHASE';
            $args->RequestContainer->Shipment->COD = false;
            $args->RequestContainer->Shipment->Insurance = false;
            $args->RequestContainer->Shipment->Service = $service;
            $args->RequestContainer->Shipment->PackageType = $shipmentType;
            $args->RequestContainer->Shipment->GoodType = 'ART';
            $args->RequestContainer->Shipment->Items = $items;
            $args->RequestContainer->Shipment->InternalNotes = '';
            $args->RequestContainer->Shipment->Notes = mb_substr($notes, 0, 50, 'utf8');
            $args->RequestContainer->Shipment->DeliveryPrivateHome = false;
            $args->RequestContainer->Shipment->DeliveryDirect = false;
            $args->RequestContainer->Shipment->MBESafeValue = false;
            $args->RequestContainer->Shipment->LabelFormat = 'OLD';
            $args->RequestContainer->Shipment->ShipmentOrigin = 'MBE eShip Prestashop 2.2.2';
            $args->RequestContainer->Shipment->Referring = $reference;

            $request_products = [];
            $request_proforma = [];
            foreach ($products as $item) {
                $product = new \stdClass;
                $product->SKUCode = $item->SKUCode;
                $product->Description = $item->Description;
                $product->Quantity = $item->Quantity;
                $request_products[] = $product;

                $proforma = new \stdClass;
                $proforma->Amount = $item->Quantity;
                $proforma->Currency = 'EUR';
                $proforma->Value = $item->Price;
                $proforma->Unit = 'PCS';
                $proforma->Description = $item->Description;
                $request_proforma[] = $proforma;
            }
            $args->RequestContainer->Shipment->Products = $request_products;
            $args->RequestContainer->Shipment->ProformaInvoice = new \stdClass;
            $args->RequestContainer->Shipment->ProformaInvoice->ProformaDetail = $request_proforma;
            $args->RequestContainer->Shipment->LanguageCode = \Context::getContext()->language->iso_code;
            $args->RequestContainer->Shipment->TaxAndDutyPluginAct = $useTaxAndDutyService;

            // DevlieryPoint
            if(!empty($dp_data)) {
                $args->RequestContainer->RecipientDeliveryPoint = clone $args->RequestContainer->Recipient;
                $args->RequestContainer->RecipientDeliveryPoint->DeliveryPointId = $dp_data['deliveryPointID'];
                $args->RequestContainer->Shipment->CourierService = $dp_data['networkCode'];
                $args->RequestContainer->Shipment->Courier = $dp_data['networkName'];
                $args->RequestContainer->Shipment->Service = $dp_data['service'];
            }

            $args->RequestContainer->Pickup = new \stdClass;
            if ($is_manual_mode && $pickup_address_id) {
                // batch id + address id mode
                $args->RequestContainer->Pickup->PickupBatchId = $pickup_batch_id;
                $args->RequestContainer->Pickup->PickupAddressId = $pickup_address_id;
                $args->RequestContainer->Pickup->MolDefaultPickupTime = false;
            } elseif ($is_manual_mode) {
                // sender mode
                $args->RequestContainer->Sender = new \stdClass;
                $args->RequestContainer->Sender->Name = $sender_data['Name'];
                $args->RequestContainer->Sender->CompanyName = $sender_data['CompanyName'];
                $args->RequestContainer->Sender->Address = $sender_data['Address'];
                $args->RequestContainer->Sender->Phone = $sender_data['Phone'];
                $args->RequestContainer->Sender->ZipCode = $sender_data['ZipCode'];
                $args->RequestContainer->Sender->City = $sender_data['City'];
                $args->RequestContainer->Sender->State = $sender_data['State'];
                $args->RequestContainer->Sender->Country = $sender_data['Country'];
                $args->RequestContainer->Sender->Email = $sender_data['Email'];
                $args->RequestContainer->Pickup->PickupData = new \stdClass;
                $args->RequestContainer->Pickup->PickupData->Notes = $pickup_data['Notes'];
                $args->RequestContainer->Pickup->PickupData->Date = $pickup_data['Date'];
                $args->RequestContainer->Pickup->PickupData->PreferredFrom = $pickup_data['PreferredFrom'];
                $args->RequestContainer->Pickup->PickupData->PreferredTo = $pickup_data['PreferredTo'];
                $args->RequestContainer->Pickup->PickupData->AlternativeFrom = $pickup_data['AlternativeFrom'];
                $args->RequestContainer->Pickup->PickupData->AlternativeTo = $pickup_data['AlternativeTo'];
            } else {
                $args->RequestContainer->Pickup->MolDefaultPickupTime = true;
            }

            $this->logVar('CREATE PICKUP SHIPPING ARGS');
            $this->logVar(var_export($args, true));

            $soapResult = $soapClient->__soapCall('ShipmentRequest', [$args]);
            $this->logVar('CREATE PICKUP SHIPPING RESPONSE');
            $this->logVar($soapResult);

            if (isset($soapResult->RequestContainer->Errors)) {
                $this->logVar('CREATE PICKUP SHIPPING ERRORS');
                $this->logVar($soapResult->RequestContainer->Errors);
                //$this->updateShippingErrors($reference, $soapResult->RequestContainer->Errors);

                foreach($soapResult->RequestContainer->Errors as $error) {
                    $errors[] = [
                        'code' => $error->ErrorCode,
                        'desc' => $error->Description
                    ];
                }

                return false;
            }

            if (!isset($soapResult->RequestContainer->Status) || $soapResult->RequestContainer->Status !== 'OK') {
                $this->log(__METHOD__ . ' - STATUS NOT OK');

                $errors[] = [
                    'code' => 'NOT_OK',
                    'desc' => ''
                ];

                return false;
            }

            $result = $soapResult->RequestContainer;
        } catch (\Exception $e) {
            $this->log(__METHOD__ . ' - CREATE PICKUP SHIPPING EXCEPTION');
            $this->log($e->getMessage());

            $errors[] = [
                'code' => 'EXCEPTION',
                'desc' => $e->getMessage()
            ];

            return false;
        }

        $this->logVar(__METHOD__ . ' - CREATE PICKUP SHIPPING RESULT');
        $this->logVar($result);
        return $result;
    }

    public function closePickupShipping($ws, $username, $password, $system, $pickup_batch_id, $preferred_from, $preferred_to, $alternative_from, $alternative_to, $notes, $date, &$errors = null)
    {
        $this->log(__METHOD__ . " - CLOSE PICKUP SHIPPING");

        try {
            $soapClient = new MbeSoapClient($ws, ['encoding' => 'utf-8', 'trace' => 1], $username, $password);

            //WS ARGS
            $args = new \stdClass;
            $args->RequestContainer = new \stdClass;
            $args->RequestContainer->System = $system;
            $args->RequestContainer->Credentials = new \stdClass;
            $args->RequestContainer->Credentials->Username = $username;
            $args->RequestContainer->Credentials->Passphrase = $password;
            $args->RequestContainer->InternalReferenceID = $this->generateRandomString();

            $args->RequestContainer->PickupBatchId = $pickup_batch_id;
            $args->RequestContainer->PickupData = new \stdClass;
            $args->RequestContainer->PickupData->Notes = $notes;
            $args->RequestContainer->PickupData->Date = $date;
            $args->RequestContainer->PickupData->PreferredFrom = $preferred_from;
            $args->RequestContainer->PickupData->PreferredTo = $preferred_to;
            $args->RequestContainer->PickupData->AlternativeFrom = $alternative_from;
            $args->RequestContainer->PickupData->AlternativeTo = $alternative_to;

            $this->logVar('CLOSE PICKUP SHIPPING ARGS');
            $this->logVar(var_export($args, true));

            $soapResult = $soapClient->__soapCall("CourierPickupClosureRequest", array($args));

            $lastResponse = $soapClient->__getLastResponse();
            $this->logVar('CLOSE PICKUP SHIPPING RESPONSE');
            $this->logVar($lastResponse);

            if (isset($soapResult->RequestContainer->Errors)) {
                $this->logVar('CLOSE PICKUP SHIPPING ERRORS');
                $this->logVar($soapResult->RequestContainer->Errors);

                foreach($soapResult->RequestContainer->Errors as $error) {
                    $errors[] = [
                        'code' => $error->ErrorCode,
                        'desc' => $error->Description
                    ];
                }

                return false;
            }

            if (!isset($soapResult->RequestContainer->Status) || $soapResult->RequestContainer->Status != "OK") {
                $this->log(__METHOD__ . " - STATUS NOT OK");

                $errors[] = [
                    'code' => 'NOT_OK',
                    'desc' => ''
                ];

                return false;
            }

            $result = $soapResult->RequestContainer;
        } catch (\Exception $e) {
            $this->log(__METHOD__ . ' - CLOSE PICKUP SHIPPING EXCEPTION');
            $this->log($e->getMessage());

            $errors[] = [
                'code' => 'EXCEPTION',
                'desc' => $e->getMessage()
            ];

            return false;
        }

        $this->logVar(__METHOD__ . ' - CLOSE PICKUP SHIPPING RESULT');
        $this->logVar($result);
        return $result;
    }


    public function getPickupDefaultData($ws, $username, $password, $system)
    {
        $this->log(__METHOD__ . " - GET PICKUP DEFAULT DATA");
        $result = false;

        try {
            $soapClient = new MbeSoapClient($ws, ['encoding' => 'utf-8', 'trace' => 1], $username, $password);

            //WS ARGS
            $args = new \stdClass;
            $args->RequestContainer = new \stdClass;
            $args->RequestContainer->System = $system;
            $args->RequestContainer->Credentials = new \stdClass;
            $args->RequestContainer->Credentials->Username = $username;
            $args->RequestContainer->Credentials->Passphrase = $password;
            $args->RequestContainer->InternalReferenceID = $this->generateRandomString();

            $soapResult = $soapClient->__soapCall("GetPickupDefaultDataRequest", [$args]);
            $this->log(__METHOD__ . " - SOAP RESULT: \n" . print_r($soapResult, true));

            if (isset($soapResult->RequestContainer->Errors)) {
                /* initial PickupDefaultData setup */
                if ($soapResult->RequestContainer->Errors->Error->ErrorCode == 'SPD_007') {
                    return $this->setPickupDefaultData($ws, $username, $password, $system, 'MORNING', '00:00', '00:00', '00:00', '00:00') ? $this->getPickupDefaultData($ws, $username, $password, $system) : false;
                }
                $this->log(__METHOD__ . " - ERRORS: \n" . print_r($soapResult->RequestContainer->Errors, true));
                return false;
            }

            if (!isset($soapResult->RequestContainer->Status) || !$soapResult->RequestContainer->Status == "OK") {
                $this->log(__METHOD__ . " - STATUS NOT OK");
                return false;
            }

            $result = get_object_vars($soapResult->RequestContainer);
        } catch (\Exception $e) {
            $this->log(__METHOD__ . " - EXCEPTION: {$e->getMessage()}");
        }

        $this->log(__METHOD__ . " - RESULT: \n" . print_r($result, true));
        return $result;
    }

    public function setPickupDefaultData($ws, $username, $password, $system, $cutoff, $preferred_from, $preferred_to, $alternative_from, $alternative_to, $notes = '')
    {
        $this->log(__METHOD__ . " - SET PICKUP DEFAULT DATA");
        $result = false;

        try {
            $soapClient = new MbeSoapClient($ws, ['encoding' => 'utf-8', 'trace' => 1], $username, $password);

            //WS ARGS
            $args = new \stdClass;
            $args->RequestContainer = new \stdClass;
            $args->RequestContainer->System = $system;
            $args->RequestContainer->Credentials = new \stdClass;
            $args->RequestContainer->Credentials->Username = $username;
            $args->RequestContainer->Credentials->Passphrase = $password;
            $args->RequestContainer->InternalReferenceID = $this->generateRandomString();
            $args->RequestContainer->Cutoff = $cutoff;
            $args->RequestContainer->Notes = $notes;
            $args->RequestContainer->PreferredFrom = $preferred_from;
            $args->RequestContainer->PreferredTo = $preferred_to;
            $args->RequestContainer->AlternativeFrom = $alternative_from;
            $args->RequestContainer->AlternativeTo = $alternative_to;

            $soapResult = $soapClient->__soapCall("SetPickupDefaultDataRequest", [$args]);
            $this->log(__METHOD__ . " - SOAP RESULT: \n" . print_r($soapResult, true));

            if (isset($soapResult->RequestContainer->Errors)) {
                $this->log(__METHOD__ . " - ERRORS: \n" . print_r($soapResult->RequestContainer->Errors, true));
                return false;
            }

            if (!isset($soapResult->RequestContainer->Status) || !$soapResult->RequestContainer->Status == "OK") {
                $this->log(__METHOD__ . " - STATUS NOT OK");
                return false;
            }

            $result = get_object_vars($soapResult->RequestContainer);
        } catch (\Exception $e) {
            $this->log(__METHOD__ . " - EXCEPTION: {$e->getMessage()}");
        }

        $this->log(__METHOD__ . " - RESULT: \n" . print_r($result, true));
        return $result;
    }

    public function getPickupManifestList(string $ws, string $username, string $password, string $system, array $trackings, array &$errors)
    {
        $this->log(__METHOD__ . " - PICKUP MANIFEST LIST");

        try {
            $soapClient = new MbeSoapClient($ws, ['encoding' => 'utf-8', 'trace' => 1], $username, $password);

            //WS ARGS
            $args = new \stdClass;
            $args->RequestContainer = new \stdClass;
            $args->RequestContainer->System = $system;
            $args->RequestContainer->Credentials = new \stdClass;
            $args->RequestContainer->Credentials->Username = $username;
            $args->RequestContainer->Credentials->Passphrase = $password;
            $args->RequestContainer->InternalReferenceID = $this->generateRandomString();

            $masterTrackingMBE = [];
            foreach ($trackings as $track) {
                $masterTrackingMBE[] = $track;
            }

            $args->RequestContainer->MasterTrackingMBE = $masterTrackingMBE;
            $this->log(__METHOD__ . " - ARGS: \n" . print_r($args, true));

            $soapResult = $soapClient->__soapCall("PickupManifestListRequest", [$args]);
            $this->log(__METHOD__ . " - SOAP RESULT: \n" . print_r($soapResult, true));

            if (isset($soapResult->RequestContainer->Errors)) {
                $this->log(__METHOD__ . " - ERRORS: \n" . print_r($soapResult->RequestContainer->Errors, true));

                foreach($soapResult->RequestContainer->Errors as $error) {
                    $errors[] = [
                        'code' => $error->ErrorCode,
                        'desc' => $error->Description
                    ];
                }

                return false;
            }

            if (!isset($soapResult->RequestContainer->Status) || !$soapResult->RequestContainer->Status == "OK") {
                $this->log(__METHOD__ . " - STATUS NOT OK");

                $errors[] = [
                    'code' => 'NOT_OK',
                    'desc' => ''
                ];

                return false;
            }

            $result = $soapResult->RequestContainer;
        } catch (\Exception $e) {
            $this->log(__METHOD__ . " - EXCEPTION: {$e->getMessage()}");

            $errors[] = [
                'code' => 'EXCEPTION',
                'desc' => $e->getMessage()
            ];

            return false;
        }

        $this->log(__METHOD__ . " - RESULT: \n" . print_r($result, true));
        return $result;
    }
    /* - Third party pickups */

    /* - Delivery Points */
    /**
     * @param $TrackingMbe
     * @return string
     */
    public function shipmentDocumentRequest($ws, $username, $password, $system, $trackingMbe, array &$errors)
    {
        $this->log('SHIPMENT DOCUMENT REQUEST');
        $this->logVar(func_get_args());
        $result = false;

        try {

            $soapClient = new MbeSoapClient($ws, array('encoding' => 'utf-8', 'trace' => 1), $username, $password);
            $internalReferenceID = $this->generateRandomString();

            $args = new \stdClass;
            $args->RequestContainer = new \stdClass;
            $args->RequestContainer->System = $system;

            $args->RequestContainer->Credentials = new \stdClass;
            $args->RequestContainer->Credentials->Username = $username;
            $args->RequestContainer->Credentials->Passphrase = $password;

            $args->RequestContainer->InternalReferenceID = $internalReferenceID;

            $args->RequestContainer->TrackingMBE = $trackingMbe;
            $args->RequestContainer->CourierWaybill = true;
            $this->log(__METHOD__ . " - ARGS: \n" . print_r($args, true));

            $soapResult = $soapClient->__soapCall("ShipmentDocumentsRequest", [$args]);
            $this->log(__METHOD__ . " - SOAP RESULT: \n" . print_r($soapResult, true));

            if (!isset($soapResult->RequestContainer->Status) || $soapResult->RequestContainer->Status != "OK") {
                $this->log(__METHOD__ . " - STATUS NOT OK");

                $errors[] = [
                    'code' => 'NOT_OK',
                    'desc' => ''
                ];

                return false;
            } else {
                if (isset($soapResult->RequestContainer->ShipmentDocuments->ShipmentDocument->Errors)) {
                    $this->log(__METHOD__ . " - ERRORS: \n" . print_r($soapResult->RequestContainer->ShipmentDocuments->ShipmentDocument, true));

                    foreach($soapResult->RequestContainer->ShipmentDocuments->ShipmentDocument->Errors as $error) {
                        $errors[] = [
                            'code' => $error->ErrorCode,
                            'desc' => $error->Description
                        ];
                    }

                    return false;
                }
            }

            $result = $soapResult->RequestContainer;

        } catch (\Exception $e) {
            $this->log('SHIPMENT DOCUMENT REQUEST EXCEPTION');
            $this->log($e->getMessage());

            $errors[] = [
                'code' => 'EXCEPTION',
                'desc' => $e->getMessage()
            ];
        }
        $this->logVar('SHIPMENT DOCUMENT REQUEST RESULT');
        $this->logVar($result);

        return $result;
    }
    /* - Delivery Points */
}
