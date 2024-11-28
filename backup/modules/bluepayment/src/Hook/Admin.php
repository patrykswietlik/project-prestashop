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

use BluePayment\Service\FactoryPaymentMethods;
use BluePayment\Service\Gateway;
use BluePayment\Service\Refund;
use BluePayment\Until\AdminHelper;
use BluePayment\Until\Helper;
use Configuration as Cfg;

class Admin extends AbstractHook
{
    public const AVAILABLE_HOOKS = [
        'adminPayments',
        'adminOrder',
        'displayAdminAfterHeader',
    ];

    /**
     * Payment statuses
     */
    public const PAYMENT_STATUS_PENDING = 'PENDING';
    public const PAYMENT_STATUS_SUCCESS = 'SUCCESS';
    public const PAYMENT_STATUS_FAILURE = 'FAILURE';

    /**
     * Get the payment methods available in the administration
     */
    public function adminPayments()
    {
        $list = $transferPayments = $wallets = [];

        $adminHelper = new AdminHelper($this->module);

        foreach (AdminHelper::getSortCurrencies() as $currency) {
            $paymentList = $adminHelper->getListChannels($currency['iso_code']);
            $title = $currency['name'] . ' (' . $currency['iso_code'] . ')';
            $factoryPaymentMethods = new FactoryPaymentMethods($this->module);
            $namespace = 'BluePayment\\Service\\PaymentMethods';
            foreach ($paymentList as $key => $payment) {
                $className = $factoryPaymentMethods->getPaymentMethodName($payment['gateway_id']);
                $class = "$namespace\\$className";

                if (class_exists($class)) {
                    $gateway = new Gateway(
                        $this->module,
                        new $class()
                    );

                    if (!$gateway->isActiveBo()) {
                        unset($paymentList[$key]);
                    }
                }
            }

            if (!empty($paymentList)) {
                $list[] = $adminHelper->renderAdditionalOptionsList($this->module, $paymentList, $title);
            }

            if ($adminHelper->getListAllPayments($currency['iso_code'], 'transfer')) {
                $transferPayments[$currency['iso_code']] = $adminHelper->getListAllPayments(
                    $currency['iso_code'],
                    'transfer'
                );
            }

            if ($adminHelper->getListAllPayments($currency['iso_code'], 'wallet')) {
                $wallets[$currency['iso_code']] = $adminHelper->getListAllPayments(
                    $currency['iso_code'],
                    'wallet'
                );
            }
        }

        $this->context->smarty->assign(
            [
                'list' => $list,
                'transfer_payments' => $transferPayments,
                'bm_assets_images' => $this->module->getAssetImages(),
                'wallets' => $wallets,
            ]
        );

        return $this->module->display(
            $this->module->getPathUrl(),
            'views/templates/admin/_configure/helpers/container_list.tpl'
        );
    }

    public function displayAdminAfterHeader(): string
    {
        $apiUrl = 'https://api-addons.prestashop.com/';
        $version = $this->module->version;
        // Connect to Prestashop addons API
        return $this->getAddonsUpdate($apiUrl, $version);
    }

    public function getAddonsUpdate($apiUrl, $version): string
    {
        // Connect to Prestashop addons API
        $params = '?format=json&iso_lang=pl&iso_code=pl&method=module&id_module=49791&method=listing&action=module';
        $currentVersionPs = '&version=' . _PS_VERSION_;
        $apiRequest = $apiUrl . $params . $currentVersionPs;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $apiRequest);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $output = curl_exec($curl);
        curl_close($curl);

        try {
            if ($output) {
                $apiResponse = json_decode($output);
                $ver = $apiResponse->modules[0]->version ?? null;
                $this->context->smarty->assign(['version' => $ver]);
            }

            if ($ver && version_compare($ver, $version, '>')) {
                \PrestaShopLogger::addLog('Autopay - Dostępna aktualizacja', 2);

                return $this->module->fetch('module:bluepayment/views/templates/admin/_partials/upgrade.tpl');
            }
        } catch (\Exception $e) {
            \PrestaShopLogger::addLog('Autopay - Błąd sprawdzania aktualizacji', 2);
        }

        return '';
    }

    public function adminOrder($params)
    {
        $this->module->id_order = $params['id_order']; // todo seter
        $order = new \Order($this->module->id_order);

        $output = '';

        if ($order->module !== 'bluepayment') {
            return $output;
        }
        $updateOrderStatusMessage = '';

        $order_payment = Helper::getLastOrderPaymentByOrderId($params['id_order']);

        $refundable = $order_payment['payment_status'] === self::PAYMENT_STATUS_SUCCESS;
        $refund_type = \Tools::getValue('bm_refund_type', 'full');
        $refund_amount = $refund_type === 'full'
            ? $order->total_paid
            : (float) str_replace(',', '.', \Tools::getValue('bm_refund_amount'));
        $refund_errors = [];
        $refund_success = [];

        if ($refundable && \Tools::getValue('go-to-refund-bm')) {
            if ($refund_amount > $order->total_paid) {
                $refund_errors[] = $this->module->l('The refund amount you entered is greater than paid amount.');
            } else {
                $refund = new Refund($this->module);
                $order = new \OrderCore($order->id);
                $currency = new \Currency($order->id_currency);

                $refundOrder = $refund->refundOrder(
                    $refund_amount,
                    $order_payment['remote_id'],
                    $currency
                );

                if (!empty($refundOrder[1]) || $refundOrder[0] !== true) {
                    $refund_errors[] = $this->module->l('Refund error: ') . $refundOrder[1];
                }

                if (empty($refund_errors) && $refundOrder[0] === true) {
                    $history = new \OrderHistory();
                    $history->id_order = (int) $order->id;
                    $history->id_employee = (int) $this->context->employee->id;
                    $history->changeIdOrderState(Cfg::get('PS_OS_REFUND'), (int) $order->id);
                    $history->addWithemail(true, []);
                    $refund_success[] = $this->module->l('Successful refund');
                }
            }
        }

        $this->context->smarty->assign([
            'BM_ORDERS' => Helper::getOrdersByOrderId($params['id_order']),
            'BM_ORDER_ID' => $this->module->id_order,
            'BM_CANCEL_ORDER_MESSAGE' => $updateOrderStatusMessage,
            'SHOW_REFUND' => $refundable,
            'REFUND_FULL_AMOUNT' => number_format((float) $order->total_paid, 2, '.', ''),
            'REFUND_ERRORS' => $refund_errors,
            'REFUND_SUCCESS' => $refund_success,
            'REFUND_TYPE' => $refund_type,
            'REFUND_AMOUNT' => $refund_amount,
        ]);

        return $this->module->fetch('module:bluepayment/views/templates/admin/status.tpl');
    }
}
