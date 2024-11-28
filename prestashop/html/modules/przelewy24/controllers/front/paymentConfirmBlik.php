<?php
/**
 * Class przelewy24paymentConfirmBlikModuleFrontController
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24paymentConfirmBlikModuleFrontController
 */
class Przelewy24paymentConfirmBlikModuleFrontController extends ModuleFrontController
{
    /**
     * Init content.
     */
    public function initContent()
    {
        parent::initContent();

        $this->context->smarty->assign(
            [
                'logo_url' => $this->module->getPathUri() . 'views/img/logo.png',
                'home_url' => _PS_BASE_URL_,
                'urls' => $this->getTemplateVarUrls(),
            ]
        );

        $this->setTemplate('module:przelewy24/views/templates/front/payment_confirm_blik.tpl');
    }
}
