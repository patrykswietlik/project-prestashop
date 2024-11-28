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

namespace BluePayment\Api;

if (!defined('_PS_VERSION_')) {
    exit;
}

use BlueMedia\OnlinePayments\Gateway;
use BlueMedia\OnlinePayments\Model\PaywayList;
use BluePayment\Config\Config;
use BluePayment\Until\Helper;
use Configuration as Cfg;

class BlueAPI
{
    private $module;

    public function __construct(\BluePayment $module)
    {
        $this->module = $module;
    }

    public function gatewayAuthentication($merchantData, $mode)
    {
        if (
            isset($merchantData[0])
            && !empty($merchantData[0])
            && isset($merchantData[1])
            && !empty($merchantData[1])
        ) {
            return $this->connectFromAPI($merchantData[0], $merchantData[1], $mode);
        }

        return null;
    }

    public function getApiMode(): string
    {
        $testMode = Cfg::get($this->module->name_upper . '_TEST_ENV');

        return $testMode ? 'sandbox' : 'live';
    }

    public function getApiMerchantData($currencyCode): array
    {
        $serviceId = Helper::parseConfigByCurrency(
            $this->module->name_upper . Config::SERVICE_PARTNER_ID,
            $currencyCode
        );
        $hashKey = Helper::parseConfigByCurrency(
            $this->module->name_upper . Config::SHARED_KEY,
            $currencyCode
        );

        return $serviceId && $hashKey ? [$serviceId, $hashKey] : [];
    }

    /**
     * @param $gateway
     * @param $mode
     * @param $currencies
     *
     * @return bool
     */
    public function getGatewaysFromAPI($gateway, $mode, $currencies): bool
    {
        if (!is_object($gateway)) {
            return false;
        }

        if ($currencies && $mode) {
            foreach ($currencies as $currency) {
                $getMerchantData = $this->getApiMerchantData($currency['iso_code']);
                $apiResponse = $this->gatewayAccountByCurrency($getMerchantData, $currency['iso_code'], $mode);

                $this->getApiResponseSyncGateway($gateway, $apiResponse, $currency);
            }
        } else {
            return false;
        }

        return true;
    }

    public function getApiResponseSyncGateway($gateway, $response, $currency, $position = 0)
    {
        if ($gateway && $response && $currency) {
            return $gateway->syncGateway($response, $currency, $position);
        } elseif (is_null($response)) {
            return $gateway->removeGatewayCurrency($currency);
        }

        return null;
    }

    public function isConnectedAPI($serviceId, $hashKey, $gatewayMode): bool
    {
        $gateway = new Gateway(
            $serviceId,
            $hashKey,
            $gatewayMode,
            Gateway::HASH_SHA256,
            Config::HASH_SEPARATOR
        );

        try {
            return (bool) $gateway->doPaywayList();
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function connectFromAPI($serviceId, $hashKey, $mode): ?PaywayList
    {
        $gateway = new Gateway(
            $serviceId,
            $hashKey,
            $mode,
            'sha256',
            Config::HASH_SEPARATOR
        );

        try {
            return $gateway->doPaywayList();
        } catch (\RuntimeException $exception) {
            return null;
        }
    }

    /**
     * @param $merchantData
     * @param $currencyCode
     * @param $mode
     *
     * @return PaywayList|null
     */
    public function gatewayAccount($merchantData, $currencyCode, $mode)
    {
        if (is_array($merchantData) && $currencyCode && $mode) {
            return $this->gatewayAuthentication($merchantData, $mode);
        }

        return null;
    }

    public function gatewayAccountByCurrency($merchantData, $currencyCode, $mode)
    {
        if (is_array($merchantData)
            && isset($merchantData[0])
            && !empty($merchantData[0])
            && isset($merchantData[1])
            && !empty($merchantData[1])
            && $currencyCode && $mode) {
            $serviceId = $merchantData[0];
            $hashKey = $merchantData[1];

            $gateway = new Gateway(
                $serviceId,
                $hashKey,
                $mode,
                'sha256',
                Config::HASH_SEPARATOR
            );

            return $gateway->doGatewayList($currencyCode);
        }

        return null;
    }
}
