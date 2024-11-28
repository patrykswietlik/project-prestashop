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

namespace SumUp\Application;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Interface ApplicationConfigurationInterface
 */
interface ApplicationConfigurationInterface
{
    /**
     * Returns application's ID.
     *
     * @return string
     */
    public function getAppId();

    /**
     * Returns application's secret.
     *
     * @return string
     */
    public function getAppSecret();

    /**
     * Returns the scopes formatted as they should appear in the request.
     *
     * @return string
     */
    public function getScopes();

    /**
     * Returns the base URL of the SumUp API.
     *
     * @return string
     */
    public function getBaseURL();

    /**
     * Returns authorization code.
     *
     * @return string
     */
    public function getCode();

    /**
     * Returns grant type.
     *
     * @return string
     */
    public function getGrantType();

    /**
     * Returns merchant's username;
     *
     * @return string
     */
    public function getUsername();

    /**
     * Returns merchant's passowrd;
     *
     * @return string
     */
    public function getPassword();

    /**
     * Returns access token.
     *
     * @return string
     */
    public function getAccessToken();

    /**
     * Returns refresh token.
     *
     * @return string
     */
    public function getRefreshToken();

    /**
     * Returns a flag whether to use GuzzleHttp over cURL if both are present.
     *
     * @return bool
     */
    public function getForceGuzzle();

    /**
     * Returns associative array with custom headers.
     *
     * @return array
     */
    public function getCustomHeaders();
}
