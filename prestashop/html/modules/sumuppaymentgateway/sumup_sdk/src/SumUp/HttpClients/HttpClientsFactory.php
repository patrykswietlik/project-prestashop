<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace SumUp\HttpClients;

if (!defined('_PS_VERSION_')) {
    exit;
}

use SumUp\Application\ApplicationConfigurationInterface;
use SumUp\Exceptions\SumUpConfigurationException;

/**
 * Class HttpClientsFactory
 */
class HttpClientsFactory
{
    private function __construct()
    {
        // a factory constructor should never be invoked
    }

    /**
     * Create the HTTP client needed for communication with the SumUp's servers.
     *
     * @param ApplicationConfigurationInterface $appConfig
     * @param SumUpHttpClientInterface|null $customHttpClient
     *
     * @return SumUpHttpClientInterface
     *
     * @throws SumUpConfigurationException
     */
    public static function createHttpClient(ApplicationConfigurationInterface $appConfig, ?SumUpHttpClientInterface $customHttpClient = null)
    {
        if ($customHttpClient) {
            return $customHttpClient;
        }

        return self::detectDefaultClient($appConfig->getBaseURL(), $appConfig->getForceGuzzle(), $appConfig->getCustomHeaders());
    }

    /**
     * Detect the default HTTP client.
     *
     * @param string $baseURL
     * @param bool $forceUseGuzzle
     *
     * @return SumUpCUrlClient|SumUpGuzzleHttpClient
     *
     * @throws SumUpConfigurationException
     */
    private static function detectDefaultClient($baseURL, $forceUseGuzzle, $customHeaders)
    {
        if (extension_loaded('curl') && !$forceUseGuzzle) {
            return new SumUpCUrlClient($baseURL, $customHeaders);
        }
        if (class_exists('GuzzleHttp\Client')) {
            return new SumUpGuzzleHttpClient($baseURL, $customHeaders);
        }

        throw new SumUpConfigurationException('No default http client found. Please install cURL or GuzzleHttp.');
    }
}
