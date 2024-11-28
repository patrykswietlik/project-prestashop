<?php
/**
 * Class przelewy24paymentSuccessfulModuleFrontController
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24paymentSuccessfulModuleFrontController
 */
class Przelewy24paymentSuccessfulModuleFrontController extends ModuleFrontController
{
    /**
     * Initializes front controller: sets smarty variables,
     * class properties, redirects depending on context, etc.
     *
     * @throws PrestaShopException
     */
    public function init()
    {
        unset($this->context->cookie->id_cart);

        return parent::init();
    }

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

        $this->setTemplate('module:przelewy24/views/templates/front/payment_successful.tpl');
    }
}
