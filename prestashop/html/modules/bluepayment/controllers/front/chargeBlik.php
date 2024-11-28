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

use BlueMedia\OnlinePayments\Model\Gateway;
use BluePayment\Config\Config;
use BluePayment\Until\Helper;

class BluePaymentChargeBlikModuleFrontController extends ModuleFrontController
{
    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws Exception
     */
    public function initContent()
    {
        parent::initContent();

        $status = true;
        $blikCode = pSQL(Tools::getValue('blikCode'));
        $postOrderId = pSQL(Tools::getValue('postOrderId'));

        if (preg_match('/[^a-z_\-0-9 ]/i', $blikCode) && Tools::strlen($blikCode) !== Config::BLIK_CODE_LENGTH) {
            $status = false;
        }

        if (Validate::isLoadedObject($this->context->cart) && !$this->context->cart->OrderExists() == false) {
            $cart = $this->context->cart;
        } else {
            $cart = $this->getCartByOrderId($postOrderId);
        }

        if ($cart->id_customer === 0
            || $cart->id_address_delivery === 0
            || $cart->id_address_invoice === 0
            || !$this->module->active
        ) {
            $status = false;
        }

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            $status = false;
        }

        if (!$status) {
            echo json_encode([
                'status' => 'FAILURE',
                'message' => $this->module->l('Client identificator not provided.', 'chargeblik'),
            ]);
            exit;
        }

        $currency = $this->context->currency->iso_code;

        $serviceId = Helper::parseConfigByCurrency($this->module->name_upper . Config::SERVICE_PARTNER_ID, $currency);
        $sharedKey = Helper::parseConfigByCurrency($this->module->name_upper . Config::SHARED_KEY, $currency);

        $totalPaid = (float) $cart->getOrderTotal(true, Cart::BOTH);
        $amount = number_format(round($totalPaid, 2), 2, '.', '');

        $customer = new Customer($cart->id_customer);
        $customerEmail = $customer->email;

        if (Validate::isLoadedObject($this->context->cart) && !$this->context->cart->OrderExists()) {
            $this->moduleValidateOrder($cart->id, $amount, $customer);
        }

        $orderId = $this->module->currentOrder . '-' . time();

        if (!empty($postOrderId)) {
            $orderId = $postOrderId;
        }

        $result = $this->initTransaction(
            $serviceId,
            $sharedKey,
            $orderId,
            $amount,
            $currency,
            $customerEmail,
            $blikCode
        );

