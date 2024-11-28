<?php
/**
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24chargeCardExtModuleFrontController
 */
class Przelewy24chargeCardExtModuleFrontController extends ModuleFrontController
{
    /**
     * Init constant and dispatch actions.
     *
     * @throws Exception
     */
    public function initContent()
    {
        parent::initContent();
        if ('register' !== Tools::getValue('action')) {
            $this->ajaxRender('', self::class, 'unknown');
            exit;
        }
        $paymentData = new Przelewy24PaymentData($this->tryGetCartFromId() ?: Context::getContext()->cart);
        $result = $this->registerCardTransaction($paymentData, $this->getValidMethod());

        $this->ajaxRender(json_encode([
            'script' => sprintf('%s%s', $this->getAjaxUrl($paymentData->getCurrency()), $result->getToken()),
            'signature' => $result->getSignature(),
        ]), self::class, 'register');
        exit;
    }

    /**
     * Register blik transaction.
     *
     * @return Przelewy24TransactionRegistrationResult
     */
    private function registerCardTransaction(Przelewy24PaymentData $paymentData, int $method)
    {
        $currency = $paymentData->getCurrency();
        $suffix = ('PLN' === $currency->iso_code) ? '' : '_' . $currency->iso_code;
        $description = Przelewy24OrderDescriptionHelper::buildDescriptionConfigured(
            $this->module->l('Order'),
            $this->module->l('Cart'),
            $suffix,
            $paymentData
        );

        $transactionSupport = new Przelewy24TransactionSupport();
        $languageIsoCode = $this->context->language->iso_code;

        return $transactionSupport->registerTransaction(
            $paymentData,
            $description,
            $languageIsoCode,
            true,
            $method
        );
    }

    /**
     * Get cart based on id in post.
     *
     * @return Cart|null
     */
    private function tryGetCartFromId()
    {
        if (!Tools::getValue('cartId')) {
            return null;
        }

        $cartId = (int) Tools::getValue('cartId');
        $cart = new Cart($cartId);
        if (!$cart->id) {
            return null;
        }

        $customer = $this->context->customer;
        if (!Przelewy24Tools::checkCartForCustomer($customer, $cart)) {
            return null;
        }

        return $cart;
    }

    private function getValidMethod()
    {
        $method = (int) Tools::getValue('method');
        $validMethods = Przelewy24OneClickHelper::getCardPaymentIds();
        if (in_array($method, $validMethods)) {
            return $method;
        } else {
            return 0;
        }
    }

    /**
     * @param CurrencyCore $currency
     *
     * @return string
     */
    private function getAjaxUrl($currency): string
    {
        $p24 = Przelewy24ClassInterfaceFactory::getForSuffix(Przelewy24Helper::getSuffix($currency->iso_code));

        return sprintf('%s%s', $p24->getHost(), 'inchtml/ajaxPayment/ajax.js?token=');
    }
}
