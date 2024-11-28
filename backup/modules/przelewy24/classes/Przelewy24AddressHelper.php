<?php
/**
 * Class Przelewy24AddressHelper
 *
 * @author    Przelewy24
 * @copyright Przelewy24
 * @license   https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class Przelewy24AddressHelper
{
    private $cart;

    private $customerId;

    private $billingAddress;
    private $deliveryAddress;

    private $addresses = [];

    /**
     * @param Cart $cart
     */
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
        $this->customerId = $cart->id_customer;
        $this->resolveAddressType($this->cart->id_address_invoice, $this->cart->id_address_delivery);
    }

    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    private function resolveAddressType($billingId, $deliveryId)
    {
        $customerId = $this->cart->id_customer;

        $customer = new Customer($customerId);
        $this->loadAddresses($customer);

        foreach ($this->addresses as $address) {
            if ($address['id_address'] === $billingId) {
                $this->setBillingAddress($address);
            }
            if ($address['id_address'] === $deliveryId) {
                $this->setDeliveryAddress($address);
            }
        }
    }

    private function setBillingAddress($address)
    {
        $this->billingAddress = $address;
    }

    private function setDeliveryAddress($address)
    {
        $this->deliveryAddress = $address;
    }

    /**
     * addresses lazy load.
     *
     * @param Customer $customer
     */
    private function loadAddresses(Customer $customer)
    {
        if ([] === $this->addresses) {
            $this->addresses = $customer->getAddresses((int) Configuration::get('PS_LANG_DEFAULT'));
        }
    }
}
