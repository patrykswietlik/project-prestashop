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

use BluePayment\Until\Helper;
use Configuration as Cfg;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class InternetTransfer implements GatewayType
{
    public function getPaymentOption(
        \BluePayment $module,
        array $data = []
    ): PaymentOption {
        $paymentName = Cfg::get(
            $module->name_upper . '_PAYMENT_GROUP_NAME',
            \Context::getContext()->language->id
        );

        $cardIdTime = \Context::getContext()->cart->id . '-' . time();

        $moduleLink = \Context::getContext()->link->getModuleLink(
            'bluepayment',
            'payment',
            [],
            true
        );

        $option = new PaymentOption();
        $option->setCallToActionText($paymentName)
            ->setAction($moduleLink)
            ->setInputs([
                [
                    'type' => 'hidden',
                    'name' => 'bluepayment_gateway',
                    'value' => '0',
                ],
                [
                    'type' => 'hidden',
                    'name' => 'bluepayment-hidden-psd2-regulation-id',
                    'value' => '0',
                ],
                [
                    'type' => 'hidden',
                    'name' => 'bluepayment_cart_id',
                    'value' => $cardIdTime,
                ],
            ])
            ->setLogo(Helper::getBrandLogo())
            ->setAdditionalInformation(
                $module->fetch('module:bluepayment/views/templates/hook/payment.tpl')
            );

        return $option;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isActiveBo(): bool
    {
        return true;
    }
}
