<?php
/**
 * Class Przelewy24ServicePaymentReturn
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24ServicePaymentReturn
 *
 * The name of class is misleading. It is for payment confirmation.
 */
class Przelewy24ServicePaymentReturn extends Przelewy24Service
{
    /**
     * Payment cache.
     *
     * @var Przelewy24CachedPaymentList
     */
    private $paymentCache;

    private function prepBigRestApi($suffix)
    {
        $restApi = Przelewy24RestBigFactory::buildForSuffix($suffix);
        $this->paymentCache = Przelewy24CachedPaymentListFactory::buildForSuffix($suffix);
        $restApi->setPaymentCache($this->paymentCache);

        return $restApi;
    }

    /**
     * hookPaymentReturn implementation.
     *
     * @param array $params
     *
     * @return array|bool updated array of parameters is returned otherwise
     *
     * @throws Exception
     */
    public function execute($params)
    {
        if (!$this->getPrzelewy24()->active) {
            return false;
        }
        $lang = $this->getPrzelewy24()->getLangArray();
        if (is_array($params) && isset($params['order'])) {
            /* Legacy variable. We should use only $cart. */
            $tmpOrder = $params['order'];
            $cart = Cart::getCartByOrderId($tmpOrder->id);
            unset($tmpOrder);
        } elseif (isset($params->cart)) {
            $cart = $params->cart;
        }

        $przelewy24ServicePaymentData = new Przelewy24PaymentData($cart);

        $this->getPrzelewy24()->getSmarty()->assign(
            'logo_url',
            $this->getPrzelewy24()->getPathUri() . 'views/img/logo.png'
        );

        $s_sid = hash('sha224', rand());
        $servicePaymentOptions = new Przelewy24ServicePaymentOptions(new Przelewy24());

        $reference = '';
        $status = 'ok';
        $productsInfo = [];
        $amount = Przelewy24Helper::p24AmountFormat($przelewy24ServicePaymentData->getTotalAmountWithExtraCharge());
        $przelewy24ModuleName = $this->getPrzelewy24()->name;
        if ($przelewy24ServicePaymentData->orderExists()
            && $przelewy24ServicePaymentData->isModuleOfOrderMatched($przelewy24ModuleName)
        ) {
            $shipping = 0;
            $amountTemp = 0;
            foreach ($przelewy24ServicePaymentData->getAllOrders() as $orderPaid) {
                if ($this->isStateBroken($orderPaid)) {
                    /* The state is broken. For historical reasons it has to be changed. */
                    $orderPaid->setCurrentState((int) Configuration::get('P24_ORDER_STATE_1'));
                }

                $status = ($orderPaid->hasInvoice()) ? 'payment' : 'ok';
                $reference = $orderPaid->reference;
                $shipping += $cart->getPackageShippingCost((int) $orderPaid->id_carrier) * 100;
                $amountTemp += $orderPaid->total_paid;
                $products = $orderPaid->getProducts();
                foreach ($products as $value) {
                    $product = new Product($value['product_id']);
                    $productsInfo[] = [
                        'name' => is_array($product->name) ? array_values($product->name)[0] : $product->name,
                        'description' => $product->description_short[1],
                        'quantity' => (int) $value['product_quantity'],
                        'price' => (int) round($value['product_price'] * 100),
                        'number' => $value['product_id'],
                    ];
                }

                $customerId = $orderPaid->id_customer;
            }
            $IdLang = $cart->id_lang;
            $currency = $przelewy24ServicePaymentData->getCurrency();
            $extracharge = $servicePaymentOptions->getExtrachargeOrder(
                $przelewy24ServicePaymentData->getFirstOrderId()
            );
            $currencySign = $currency->sign;
            $totalToPay = Tools::displayPrice(
                $przelewy24ServicePaymentData->getTotalAmountWithExtraCharge(),
                $currency,
                false
            );
        } else {
            $currency = new Currency($cart->id_currency);
            $shipping = $cart->getPackageShippingCost((int) $cart->id_carrier) * 100;
            $products = $cart->getProducts();
            $productsInfo = [];
            foreach ($products as $product) {
                $productsInfo[] = [
                    'name' => isset($product['name']) ? $product['name'] : '',
                    'description' => $product['description_short'],
                    'quantity' => (int) $product['cart_quantity'],
                    'price' => (int) round($product['price'] * 100),
                    'number' => $product['id_product'],
                ];
            }
            $customerId = $cart->id_customer;
            $IdLang = $cart->id_lang;

            $suffix = Przelewy24Helper::getSuffix($currency->iso_code);
            $extracharge = $servicePaymentOptions->getExtraCharge(
                $cart->getOrderTotal(true, Cart::BOTH),
                $suffix
            );
            $totalToPay = Tools::displayPrice(
                $cart->getOrderTotal(true, Cart::BOTH) + $extracharge,
                $currency,
                false
            );
            if (0 === (int) $amount) {
                $status = 'payment';
            }

            $currencySign = $currency->sign;
        }
        $description = Przelewy24OrderDescriptionHelper::buildDescription(
            $lang['Order'],
            Przelewy24Helper::getSuffix($currency->iso_code),
            $przelewy24ServicePaymentData,
            $lang['Cart'] . " {$cart->id}"
        );

        $suffix = Przelewy24Helper::getSuffix($currency->iso_code);

        $p24Class = Przelewy24ClassInterfaceFactory::getForSuffix($suffix);
        $restApi = $this->prepBigRestApi($suffix);

        $sessionId = $cart->id . '|' . $s_sid;
        $customer = new Customer((int) $customerId);

        $translations = [
            'virtual_product_name' => $lang['Extra charge [VAT and discounts]'],
            'cart_as_product' => $lang['Your order'],
        ];
        $p24Product = new Przelewy24Product($translations);
        $p24ProductItems = $p24Product->prepareCartItems($amount, $productsInfo, $shipping);
        $addressHelper = new Przelewy24AddressHelper($cart);

        $addressObj = $addressHelper->getBillingAddress();
        $address = new Address((int) $addressObj['id_address']);
        $s_lang = new Country((int) $address->id_country);

        $acceptInShop = (bool) Configuration::get('P24_ACCEPTINSHOP_ENABLE' . $suffix);

        $currentYear = (int) date('Y');

        $data = [
            'p24_session_id' => $sessionId,
            'p24_merchant_id' => Configuration::get('P24_MERCHANT_ID' . $suffix),
            'p24_pos_id' => Configuration::get('P24_SHOP_ID' . $suffix),
            'p24_email' => $customer->email,
            'p24_address' => $address->address1 . ' ' . $address->address2,
            'p24_zip' => $address->postcode,
            'p24_city' => $address->city,
            'p24_country' => $s_lang->iso_code,
            'p24_amount' => Przelewy24Helper::p24AmountFormat(
                $przelewy24ServicePaymentData->getTotalAmountWithExtraCharge()
            ),
            'p24_currency' => $currency->iso_code,
            'shop_name' => $this->getPrzelewy24()->getContext()->shop->name,
            'p24_description' => $description,
            'cartId' => $cart->id,
            'status' => $status,
            'p24_url' => $p24Class->trnDirectUrl(),
            'p24_url_status' => $this->getPrzelewy24()->getContext()->link->getModuleLink(
                'przelewy24',
                'paymentStatus',
                ['id_cart' => $cart->id],
                '1' === Configuration::get('PS_SSL_ENABLED')
            ),
            'p24_url_return' => Przelewy24TransactionSupport::generateReturnUrl($przelewy24ServicePaymentData),
            'p24_api_version' => P24_VERSION,
            'p24_ecommerce' => 'prestashop_' . _PS_VERSION_,
            'p24_ecommerce2' => Configuration::get('P24_PLUGIN_VERSION'),
            'p24_language' => Tools::strtolower(Language::getIsoById($IdLang)),
            'p24_client' => $customer->firstname . ' ' . $customer->lastname,
            'p24ProductItems' => $p24ProductItems,
            'p24_wait_for_result' => 0,
            'p24_shipping' => $shipping,
            'total_to_pay' => $totalToPay,
            'customer_is_guest' => (int) $customer->is_guest,
            'logo_url' => $this->getPrzelewy24()->getPathUri() . 'views/img/logo.png',
            'validationRequired' => Configuration::get('P24_VERIFYORDER' . $suffix),
            'accept_in_shop' => $acceptInShop,
            'p24_blik_inside_enable' => (bool) Configuration::get('P24_BLIK_INSIDE_ENABLE' . $suffix),
            'p24_card_inside_enable' => (bool) Configuration::get('P24_PAY_CARD_INSIDE_ENABLE' . $suffix),
            'p24_blik_needs_term_accept' => !$acceptInShop,
            'p24_card_needs_term_accept' => !$acceptInShop,
            'p24_charge_blik_url' => $this->getPrzelewy24()->getContext()->link->getModuleLink(
                'przelewy24',
                'chargeBlik',
                []
            ),
            'p24_charge_card_url' => $this->getPrzelewy24()->getContext()->link->getModuleLink(
                'przelewy24',
                'chargeCardExt',
                []
            ),
            'p24_card_translation' => $this->getPrzelewy24()->getCardLangArray(),
            'p24_card_ids_string' => implode(',', Przelewy24OneClickHelper::getCardPaymentIds()),
            'p24_card_years' => range($currentYear, $currentYear + 10),
            'p24_page_type' => 'payment',
            'p24_cart_id' => (int) $cart->id,
            'may_skip' => (bool) Configuration::get('P24_SKIP_CONFIRMATION_ENABLE'),
            'form_action' => $this->getPrzelewy24()->getContext()->link->getModuleLink(
                'przelewy24',
                'paymentConfirmation'
            ),
            'p24_failure_url' => $this->getPrzelewy24()->getContext()
                ->link->getModuleLink('przelewy24', 'paymentFailed'),
            'p24_success_url' => Przelewy24TransactionSupport::generateReturnUrl($przelewy24ServicePaymentData, true),
        ];

        $data['p24_paymethod_graphics'] = Configuration::get('P24_GRAPHICS_PAYMENT_METHOD_LIST' . $suffix);
        $data['reference'] = $reference;
        $paymentMethod = (int) Tools::getValue('payment_method');
        if ($paymentMethod > 0 && Configuration::get('P24_PAYMENT_METHOD_CHECKOUT_LIST' . $suffix)) {
            $paymentMethod = (int) Tools::getValue('payment_method');
            $promotePaymethodList = $restApi->getPromotedPaymentListForConsumer($currency->iso_code);
            if (!empty($promotePaymethodList['p24_paymethod_list_promote'])
                && !empty($promotePaymethodList['p24_paymethod_list_promote'][$paymentMethod])) {
                $data['payment_method_selected_name'] =
                    $promotePaymethodList['p24_paymethod_list_promote'][$paymentMethod];
            } else {
                $paymentMethod = 0; // not available method
            }
        }

        if ((int) $paymentMethod == 303 && (string) $currency->iso_code == 'PLN'
            && (int) $data['p24_amount'] >= 100 && (int) $data['p24_amount'] <= 50000) {
            $data['p24_channel'] = 2048;
        }

        $data['payment_method_selected_id'] = $paymentMethod;
        $data['card_remember_input'] = false;
        $data['remember_customer_cards'] = Przelewy24CustomerSetting::initialize($customer->id)->card_remember;
        $data['p24_one_click'] = false;

        // oneClick
        if (Przelewy24OneClickHelper::isOneClickEnable($suffix)) {
            if (0 === $paymentMethod || in_array($paymentMethod, Przelewy24OneClickHelper::getCardPaymentIds())) {
                $data['card_remember_input'] = true;
            }

            $data['p24_one_click'] = true;
            $data['p24_ajax_notices_url'] = $this->getPrzelewy24()->getContext()->link->getModuleLink(
                'przelewy24',
                'ajaxNotices',
                ['card_remember' => 1],
                '1' === Configuration::get('PS_SSL_ENABLED')
            );
            $data['customer_cards'] = Przelewy24Recurring::findArrayByCustomerId($customer->id);
            $data['charge_card_url'] = $this->getPrzelewy24()->getContext()->link->getModuleLink(
                'przelewy24',
                'chargeCard',
                ['id_cart' => (int) $cart->id]
            );
        }
        $data['card_ids'] = Przelewy24OneClickHelper::getCardPaymentIds();

        if ($paymentMethod) {
            $data['p24_paymethod_list_exists'] = false;
        } else {
            $data['p24_paymethod_list_exists'] = Configuration::get('P24_PAYMENT_METHOD_CONFIRM_LIST' . $suffix);
        }

        if (Configuration::get('P24_PAYMENT_METHOD_CONFIRM_LIST' . $suffix)) {
            $data += $this->getMethodsForValue($restApi, $currency->iso_code, $data['p24_amount']);
            $data['p24_paymethod_description'] = $restApi->replacePaymentDescriptionsListToOwn(
                $restApi->availablePaymentMethodsForConsumer($currency->iso_code),
                $suffix
            );
        }

        $data['p24_method'] = false;
        $data['extracharge'] = $extracharge;
        $data['extrachargeFormatted'] = number_format($extracharge, 2, ',', ' ');
        $data['currencySign'] = $currencySign;
        $lang = Context::getContext()->language->iso_code;
        $data['payment_methods_map'] = $this->paymentCache->getList($lang);
        $data['payment_methods_extra'] = [
            Przelewy24Overdrive298::ID => Przelewy24Overdrive298::getExtraData($this->getPrzelewy24()),
        ];

        return $data;
    }

