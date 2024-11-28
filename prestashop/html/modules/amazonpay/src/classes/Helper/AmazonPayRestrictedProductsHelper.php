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

class AmazonPayRestrictedProductsHelper
{

    /**
     * @param $id_product
     * @return bool
     */
    public static function isRestrictedProduct($id_product)
    {
        if (Configuration::get('AMAZONPAY_RESTRICTED_CATEGORIES')) {
            $restrictedCategories = trim(Configuration::get('AMAZONPAY_RESTRICTED_CATEGORIES'));
            $restrictedCategories = explode(",", $restrictedCategories);
            if (sizeof($restrictedCategories) == 0) {
                return false;
            }
            $restrictedCategories = self::prepRestrictedCategories($restrictedCategories);
            if (Product::idIsOnCategoryId((int)$id_product, $restrictedCategories)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public static function cartHasRestrictedProducts()
    {
        if (Configuration::get('AMAZONPAY_RESTRICTED_CATEGORIES')) {
            $restrictedCategories = trim(Configuration::get('AMAZONPAY_RESTRICTED_CATEGORIES'));
            $restrictedCategories = explode(",", $restrictedCategories);
            if (sizeof($restrictedCategories) == 0) {
                return false;
            }
            $restrictedCategories = self::prepRestrictedCategories($restrictedCategories);
            $idCart = Context::getContext()->cart->id;
            if (!$idCart) {
                return false;
            }
            $cart = New Cart(Context::getContext()->cart->id);
            $productsCart = $cart->getProducts();
            foreach ($productsCart as $productCart) {
                if (Product::idIsOnCategoryId((int)$productCart['id_product'], $restrictedCategories)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $restrictedCategories
     * @return array
     */
    protected static function prepRestrictedCategories($restrictedCategories)
    {
        $tmp = [];
        foreach ($restrictedCategories as $restrictedCategory) {
            $tmp[] = ['id_category' => $restrictedCategory];
        }
        return $tmp;
    }
}
