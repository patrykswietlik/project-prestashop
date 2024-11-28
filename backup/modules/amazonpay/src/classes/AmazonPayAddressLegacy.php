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

class AmazonPayAddressLegacy extends AmazonPayAddress
{

    /**
     * @param false $null_values
     * @param bool $auto_date
     * @return mixed
     */
    public function save($null_values = false, $auto_date = true)
    {
        return parent::save($null_values, $auto_date);
    }

    /**
     * Validate required fields.
     *
     * @param bool $htmlentities
     *
     * @return array
     * @throws PrestaShopException
     */
    public function validateFieldsRequiredDatabase($htmlentities = true)
    {
        $this->cacheFieldsRequiredDatabase();
        $errors = array();
        $required_fields = (isset(self::$fieldsRequiredDatabase['Address'])) ? self::$fieldsRequiredDatabase['Address'] : array();

        foreach ($this->def['fields'] as $field => $data) {
            if (!in_array($field, $required_fields)) {
                continue;
            }

            if (!method_exists('Validate', $data['validate'])) {
                throw new PrestaShopException('Validation function not found. '.$data['validate']);
            }

            $value = Tools::getValue($field);

            if (empty($value)) {
                $errors[$field] = sprintf(Tools::displayError('The field %s is required.'), self::displayFieldName($field, 'Address', $htmlentities));
            }
        }

        return $errors;
    }

    /**
     * @param false $all
     * @return array|bool|mysqli_result|PDOStatement|resource|null
     */
    public function getFieldsRequiredDatabase($all = false)
    {
        return Db::getInstance()->executeS('
		SELECT id_required_field, object_name, field_name
		FROM '._DB_PREFIX_.'required_field
		'.(!$all ? 'WHERE object_name = \'Address\'' : ''));
    }

    /**
     * Validate a single field
     *
     * @since 1.5.0.1
     * @param string   $field        Field name
     * @param mixed    $value        Field value
     * @param int|null $id_lang      Language ID
     * @param array    $skip         Array of fields to skip.
     * @param bool     $human_errors If true, uses more descriptive, translatable error strings.
     *
     * @return true|string True or error message string.
     * @throws PrestaShopException
     */
    public function validateField($field, $value, $id_lang = null, $skip = array(), $human_errors = false)
    {
        static $ps_lang_default = null;
        static $ps_allow_html_iframe = null;

        if ($ps_lang_default === null) {
            $ps_lang_default = Configuration::get('PS_LANG_DEFAULT');
        }

        if ($ps_allow_html_iframe === null) {
            $ps_allow_html_iframe = (int)Configuration::get('PS_ALLOW_HTML_IFRAME');
        }

        $this->cacheFieldsRequiredDatabase();
        $data = $this->def['fields'][$field];

        // Check if field is required
        $required_fields = (isset(self::$fieldsRequiredDatabase['Address'])) ? self::$fieldsRequiredDatabase['Address'] : array();
        if (!$id_lang || $id_lang == $ps_lang_default) {
            if (!in_array('required', $skip) && (!empty($data['required']) || in_array($field, $required_fields))) {
                if (Tools::isEmpty($value)) {
                    if ($human_errors) {
                        return sprintf(Tools::displayError('The %s field is required.'), $this->displayFieldName($field, 'Address'));
                    } else {
                        return 'Property '.'Address'.'->'.$field.' is empty';
                    }
                }
            }
        }

        // Default value
        if (!$value && !empty($data['default'])) {
            $value = $data['default'];
            $this->$field = $value;
        }

        // Check field values
        if (!in_array('values', $skip) && !empty($data['values']) && is_array($data['values']) && !in_array($value, $data['values'])) {
            return 'Property '.'Address'.'->'.$field.' has bad value (allowed values are: '.implode(', ', $data['values']).')';
        }

        // Check field size
        if (!in_array('size', $skip) && !empty($data['size'])) {
            $size = $data['size'];
            if (!is_array($data['size'])) {
                $size = array('min' => 0, 'max' => $data['size']);
            }

            $length = Tools::strlen($value);
            if ($length < $size['min'] || $length > $size['max']) {
                if ($human_errors) {
                    if (isset($data['lang']) && $data['lang']) {
                        $language = new Language((int)$id_lang);
                        return sprintf(Tools::displayError('The field %1$s (%2$s) is too long (%3$d chars max, html chars including).'), $this->displayFieldName($field, 'Address'), $language->name, $size['max']);
                    } else {
                        return sprintf(Tools::displayError('The %1$s field is too long (%2$d chars max).'), $this->displayFieldName($field, 'Address'), $size['max']);
                    }
                } else {
                    return 'Property '.'Address'.'->'.$field.' length ('.$length.') must be between '.$size['min'].' and '.$size['max'];
                }
            }
        }

        // Check field validator
        if (!in_array('validate', $skip) && !empty($data['validate'])) {
            if (!method_exists('Validate', $data['validate'])) {
                throw new PrestaShopException('Validation function not found. '.$data['validate']);
            }

            if (!empty($value)) {
                $res = true;
                if (Tools::strtolower($data['validate']) == 'iscleanhtml') {
                    if (!call_user_func(array('Validate', $data['validate']), $value, $ps_allow_html_iframe)) {
                        $res = false;
                    }
                } else {
                    if (!call_user_func(array('Validate', $data['validate']), $value)) {
                        $res = false;
                    }
                }
                if (!$res) {
                    if ($human_errors) {
                        return sprintf(Tools::displayError('The %s field is invalid.'), $this->displayFieldName($field, 'Address'));
                    } else {
                        return 'Property '.'Address'.'->'.$field.' is not valid';
                    }
                }
            }
        }

        return true;
    }


    /**
     * Checks if object field values are valid before database interaction
     *
     * @param bool $die
     * @param bool $error_return
     *
     * @return bool|string True, false or error message.
     * @throws PrestaShopException
     */
    public function validateFields($die = true, $error_return = false)
    {
        foreach ($this->def['fields'] as $field => $data) {
            if (!empty($data['lang'])) {
                continue;
            }

            if (is_array($this->update_fields) && empty($this->update_fields[$field]) && isset($this->def['fields'][$field]['shop']) && $this->def['fields'][$field]['shop']) {
                continue;
            }

            $message = $this->validateField($field, $this->$field);
            if ($message !== true) {
                if ($die) {
                    throw new PrestaShopException($field);
                }
                return $error_return ? $message : false;
            }
        }

        return true;
    }
}