    public function canBePaid($params)
    {
        if (is_array($params) && isset($params['order'])) {
            $order = $params['order'];
            assert($order instanceof OrderCore);
            $state = $order->getCurrentOrderState();
            if ($state->paid) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all methods that can be chosen.
     *
     * @param Przelewy24RestBig $restApi The api
     * @param string $currencyCode Currency ISO code
     * @param int $totalToPay Total to pay
     * @return array
     */
    private function getMethodsForValue(Przelewy24RestBig $restApi, $currencyCode, $totalToPay)
    {
        // payments method list and order
        $paymethodList = $restApi->getFirstAndSecondPaymentListForConsumer($currencyCode);
        $data['p24_paymethod_list_first'] = $paymethodList['p24_paymethod_list_first'];
        $data['p24_paymethod_list_second'] = $paymethodList['p24_paymethod_list_second'];

        $totalToPay = (int) round($totalToPay);
        if ($totalToPay > 500000) {
            unset($data['p24_paymethod_list_first'][Przelewy24RestBig::PAY_PO_ID]);
            unset($data['p24_paymethod_list_second'][Przelewy24RestBig::PAY_PO_ID]);
        }

        return $data;
    }

    /**
     * Check if state of order is broken and should be changed.
     *
     * @param Order $order An order to check
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    private function isStateBroken(Order $order)
    {
        if ((int) $order->current_state === (int) Configuration::get('P24_ORDER_STATE_1')) {
            /* Expected status for pending payment. */
            return false;
        } elseif ((int) $order->current_state === (int) Configuration::get('P24_ORDER_STATE_2')) {
            /* Expected status for completed payment. */
            return false;
        }

        /* The custom status was set. */
        $stateObj = new OrderState($order->getCurrentState());
        if ($stateObj->paid) {
            /* Expected custom status for paid orders. */
            return false;
        } elseif ($stateObj->logable) {
            /* Unexpected, but do not change status of processed orders. */
            return false;
        } elseif ($stateObj->invoice) {
            /* Unexpected, but do not change status of orders with invoices. */
            return false;
        } else {
            return true;
        }
    }
}
