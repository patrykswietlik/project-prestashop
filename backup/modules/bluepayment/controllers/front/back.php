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
if (!defined('_PS_VERSION_')) {
    exit;
}

class BluePaymentBackModuleFrontController extends ModuleFrontController
{
    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function initContent()
    {
        parent::initContent();

        $orderId = Tools::getValue('OrderID');
        $order = explode('-', $orderId)[0];
        $order = new OrderCore($order);

        $customer = new CustomerCore($order->id_customer);

        Tools::redirect(
            'index.php?controller=order-confirmation&id_cart=' . $order->id_cart . '&id_module=' . $this->module->id
            . '&id_order=' . $order->id . '&key=' . $customer->secure_key
        );
    }
}
