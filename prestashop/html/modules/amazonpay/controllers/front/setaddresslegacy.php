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

if (!defined('_PS_VERSION_')) {
    exit;
}

class AmazonpaySetaddresslegacyModuleFrontController extends AddressControllerCore
{

    public $is_amazon_pay = true;
    public $internal_id_address = 0;

    /**
     * Assign template vars related to page content.
     *
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        if (!$this->ajax && $this->should_redirect) {
            $amazonpay = new Amazonpay();
            if ($amazonpay->isOnePageCheckoutPSInstalled()) {
                Tools::redirect(
                    $this->context->link->getPageLink('order-opc', true, $this->context->language->id, '&checkout=1')
                );
            } elseif ($amazonpay->isTheCheckoutInstalled()) {
                Tools::redirect(
                    $this->context->link->getPageLink('order', true, $this->context->language->id)
                );
            }
            Tools::redirect(
                $this->context->link->getPageLink('order', true, $this->context->language->id, '&step=2')
            );
        }
        parent::initContent();
    }

    public function setMedia()
    {
        $amazonpay = new Amazonpay();
        $this->context->controller->addJquery();
        $this->context->controller->addJS($amazonpay->getPathUri() . 'views/js/amazonpay_16_address.js');
        parent::setMedia();
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        $amazonPayCheckoutSession = new AmazonPayCheckoutSession(false);
        if ($amazonPayCheckoutSession->checkStatus()) {
            $coData = $amazonPayCheckoutSession->assocReturn();
        } else {
            // @todo: add error handling
            die('NOT AUTHORIZED');
        }
        $this->context->smarty->assign('editing', false);
        if (Tools::isSubmit('submitAddress')) {
            $fetchAddress = $this->processSubmitAddressLegacy();

            if (!$this->errors && $fetchAddress) {
                $this->should_redirect = true;

                AmazonPayAddress::saveAddressAmazonReference(
                    $fetchAddress,
                    Context::getContext()->cookie->amazon_pay_checkout_session_id,
                    $this->context->customer->id,
                    $coData['shippingAddress']
                );


                if (AmazonPayHelper::jumpInvoiceAddress()) {
                    $address_invoice = new Address($this->context->cart->id_address_invoice);
                } else {
                    $address_invoice = AmazonPayAddress::findByAmazonOrderReferenceIdOrNew(
                        Context::getContext()->cookie->amazon_pay_checkout_session_id . '-invoice',
                        $coData['billingAddress'],
                        $this->context->customer->id,
                        false
                    );
                    $address_invoice->processFromArray($coData['billingAddress']);

                    try {
                        $address_invoice->save();
                        Hook::exec('actionAmazonPayAddressSave', ['type' => 'invoice', 'address' => $address_invoice]);
                    } catch (\Exception $e) {
                        $fields_to_set = AmazonPayAddressLegacy::fetchInvalidInput($address_invoice);
                        foreach ($fields_to_set as $field_to_set) {
                            $address_invoice->$field_to_set = isset($fetchAddress->$field_to_set) ? $fetchAddress->$field_to_set : '';
                        }
                        try {
                            $address_invoice->save();
                            Hook::exec('actionAmazonPayAddressSave', ['type' => 'invoice', 'address' => $address_invoice]);
                        } catch (\Exception $e) {
                            $fieldtoset = $e->getMessage();
                            $address_invoice->$fieldtoset = isset($fetchAddress->$fieldtoset) ? $fetchAddress->$fieldtoset : '';
                        }
                    }

                    $fields_to_set = AmazonPayAddressLegacy::fetchInvalidInput($address_invoice);
                    foreach ($fields_to_set as $field_to_set) {
                        $address_invoice->$field_to_set = isset($fetchAddress->$field_to_set) ? $fetchAddress->$field_to_set : '';
                    }
                    $address_invoice->save();
                    Hook::exec('actionAmazonPayAddressSave', ['type' => 'invoice', 'address' => $address_invoice]);
                }

                AmazonPayAddress::saveAddressAmazonReference(
                    $address_invoice,
                    Context::getContext()->cookie->amazon_pay_checkout_session_id . '-invoice',
                    $this->context->customer->id,
                    $coData['billingAddress']
                );

                $this->context->cart->id_address_delivery = $fetchAddress->id;
                $this->context->cart->id_address_invoice = $address_invoice->id;
                $sql = 'UPDATE `' . _DB_PREFIX_ . 'cart_product`
                           SET `id_address_delivery` = ' . (int) $fetchAddress->id . '
                         WHERE `id_cart` = ' . (int) $this->context->cart->id . '';
                Db::getInstance()->execute($sql);

                $sql = 'UPDATE `' . _DB_PREFIX_ . 'customization`
                           SET `id_address_delivery` = ' . (int) $fetchAddress->id . '
                         WHERE `id_cart` = ' . (int) $this->context->cart->id . '';
                Db::getInstance()->execute($sql);
                $this->context->cart->save();

                CartRule::autoRemoveFromCart($this->context);
                CartRule::autoAddToCart($this->context);
            }
        } else {
            $address_delivery = AmazonPayAddress::findByAmazonOrderReferenceIdOrNew(
                Context::getContext()->cookie->amazon_pay_checkout_session_id,
                $coData['shippingAddress'],
                $this->context->customer->id,
                false
            );
            $address_delivery->processFromArray($coData['shippingAddress']);
            $this->_address = $address_delivery;
            $this->internal_id_address = $address_delivery->id;
            $this->processSubmitAddressLegacy(false, $this->_address);
        }
    }

    /**
     * @param $var
     * @param $frompost
     * @return false|mixed
     */
    protected function getValueFromPostOrAddress($var, $frompost)
    {
        if ($frompost) {
            return Tools::getValue($var);
        } else {
            if (isset($this->_address)) {
                if (isset($this->_address->$var)) {
                    return $this->_address->$var;
                }
            }
        }
    }

