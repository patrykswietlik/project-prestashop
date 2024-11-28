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

use SumUp\Exceptions\SumUpConfigurationException;

/**
 * Class ApplicationConfiguration
 */
class ApplicationConfiguration implements ApplicationConfigurationInterface
{
    /**
     * The default scopes that are recommended to be requested every time.
     */
    const DEFAULT_SCOPES = ['payments', 'transactions.history', 'user.app-settings', 'user.profile_readonly'];

    /**
     * The possible values for grant type.
     */
    const GRANT_TYPES = ['authorization_code', 'client_credentials', 'password'];

    /**
     * The client ID.
     *
     * @var string
     */
    protected $appId;

    /**
     * The client secret.
     *
     * @var string
     */
    protected $appSecret;

    /**
     * The scopes that are needed for all services the application uses.
     *
     * @var array
     */
    protected $scopes;

    /**
     * The base URL of the SumUp API.
     *
     * @var string;
     */
    protected $baseURL;

    /**
     * The merchant's username. Needed only if the authorization flow is "password".
     *
     * @var string
     */
    protected $username;

    /**
     * The merchant's account password. Needed only if the authorization flow is "password".
     *
     * @var string
     */
    protected $password;

    /**
     * The authorization grant type. Allowed values are: 'authorization_code'|'client_credentials'|'password'.
     *
     * @var string
     */
    protected $grantType;

    /**
     * The authorization code.
     *
     * @var string
     */
    protected $code;

    /**
     * The access token.
     *
     * @var string|null
     */
    protected $accessToken;

    /**
     * The refresh token.
     *
     * @var string|null
     */
    protected $refreshToken;

    /**
     * Flag whether to use GuzzleHttp over cURL if both are present.
     *
     * @var
     */
    protected $forceGuzzle;

    /**
     * Custom headers to be sent with every request.
     *
     * @var array
     */
    protected $customHeaders;

    /**
     * Create a new application configuration.
     *
     * @param array $config
     *
     * @throws SumUpConfigurationException
     */
    public function __construct(array $config = [])
    {
        $config = array_merge([
            'app_id' => null,
            'app_secret' => null,
            'grant_type' => 'authorization_code',
            'base_uri' => 'https://api.sumup.com',
            'scopes' => [],
            'code' => null,
            'access_token' => null,
            'refresh_token' => null,
            'username' => null,
            'password' => null,
            'use_guzzlehttp_over_curl' => false,
            'custom_headers' => [],
        ], $config);

        $this->setAppId($config['app_id']);
        $this->setAppSecret($config['app_secret']);
        $this->setScopes($config['scopes']);
        $this->setGrantType($config['grant_type']);
        $this->baseURL = $config['base_uri'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->code = $config['code'];
        $this->accessToken = $config['access_token'];
        $this->refreshToken = $config['refresh_token'];
        $this->setForceGuzzle($config['use_guzzlehttp_over_curl']);
        $this->setCustomHeaders($config['custom_headers']);
    }

    /**
     * Returns the client ID.
     *
     * @return string
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * Returns the client secret.
     *
     * @return string
     */
    public function getAppSecret()
    {
        return $this->appSecret;
    }

    /**
     * Returns the scopes.
     *
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Returns the scopes formatted as they should appear in the request.
     *
     * @return string
     */
    public function getFormattedScopes()
    {
        return implode(' ', $this->scopes);
    }

    /**
     * Returns the base URL of the SumUp API.
     *
     * @return string
     */
    public function getBaseURL()
    {
        return $this->baseURL;
    }

    /**
     * Returns authorization code.
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns grant type.
     *
     * @return string;
     */
    public function getGrantType()
    {
        return $this->grantType;
    }

    /**
     * Returns merchant's username.
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns merchant's password.
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Returns initial access token.
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Returns initial refresh token.
     *
     * @return string|null
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Returns the flag whether to use GuzzleHttp.
     *
     * @return bool
     */
    public function getForceGuzzle()
    {
        return $this->forceGuzzle;
    }

    /**
     * Returns associative array with custom headers.
     *
     * @return array
     */
    public function getCustomHeaders()
    {
        return $this->customHeaders;
    }

    /**
     * Set application ID.
     *
     * @param string $appId
     *
     * @throws SumUpConfigurationException
     */
    protected function setAppId($appId)
    {
        if (empty($appId)) {
            throw new SumUpConfigurationException('Missing mandatory parameter app_id');
        }
        $this->appId = $appId;
    }

    /**
     * Set application secret.
     *
     * @param string $appSecret
     *
     * @throws SumUpConfigurationException
     */
    protected function setAppSecret($appSecret)
    {
        if (empty($appSecret)) {
            throw new SumUpConfigurationException('Missing mandatory parameter app_secret');
        }
        $this->appSecret = $appSecret;
    }

    /**
     * Set the authorization grant type.
     *
     * @param array $grantType
     *
     * @throws SumUpConfigurationException
     */
    protected function setGrantType($grantType)
    {
        if (!in_array($grantType, $this::GRANT_TYPES)) {
            throw new SumUpConfigurationException('Invalid parameter for "grant_type". Allowed values are: ' . implode($this::GRANT_TYPES, ' | ') . '.');
        }
        $this->grantType = $grantType;
    }

    /**
     * Set the scopes and always include the default ones.
     *
     * @param array $scopes
     */
    protected function setScopes(array $scopes = [])
    {
        $arr = array_merge($this::DEFAULT_SCOPES, $scopes);
        $this->scopes = array_unique($arr, SORT_REGULAR);
    }

    /**
     * Set the flag whether to use GuzzleHttp.
     *
     * @param bool $forceGuzzle
     *
     * @throws SumUpConfigurationException
     */
    protected function setForceGuzzle($forceGuzzle)
    {
        if (!is_bool($forceGuzzle)) {
            throw new SumUpConfigurationException('Invalid value for boolean parameter use_guzzlehttp_over_curl.');
        }
        $this->forceGuzzle = $forceGuzzle;
    }

    /**
     * Set the associative array with custom headers.
     *
     * @param array $customHeaders
     */
    public function setCustomHeaders($customHeaders)
    {
        $this->customHeaders = is_array($customHeaders) ? $customHeaders : [];
    }
}
