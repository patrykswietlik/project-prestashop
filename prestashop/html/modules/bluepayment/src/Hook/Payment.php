<?php
/**
 * NOTICE OF LICENSE
 * This source file is subject to the GNU Lesser General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/lgpl-3.0.en.html
 *
 * @author     Autopay S.A.
 * @copyright  Since 2015 Autopay S.A.
 * @license    https://www.gnu.org/licenses/lgpl-3.0.en.html GNU Lesser General Public License
 */

declare(strict_types=1);

namespace BluePayment\Hook;

if (!defined('_PS_VERSION_')) {
    exit;
}

use BluePayment\Statuses\OrderStatusMessageDictionary;
use Configuration as Cfg;

class Payment extends AbstractHook
{
    public const AVAILABLE_HOOKS = [
        'paymentReturn',
        'orderConfirmation',
    ];

    /**
     * Return payment/order confirmation step hook
     *
     * @param $params
     *
     * @return string|void
     */
    public function paymentReturn($params)
    {
        if (
            !$this->module->active
            || !isset($params['order'])
            || ($params['order']->module != $this->module->name)
        ) {
            return null;
        }

        $currency = new \Currency($params['order']->id_currency);

        $orderData = $this->getDataToOrderResults(
            $params,
            $currency
        );

        if (!$orderData) {
            return;
        }

        return $this->module->fetch('module:bluepayment/views/templates/hook/paymentReturn.tpl');
    }

    public function getDataToOrderResults($params, $currency): bool
    {
        $products = [];

        if (!empty($params['order']->getProducts())) {
            foreach ($params['order']->getProducts() as $product) {
                $cat = new \Category($product['id_category_default'], $this->context->language->id);

                $newProduct = new \stdClass();
                $newProduct->name = $product['product_name'];
                $newProduct->category = $cat->name;
                $newProduct->price = $product['price'];
                $newProduct->quantity = $product['product_quantity'];
                $newProduct->sku = $product['product_reference'];

                $products[] = $newProduct;
            }
        } else {
            return false;
        }

        $this->context->smarty->assign([
            'order_id' => $params['order']->id,
            'shop_name' => $this->context->shop->name,
            'revenue' => $params['order']->total_paid,
            'shipping' => $params['order']->total_shipping,
            'tax' => $params['order']->carrier_tax_rate,
            'currency' => $currency->iso_code,
            'products' => $products,
        ]);

        return true;
    }

    public function orderConfirmation($params)
    {
        if (!$params['order'] || !$params['order']->id) {
            return null;
        }

        $id_default_lang = (int) Cfg::get('PS_LANG_DEFAULT');
        $order = new \OrderCore($params['order']->id);
        $state = $order->getCurrentStateFull($id_default_lang);

        $orderStatusMessage = OrderStatusMessageDictionary::getMessage($state['id_order_state']) ?? $state['name'];

        $this->context->smarty->assign([
            'order_status' => $this->module->l($orderStatusMessage),
        ]);

        return $this->module->fetch('module:bluepayment/views/templates/hook/order-confirmation.tpl');
    }
}
