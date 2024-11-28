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

class BlikLater implements GatewayType
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

        $blikLaterMerchantInfo = \Context::getContext()->link->getModuleLink(
            'bluepayment',
            'merchantInfo',
            [],
            true
        );

        \Context::getContext()->smarty->assign([
            'blikLater_merchantInfo' => $blikLaterMerchantInfo,
        ]);

        $option = new PaymentOption();
        $option
            ->setInputs([
                [
                    'type' => 'hidden',
                    'name' => 'bluepayment_gateway',
                    'value' => Config::GATEWAY_ID_BLIK_LATER,
                ],
                [
                    'type' => 'hidden',
                    'name' => 'bluepayment_gateway_id',
                    'value' => Config::GATEWAY_ID_BLIK_LATER,
                ],
            ])
            ->setCallToActionText($data['gateway_name'])
            ->setAction($moduleLink)
            ->setLogo($data['gateway_logo_url'])
            ->setAdditionalInformation(
                $module->fetch('module:bluepayment/views/templates/hook/paymentBlikLater.tpl')
            );

        return $option;
    }

    /**
     * @return bool
     */
    public function isActive($cart_total = null): bool
    {
        $iso_code = Helper::getIsoFromContext(\Context::getContext());

        $blikLater = BlueGatewayChannels::getByGatewayIdAndCurrency(
            Config::GATEWAY_ID_BLIK_LATER,
            $iso_code
        );

        if (!$cart_total) {
            $cart_total = isset(\Context::getContext()->cart) ? \Context::getContext()->cart->getOrderTotal(true, \Cart::BOTH) : 0;
        }

        return $blikLater->id
            && (float) $cart_total >= (float) $blikLater->min_amount
            && (float) $cart_total <= (float) $blikLater->max_amount;
    }

    /**
     * @return bool
     */
    public function isActiveBo(): bool
    {
        $iso_code = Helper::getIsoFromContext(\Context::getContext());

        $blikLater = BlueGatewayChannels::getByGatewayIdAndCurrency(
            Config::GATEWAY_ID_BLIK_LATER,
            $iso_code
        );

        return (bool) $blikLater->id;
    }
}
