<?php
/**
 * 2007-2023 patworx.de
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade AmazonPay to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    patworx multimedia GmbH <service@patworx.de>
 *  @copyright 2007-2023 patworx multimedia GmbH
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AmazonpayLoginModuleFrontController extends ModuleFrontController
{

    public $display_column_left = false;

    public $display_column_right = false;

    /**
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        $this->context->smarty->assign('isCo', false);
        if (Tools::getValue('isCo')) {
            $this->context->smarty->assign('isCo', true);
        }
        $this->context->smarty->assign('moduleViewPath', $this->module->getViewPath());
        if ($this->module->isPrestaShop16()) {
            $this->setTemplate('login_16.tpl');
        } else {
            $this->setTemplate('module:amazonpay/views/templates/front/login.tpl');
        }
    }
}
