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

class UpsUapHelper {

	const MBE_UPS_ACCESS_LICENSE_NUMBER = "6CB804D87F868625";
	const MBE_UPS_USER_ID = "ITMBE0001Z";
	const MBE_UPS_PASSWORD = "1tMB3#ooo1";
	const MBE_UPS_URI_TEST = "https://wwwcie.ups.com/ups.app/xml/Locator";
	const MBE_UPS_URI_PROD = "https://onlinetools.ups.com/ups.app/xml/Locator";
	const MBE_UPS_DEFAULT_MAXIMUM_LIST_SIZE = "10";
	const MBE_UPS_DEFAULT_SEARCH_RADIUS = "20";
	const MBE_UPS_DEFAULT_UNIT_OF_MEASUREMENT ="KM";
	const MBE_UPS_OPTION_DROP_LOCATIONS_AND_WILL_CALL_LOCATIONS = 1;
	const MBE_UPS_OPTION_ALL_AVAILABLE_ADDITIONAL_SERVICES = 8;
	const MBE_UPS_OPTION_ALL_AVAILABLE_PROGRAM_TYPES = 16;
	const MBE_UPS_OPTION_ALL_AVAILABLE_ADDITIONAL_SERVICES_AND_PROGRAM_TYPES = 24;
	const MBE_UPS_OPTION_ALL_AVAILABLE_RETAIL_LOCATIONS = 32;
	const MBE_UPS_OPTION_ALL_AVAILABLE_RETAIL_LOCATIONS_AND_ADDITIONAL_SERVICES = 40;
	const MBE_UPS_OPTION_ALL_AVAILABLE_RETAIL_LOCATIONS_AND_PROGRAM_TYPES = 48;
	const MBE_UPS_OPTION_ALL_AVAILABLE_RETAIL_LOCATIONS_AND_ADDITIONAL_SERVICES_AND_PROGRAM_TYPES = 56;
	const MBE_UPS_OPTION_UPS_ACCESS_POINT_LOCATIONS = 64;

	public static function getUapList($filter, $simplyfied = true, $userId = null, $password = null, $accessLicenseNumber = null, $test = false)
	{
		$accessLicenseNumber = $accessLicenseNumber?:self::MBE_UPS_ACCESS_LICENSE_NUMBER;
		$userId = $userId?:self::MBE_UPS_USER_ID;
		$password = $password?:self::MBE_UPS_PASSWORD;

		$endpointurl = self::MBE_UPS_URI_PROD;
		if ($test) {
			$endpointurl = self::MBE_UPS_URI_TEST;
		}

		try {
			$accessRequestXML = new \SimpleXMLElement("<AccessRequest></AccessRequest>");
			$locatorRequestXML = new \SimpleXMLElement("<LocatorRequest ></LocatorRequest >");

			$accessRequestXML->addChild("AccessLicenseNumber", $accessLicenseNumber);
			$accessRequestXML->addChild("UserId", $userId);
			$accessRequestXML->addChild("Password", $password);

			$request = $locatorRequestXML->addChild('Request');
			$request->addChild("RequestAction", "Locator");
			$request->addChild("RequestOption",self::MBE_UPS_OPTION_DROP_LOCATIONS_AND_WILL_CALL_LOCATIONS);

			$translate = $locatorRequestXML->addChild('Translate');
			$translate->addChild("LanguageCode", $filter["language"]?:'EN');

//			if(!empty($filter["LocationID"])) {
//				$locatorRequestXML->addChild ( "LocationID", $filter["LocationID"]);
//			} else {
				$originAddress    = $locatorRequestXML->addChild( 'OriginAddress' );
				$addressKeyFormat = $originAddress->addChild( 'AddressKeyFormat' );
				$addressKeyFormat->addChild( "AddressLine", $filter["AddressLine1"]?:'' );
				$addressKeyFormat->addChild( "PostcodePrimaryLow", $filter["PostcodePrimaryLow"]?:'');
				$addressKeyFormat->addChild( "PoliticalDivision2", $filter["PoliticalDivision2"]?:'' );
				$addressKeyFormat->addChild( "PoliticalDivision1", $filter["PoliticalDivision1"]?:'' );
				$addressKeyFormat->addChild( "CountryCode", $filter["CountryCode"]?:'' );


				$unitOfMeasurement = $locatorRequestXML->addChild( 'UnitOfMeasurement' );
				$unitOfMeasurement->addChild( "Code",self::MBE_UPS_DEFAULT_UNIT_OF_MEASUREMENT );

				$LocationSearchCriteria = $locatorRequestXML->addChild( 'LocationSearchCriteria' );
				$LocationSearchCriteria->addChild( "MaximumListSize", $filter["MaximumListSize"] ?: self::MBE_UPS_DEFAULT_MAXIMUM_LIST_SIZE );
				$LocationSearchCriteria->addChild( "SearchRadius", $filter["SearchRadius"] ?: self::MBE_UPS_DEFAULT_SEARCH_RADIUS );

				$SortCriteria = $locatorRequestXML->addChild( 'SortCriteria' );
				$SortCriteria->addChild( 'SortType', "01" );
//			}

			$requestXML = $accessRequestXML->asXML() . $locatorRequestXML->asXML();

			$form = array(
				'http' => array(
					'method' => 'POST',
					'header' => 'Content-type: application/x-www-form-urlencoded',
					'content' => "$requestXML"
				)
			);

			$request = stream_context_create($form);
			$browser = fopen($endpointurl, 'rb', false, $request);
			if (!$browser) {
				throw new Exception("Connection failed.");
			}

			// get response
			$response = stream_get_contents($browser);
			fclose($browser);

			$upsUapList = null;

			if ($response == false) {
				throw new Exception("Bad data.");
			} else {
				$xmlResponse = simplexml_load_string($response);
				unset($response);
				unset($xmlResponse->Response);
				$xmlResponse = json_decode(json_encode($xmlResponse), true);
				if ($simplyfied) {
					$dropLocation = $xmlResponse['SearchResults']['DropLocation'];
					if(isset($dropLocation['LocationID'])) {
                        if(array_key_exists("AccessPointInformation", $dropLocation) && array_key_exists("AddressKeyFormat", $dropLocation) && array_key_exists("Distance", $dropLocation))
						    $upsUapList[] = $dropLocation['AccessPointInformation'] + $dropLocation['AddressKeyFormat'] + ['LocationID' => $dropLocation['LocationID']] + ['Distance' => $dropLocation['Distance']['Value'].' '.$dropLocation['Distance']['UnitOfMeasurement']['Code']]+ ['StandardHoursOfOperation' => $dropLocation['StandardHoursOfOperation']];
					} else {
						foreach ( $dropLocation as $item ) {
                            if(array_key_exists("AccessPointInformation", $item) && array_key_exists("AddressKeyFormat", $item) && array_key_exists("Distance", $item))
							    $upsUapList[] =  $item['AccessPointInformation'] + $item['AddressKeyFormat'] + [ 'LocationID' => $item['LocationID'] ] + ['Distance' => $item['Distance']['Value'].' '.$item['Distance']['UnitOfMeasurement']['Code']] + ['StandardHoursOfOperation' => $item['StandardHoursOfOperation']];
						}
					}
					return $upsUapList;
				}
				return $xmlResponse;
			}
		} catch (Exception $ex) {
			return $ex;
		}
	}

}
