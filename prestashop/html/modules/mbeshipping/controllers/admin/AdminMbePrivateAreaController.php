<?php
/**
 * 2017-2022 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    MBE Worldwide
 * @copyright 2017-2024 MBE Worldwide
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of MBE Worldwide
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'mbeshipping/classes/AuthAPI.php';

class AdminMbePrivateAreaController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();
    }

    public function initContent()
    {
        $access_token = Configuration::get('MBESHIPPING_ACCESS_TOKEN');
        if (AuthAPI::isJwtExpired($access_token)) {
            $refresh_token = Configuration::get('MBESHIPPING_REFRESH_TOKEN');
            $access_token = AuthAPI::isJwtExpired($refresh_token) ? AuthAPI::getAccessToken() : AuthAPI::getAccessToken($refresh_token);
        }

        if ($access_token) {
            $this->context->smarty->assign([
                'private_area_iframe_url' => 'https://www.mbe-hub.com/direct-channel-registration/private-area',
                'private_area_iframe_access_token' => $access_token,
                'private_area_iframe_lang' => Language::getIsoById($this->context->employee->id_lang),
                'private_area_iframe_login_url' => $this->context->link->getAdminLink('AdminMbePrivateArea')
            ]);

            $this->content = $this->context->smarty->fetch($this->module->getLocalPath() . 'views/templates/admin/private_area.tpl');
        } else {
            $this->errors[] = $this->module->l('Unable to connect to MBE services.', 'AdminMbePrivateAreaController') . ' ' .
                '<a href="'. $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->module->name .'">'
                . $this->module->l('Please check your credentials here', 'AdminMbePrivateAreaController') . '</a>';
        }

        parent::initContent();
    }
}
