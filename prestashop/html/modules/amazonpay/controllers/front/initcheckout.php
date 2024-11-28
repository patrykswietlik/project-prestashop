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

class AmazonpayInitcheckoutModuleFrontController extends ModuleFrontController
{

    /**
     * @throws Exception
     */
    public function postProcess()
    {
        if (!isset(Context::getContext()->cookie->amazon_pay_checkout_session_id) ||
            Tools::getValue('amazonCheckoutSessionId') != Context::getContext()->cookie->amazon_pay_checkout_session_id) {
            if (Tools::getValue('decoupled') == '1') {
                $amazonPayCheckoutSession = new AmazonPayCheckoutSession(false, Tools::getValue('amazonCheckoutSessionId'));
                try {
                    if ($amazonPayCheckoutSession->isOpen()) {
                        $amazonPayCheckoutSession->saveSession();
                    }
                } catch (\Exception $e) {
                    die('NOT AUTHORIZED');
                }
            } else {
                die('NOT AUTHORIZED');
            }
        }

        $amazonPayCheckoutSession = new AmazonPayCheckoutSession(false);
        if ($amazonPayCheckoutSession->checkStatus()) {
            $coData = $amazonPayCheckoutSession->assocReturn();
        } else {
            die('NOT AUTHORIZED');
        }

        try {
            $setaddress = 'setaddress';
            if (AmazonPay::isPrestaShop16Static()) {
                $setaddress = 'setaddresslegacy';
            }

            if ($this->context->customer->isLogged()) {
                $customer = new Customer((int)$this->context->customer->id);
            } else {
                $customerId = Customer::customerExists($coData['buyer']['email'], true);
                if ($customerId) {
                    $customer = new Customer($customerId);
                } else {
                    $names = AmazonPayAddress::prepName($coData['buyer']['name']);
                    $customer = new Customer();
                    $customer->lastname = $names[1];
                    $customer->firstname = $names[0];
                    $customer->email = $coData['buyer']['email'];
                    if ($this->module->isPrestaShop16()) {
                        $customer->passwd = md5(time() . _COOKIE_KEY_);
                    } else {
                        $clearTextPassword = $this->get('hashing')->hash(
                            microtime(),
                            _COOKIE_KEY_
                        );
                        $customer->passwd = $this->get('hashing')->hash(
                            $clearTextPassword,
                            _COOKIE_KEY_
                        );
                    }

                    $customer->is_guest = AmazonPayCustomerHelper::forceAccountCreation() ? true : false;
                    $customer->save();

                    Hook::exec('actionCustomerAccountAdd', array(
                        'newCustomer' => $customer,
                    ));
                }

                if ($this->module->isPrestaShop16()) {
                    $this->context->cookie->id_compare = isset($this->context->cookie->id_compare) ? $this->context->cookie->id_compare: CompareProduct::getIdCompareByIdCustomer($customer->id);
                    $this->context->cookie->id_customer = (int)($customer->id);
                    $this->context->cookie->customer_lastname = $customer->lastname;
                    $this->context->cookie->customer_firstname = $customer->firstname;
                    $this->context->cookie->logged = 1;
                    $customer->logged = 1;
                    $this->context->cookie->is_guest = $customer->isGuest();
                    $this->context->cookie->passwd = $customer->passwd;
                    $this->context->cookie->email = $customer->email;
                    $this->context->customer = $customer;
                    $this->context->cart->id_customer = (int)$customer->id;
                    $this->context->cart->secure_key = $customer->secure_key;
                    $this->context->cart->save();
                    $this->context->cookie->id_cart = (int)$this->context->cart->id;
                    $this->context->cookie->write();
                    $this->context->cart->autosetProductAddress();
                    Hook::exec('actionAuthentication', array('customer' => $this->context->customer));
                } else {
                    $this->context->updateCustomer($customer);
                }
                $this->context->cart->update();
            }

            $old_delivery_address_id = $this->context->cart->id_address_delivery;

            $amazon_address_to_use_for_shipping = $coData['shippingAddress'];
            if ($coData['shippingAddress'] == '') {
                if ($this->context->cart->isVirtualCart()) {
                    $amazon_address_to_use_for_shipping = $coData['billingAddress'];
                }
            }

            $address_delivery = AmazonPayAddress::findByAmazonOrderReferenceIdOrNew(
                Context::getContext()->cookie->amazon_pay_checkout_session_id,
                $amazon_address_to_use_for_shipping,
                $customer->id,
                false
            );

            $address_delivery->processFromArray($amazon_address_to_use_for_shipping);
            try {
                $address_delivery->save();
                Hook::exec('actionAmazonPayAddressSave', ['type' => 'shipping', 'address' => $address_delivery]);
            } catch (\Exception $e) {
                Tools::redirect(
                    $this->context->link->getModuleLink(
                        'amazonpay',
                        $setaddress,
                        ['amazonCheckoutSessionId' => Context::getContext()->cookie->amazon_pay_checkout_session_id]
                    )
                );
            }

            AmazonPayAddress::saveAddressAmazonReference(
                $address_delivery,
                Context::getContext()->cookie->amazon_pay_checkout_session_id,
                $customer->id,
                $amazon_address_to_use_for_shipping
            );

            if (is_null($coData['billingAddress']) || AmazonPayHelper::jumpInvoiceAddress()) {
                if ($this->context->cart->id_address_delivery !== $this->context->cart->id_address_invoice) {
                    $address_invoice = new Address($this->context->cart->id_address_invoice);
                } else {
                    $address_invoice = $address_delivery;
                }
            } else {
                $address_invoice = AmazonPayAddress::findByAmazonOrderReferenceIdOrNew(
                    Context::getContext()->cookie->amazon_pay_checkout_session_id . '-invoice',
                    $coData['billingAddress'],
                    $customer->id,
                    false
                );
                $address_invoice->processFromArray($coData['billingAddress']);
                try {
                    $address_invoice->save();
                    Hook::exec('actionAmazonPayAddressSave', ['type' => 'invoice', 'address' => $address_invoice]);
                } catch (\Exception $e) {
                    $fields_to_set = AmazonPayAddress::fetchInvalidInput($address_invoice);
                    foreach ($fields_to_set as $field_to_set) {
                        $address_invoice->$field_to_set = isset($address_delivery->$field_to_set) ? $address_delivery->$field_to_set : '';
                    }
                    $address_invoice->save();
                    Hook::exec('actionAmazonPayAddressSave', ['type' => 'invoice', 'address' => $address_invoice]);
                }
            }

            AmazonPayAddress::saveAddressAmazonReference(
                $address_invoice,
                Context::getContext()->cookie->amazon_pay_checkout_session_id . '-invoice',
                $customer->id,
                $coData['billingAddress']
            );

            $this->context->cart->id_address_delivery = $address_delivery->id;
            $this->context->cart->id_address_invoice = $address_invoice->id;
            $this->context->cart->updateAddressId($old_delivery_address_id, $address_delivery->id);
            $this->context->cart->save();

            CartRule::autoRemoveFromCart($this->context);
            CartRule::autoAddToCart($this->context);
        } catch (\Exception $e) {
            echo $e->getMessage();
            die();
        }

        if ($this->module->isPrestaShop16()) {
            if ($this->module->isOnePageCheckoutPSInstalled()) {
                Tools::redirect(
                    $this->context->link->getPageLink('order-opc', true, $this->context->language->id, '&checkout=1')
                );
            } elseif ($this->module->isTheCheckoutInstalled()) {
                Tools::redirect(
                    $this->context->link->getPageLink('order', true, $this->context->language->id)
                );
            }
            Tools::redirect(
                $this->context->link->getPageLink('order', true, $this->context->language->id, '&step=2')
            );
        } else {
            if ($this->module->isTheCheckoutInstalled()) {
                Tools::redirect(
                    $this->context->link->getPageLink('order', true, $this->context->language->id)
                );
            }
            if ($this->module->isOnePageCheckoutPSInstalled()) {
                Tools::redirect(
                    $this->context->link->getPageLink('order', true, $this->context->language->id, '&checkout=1')
                );
            }
            if ($this->module->isSupercheckoutInstalled()) {
                Tools::redirect(
                    $this->context->link->getPageLink('order', true, $this->context->language->id)
                );
            }
            Tools::redirect(
                $this->context->link->getModuleLink('amazonpay', 'checkout')
            );
        }
    }
}
