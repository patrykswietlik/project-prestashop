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

use PrestaShop\Module\Mbeshipping\Helper\DataHelper;

class MbeshippingMbedpModuleFrontController extends ModuleFrontController

{
    public $module;
    private $is_debug = true;
    protected $validActions = ['setPickUpPointForCurrentCart'];

    public function __construct()
    {
        $this->maintenance = false;
        $this->module = 'mbeshipping';

        parent::__construct();
    }

    private function respondAndDie($message, $data, $response_code = 200)
    {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Content-Type: application/json');
        http_response_code($response_code);

        $response = [
            'status' => $response_code,
            'message' => $message,
            'data' => $data,
        ];

        echo json_encode($response, JSON_UNESCAPED_SLASHES);
        exit;
    }

    const XML_PATH_WS_URL = 'url';
    const XML_PATH_WS_USERNAME = 'username';

    public function setPickUpPointForCurrentCart()
    {
        $payload_request = Tools::getValue('payload');


        // save in database with objectmodel
        $id_cart = Context::getContext()->cart->id;
        if (!empty($id_cart)) {
            // save date in table mbe_shipping_dp
            MbeShippingDPHelper::storageData($id_cart, $payload_request);
        }

        // Renderizza da tpl per riepilogo
        Context::getContext()->smarty->assign('payload', $payload_request);
        $html = Context::getContext()->smarty->fetch(_PS_MODULE_DIR_ . $this->module->name
            . '/views/templates/hook/carrier-pick-up-point-details.tpl');

        $vat_included = '(' . $this->module->l('Tax incl.') . ')';
        if (Tools::version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
            $vat_included = $this->module->l('Tax incl.');
        }

        $cost = MbeShippingDPHelper::formatEurPrice($payload_request['cost']) . ' ' . $vat_included;

        return ['html' => $html, 'cost' => $cost];
    }

    public function postProcess()
    {
        $token_request = Tools::getValue('token');
        $action_request = Tools::getValue('action');
        // $status_request = Tools::getValue('status');

        if (empty($token_request) || $token_request !== \MbeShippingDPHelper::MBE_GELPRX_CONFIG_AJAX_TOKEN) {
            $this->respondAndDie(
                ['_WS_ERROR_TOKEN_'],
                false,
                500
            );
        } elseif (!in_array($action_request, $this->validActions, true)) {
            $this->respondAndDie(
                ['_WS_ERROR_ACTION_'],
                false,
                500
            );
        } elseif ($action_request === 'setPickUpPointForCurrentCart') {
            $this->respondAndDie(
                false,
                $this->setPickUpPointForCurrentCart(),
                200
            );
        }
    }
}
