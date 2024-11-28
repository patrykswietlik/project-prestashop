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

use BlueMedia\OnlinePayments\Gateway;
use BlueMedia\OnlinePayments\Model\TransactionStandard;
use BluePayment\Config\Config;
use BluePayment\Until\Helper;

class BluePaymentPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;

        if (
            $cart->id_customer === 0 || $cart->id_address_delivery === 0
            || $cart->id_address_invoice === 0 || !$this->module->active
        ) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        if (!$this->moduleAuthorized()) {
            exit($this->module->l('This payment method is not available.', 'bluepayment'));
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        if (Validate::isLoadedObject($this->context->cart) && !$this->context->cart->OrderExists()) {
            $cartId = $cart->id;

            $totalPaid = (float) $cart->getOrderTotal(true, Cart::BOTH);
            $amount = number_format(round($totalPaid, 2), 2, '.', '');

            $this->module->validateOrder(
                $cartId,
                Configuration::get($this->module->name_upper . '_STATUS_WAIT_PAY_ID'),
                $amount,
                $this->module->displayName,
                null,
                [],
                null,
                false,
                $customer->secure_key
            );
            $orderId = $this->module->currentOrder . '-' . time();
        } else {
            $bluepaymentCartId = Tools::getValue('bluepayment_cart_id', null);

            if (empty($bluepaymentCartId)) {
                exit($this->module->l('This cart is empty.', 'bluepayment'));
            } else {
                $bluepaymentCartId = explode('-', $bluepaymentCartId);
                $bluepaymentCartId = empty($bluepaymentCartId[0]) ? 0 : $bluepaymentCartId[0];

                $order = Order::getByCartId($bluepaymentCartId);
                $cart = Cart::getCartByOrderId($order->id);

                $totalPaid = (float) $cart->getOrderTotal(true, Cart::BOTH);
                $amount = number_format(round($totalPaid, 2), 2, '.', '');

                $orderId = $order->id . '-' . time();
            }
        }

        $gateway_id = (int) Tools::getValue('bluepayment_gateway', 0);

        $this->context->smarty->assign([
            'bm_dir' => $this->module->getPathUrl(),
            'form' => $this->createTransaction($gateway_id, $orderId, $amount, $customer),
        ]);

        $this->createTransactionQuery($orderId);

        $this->setTemplate('module:bluepayment/views/templates/front/payment.tpl');
    }

    /**
     * Check if the payment option is still active in case the customer
     * makes a change of address before finalizing the order
     *
     * @return bool
     */
    private function moduleAuthorized(): bool
    {
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] === 'bluepayment') {
                $authorized = true;
                break;
            }
        }

        return $authorized;
    }

    private function createTransaction($gateway_id, $orderId, $amount, $customer)
    {
        $isoCode = $this->context->currency->iso_code;

        $service_id = (int) Helper::parseConfigByCurrency(
            $this->module->name_upper . Config::SERVICE_PARTNER_ID,
            $isoCode
        );

        $shared_key = Helper::parseConfigByCurrency(
            $this->module->name_upper . Config::SHARED_KEY,
            $isoCode
        );

        $test_mode = Configuration::get($this->module->name_upper . '_TEST_ENV');
        $gateway_mode = $test_mode ? Gateway::MODE_SANDBOX : Gateway::MODE_LIVE;

        $gateway = new Gateway($service_id, $shared_key, $gateway_mode);

        $transactionStandard = new TransactionStandard();
        $transactionStandard->setOrderId((string) $orderId)
            ->setAmount($amount)
            ->setCustomerEmail($customer->email)
            ->setCurrency($isoCode)
            ->setHtmlFormLanguage($this->context->language->iso_code ?: Config::DEFAULT_PAYMENT_FORM_LANGUAGE)
            ->setLanguage($this->context->language->iso_code ?: Config::DEFAULT_PAYMENT_FORM_LANGUAGE);

        $regulationId = Tools::getValue('bluepayment-hidden-psd2-regulation-id', null);

        // Parametr regulation-id jest przekazywany tylko w przypadku kanałow z regulaminami PSD
        if (empty($regulationId) === false) {
            // Zaakceptowana przez uzytkownika PSD2 klauzula
            $transactionStandard
                ->setDefaultRegulationAcceptanceID(Tools::getValue('bluepayment-hidden-psd2-regulation-id'))
                ->setDefaultRegulationAcceptanceState('ACCEPTED')
                ->setDefaultRegulationAcceptanceTime(date('Y-m-d H:i:s'));
        }

        if ($gateway_id !== 0) {
            $transactionStandard->setGatewayId($gateway_id);
        }

        $form = '';

        /* @var Gateway $gateway */
        try {
            $form = $gateway->doTransactionStandard($transactionStandard);
        } catch (Exception $exception) {
            Tools::error_log($exception);
        }

        return $form;
    }

    private function createTransactionQuery($orderId)
    {
        $ga = $_COOKIE['_ga'] ?? '';

        Db::getInstance()->insert(
            'blue_transactions',
            [
                'order_id' => $orderId,
                'created_at' => date('Y-m-d H:i:s'),
                'gtag_uid' => $ga,
            ]
        );
    }
}
