<?php
/**
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

if (!class_exists('Przelewy24Product', false)) {
    /**
     * Class Przelewy24Product
     */
    class Przelewy24Product implements Przelewy24Interface
    {
        /**
         * Array with transations.
         *
         * @var array
         */
        private $translations;

        public function __construct(array $translations = [])
        {
            $this->setTranslations($translations);
        }

        /**
         * Prepare cart items for form trnRegister.
         *
         * Read more: /docs/przelewy24product.md.
         */
        public function prepareCartItems($amount, array $items = [], $shipping = 0)
        {
            $cartItems = [];

            if (empty($items)) {
                return $cartItems;
            }

            $amount = (int) $amount;
            $shipping = (int) $shipping;

            $number = 0;
            $sumProductsPrice = 0;
            $joinName = '';
            foreach ($items as $item) {
                ++$number;

                $cartItems['p24_name_' . $number] = Tools::substr(strip_tags($item['name']), 0, 127);
                $cartItems['p24_description_' . $number] = Tools::substr(strip_tags($item['description']), 0, 127);
                $cartItems['p24_quantity_' . $number] = (int) $item['quantity'];
                $cartItems['p24_price_' . $number] = (int) $item['price'];
                $cartItems['p24_number_' . $number] = (int) $item['number'];

                $joinName .= strip_tags($item['name']) . ', ';
                $sumProductsPrice += ((int) $item['quantity'] * (int) $item['price']);
            }

            if ($amount > $shipping + $sumProductsPrice) {
                ++$number;

                $cartItems['p24_name_' . $number] = $this->translations['virtual_product_name'];
                $cartItems['p24_description_' . $number] = '';
                $cartItems['p24_quantity_' . $number] = 1;
                $cartItems['p24_price_' . $number] = $amount - ($shipping + $sumProductsPrice);
                $cartItems['p24_number_' . $number] = 0;
            } elseif ($amount < $shipping + $sumProductsPrice) {
                $cartItems = [];
                $number = 1;
                $joinName = $this->translations['cart_as_product'] . ' [' . trim($joinName, ', ') . ']';

                $cartItems['p24_name_' . $number] = Tools::substr($joinName, 0, 127);
                $cartItems['p24_description_' . $number] = '';
                $cartItems['p24_quantity_' . $number] = 1;
                $cartItems['p24_price_' . $number] = $amount - $shipping;
                $cartItems['p24_number_' . $number] = 0;
            }
            // when is correct
            return $cartItems;
        }

        /**
         * Set transactions.
         *
         * @param array $translations
         */
        public function setTranslations(array $translations = [])
        {
            $this->translations = $translations;
            // set default values
            if (empty($this->translations['virtual_product_name'])) {
                $this->translations['virtual_product_name'] = 'Różnica';
            }
            if (empty($this->translations['cart_as_product'])) {
                $this->translations['cart_as_product'] = 'Zamówienie';
            }
        }
    }
}
