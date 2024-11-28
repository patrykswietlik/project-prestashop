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

trait AmazonPayBackendHooksTrait
{

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path.'views/js/admin_4.2.0.js');
            $this->context->controller->addCSS($this->_path.'views/css/admin_4.2.0.css');
        } elseif (Tools::getValue('controller') == 'AdminOrders') {
            $this->context->controller->addCSS($this->_path.'views/css/admin_orders.css');
        }
    }

    /**
     * Add CSS & JavaScript in modern PrestaShop Versions correctly in BO.
     */
    public function hookActionAdminControllerSetMedia()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path.'views/js/admin_4.2.0.js');
            $this->context->controller->addCSS($this->_path.'views/css/admin_4.2.0.css');
        }
    }

    /**
     * New hook in PrestaShop 1.7.7.0 - replaces the DisplayAdminOrderContentOrder hook
     *
     * @param $params
     */
    public function hookDisplayAdminOrderTabContent($params)
    {
        if (!isset($params['order'])) {
            if (isset($params['id_order'])) {
                $params['order'] = new Order((int)$params['id_order']);
            }
        }
        return $this->hookDisplayAdminOrderContentOrder($params);
    }

    /**
     * Workarround... is used for handling of Amazon Pay actions, then redirection to avoid doubled form submission
     *
     * @param $params
     */
    public function hookDisplayAdminOrderContentOrder($params)
    {
        if (Tools::getValue('amazonpay_action') != '') {
            $redirect = false;
            $amazonPayOrder = AmazonPayOrder::findByIdOrder($params['order']->id);
            $amazonPayCheckoutSession = new AmazonPayCheckoutSession(
                false,
                $amazonPayOrder->amazon_checkout_session_id
            );
            $chargeinfo = $amazonPayCheckoutSession->getCharge();
            if (isset($chargeinfo['info'])) {
                $chargeinfo = $chargeinfo['info'];
            }

            $is_workarround = false;
            // workarround for SDK V2 bug
            if ($chargeinfo['chargeId'] == '') {
                $chargeinfo['chargeId'] = $amazonPayOrder->amazon_charge_id;
                $chargeinfo['chargePermissionId'] = $amazonPayOrder->amazon_charge_permission_id;
                $currency = new Currency((int)$params['order']->id_currency);
                $charge_amount = Tools::ps_round($params['order']->total_paid, 2);
                $currency_code = $currency->iso_code;
                $is_workarround = true;
            } else {
                if (isset($chargeinfo['chargeAmount']['amount'])) {
                    $charge_amount = $chargeinfo['chargeAmount']['amount'];
                    $currency_code = $chargeinfo['chargeAmount']['currencyCode'];
                } else {
                    $charge_amount = Tools::ps_round($params['order']->total_paid, 2);
                    $currency_code = AmazonPayHelper::getCurrentCurrency();
                }
            }
            $amazonpay_action_status = false;

            switch (Tools::getValue('amazonpay_action')) {
                case 'amazonpay_refund':
                    if (Tools::getValue('amazon_refund_amount') != '') {
                        $refundAmount = (float)str_replace(',', '.', Tools::getValue('amazon_refund_amount'));
                        $amazonpay_action_status = $amazonPayCheckoutSession->refund(
                            $chargeinfo['chargeId'],
                            $refundAmount,
                            $currency_code,
                            $params['order']->reference
                        );
                    }
                    break;
                case 'amazonpay_charge':
                    $amazonpay_action_status = $amazonPayCheckoutSession->capture(
                        $chargeinfo['chargeId'],
                        $charge_amount,
                        $currency_code,
                        $params['order']->reference
                    );
                    if ($amazonpay_action_status) {
                        if (!$is_workarround) {
                            $amazonPayOrder->amazon_charge_id = $chargeinfo['chargePermissionId'];
                        }
                        $amazonPayOrder->save();
                        $amazonPayOrder->setOrderStatus(AmazonPayHelper::getStatus('captured'));
                    }
                    break;
                case 'amazonpay_cancel':
                    if (Tools::getValue('amazon_cancel_reason') == '') {
                        Context::getContext()->controller->errors[] = $this->l('Please enter a cancellation reason');
                    } else {
                        $amazonpay_action_status = $amazonPayCheckoutSession->cancel(
                            $amazonPayOrder->amazon_charge_id,
                            Tools::getValue('amazon_cancel_reason')
                        );
                    }
                    break;
                case 'amazonpay_close':
                    if (Tools::getValue('amazon_close_reason') == '') {
                        Context::getContext()->controller->errors[] = $this->l('Please enter a close reason');
                    } else {
                        $amazonpay_action_status = $amazonPayCheckoutSession->close(
                            $amazonPayOrder->amazon_charge_permission_id,
                            Tools::getValue('amazon_close_reason')
                        );
                    }
                    break;
            }
            if (isset($amazonpay_action_status) && $amazonpay_action_status) {
                $redirect = true;
            } else {
                Context::getContext()->controller->errors[] = $amazonPayCheckoutSession->getLastMessage();
            }
            if ($redirect) {
                Tools::redirectAdmin(
                    $this->getAdminLink(
                        'AdminOrders',
                        true,
                        array(),
                        array(
                            'vieworder' => '',
                            'id_order' => $params['order']->id
                        )
                    ) . '#amazonpay_transactions'
                );
            }
        }
    }

    /**
     * Workarround PS 1.7.7.0
     *
     * @param $params
     * @return mixed
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayAdminOrder($params)
    {
        return $this->hookAdminOrder($params);
    }

    /**
     * Build Amazon Pay Transactions overview and actions in order view
     *
     * @param $params
     * @return mixed
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookAdminOrder($params)
    {
        if ($this->isReadyForFrontend()) {
            $this->context->smarty->assign('isHigher176', AmazonPay::isPrestaShop177OrHigherStatic());
            $order = new Order($params['id_order']);
            if ($order->module == $this->name) {
                $amazonPayOrder = AmazonPayOrder::findByIdOrder($params['id_order']);
                if ($amazonPayOrder) {
                    $transactions = AmazonPayTransaction::getByCheckoutSession($amazonPayOrder->amazon_checkout_session_id);
                    if ($this->dynamicRefresh($amazonPayOrder, $transactions)) {
                        $transactions = AmazonPayTransaction::getByCheckoutSession($amazonPayOrder->amazon_checkout_session_id);
                    }
                    $adminActionsHelper = new AmazonPayAdminActionsHelper($transactions);
                    $this->context->smarty->assign(
                        'transactions',
                        $transactions
                    );
                    $this->context->smarty->assign('OrderObj', $order);
                    $this->context->smarty->assign(
                        'amazon_form_action',
                        $this->getAdminLink(
                            'AdminOrders',
                            true,
                            array(),
                            array(
                                'vieworder' => '',
                                'id_order' => $params['id_order']
                            )
                        )
                    );
                    $this->context->smarty->assign(
                        'amazon_actions',
                        $adminActionsHelper->getSummary()
                    );
                    return $this->display($this->this_file, 'views/templates/admin/skeleton/transactions.tpl');
                }
            }
        }
    }

    /**
     * @param AmazonPayOrder $amazonPayOrder
     * @param array $transactions
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function dynamicRefresh(AmazonPayOrder $amazonPayOrder, array $transactions)
    {
        if (sizeof($transactions) == 0) {
            return;
        }
        $transaction = false;
        $refresh = true;
        $refresh_refund = false;
        foreach ($transactions as $t) {
            if (!$transaction) {
                $transaction = $t;
            }
            if ($t['transaction_type'] == 'capture' || $t['transaction_type'] == 'cancel' || $t['transaction_type'] == 'close') {
                $refresh = false;
            }
            if ($t['transaction_type'] == 'refund_pending') {
                $refresh_refund = $t;
            }
        }
        if ($refresh) {
            $amazonPayCheckoutSession = new AmazonPayCheckoutSession(
                false,
                $transaction['amazon_checkout_session']
            );
            $chargeinfo = $amazonPayCheckoutSession->getCharge();

            AmazonPayLogger::getInstance()->addLog(
                'Refreshing Transaction ' . $amazonPayCheckoutSession->getAmazonPayCheckoutSessionId(),
                3,
                false,
                $chargeinfo
            );

            if (isset($chargeinfo['info']['statusDetails']['state'])) {
                switch ($chargeinfo['info']['statusDetails']['state']) {
                    case 'Declined':
                        AmazonPayCronHandler::handleDeclinedTransaction(
                            $amazonPayOrder,
                            $transaction,
                            $chargeinfo
                        );
                        break;
                    case 'Authorized':
                        AmazonPayCronHandler::handleAuthorizedTransaction(
                            $amazonPayOrder,
                            $amazonPayCheckoutSession,
                            $chargeinfo
                        );
                        break;
                }
            }
            return true;
        }
        if ($refresh_refund) {
            $amazonPayCheckoutSession = new AmazonPayCheckoutSession(
                false,
                $transaction['amazon_checkout_session']
            );
            $refundinfo = $amazonPayCheckoutSession->getRefund($t['amazon_transaction']);
            if (isset($refundinfo['statusDetails']['state'])) {
                switch ($refundinfo['statusDetails']['state']) {
                    case 'Declined':
                        AmazonPayCronHandler::handleDeclinedRefund(
                            $amazonPayOrder,
                            $transaction,
                            $refundinfo
                        );
                        break;
                    case 'Refunded':
                        AmazonPayCronHandler::handleRefundedRefund(
                            $amazonPayOrder,
                            $amazonPayCheckoutSession,
                            $refundinfo
                        );
                        break;
                }
            }
        }
        return false;
    }

    /**
     * @param $params
     * @return bool|void
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookActionAdminOrdersTrackingNumberUpdate($params)
    {
        if (Configuration::get('AMAZONPAY_ALEXA_DELIVERY_NOTIFICATIONS') && Configuration::get('AMAZONPAY_LIVEMODE')) {
            $q = 'SELECT ` amazon_charge_permission_id ` FROM `'
                . _DB_PREFIX_ . 'amazonpay_orders` WHERE `id_order` = ' . (int)$params['order']->id;
            $r = Db::getInstance()->getRow($q);
            if ($r[' amazon_charge_permission_id ']) {
                $carrier_id = $params['order']->getIdOrderCarrier();
                $order_carrier = new OrderCarrier((int)$carrier_id);
                $carrier = new Carrier((int)$order_carrier->id_carrier);
                if ($carrier_code = $this->getMappedCarrier((int)$order_carrier->id_carrier, (int)$carrier->id_reference)) {
                    $shipping_number = $params['order']->getWsShippingNumber();
                    $payload = array(
                        'amazonOrderReferenceId' => $r[' amazon_charge_permission_id '],
                        'externalOrderId' => $params['order']->reference,
                        'deliveryDetails' => array(array(
                            'trackingNumber' => $shipping_number,
                            'carrierCode' => $carrier_code
                        ))
                    );
                    try {
                        $client = AmazonPayHelper::getClient();
                        $result = $client->deliveryTrackers($payload);
                        if ($result['status'] === 200) {
                            return true;
                        } else {
                            AmazonPayLogger::getInstance()->addLog(
                                'Alexa Notification Error',
                                2,
                                false,
                                $result
                            );
                        }
                    } catch (\Exception $e) {
                        AmazonPayLogger::getInstance()->addLog(
                            'Alexa Notification Exception',
                            2,
                            $e
                        );
                    }
                }
            }
            return;
        }
    }

    /**
     * @param $params
     * @throws PrestaShopDatabaseException
     */
    public function hookDisplayBackOfficeFooter($params)
    {
        if (AmazonPayHelper::captureAtShipping()) {
            if ((int)Configuration::get('AMAZONPAY_SHIPPING_STATUS_ID') > 0) {
                $q = 'SELECT DISTINCT
                         ao.id_order,
                         ao.amazon_checkout_session_id,
                         ao.amazon_charge_permission_id
                    FROM ' . _DB_PREFIX_ . 'orders o
                    JOIN ' . _DB_PREFIX_ . 'amazonpay_orders ao ON o.id_order = ao.id_order
			        JOIN ' . _DB_PREFIX_ . 'amazonpay_transactions AS a1 ON (ao.amazon_charge_permission_id = a1.amazon_transaction AND a1.transaction_type = \'chargePermission\')
			       WHERE
			             ao.amazon_charge_id = \'\'
			         AND
			             o.current_state = \'' . pSQL(Configuration::get('AMAZONPAY_SHIPPING_STATUS_ID')) . '\'';
                $rs = Db::getInstance()->ExecuteS($q);
                foreach ($rs as $r) {
                    try {
                        $amazonPayOrder = AmazonPayOrder::findByIdOrder((int)$r['id_order']);
                        $regularOrder = new Order((int)$r['id_order']);
                        $amazonPayCheckoutSession = new AmazonPayCheckoutSession(
                            false,
                            $amazonPayOrder->amazon_checkout_session_id
                        );
                        $chargeinfo = $amazonPayCheckoutSession->getCharge();
                        $charge_amount = $chargeinfo['info']['chargeAmount']['amount'];
                        $currency_code = $chargeinfo['info']['chargeAmount']['currencyCode'];

                        $amazonpay_action_status = $amazonPayCheckoutSession->capture(
                            $chargeinfo['chargeId'],
                            $charge_amount,
                            $currency_code,
                            $regularOrder->reference
                        );
                        if ($amazonpay_action_status) {
                            $amazonPayOrder->amazon_charge_id = $chargeinfo['chargeId'];
                            $amazonPayOrder->save();
                            $amazonPayOrder->setOrderStatus(AmazonPayHelper::getStatus('captured'));
                        }
                    } catch (Exception $e) {
                        AmazonPayLogger::getInstance()->addLog(
                            'Automatic capture after shipping',
                            2,
                            $e
                        );
                    }
                }
            }
        }
    }
}
