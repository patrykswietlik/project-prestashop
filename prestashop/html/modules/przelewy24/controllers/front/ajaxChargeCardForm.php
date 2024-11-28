<?php
/**
 * Class przelewy24ajaxChargeCardFormModuleFrontController
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/gpl.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24ajaxChargeCardFormModuleFrontController
 */
class Przelewy24ajaxChargeCardFormModuleFrontController extends ModuleFrontController
{
    /**
     * Init content.
     */
    public function initContent()
    {
        parent::initContent();

        try {
            $response = $this->doChargeCard();
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                'przelewy24ajaxChargeCardFormModuleFrontController - ' .
                json_encode(['exception' => $e->getMessage()]),
                1
            );

            $response = [];
        }
        exit(json_encode($response));
    }

    /**
     * Charges card.
     *
     * @return array
     *
     * @throws Exception
     */
    private function doChargeCard()
    {
        $cartId = (int) Tools::getValue('cartId', 0);

        $przelewy24 = new Przelewy24();

        if ('cardCharge' !== Tools::getValue('action') || $cartId < 0) {
            throw new Exception($przelewy24->getLangString('Invalid request'));
        }

        /** @var $cart \PrestaShop\PrestaShop\Adapter\Entity\Order */
        $cart = new Cart($cartId);

        if (!$cart) {
            throw new Exception($przelewy24->getLangString('Invalid cart ID'));
        }
        $przelewy24ServicePaymentData = new Przelewy24PaymentData($cart);
        $orderId = $przelewy24ServicePaymentData->getFirstOrderId();
        $order = new Order($orderId);
        // products cart
        $productsInfo = [];
        $extracharge = 0;
        if ($orderId && 'przelewy24' === $order->module) {
            $currency = new Currency($order->id_currency);
            $suffix = Przelewy24Helper::getSuffix($currency->iso_code);
            $shipping = $cart->getPackageShippingCost((int) $order->id_carrier) * 100;
            $amount = Przelewy24Helper::p24AmountFormat($przelewy24ServicePaymentData->getTotalAmountWithExtraCharge());
            $products = $przelewy24ServicePaymentData->getProducts();
            foreach ($products as $product) {
                if (!is_array($product)) {
                    $product = [];
                }
                $product = array_merge([
                    'product_name' => 'error',
                    'description_short' => '',
                    'product_quantity' => 0,
                    'unit_price_tax_incl' => 0,
                    'number' => 'error',
                ], $product);
                $productsInfo[] = [
                    'name' => $product['product_name'],
                    'description' => $product['description_short'],
                    'quantity' => (int) $product['product_quantity'],
                    'price' => null !== $product['unit_price_tax_incl']
                        ? (int) round($product['unit_price_tax_incl'] * 100) : null,
                    'number' => (string) $product['id_product'],
                ];
            }
            $customerId = $order->id_customer;
        } else {
            $currency = new Currency($cart->id_currency);
            $suffix = Przelewy24Helper::getSuffix($currency->iso_code);

            $shipping = $cart->getPackageShippingCost((int) $cart->id_carrier) * 100;
            $amount = Przelewy24Helper::p24AmountFormat($cart->getOrderTotal(true, Cart::BOTH));

            $servicePaymentOptions = new Przelewy24ServicePaymentOptions(new Przelewy24());
            $extracharge = $servicePaymentOptions->getExtracharge(
                $cart->getOrderTotal(true, Cart::BOTH),
                $suffix
            ) * 100;

            $products = $cart->getProducts();
            foreach ($products as $product) {
                if (!is_array($product)) {
                    $product = [];
                }
                $product = array_merge([
                    'name' => 'error',
                    'description_short' => '',
                    'cart_quantity' => 0,
                    'price' => 0,
                    'id_product' => 'error',
                ], $product);
                $productsInfo[] = [
                    'name' => $product['name'],
                    'description' => $product['description_short'],
                    'quantity' => (int) $product['cart_quantity'],
                    'price' => null !== $product['price'] ? (int) round($product['price'] * 100) : null,
                    'number' => (int) $product['id_product'],
                ];
            }
            $customerId = $cart->id_customer;
        }

        $lang = (new Przelewy24())->getLangArray();
        $description = Przelewy24OrderDescriptionHelper::buildDescription(
            $this->module->l('Order'),
            $suffix,
            new Przelewy24PaymentData($cart),
            $lang['Cart'] . " {$cart->id}"
        );
        $customer = new Customer($customerId);

        if (empty($this->context->customer->id) || empty($cart->id_customer)
            || (int) $this->context->customer->id !== (int) $cart->id_customer) {
            if (empty($cart->id_customer) || !$customer) {
                throw new Exception($przelewy24->getLangString('Order not exist for this customer'));
            }
        }

        $my_currency_iso_code = $currency->iso_code;
        $suffix = Przelewy24Helper::getSuffix($my_currency_iso_code);
        $p24_session_id = $cart->id . '|' . hash('sha224', rand());

        $addressHelper = new Przelewy24AddressHelper($cart);
        $address = new Address((int) $addressHelper->getBillingAddress()['id_address']);

        $s_lang = new Country((int) $address->id_country);
        $iso_code = $this->context->language->iso_code;

        $commonUrlArgs = $orderId ? ['id_order' => $orderId] : ['id_cart' => $cartId];
        $url_status = $this->context->link->getModuleLink(
            'przelewy24',
            'paymentStatus',
            $commonUrlArgs,
            '1' === (string) Configuration::get('PS_SSL_ENABLED')
        );

        $urlReturn = Przelewy24TransactionSupport::generateReturnUrl($przelewy24ServicePaymentData);

        $restApi = Przelewy24RestTransactionFactory::buildForSuffix($suffix);

        if ($extracharge > 0) {
            $amount += $extracharge;
        }

        $payload = new Przelewy24PayloadForRestTransaction();
        $payload->merchantId = (int) Configuration::get('P24_MERCHANT_ID' . $suffix);
        $payload->posId = (int) Configuration::get('P24_SHOP_ID' . $suffix);
        $payload->sessionId = (string) $p24_session_id;
        $payload->amount = (int) $amount;
        $payload->currency = (string) $my_currency_iso_code;
        $payload->description = (string) $description;
        $payload->email = (string) $customer->email;
        $payload->client = $customer->firstname . ' ' . $customer->lastname;
        $payload->address = $address->address1 . ' ' . $address->address2;
        $payload->zip = (string) $address->postcode;
        $payload->city = (string) $address->city;
        $payload->country = (string) $s_lang->iso_code;
        $payload->language = Tools::strtolower($iso_code);
        $payload->urlReturn = (string) $urlReturn;
        $payload->urlStatus = (string) $url_status;
        $payload->shipping = (int) $shipping;

        $p24ProductRest = new Przelewy24ProductRest();
        $p24ProductRest->prepareCartItemsRest($payload, $productsInfo);

        $token = $restApi->registerRawToken($payload);

        if ($token) {
            $testMode = (bool) Configuration::get('P24_TEST_MODE' . $suffix);
            $host = Przelewy24Class::getHostForEnvironment($testMode);

            return [
                'p24jsURL' => $host . 'inchtml/card/register_card_and_pay/ajax.js?token=' . $token,
                'p24cssURL' => $host . 'inchtml/card/register_card_and_pay/ajax.css',
                'p24_sign' => $payload->sign,
                'sessionId' => $p24_session_id,
                'client_id' => $customer->id,
            ];
        }

        throw new Exception($przelewy24->getLangString('Failed transaction registration in Przelewy24'));
    }
}
