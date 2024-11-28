<?php
/**
 * Class przelewy24paymentConfirmationModuleFrontController
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24paymentConfirmationModuleFrontController
 */
class Przelewy24paymentConfirmationModuleFrontController extends ModuleFrontController
{
    private $p24Suffix;

    /**
     * Post process.
     */
    public function postProcess()
    {
        $cart = $this->getAndCheckCart();

        if (!$cart
            || (0 === (int) $cart->id_customer) || (0 === (int) $cart->id_address_delivery)
            || (0 === (int) $cart->id_address_invoice) || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        $currency = $this->context->currency;
        $suffix = Przelewy24Helper::getSuffix($currency->iso_code);
        $this->p24Suffix = $suffix;

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $this->tryToRedirect($suffix, $cart);

        if (0 === (int) Configuration::get('P24_VERIFYORDER' . $suffix)) {
            $paymentData = new Przelewy24PaymentData($cart);
            $this->tryVerifyUnpaidOrder($paymentData, $suffix);
        }

        $service = new Przelewy24ServicePaymentReturn(new Przelewy24());
        $data = $service->execute($this->context);

        $przelewy24 = new Przelewy24();
        $protocol = $przelewy24->getProtocol();

        $smarty = Context::getContext()->smarty;
        $data['base_url'] = $protocol . htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8') . __PS_BASE_URI__;
        $smarty->assign($data);
        $this->setTemplate('module:przelewy24/views/templates/front/payment_confirmation.tpl');
    }

    /**
     * Initializes common front page content: header, footer and side columns.
     */
    public function initContent()
    {
        parent::initContent();

        $this->registerStylesheet('p24-style-local', 'modules/przelewy24/views/css/przelewy24.css');
        $this->registerJavascript('p24-script-local', 'modules/przelewy24/views/js/przelewy24.js');
        if ((int) Configuration::get('P24_BLIK_UID_ENABLE') > 0) {
            $this->registerJavascript('p24-script-blik', 'modules/przelewy24/views/js/przelewy24Blik.js');
        }
    }

    private function tryToRedirect($suffix, $cart)
    {
        $ready = $this->validatePostData();
        if (!$ready) {
            /* Nothing to do. */
            return;
        }

        $paymentData = new Przelewy24PaymentData($cart);
        $this->tryVerifyUnpaidOrder($paymentData, $suffix);

        $transactionSupport = new Przelewy24TransactionSupport();
        $description = Przelewy24OrderDescriptionHelper::buildDescriptionConfigured(
            $this->module->l('Order'),
            $this->module->l('Cart'),
            $suffix,
            $paymentData
        );
        $languageIsoCode = $this->context->language->iso_code;
        $method = Tools::getValue('p24_method');
        $regulationAccept = (bool) Tools::getValue('p24_regulation_accept');
        $token = $transactionSupport->registerTransaction(
            $paymentData,
            $description,
            $languageIsoCode,
            $regulationAccept,
            $method
        )->getToken();

        $isTest = (bool) Configuration::get('P24_TEST_MODE' . $suffix);
        $url = Przelewy24Class::getHostForEnvironment($isTest) . 'trnRequest/' . $token;
        Tools::redirectLink($url);
    }

    private function tryVerifyUnpaidOrder(Przelewy24PaymentData $paymentData, $suffix)
    {
        $verifyOrder = (int) Configuration::get('P24_VERIFYORDER' . $suffix);
        if (in_array($verifyOrder, [0, 2]) && !$paymentData->orderExists()) {
            $cart = $paymentData->getCart();
            $idOrderState = Configuration::get('P24_ORDER_STATE_1');
            $total = $paymentData->getTotalAmountWithoutExtraCharge();
            $customer = new Customer($cart->id_customer);
            $this->module->validateOrder(
                (int) $cart->id,
                (int) $idOrderState,
                $total,
                $this->module->displayName,
                null,
                [],
                (int) $cart->id_currency,
                false,
                $customer->secure_key
            );
        }
    }

    private function validatePostData()
    {
        $ready = Tools::getValue('p24_data_ready', false);
        if (!$ready) {
            /* Nothing to validate. */
            return false;
        }

        return true;
    }

    /**
     * Try to get a cart. Do some checks too.
     *
     * @return Cart|null
     */
    private function getAndCheckCart()
    {
        /* The passed value is preferred. */
        $cartId = Tools::getValue('cart_id');
        if ($cartId) {
            $cart = new Cart($cartId);
        } else {
            $cart = $this->context->cart;
        }
        if ((int) $cart->id_customer !== (int) $this->context->customer->id) {
            $cart = null;
        }

        return $cart;
    }
}
