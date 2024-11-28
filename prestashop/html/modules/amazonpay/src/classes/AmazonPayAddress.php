<?php
/**
 * 2007-2023 patworx.de
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade AmazonPay to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    patworx multimedia GmbH <service@patworx.de>
 *  @copyright 2007-2023 patworx multimedia GmbH
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class AmazonPayAddress extends Address
{

    public static $validation_errors = array();

    /**
     * Parse address data from Amazon Pay array and assign to Address-object
     *
     * @param array $arr
     */
    public function processFromArray($arr)
    {
        $arr = AmazonPay::parseAddressdata($arr);
        $phone = '0000000000';
        if ($arr['phoneNumber'] != '' && Validate::isPhoneNumber($arr['phoneNumber'])) {
            $phone = $arr['phoneNumber'];
        }
        $names = AmazonPayAddress::prepName($arr['name']);

        $this->company = '';
        $this->address1 = '';
        $this->address2 = '';
        $this->id_customer = (int)Context::getContext()->cookie->id_customer;
        $this->alias = 'Amazon Pay';
        $this->lastname = $names[1];
        $this->firstname = $names[0];

        $s_company_name = '';
        if ($arr['addressLine3'] != '') {
            $s_street = Tools::substr($arr['addressLine3'], 0, Tools::strrpos($arr['addressLine3'], ' '));
            $s_street_nr = Tools::substr($arr['addressLine3'], Tools::strrpos($arr['addressLine3'], ' ') + 1);
            $s_company_name = trim($arr['addressLine1'] . $arr['addressLine2']);
        } else {
            if ($arr['addressLine2'] != '') {
                $s_street = Tools::substr($arr['addressLine2'], 0, Tools::strrpos($arr['addressLine2'], ' '));
                $s_street_nr = Tools::substr($arr['addressLine2'], Tools::strrpos($arr['addressLine2'], ' ') + 1);
                $s_company_name = trim($arr['addressLine1']);
            } else {
                $s_street = Tools::substr($arr['addressLine1'], 0, Tools::strrpos($arr['addressLine1'], ' '));
                $s_street_nr = Tools::substr($arr['addressLine1'], Tools::strrpos($arr['addressLine1'], ' ') + 1);
            }
        }
        if (in_array(Tools::strtolower($arr['countryCode']), array('de', 'at', 'uk'))) {
            if ($s_company_name != '') {
                $this->company = $s_company_name;
            }
            $this->address1 = (string)$s_street . ' ' . (string)$s_street_nr;
        } else {
            $this->address1 = $arr['addressLine1'];
            if (trim($this->address1) == '') {
                $this->address1 = $arr['addressLine2'];
            } else {
                if (trim($arr['addressLine2']) != '') {
                    $this->address2 = $arr['addressLine2'];
                }
            }
            if (trim($arr['addressLine3']) != '') {
                $this->address2 .= ' ' . $arr['addressLine3'];
            }
        }
        $this->postcode = $arr['postalCode'];
        $this->id_country = Country::getByIso($arr['countryCode']);
        if ($phone != '') {
            $this->phone = $phone;
            $this->phone_mobile = $phone;
        }
        $this->id_state = 0;
        if ($arr['stateOrRegion'] != '') {
            $state_id = State::getIdByIso($arr['stateOrRegion'], Country::getByIso($arr['countryCode']));
            if (!$state_id) {
                $state_id = self::getStateIdByName($arr['stateOrRegion']);
            }
            if (!$state_id) {
                $state_id = AmazonPayPostalCodesHelper::getIdByPostalCodeAndCountry($arr['postalCode'], $arr['countryCode']);
            }
            if (!$state_id) {
                $state_id = AmazonPayPostalCodesHelper::getIdByFuzzyName($arr['stateOrRegion']);
            }
            if ($state_id && self::stateBelongsToCountry($state_id, $this->id_country)) {
                $this->id_state = $state_id;
            }
        }
        $this->prepareAddressLines();
        $this->city = $arr['city'];
        $this->phone = $phone;
    }

    /**
     * @param $orderref
     * @param $amazon_address
     * @param $id_customer
     * @param bool $boolean
     * @return AmazonPayAddress|bool
     */
    public static function findByAmazonOrderReferenceIdOrNew($orderref, $amazon_address, $id_customer, $boolean = false)
    {
        $amazon_hash = self::createHash($amazon_address, $id_customer);
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
			SELECT a.`id_address`
			FROM `' . _DB_PREFIX_ . 'address` a
            JOIN `' . _DB_PREFIX_ . 'amazonpay_address_reference` aa ON aa.id_address = a.id_address
			WHERE
			    (aa.`amazon_order_reference_id` = "' . pSQL($orderref) . '"
		         AND a.id_customer = \'' . (int)$id_customer . '\'
		        )' .
            ($amazon_hash != '' ? ' OR aa.`amazon_hash` = "' . pSQL($amazon_hash) . '"' : false));
        if ($boolean) {
            return $result && isset($result['id_address']) ? true : false;
        } else {
            if (AmazonPay::isPrestaShop16Static()) {
                return $result && isset($result['id_address']) ? new AmazonPayAddressLegacy($result['id_address']) : new AmazonPayAddressLegacy();
            } else {
                return $result && isset($result['id_address']) ? new self($result['id_address']) : new self();
            }
        }
    }

    /**
     * @param Address $address
     * @param $orderref
     * @param $id_customer
     * @param bool $amazon_address
     * @throws PrestaShopDatabaseException
     */
    public static function saveAddressAmazonReference(Address $address, $orderref, $id_customer, $amazon_address = false)
    {
        $amazon_hash = self::createHash($amazon_address, $id_customer);
        if (self::findByAmazonOrderReferenceIdOrNew($orderref, $amazon_address, $id_customer, true)) {
            Db::getInstance(_PS_USE_SQL_SLAVE_)->update('amazonpay_address_reference', array(
                'amazon_order_reference_id' => pSQL($orderref),
                'amazon_hash' => pSQL($amazon_hash)
            ), 'id_address = \'' . (int)$address->id . '\'');
        } else {
            Db::getInstance(_PS_USE_SQL_SLAVE_)->insert('amazonpay_address_reference', array(
                'id_address' => pSQL((int)$address->id),
                'amazon_order_reference_id' => pSQL($orderref),
                'amazon_hash' => pSQL($amazon_hash)
            ));
        }
    }

    /**
     * @param $amazon_address
     * @param $id_customer
     * @return string
     */
    public static function createHash($amazon_address, $id_customer)
    {
        $amazon_hash = '';
        if ($amazon_address && is_array($amazon_address)) {
            $amazon_hash .= (string)$amazon_address['countryCode'];
            $amazon_hash .= (string)$amazon_address['city'];
            $amazon_hash .= (string)$amazon_address['postalCode'];
            $amazon_hash .= (string)$amazon_address['stateOrRegion'];
            $amazon_hash .= (string)$amazon_address['name'];
            $amazon_hash .= (string)$amazon_address['phoneNumber'];
            $amazon_hash .= (string)$amazon_address['addressLine1'];
            $amazon_hash .= (string)$amazon_address['addressLine2'];
            $amazon_hash .= (string)$amazon_address['addressLine3'];
            $amazon_hash = $id_customer . '-' . md5($amazon_hash);
        }
        return $amazon_hash;
    }

    /**
     * @return $this
     */
    public function prepareAddressLines()
    {
        $this->address1 = Tools::str_replace_once('_', '-', $this->address1);
        $this->address2 = Tools::str_replace_once('_', '-', $this->address2);
        return $this;
    }

    /**
     * @param $id_state
     * @param $id_country
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function stateBelongsToCountry($id_state, $id_country)
    {
        $state = new State((int)$id_state);
        return $state->id_country == $id_country;
    }

    /**
     * @param $state
     * @param null $id_country
     * @return false|int
     */
    public static function getStateIdByName($state, $id_country = null)
    {
        if (empty($state)) {
            return false;
        }

        $result = (int) Db::getInstance()->getValue('
            SELECT `id_state`
            FROM `' . _DB_PREFIX_ . 'state`
            WHERE `name` = \'' . pSQL($state) . '\'
            ' . ($id_country ? 'AND `id_country` = ' . (int) $id_country : ''));

        return $result;
    }

    /**
     * @param $str
     * @return array
     */
    public static function prepName($str)
    {
        $names_array = explode(' ', $str, 2);
        if (!isset($names_array[1])) {
            $names_array[1] = '';
        }
        $regex = '/[^a-zA-ZäöüÄÖÜßÂâÀÁáàÇçÈÉËëéèÎîÏïÙÛùúòóûêôíÍŸÿªñÑ\s]/u';
        $names_array[0] = preg_replace($regex, '', $names_array[0]);
        $names_array[1] = preg_replace($regex, '', $names_array[1]);

        $names_array[0] = preg_replace('/(\d+)/', ' ', $names_array[0]);
        $names_array[0] = trim(preg_replace('/ {2,}/', ' ', $names_array[0]));

        $names_array[1] = preg_replace('/(\d+)/', ' ', $names_array[1]);
        $names_array[1] = trim(preg_replace('/ {2,}/', ' ', $names_array[1]));

        if (trim($names_array[1]) == '') {
            $splitted_names_array = explode(' ', $names_array[0], 2);
            $names_array[0] = $splitted_names_array[0];
            if (!isset($splitted_names_array[1]) || trim($splitted_names_array[1]) == '') {
                $names_array[1] = $names_array[0];
            } else {
                $names_array[1] = $splitted_names_array[1];
            }
        }
        return $names_array;
    }

    /**
     * @param $address
     * @param array|bool $additional_data
     * @return array
     */
    public static function fetchInvalidInput($address, $additional_data = false)
    {
        if ((int)$address->id > 0) {
            if (class_exists('CustomerAddress')) {
                $address = new CustomerAddress($address->id);
            } else {
                $address = new Address($address->id);
            }
        }
        $fields_to_set = array();
        foreach (Address::getFieldsValidate() as $field_to_validate => $validation_rule) {
            $check_value = $address->$field_to_validate;
            $validation = $address->validateField($field_to_validate, $check_value, null, array(), true);
            if ($validation !== true) {
                $fields_to_set[] = $field_to_validate;
                self::$validation_errors[] = $validation;
            }
        }
        if (is_array($additional_data)) {
            foreach ($additional_data as $field => $value) {
                if (!in_array($field, $fields_to_set)) {
                    $fields_to_set[] = $field;
                }
            }
        }
        if ((int)$address->id_country > 0) {
            $country = new Country((int)$address->id_country);
            if ($country->need_identification_number) {
                $validation = $address->validateField('dni', $address->dni, null, array(), true);
                if ($validation !== true) {
                    $fields_to_set[] = 'dni';
                    self::$validation_errors[] = $validation;
                }
            }
        }
        return $fields_to_set;
    }

    /**
     * @param Address $address
     * @param array $additional_data
     * @return Address
     */
    public static function addAdditionalValues(Address $address, array $additional_data)
    {
        foreach ($additional_data as $field => $value) {
            if (!($field == 'id_state' && (int)$value < 0) && trim($value) != '') {
                $address->$field = pSQL($value);
            }
        }
        return $address;
    }

    /**
     * @return false|string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function dataForAbp()
    {
        if (!isset(Context::getContext()->cart->id_address_delivery)) {
            return;
        }
        $id_address_delivery = Context::getContext()->cart->id_address_delivery;
        $address = new self((int)$id_address_delivery);
        if ($address->id_state > 0) {
            $state = new State((int)$address->id_state);
        }
        $phone = '0000 0000';
        if (trim($address->phone) != '') {
            $phone = $address->phone;
        } elseif (trim($address->phone_mobile) != '') {
            $phone = $address->phone_mobile;
        }
        $jsonData = [
            'name' => $address->firstname . ' ' . $address->lastname,
            'addressLine1' => $address->address1,
            'addressLine2' => $address->address2,
            'city' => $address->city,
            'postalCode' => $address->postcode,
            'countryCode' => Country::getIsoById((int)$address->id_country),
            'stateOrRegion' => $address->id_state > 0 ? $state->iso_code : '',
            'phoneNumber' => $phone
        ];
        if (in_array($jsonData['countryCode'], ['UK', 'GB', 'SG', 'AE', 'MX' ])) {
            unset($jsonData['stateOrRegion']);
        }
        return $jsonData;
    }
}