    /**
     * Process changes on an address
     */
    protected function processSubmitAddressLegacy($validateController = true, $address = false)
    {
        if (!$address) {
            $address = new AmazonPayAddressLegacy();
        }
        if ($validateController) {
            $this->errors = $address->validateController();
        }
        $address->id_customer = (int)$this->context->customer->id;

        // Check page token
        if ($validateController) {
            if ($this->context->customer->isLogged() && !$this->isTokenValid()) {
                $this->errors[] = Tools::displayError('Invalid token.');
            }
        }

        // Check phone
        if (Configuration::get('PS_ONE_PHONE_AT_LEAST') && !$this->getValueFromPostOrAddress('phone', $validateController) && !$this->getValueFromPostOrAddress('phone_mobile', $validateController)) {
            $this->errors[] = Tools::displayError('You must register at least one phone number.');
        }
        if ($address->id_country) {
            // Check country
            if (!($country = new Country($address->id_country)) || !Validate::isLoadedObject($country)) {
                throw new PrestaShopException('Country cannot be loaded with address->id_country');
            }

            if ((int)$country->contains_states && !(int)$address->id_state) {
                $this->errors[] = Tools::displayError('This country requires you to chose a State.');
            }

            if (!$country->active) {
                $this->errors[] = Tools::displayError('This country is not active.');
            }

            $postcode = $this->getValueFromPostOrAddress('postcode', $validateController);
            /* Check zip code format */
            if ($country->zip_code_format && !$country->checkZipCode($postcode)) {
                $this->errors[] = sprintf(Tools::displayError('The Zip/Postal code you\'ve entered is invalid. It must follow this format: %s'), str_replace('C', $country->iso_code, str_replace('N', '0', str_replace('L', 'A', $country->zip_code_format))));
            } elseif (empty($postcode) && $country->need_zip_code) {
                $this->errors[] = Tools::displayError('A Zip/Postal code is required.');
            } elseif ($postcode && !Validate::isPostCode($postcode)) {
                $this->errors[] = Tools::displayError('The Zip/Postal code is invalid.');
            }

            // Check country DNI
            if ($country->isNeedDni() && (!$this->getValueFromPostOrAddress('dni', $validateController) || !Validate::isDniLite($this->getValueFromPostOrAddress('dni', $this->getValueFromPostOrAddress)))) {
                $this->errors[] = Tools::displayError('The identification number is incorrect or has already been used.');
            } elseif (!$country->isNeedDni()) {
                $address->dni = null;
            }
        }
        // Check if the alias exists
        if (!$this->context->customer->is_guest && Tools::getValue('alias') != '' && (int)$this->context->customer->id > 0) {
            $id_address = Tools::getValue('id_address');
            if (Configuration::get('PS_ORDER_PROCESS_TYPE') && (int)Tools::getValue('opc_id_address_'.Tools::getValue('type')) > 0) {
                $id_address = Tools::getValue('opc_id_address_'.Tools::getValue('type'));
            }

            if (Address::aliasExist(Tools::getValue('alias'), (int)$id_address, (int)$this->context->customer->id)) {
                //$address->alias = 'Amazon Pay ' . Tools::passwdGen(5, 'NUMERIC');
            }
        }

        // Check the requires fields which are settings in the BO
        $this->errors = array_merge($this->errors, $address->validateFieldsRequiredDatabase());

        // Don't continue this process if we have errors !
        if ($this->errors && !$this->ajax) {
            return;
        }

        // If we edit this address, delete old address and create a new one
        if (Validate::isLoadedObject($this->_address)) {
            if (Validate::isLoadedObject($country) && !$country->contains_states) {
                $address->id_state = 0;
            }
            $address_old = $this->_address;
            if (Customer::customerHasAddress($this->context->customer->id, (int)$address_old->id)) {
                if ($address_old->isUsed()) {
                    try {
                        $address_old->delete();
                    } catch (\Exception $e) {
                    }
                } else {
                    $address->id = (int)$address_old->id;
                    $address->date_add = $address_old->date_add;
                }
            }
        }

        if ($this->ajax && Configuration::get('PS_ORDER_PROCESS_TYPE')) {
            $this->errors = array_unique(array_merge($this->errors, $address->validateController()));
            if (count($this->errors)) {
                $return = array(
                    'hasError' => (bool)$this->errors,
                    'errors' => $this->errors
                );
                $this->ajaxDie(json_encode($return));
            }
        }

        // Save address
        if ($result = $address->save()) {
            Hook::exec('actionAmazonPayAddressSave', ['type' => 'shipping', 'address' => $address]);
            // Update id address of the current cart if necessary
            if (isset($address_old) && $address_old->isUsed()) {
                $this->context->cart->updateAddressId($address_old->id, $address->id);
            } else { // Update cart address
                $this->context->cart->autosetProductAddress();
            }

            if ((bool)Tools::getValue('select_address', false) == true || (Tools::getValue('type') == 'invoice' && Configuration::get('PS_ORDER_PROCESS_TYPE'))) {
                $this->context->cart->id_address_invoice = (int)$address->id;
            } elseif (Configuration::get('PS_ORDER_PROCESS_TYPE')) {
                $this->context->cart->id_address_invoice = (int)$this->context->cart->id_address_delivery;
            }
            $this->context->cart->update();

            if ($this->ajax) {
                $return = array(
                    'hasError' => (bool)$this->errors,
                    'errors' => $this->errors,
                    'id_address_delivery' => (int)$this->context->cart->id_address_delivery,
                    'id_address_invoice' => (int)$this->context->cart->id_address_invoice
                );
                $this->ajaxDie(json_encode($return));
            }
            return $address;
        }
        $this->errors[] = Tools::displayError('An error occurred while updating your address.');
        return false;
    }

    /**
     * @param string $canonical_url
     *
     * Don't use regular redirection to stay in module controller
     */
    protected function canonicalRedirection($canonical_url = '')
    {
        return;
    }
}
