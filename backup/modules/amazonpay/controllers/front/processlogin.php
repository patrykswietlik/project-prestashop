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

class AmazonpayProcessloginModuleFrontController extends ModuleFrontController
{

    public $display_column_left = false;

    public $display_column_right = false;

    /**
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        $buyerToken = Tools::getValue('buyerToken');
        if (!$buyerToken || trim($buyerToken) == '') {
            die('NOT AUTHORIZED');
        }

        $client = AmazonPayHelper::getClient();
        $buyer = $client->getBuyer($buyerToken);
        if (isset($buyer['status']) && $buyer['status'] == 200) {
            $buyerData = json_decode($buyer['response'], true);

            try {
                if ($this->context->customer->isLogged()) {
                    $customer = $customer = new Customer((int)$this->context->customer->id);
                } else {
                    $customerId = Customer::customerExists($buyerData['email'], true);
                    if ($customerId) {
                        $customer = new Customer($customerId);
                    } else {
                        $names = AmazonPayAddress::prepName($buyerData['name']);
                        $customer = new Customer();
                        $customer->lastname = $names[1];
                        $customer->firstname = $names[0];
                        $customer->email = $buyerData['email'];
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

                        $customer->is_guest = false;
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
                CartRule::autoRemoveFromCart($this->context);
                CartRule::autoAddToCart($this->context);
            } catch (\Exception $e) {
                echo $e->getMessage();
                die();
            }

            if (Tools::getValue('toCheckout') == '1') {
                Tools::redirect(
                    $this->context->link->getPageLink('order', true, $this->context->language->id, '&step=2')
                );
            }
            Tools::redirect(
                $this->context->link->getPageLink('my-account', true, $this->context->language->id)
            );
        } else {
            Tools::redirect(
                $this->context->link->getPageLink('my-account', true, $this->context->language->id)
            );
        }
    }
}
