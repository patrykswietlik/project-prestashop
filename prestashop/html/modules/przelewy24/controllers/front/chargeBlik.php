<?php
/**
 * Class przelewy24chargeBlikModuleFrontController
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24chargeBlikModuleFrontController
 */
class Przelewy24chargeBlikModuleFrontController extends ModuleFrontController
{
    /**
     * Init contant and dispatch actions.
     *
     * @throws Exception
     */
    public function initContent()
    {
        parent::initContent();

        $actionName = Tools::getValue('action');
        switch ($actionName) {
            case 'executeBlik':
                $this->executeBlik();
                break;
            default:
                $this->ajaxRender('', self::class, 'unknown');
                exit;
        }
    }

    /**
     * Execute a Blik payment.
     *
     * @return void
     */
    private function executeBlik()
    {
        $success = false;
        $returnUrl = '/';
        $reload = true;

        $cart = $this->tryGetCartFromId();
        if (!$cart) {
            $cart = Context::getContext()->cart;
        }

        $blikCode = $this->getValidBlikCode();
        if (!$blikCode) {
            $reload = false;
        } elseif ($cart && $cart->id) {
            $przelewy24 = new Przelewy24();
            $paymentData = new Przelewy24PaymentData($cart);

            $commonHelper = new Przelewy24Common($przelewy24);
            $commonHelper->validateOrderIfNot1($paymentData);

            $token = $this->registerBlikTransaction($paymentData);
            if ($token) {
                $currencySuffix = $commonHelper->getSuffix($paymentData);
                $restBlik = Przelewy24RestBlikFactory::buildForSuffix($currencySuffix);
                $response = $restBlik->executePaymentByBlikCode($token, $blikCode);

                if (isset($response['data']['orderId']) && $response['data']['orderId']) {
                    $this->context->cookie->id_cart = null;
                    $success = true;
                }
            }

            if ($success) {
                $returnUrl = Przelewy24TransactionSupport::generateReturnUrlForBlik($paymentData);
            } elseif ($paymentData->orderExists()) {
                $returnUrl = Przelewy24TransactionSupport::generateReturnUrlForBlik($paymentData);
            } else {
                $reload = false;
            }
        }

        $data = [
            'success' => $success,
            'returnUrl' => $returnUrl,
            'reload' => $reload,
        ];

        $this->ajaxRender(json_encode($data), self::class, 'executeBlik');
        exit;
    }

    /**
     * Register blik transaction.
     *
     * @return string
     */
    private function registerBlikTransaction(Przelewy24PaymentData $paymentData)
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

        return $transactionSupport->registerTransaction($paymentData, $description, $languageIsoCode)->getToken();
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

    /**
     * Get valid Blik code or null.
     *
     * @return string|null
     */
    private function getValidBlikCode()
    {
        $blikCode = Tools::getValue('blikCode');
        if (preg_match('/^\\d{6}$/', $blikCode)) {
            return $blikCode;
        } else {
            return null;
        }
    }
}
