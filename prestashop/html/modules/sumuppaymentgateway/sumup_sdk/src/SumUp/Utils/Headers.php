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

namespace SumUp\Utils;

if (!defined('_PS_VERSION_')) {
    exit;
}

use SumUp\Authentication\AccessToken;

/**
 * Class Headers
 */
class Headers
{
    /**
     * Cached value of the project's version.
     *
     * @var string
     */
    protected static $cacheVersion;

    /**
     * Get the common header for Content-Type: application/json.
     *
     * @return array
     */
    public static function getCTJson()
    {
        return ['Content-Type' => 'application/json'];
    }

    /**
     * Get the common header for Content-Type: application/x-www-form-urlencoded.
     *
     * @return array
     */
    public static function getCTForm()
    {
        return ['Content-Type' => 'application/x-www-form-urlencoded'];
    }

    /**
     * Get the authorization header with token.
     *
     * @param AccessToken $accessToken
     *
     * @return array
     */
    public static function getAuth(AccessToken $accessToken)
    {
        return ['Authorization' => 'Bearer ' . $accessToken->getValue()];
    }

    /**
     * Get custom array.
     *
     * @return array
     */
    public static function getTrk()
    {
        return ['X-SDK' => 'PHP-SDK/v' . self::getProjectVersion() . ' PHP/v' . phpversion()];
    }

    /**
     * Get the version of the project accroding to the composer.json
     *
     * @return string
     */
    public static function getProjectVersion()
    {
        $composerPath = _PS_MODULE_DIR_ . '/sumuppaymentgateway/sumup_sdk/composer.json';

        if (is_null(self::$cacheVersion)) {
            $content = \Tools::file_get_contents($composerPath);
            $content = json_decode($content, true);
            self::$cacheVersion = $content['version'];
        }

        return self::$cacheVersion;
    }

    /**
     * Get standard headers needed for every request.
     *
     * @return array
     */
    public static function getStandardHeaders()
    {
        $headers = self::getCTJson();
        $headers += self::getTrk();

        return $headers;
    }
}
