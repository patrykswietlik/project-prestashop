<?php
/**
 * NOTICE OF LICENSE
 * This source file is subject to the GNU Lesser General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/lgpl-3.0.en.html
 *
 * @author     Autopay S.A.
 * @copyright  Since 2015 Autopay S.A.
 * @license    https://www.gnu.org/licenses/lgpl-3.0.en.html GNU Lesser General Public License
 */

declare(strict_types=1);

namespace BluePayment\Until;

if (!defined('_PS_VERSION_')) {
    exit;
}

use BluePayment\Config\Config;
use Currency;
use HelperList;

class AdminHelper
{
    protected $module;

    public function __construct(\BluePayment $module)
    {
        $this->module = $module;
    }

    /**
     * Native prestashop function generate HelperList to admin
     *
     * @codeCoverageIgnore
     */
    public function renderAdditionalOptionsList($module, $payments, $title)
    {
        $helper = new \HelperList();
        $helper->table = 'blue_gateway_channels';
        $helper->name_controller = $module->name;
        $helper->module = $module;
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->identifier = 'id_blue_gateway_channels';
        $helper->no_link = true;
        $helper->title = $title;
        $helper->currentIndex = \AdminController::$currentIndex;
        $content = $payments;
        $helper->token = \Tools::getAdminTokenLite('AdminBluepaymentPayments');
        $helper->position_identifier = 'position';
        $helper->orderBy = 'position';
        $helper->orderWay = 'ASC';
        $helper->show_toolbar = false;

        return $helper->generateList($content, $this->getGatewaysListFields($module));
    }

    public function displayGatewayLogo($gatewayLogo, $object)
    {
        $name = _MODULE_DIR_ . 'bluepayment/views/img/';
        $context = $this->module->getContext();
        $currency = $object['gateway_currency'];

        if ($gatewayLogo === $name . 'payments.png') {
            $context->smarty->assign([
                'gateway_slideshow' => Helper::getImgPayments(
                    'transfers',
                    $context->currency->iso_code,
                    $context->shop->id
                ),
                'gateway_type' => 'transfers',
                'currency' => $currency,
            ]);

            $result = $this->module->fetch(
                'module:bluepayment/views/templates/admin/_configure/helpers/form/button-payment.tpl'
            );
        } elseif ($gatewayLogo === $name . 'cards.png') {
            $context->smarty->assign([
                'gateway_slideshow' => Helper::getImgPayments(
                    'wallet',
                    $context->currency->iso_code,
                    $context->shop->id
                ),
                'gateway_type' => 'transfers',
                'currency' => $currency,
            ]);

            $result = $this->module->fetch(
                'module:bluepayment/views/templates/admin/_configure/helpers/form/button-payment.tpl'
            );
        } else {
            $result = '<img width="65" class="img-fluid" src="' . $gatewayLogo . '" />';
        }

        return $result;
    }

    public function displayGatewayPayments($gatewayLogo, $object)
    {
        if ($gatewayLogo == 1) {
            return '<div class="btn-info" data-toggle="modal" data-target="#' . str_replace(
                ' ',
                '_',
                $object['gateway_name']
            ) . '_' . $object['gateway_currency'] . '">
            <img class="img-fluid" width="24" src="' . Config::BM_IMAGES_PATH . 'question.png" alt=""></div>';
        } else {
            return '';
        }
    }

    public function getListChannels($currency)
    {
        $idShop = \Context::getContext()->shop->id;

        $query = new \DbQuery();
        $query->select('gc.*, gcs.id_shop');
        $query->from('blue_gateway_channels', 'gc');
        $query->leftJoin('blue_gateway_channels_shop', 'gcs', 'gc.id_blue_gateway_channels 
        = gcs.id_blue_gateway_channels');

        $query->where('gc.gateway_currency = "' . pSql($currency) . '"');

        if (\Shop::isFeatureActive()) {
            $query->where('gcs.id_shop = ' . (int) $idShop);
        }

        $query->orderBy('gc.position ASC');
        $query->groupBy('gc.id_blue_gateway_channels');

        return \Db::getInstance()->ExecuteS($query);
    }

    public function getGatewaysListFields($module): array
    {
        return [
            'position' => [
                'title' => $module->l('Position'),
                'position' => 'position',
                'ajax' => true,
                'align' => 'center',
                'orderby' => false,
            ],
            'gateway_logo_url' => [
                'title' => $module->l('Payment method'),
                'callback' => 'displayGatewayLogo',
                'callback_object' => $this,
                'orderby' => false,
                'search' => false,
            ],
            'gateway_name' => [
                'title' => '',
                'orderby' => false,
            ],
            'gateway_payments' => [
                'title' => '',
                'callback' => 'displayGatewayPayments',
                'callback_object' => $this,
                'orderby' => false,
            ],
        ];
    }

    public function getListAllPayments($currency = 'PLN', $type = null)
    {
        $idShop = \Context::getContext()->shop->id;

        $q = '';
        if ($type === 'wallet') {
            $q = 'IN (' . Helper::getWalletsList() . ')';
        } elseif ($type === 'transfer') {
            $q = 'NOT IN (' . Helper::getGatewaysList() . ')';
        }

        $query = new \DbQuery();
        $query->select('gt.*');
        $query->from('blue_gateway_transfers', 'gt');
        $query->leftJoin('blue_gateway_transfers_shop', 'gcs', 'gcs.id = gt.id');
        $query->where('gt.gateway_id ' . $q);
        $query->where('gt.gateway_currency = "' . pSql($currency) . '"');

        if (\Shop::isFeatureActive()) {
            $query->where('gcs.id_shop = ' . (int) $idShop);
        }

        $query->orderBy('gt.position ASC');
        $query->groupBy('gt.id');

        return \Db::getInstance()->ExecuteS($query);
    }

    /**
     * Sort currency by id
     *
     * @param null $currency
     *
     * @return array
     */
    public static function getSortCurrencies($currency = null): array
    {
        $sortCurrencies = $currency ?: \Currency::getCurrenciesByIdShop(\Context::getContext()->shop->id);

        usort($sortCurrencies, function ($a, $b) {
            if ($a['id_currency'] == $b['id_currency']) {
                return 0;
            }

            return $a['id_currency'] > $b['id_currency'] ? 1 : -1;
        });

        return (array) $sortCurrencies;
    }
}
