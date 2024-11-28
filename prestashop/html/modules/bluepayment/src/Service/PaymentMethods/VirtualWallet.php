<?php
/**
 * NOTICE OF LICENSE
 * This source file is subject to the GNU Lesser General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/lgpl-3.0.en.html.
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
use Configuration as Cfg;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class VirtualWallet implements GatewayType
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
        $walletMerchantInfo = \Context::getContext()->link->getModuleLink(
            'bluepayment',
            'merchantInfo',
            [],
            true
        );
        $gpay_moduleLinkCharge = \Context::getContext()->link->getModuleLink(
            'bluepayment',
            'chargeGPay',
            [],
            true
        );

        $gpayRedirect = false;
        if (Cfg::get($module->name_upper . '_GPAY_REDIRECT')) {
            $gpayRedirect = true;
        }

        if (!is_object(\Context::getContext()->currency)) {
            $currency = \Context::getContext()->currency['iso_code'];
        } else {
            $currency = \Context::getContext()->currency->iso_code;
        }
        $googlePay = $this->checkIfActiveSubChannel(Config::GATEWAY_ID_GOOGLE_PAY, $currency);
        $applePay = $this->checkIfActiveSubChannel(Config::GATEWAY_ID_APPLE_PAY, $currency);
        $idShop = \Context::getContext()->shop->id;

        \Context::getContext()->smarty->assign([
            'wallet_merchantInfo' => $walletMerchantInfo,
            'gpayRedirect' => $gpayRedirect,
            'gpay_moduleLinkCharge' => $gpay_moduleLinkCharge,
            'googlePay' => $googlePay,
            'applePay' => $applePay,
            'img_wallets' => Helper::getImgPayments('wallet', $currency, $idShop),
        ]);

        $option = new PaymentOption();
        $option->setCallToActionText($module->l('Virtual wallet'))
            ->setAction($moduleLink)
            ->setInputs([
                [
                    'type' => 'hidden',
                    'name' => 'bluepayment_gateway',
                    'value' => 0,
                ],
                [
                    'type' => 'hidden',
                    'name' => 'gpay_get_merchant_info',
                    'value' => $walletMerchantInfo,
                ],
            ])
            ->setLogo(Helper::getBrandLogo())
            ->setAdditionalInformation(
                $module->fetch('module:bluepayment/views/templates/hook/wallet.tpl')
            );

        return $option;
    }

    /**
     * Function check if active Gpay or Apple Pay
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $isoCode = Helper::getIsoFromContext(\Context::getContext());

        $googlePay = $this->checkIfActiveSubChannel(Config::GATEWAY_ID_GOOGLE_PAY, $isoCode);
        $applePay = $this->checkIfActiveSubChannel(Config::GATEWAY_ID_APPLE_PAY, $isoCode);

        return $googlePay || $applePay;
    }

    /**
     * Function check if active Gpay or Apple Pay
     *
     * @return bool
     */
    public function isActiveBo(): bool
    {
        $isoCode = Helper::getIsoFromContext(\Context::getContext());

        $googlePay = $this->checkIfActiveSubChannel(Config::GATEWAY_ID_GOOGLE_PAY, $isoCode);
        $applePay = $this->checkIfActiveSubChannel(Config::GATEWAY_ID_APPLE_PAY, $isoCode);

        return $googlePay || $applePay;
    }

    public function checkIfActiveSubChannel($gatewayId, $currency): bool
    {
        return BlueGatewayTransfers::isTransferActive(
            $gatewayId,
            $currency
        );
    }
}
