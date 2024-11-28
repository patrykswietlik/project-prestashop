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

namespace BluePayment\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

use BluePayment\Service\PaymentMethods\GatewayType;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class Gateway
{
    private $module;
    private $gateway;

    public function __construct(
        \BluePayment $module,
        GatewayType $gateway
    ) {
        $this->gateway = $gateway;
        $this->module = $module;
    }

    public function getPaymentOption(array $data = []): PaymentOption
    {
        return $this->gateway->getPaymentOption($this->module, $data);
    }

    public function isActive(): bool
    {
        return $this->gateway->isActive();
    }

    public function isActiveBo(): bool
    {
        return $this->gateway->isActiveBo();
    }
}
