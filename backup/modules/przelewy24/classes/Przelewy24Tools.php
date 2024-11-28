<?php
/**
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Przelewy24Tools
{
    /**
     * Check if cart is valid for customer.
     *
     * @param Customer $customer
     * @param Cart $cart
     *
     * @return bool
     */
    public static function checkCartForCustomer($customer, $cart)
    {
        $cartCustomer = new Customer($cart->id_customer);
        $isGuest = $cartCustomer->isGuest();
        if ($isGuest) {
            /* The test below do now work for guests and is useless. */
            return true;
        }

        return $customer
            && $customer->id === (int) $cart->id_customer
            && $customer->id_shop === $cart->id_shop
            && (0 === (int) $cart->id_shop_group || (int) $customer->id_shop_group === (int) $cart->id_shop_group);
    }

    /**
     * The hard way to escape string for JavaScript Object Notation.
     *
     * Should be quite resistive for double escaping.
     *
     * @param string $input
     * @return string
     */
    public static function HardEscapeForJavaScriptNotation($input)
    {
        /* The backslash has to be first in this array. */
        $chars = ['\\', '/', "'", '"', '<', '>', '&'];
        foreach ($chars as $char) {
            $pattern = '#' . preg_quote($char) . '#';
            $replacement = '\u00' . bin2hex($char);
            $input = preg_replace($pattern, $replacement, $input);
        }

        return $input;
    }
}
