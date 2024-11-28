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

namespace SumUp\Authentication;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class AccessToken
 */
class AccessToken
{
    /**
     * The access token value.
     *
     * @var string
     */
    protected $value = '';

    /**
     * The access token type.
     *
     * @var string
     */
    protected $type = '';

    /**
     * The number of seconds the access token will be valid.
     *
     * @var int
     */
    protected $expiresIn;

    /**
     * The scopes for this access token.
     *
     * @var array
     */
    protected $scope;

    /**
     * The refresh token.
     *
     * @var string
     */
    protected $refreshToken;

    /**
     * Create a new access token entity.
     *
     * @param string $value
     * @param string $type
     * @param int $expiresIn
     * @param array $scope
     * @param string $refreshToken
     */
    public function __construct($value, $type = '', $expiresIn = -1, array $scope = [], $refreshToken = null)
    {
        if ($value) {
            $this->value = $value;
        }
        if ($type) {
            $this->type = $type;
        }
        if ($expiresIn) {
            $this->expiresIn = $expiresIn;
        }
        if ($scope) {
            $this->scope = $scope;
        }
        if ($refreshToken) {
            $this->refreshToken = $refreshToken;
        }
    }

    /**
     * Returns the access token.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the type of the access token.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the total number of seconds that the token will be valid.
     *
     * @return int
     */
    public function getExpiresIn()
    {
        return $this->expiresIn;
    }

    /**
     * Returns the scopes for the current access token.
     *
     * @return array
     */
    public function getScopes()
    {
        return $this->scope;
    }

    /**
     * Returns the refresh token if any.
     *
     * @return string|null
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }
}
