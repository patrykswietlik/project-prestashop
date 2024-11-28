<?php
/**
 * Class Przelewy24ServiceOrderRepeatPayment
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24ServiceOrderRepeatPayment
 */
class Przelewy24ServiceOrderRepeatPayment extends Przelewy24Service
{
    /**
     * Executes action (order repeat).
     *
     * @return bool|Order
     */
    public function execute()
    {
        if (!$this->getPrzelewy24()->active) {
            return false;
        }

        $idOrder = (int) Tools::getValue('id_order');
        if (!$idOrder) {
            // if there is no idOrder that mean that we do guest tracking and  have order reference & email
            $orderReference = Tools::getValue('order_reference');
            $orderReferenceEmail = Tools::getValue('email');

            $order = Order::getByReferenceAndEmail($orderReference, $orderReferenceEmail);
        } else {
            $order = new Order((int) $idOrder);
        }

        $smartyVariables = $this->getSmartyVariables($order);
        $this->getPrzelewy24()->getSmarty()->assign($smartyVariables);

        return $order;
    }

    public function getSmartyVariables($order)
    {
        $link = new Link();

        $idCart = $order->id_cart;
        $secureKey = $order->secure_key;
        $moduleId = Module::getModuleIdByName('przelewy24');
        $orderConfirmation = $link->getPageLink('order-confirmation');

        $logoUrl = $this->getPrzelewy24()->getPathUri() . 'views/img/logo.png';
        $redirectUrl = $orderConfirmation .
            '?id_cart=' . $idCart .
            '&id_module=' . $moduleId .
            '&id_order=' . $order->id .
            '&key=' . $secureKey;

        return [
            'logo_url' => $logoUrl,
            'redirect_url' => $redirectUrl,
        ];
    }
}
