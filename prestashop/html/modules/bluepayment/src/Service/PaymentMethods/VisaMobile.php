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

use BluePayment\Api\BlueGatewayTransfers;
use BluePayment\Config\Config;
use BluePayment\Until\Helper;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class VisaMobile implements GatewayType
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

        $option = new PaymentOption();
        $option->setCallToActionText($module->l($data['gateway_name']))
            ->setAction($moduleLink)
            ->setInputs([
                [
                    'type' => 'hidden',
                    'name' => 'bluepayment_gateway',
                    'value' => Config::GATEWAY_ID_VISA_MOBILE,
                ],
                [
                    'type' => 'hidden',
                    'name' => 'bluepayment_gateway_id',
                    'value' => Config::GATEWAY_ID_VISA_MOBILE,
                ],
            ])
            ->setLogo($data['gateway_logo_url'])
            ->setAdditionalInformation(
                $module->fetch('module:bluepayment/views/templates/hook/paymentRedirectVisaMobile.tpl')
            );

        return $option;
    }

    /**
     * @return bool
     */
    public function isActive($cart_total = null): bool
    {
        $iso_code = Helper::getIsoFromContext(\Context::getContext());

        return BlueGatewayTransfers::isTransferActive(
            Config::GATEWAY_ID_VISA_MOBILE,
            $iso_code
        );
    }

    /**
     * @return bool
     */
    public function isActiveBo(): bool
    {
        $iso_code = Helper::getIsoFromContext(\Context::getContext());

        return BlueGatewayTransfers::isTransferActive(
            Config::GATEWAY_ID_VISA_MOBILE,
            $iso_code
        );
    }
}
