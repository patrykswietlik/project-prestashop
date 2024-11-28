<?php
/**
 * Class przelewy24paymentFailedModuleFrontController
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24paymentFailedModuleFrontController
 */
class Przelewy24paymentFailedModuleFrontController extends ModuleFrontController
{
    /**
     * Init content.
     */
    public function initContent()
    {
        parent::initContent();

        if (Tools::getValue('errorCode')) {
            $przelewy24BlikErrorEnum = new Przelewy24BlikErrorEnum($this);
            /** @var Przelewy24ErrorResult $error */
            $error = $przelewy24BlikErrorEnum->getErrorMessage((int) Tools::getValue('errorCode'));
            $this->context->smarty->assign(['errorReason' => $error->getErrorMessage()]);
        }

        $order = $this->getOrder();
        if ($order) {
            $hasOrder = true;
            $przelewy24ServiceOrderRepeatPayment = new Przelewy24ServiceOrderRepeatPayment($this->module);
            $extra = $przelewy24ServiceOrderRepeatPayment->getSmartyVariables($order);
        } else {
            $hasOrder = false;
            $extra = null;
        }

        $this->context->smarty->assign(
            [
                'logo_url' => $this->module->getPathUri() . 'views/img/logo.png',
                'home_url' => _PS_BASE_URL_,
                'urls' => $this->getTemplateVarUrls(),
                'has_order' => $hasOrder,
                'extra' => $extra,
            ]
        );

        $this->setTemplate('module:przelewy24/views/templates/front/payment_failed.tpl');
    }

    private function getOrder()
    {
        $orderId = Tools::getValue('id_order');
        if (!$orderId) {
            return null;
        }

        $order = new Order($orderId);
        if (!$order->id) {
            return null;
        }

        if ((int) $order->id_customer !== (int) $this->context->customer->id) {
            return null;
        }

        return $order;
    }
}