        echo json_encode($result);
        exit;
    }

    private function getCartByOrderId($postOrderId)
    {
        if (empty($postOrderId)) {
            $cart = $this->context->cart;
        } else {
            $orderIdItem = explode('-', $postOrderId);
            $orderIdItem = empty($orderIdItem[0]) ? 0 : $orderIdItem[0];
            $cart = Cart::getCartByOrderId($orderIdItem);
        }

        return $cart;
    }

    private function getTransactionData($orderId, $blikCode)
    {
        $query = new DbQuery();
        $query->from('blue_transactions')
            ->where('order_id = ' . (int) $orderId)
            ->where('blik_code = \'' . pSQL($blikCode) . '\'')
            ->select('*');

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query, false);
    }

    private function initTransaction($serviceId, $sharedKey, $orderId, $amount, $currency, $customerEmail, $blikCode): array
    {
        $transaction = $this->getTransactionData($orderId, $blikCode);

        if (empty($transaction)) {
            $request = $this->sendRequest(
                $serviceId,
                $sharedKey,
                $orderId,
                $amount,
                $currency,
                $customerEmail,
                $blikCode
            );

            $result = $this->validateRequest($request, $orderId, $blikCode);
        } else {
            $result = $this->validateTransaction($transaction, $orderId);
        }

        $result['postOrderId'] = $orderId;

        if ($result['status'] == 'SUCCESS') {
            $result['backUrl'] = $this->context->link->getModuleLink(
                'bluepayment',
                'blik',
                [
                    'OrderID' => $orderId,
                    'PaymentStatus' => $result['status'],
                ],
                true
            );
        }

        return $result;
    }

    private function sendRequest($serviceId, $sharedKey, $orderId, $amount, $currency, $customerEmail, $blikCode)
    {
        $test_mode = Configuration::get($this->module->name_upper . '_TEST_ENV');
        $gateway_mode = $test_mode
            ? \BlueMedia\OnlinePayments\Gateway::MODE_SANDBOX
            : \BlueMedia\OnlinePayments\Gateway::MODE_LIVE;

        $gateway = new \BlueMedia\OnlinePayments\Gateway($serviceId, $sharedKey, $gateway_mode);

        $data = [
            'ServiceID' => $serviceId,
            'OrderID' => $orderId,
            'Amount' => $amount,
            'Description' => 'BLIK Payment',
            'GatewayID' => (string) Gateway::GATEWAY_ID_BLIK,
            'Currency' => $currency,
            'CustomerEmail' => $customerEmail,
            'CustomerIP' => $_SERVER['REMOTE_ADDR'],
            'Title' => 'BLIK Payment',
            'AuthorizationCode' => $blikCode,
            'ScreenType' => 'FULL',
            'PlatformName' => 'PrestaShop',
            'PlatformVersion' => _PS_VERSION_,
            'PlatformPluginVersion' => $this->module->version,
        ];

        $hash = array_merge($data, [$sharedKey]);
        $hash = Helper::generateAndReturnHash($hash);

        $data['Hash'] = $hash;
        $fields = is_array($data) ? http_build_query($data) : $data;

        try {
            $curl = curl_init($gateway::getActionUrl($gateway::PAYMENT_ACTON_PAYMENT));
            curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['BmHeader: pay-bm-continue-transaction-url']);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
            $curlResponse = curl_exec($curl);
            curl_close($curl);
            if ($curlResponse === 'ERROR') {
                return false;
            }

            return simplexml_load_string($curlResponse);
        } catch (Exception $e) {
            Tools::error_log($e);

            return false;
        }
    }

    private function validateRequest($response, $orderId, $blikCode): array
    {
        $array = [];
        $data = [
            'order_id' => pSQL($orderId),
            'blik_code' => pSQL($blikCode),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $query = new DbQuery();
        $query->from('blue_transactions')
            ->where('order_id = ' . (int) $orderId)
            ->select('*');

        $transaction = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query, false);

        if (isset($response->confirmation) && $response->confirmation == 'CONFIRMED') {
            if ($response->paymentStatus == 'PENDING') {
                $array = [
                    'status' => 'PENDING',
                    'message' => $this->module->l('Confirm the operation in your bank\'s application.', 'chargeblik'),
                ];

                $data['blik_status'] = 'PENDING';
                $this->transactionQuery($transaction, $orderId, $data);
            } elseif ($response->paymentStatus == 'SUCCESS') {
                $array = [
                    'status' => 'SUCCESS',
                    'message' => $this->module->l('Payment has been successfully completed.', 'chargeblik'),
                ];

                $data['blik_status'] = 'SUCCESS';
                $this->transactionQuery($transaction, $orderId, $data);
            } else {
                $array = [
                    'status' => 'FAILURE',
                    'message' => $this->module->l('The entered code is not valid.', 'chargeblik'),
                ];
            }
        } elseif (isset($response->confirmation)
            && $response->confirmation == 'NOTCONFIRMED'
            && $response->reason == 'WRONG_TICKET'
        ) {
            $array = [
                'status' => 'FAILURE',
                'message' => $this->module->l('The entered code is not valid.', 'chargeblik'),
            ];
            $data['blik_status'] = 'WRONG_TICKET';
            $this->transactionQuery($transaction, $orderId, $data);
        } elseif (isset($response->confirmation)
            && $response->confirmation == 'NOTCONFIRMED'
            && $response->reason == 'MULTIPLY_PAID_TRANSACTION'
        ) {
            $array = [
                'status' => 'FAILURE',
                'message' => $this->module->l('Your BLIK transaction has already been paid for.', 'chargeblik'),
            ];
            $data['blik_status'] = 'MULTIPLY_PAID_TRANSACTION';
            $this->transactionQuery($transaction, $orderId, $data);
        }

        if (empty($array)) {
            $array = [
                'status' => 'FAILURE',
                'message' => $this->module->l('The code has expired. Try again.', 'chargeblik'),
            ];
        }

        return $array;
    }

    private function transactionQuery($transaction, $orderId, $data)
    {
        if (empty($transaction)) {
            Db::getInstance()->insert('blue_transactions', $data);
        } else {
            unset($data['order_id']);
            Db::getInstance()->update('blue_transactions', $data, 'order_id = ' . (int) $orderId);
        }
    }

    private function validateTransaction($transaction, $orderId): array
    {
        $array = [];
        $transaction = (object) $transaction;

        if (isset($transaction->blik_status) && $transaction->blik_status == 'SUCCESS') {
            $array = [
                'status' => 'SUCCESS',
                'message' => $this->module->l('Payment has been successfully completed.', 'chargeblik'),
            ];
            $data['blik_status'] = 'SUCCESS';
            $this->transactionQuery($transaction, $orderId, $data);
        }
        if (isset($transaction->blik_status) && $transaction->blik_status == 'PENDING') {
            $array = [
                'status' => 'PENDING',
                'message' => $this->module->l('Confirm the operation in your bank\'s application.', 'chargeblik'),
            ];

            if ($transaction->payment_status == 'SUCCESS') {
                $ga = $_COOKIE['_ga'] ?? '';

                $data['blik_status'] = 'SUCCESS';
                $data['gtag_uid'] = $ga;
                $this->transactionQuery($transaction, $orderId, $data);
            }
        }
        if (isset($transaction->created_at)
            && time() >= strtotime('+2 minutes', strtotime($transaction->created_at))
        ) {
            $array = [
                'status' => 'FAILURE',
                'message' => $this->module->l('The BLIK code has expired.', 'chargeblik'),
            ];

            $data['blik_status'] = 'FAILURE';
            $this->transactionQuery($transaction, $orderId, $data);
        }

        if (empty($array)) {
            $array = [
                'status' => 'FAILURE',
                'message' => $this->module->l('The code has expired. Try again.', 'chargeblik'),
            ];
        }

        return $array;
    }

    private function moduleValidateOrder($cartId, $amount, $customer)
    {
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
    }
}
