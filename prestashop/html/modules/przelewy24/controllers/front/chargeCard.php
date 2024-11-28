<?php
/**
 * Class przelewy24chargeCardModuleFrontController
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24chargeCardModuleFrontController
 */
class Przelewy24chargeCardModuleFrontController extends ModuleFrontController
{
    /**
     * Init content.
     *
     * @throws Exception
     */
    public function initContent()
    {
        parent::initContent();

        /* If request is valid, the $redirect will be overwritten. */
        $redirect = '/';

        $toolsIdCart = (int) Tools::getValue('id_cart');
        $toolsP24CardCustomerId = (int) Tools::getValue('p24_card_customer_id');

        if (!empty($toolsIdCart) && !empty($toolsP24CardCustomerId)) {
            $cartId = (int) Tools::getValue('id_cart');
            /** @var $order \PrestaShop\PrestaShop\Adapter\Entity\Order */
            $cart = new Cart($cartId);
            $currency = new Currency($cart->id_currency);
            $suffix = Przelewy24Helper::getSuffix($currency->iso_code);
            $customer = new Customer((int) $cart->id_customer);

            $cardId = (int) Tools::getValue('p24_card_customer_id');

            $creditCards = Przelewy24Recurring::findByCustomerId($customer->id);

            if (is_array($creditCards) && !empty($creditCards)) {
                foreach ($creditCards as $creditCard) {
                    if (isset($creditCard->id) && $cardId === (int) $creditCard->id) {
                        $refId = $creditCard->reference_id;
                        $token = $this->getTokenForCardTransaction($refId, $cart, $suffix);

                        if ($token) {
                            $redirect = $this->doCardTransactionForToken($token, $suffix);
                        } else {
                            $redirect = $this->getUrlForFailedTransaction($cart);
                        }
                        break;
                    }
                }
            }
        }

        Tools::redirect($redirect);
    }

    /**
     * Get token for card transaction.
     *
     * @param string $ref
     * @param Cart $cart
     * @param string $suffix
     *
     * @return string
     *
     * @throws RuntimeException
     */
    private function getTokenForCardTransaction($ref, $cart, $suffix)
    {
        $restTransaction = Przelewy24RestTransactionInterfaceFactory::buildForSuffix($suffix);
        $statusUrl = $this->context->link->getModuleLink(
            'przelewy24',
            'paymentStatus',
            ['id_cart' => $cart->id],
            '1' === Configuration::get('PS_SSL_ENABLED')
        );

        $transactionSupport = new Przelewy24TransactionSupport();
        $przelewy24 = new Przelewy24();
        $commonHelper = new Przelewy24Common($przelewy24);
        $paymentData = new Przelewy24PaymentData($cart);
        $commonHelper->validateOrderIfNot1($paymentData);
        $description = Przelewy24OrderDescriptionHelper::buildDescriptionConfigured(
            $this->module->l('Order'),
            $this->module->l('Cart'),
            $suffix,
            $paymentData
        );
        $languageCode = $this->context->language->iso_code;
        $payload = $transactionSupport->getPayload($paymentData, $description, $languageCode);
        $payload->urlStatus = (string) filter_var($statusUrl, FILTER_SANITIZE_URL);
        $payload->methodRefId = (string) $ref;

        $ret = $restTransaction->register($payload);
        if (isset($ret['data']['token'])) {
            $token = $ret['data']['token'];
        } else {
            $token = '';
        }

        return $token;
    }

    /**
     * Do card transaction for token.
     *
     * @param string $token
     * @param string $suffix
     *
     * @return string|null
     *
     * @throws RuntimeException
     */
    private function doCardTransactionForToken($token, $suffix)
    {
        $restCard = Przelewy24RestCardInterfaceFactory::buildForSuffix($suffix);
        $ret = $restCard->chargeWith3ds($token);
        if (isset($ret['data'])) {
            $data = $ret['data'];
        } else {
            $data = [];
        }
        if (isset($data['redirectUrl'])) {
            return $data['redirectUrl'];
        } else {
            return null;
        }
    }

    /**
     * Get URL for failed tranaction.
     *
     * @param Cart $cart
     * @return string
     */
    private function getUrlForFailedTransaction(Cart $cart): string
    {
        $paymentData = new Przelewy24PaymentData($cart);
        $redirect = Przelewy24TransactionSupport::generateReturnUrl($paymentData);

        return $redirect;
    }
}
