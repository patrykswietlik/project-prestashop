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

namespace BluePayment\Service\PaymentMethods;

if (!defined('_PS_VERSION_')) {
    exit;
}

use BluePayment\Api\BlueGatewayChannels;
use BluePayment\Config\Config;
use BluePayment\Until\Helper;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class PayPo implements GatewayType
{
    public function getPaymentOption(
        \BluePayment $module,
        array $data = []
    ): PaymentOption {
        $moduleLink = \Context::getContext()->link->getModuleLink(
            'bluepayment',
            'payment',
            [],
            true
        );

        $payPoMerchantInfo = \Context::getContext()->link->getModuleLink(
            'bluepayment',
            'merchantInfo',
            [],
            true
        );

        \Context::getContext()->smarty->assign([
            'payPo_merchantInfo' => $payPoMerchantInfo,
        ]);

        $option = new PaymentOption();
        $option->setCallToActionText($module->l($data['gateway_name']))
            ->setAction($moduleLink)
            ->setInputs([
                [
                    'type' => 'hidden',
                    'name' => 'bluepayment_gateway',
                    'value' => Config::GATEWAY_ID_PAYPO,
                ],
                [
                    'type' => 'hidden',
                    'name' => 'bluepayment_gateway_id',
                    'value' => Config::GATEWAY_ID_PAYPO,
                ],
            ])
            ->setLogo($data['gateway_logo_url'])
            ->setAdditionalInformation(
                $module->fetch('module:bluepayment/views/templates/hook/paymentRedirectPayPo.tpl')
            );

        return $option;
    }

    /**
     * @return bool
     */
    public function isActive($cart_total = null): bool
    {
        $iso_code = Helper::getIsoFromContext(\Context::getContext());
        if (!$cart_total) {
            $cart_total = isset(\Context::getContext()->cart) ? \Context::getContext()->cart->getOrderTotal(true, \Cart::BOTH) : 0;
        }

        $paypo = BlueGatewayChannels::getByGatewayIdAndCurrency(
            Config::GATEWAY_ID_PAYPO,
            $iso_code
        );

        return $paypo->id
            && (float) $cart_total >= (float) $paypo->min_amount
            && (float) $cart_total <= (float) $paypo->max_amount;
    }

    /**
     * @return bool
     */
    public function isActiveBo(): bool
    {
        $iso_code = Helper::getIsoFromContext(\Context::getContext());

        $paypo = BlueGatewayChannels::getByGatewayIdAndCurrency(
            Config::GATEWAY_ID_PAYPO,
            $iso_code
        );

        return (bool) $paypo->id;
    }
}
