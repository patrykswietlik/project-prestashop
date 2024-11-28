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

class AmazonpaySetaddressModuleFrontController extends AddressControllerCore
{

    public $is_amazon_pay = true;
    public $internal_id_address = 0;
    protected $should_redirect = false;

    /**
     * Assign template vars related to page content.
     *
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        if (!$this->ajax && $this->should_redirect) {
            Tools::redirect(
                $this->context->link->getModuleLink('amazonpay', 'checkout')
            );
        }

        parent::initContent();
        $this->setTemplate('customer/address', array('entity' => 'address', 'id' => $this->internal_id_address > 0 ? $this->internal_id_address : Tools::getValue('id_address')));
    }

    /**
     * @throws PrestaShopException
     */
    public function displayAjaxAddressForm()
    {
        $addressForm = $this->makeAddressForm();

        if (Tools::getIsset('id_address') && ($id_address = (int) Tools::getValue('id_address'))) {
            $addressForm->loadAddressById($id_address);
        }

        if (Tools::getIsset('id_country')) {
            $addressForm->fillWith(['id_country' => Tools::getValue('id_country')]);
        }

        ob_end_clean();
        header('Content-Type: application/json');
        $this->context->smarty->assign($addressForm->getTemplateVariables());
        $this->ajaxRender(json_encode([
            'address_form' => $this->context->smarty->fetch(
                'module:amazonpay/views/templates/front/address-form.tpl'
            )
        ]));
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
        if (!isset($this->address_form)) {
            $this->address_form = $this->makeAddressForm();
        }
        $this->address_form->setTemplate('module:amazonpay/views/templates/front/address-form.tpl');
        $this->address_form->fillWith(Tools::getAllValues());
        if (Tools::isSubmit('submitAddress')) {
            if (!$this->address_form->submit()) {
                $this->errors[] = $this->trans('Please fix the error below.', array(), 'Shop.Notifications.Error');
            } else {
                if (Tools::getValue('id_address')) {
                    $this->success[] = $this->trans('Address successfully updated!', array(), 'Shop.Notifications.Success');
                } else {
                    $this->success[] = $this->trans('Address successfully added!', array(), 'Shop.Notifications.Success');
                }
                $this->should_redirect = true;

                AmazonPayAddress::saveAddressAmazonReference(
                    $this->address_form->getAddress(),
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
                        $fields_to_set = AmazonPayAddress::fetchInvalidInput($address_invoice);
                        foreach ($fields_to_set as $field_to_set) {
                            $address_invoice->$field_to_set = isset($this->address_form->getAddress()->$field_to_set) ? $this->address_form->getAddress()->$field_to_set : '';
                        }
                        $address_invoice->save();
                        Hook::exec('actionAmazonPayAddressSave', ['type' => 'invoice', 'address' => $address_invoice]);
                    }

                    $fields_to_set = AmazonPayAddress::fetchInvalidInput($address_invoice);
                    foreach ($fields_to_set as $field_to_set) {
                        $address_invoice->$field_to_set = isset($this->address_form->getAddress()->$field_to_set) ? $this->address_form->getAddress()->$field_to_set : '';
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

                $this->context->cart->id_address_delivery = $this->address_form->getAddress()->id;
                $this->context->cart->id_address_invoice = $address_invoice->id;
                $sql = 'UPDATE `' . _DB_PREFIX_ . 'cart_product`
                           SET `id_address_delivery` = ' . (int) $this->address_form->getAddress()->id . '
                         WHERE `id_cart` = ' . (int) $this->context->cart->id . '';
                Db::getInstance()->execute($sql);

                $sql = 'UPDATE `' . _DB_PREFIX_ . 'customization`
                           SET `id_address_delivery` = ' . (int) $this->address_form->getAddress()->id . '
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
            if ((int)$address_delivery->id > 0) {
                $this->address_form->loadAddressById((int)$address_delivery->id);
            }
            $this->address_form->fillWith(get_object_vars($address_delivery));
            $this->internal_id_address = $address_delivery->id;
        }
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
