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

namespace SumUp\Services;

if (!defined('_PS_VERSION_')) {
    exit;
}

use SumUp\Authentication\AccessToken;
use SumUp\Exceptions\SumUpArgumentException;
use SumUp\HttpClients\SumUpHttpClientInterface;
use SumUp\Utils\ExceptionMessages;
use SumUp\Utils\Headers;

/**
 * Class Merchant
 */
class Merchant implements SumUpService
{
    /**
     * The client for the http communication.
     *
     * @var SumUpHttpClientInterface
     */
    protected $client;

    /**
     * The access token needed for authentication for the services.
     *
     * @var AccessToken
     */
    protected $accessToken;

    /**
     * Checkouts constructor.
     *
     * @param SumUpHttpClientInterface $client
     * @param AccessToken $accessToken
     */
    public function __construct(SumUpHttpClientInterface $client, AccessToken $accessToken)
    {
        $this->client = $client;
        $this->accessToken = $accessToken;
    }

    /**
     * Get merchant's profile.
     *
     * @return \SumUp\HttpClients\Response
     *
     * @throws \SumUp\Exceptions\SumUpConnectionException
     * @throws \SumUp\Exceptions\SumUpResponseException
     * @throws \SumUp\Exceptions\SumUpAuthenticationException
     * @throws \SumUp\Exceptions\SumUpSDKException
     */
    public function getProfile()
    {
        $path = '/v0.1/me/merchant-profile';
        $headers = array_merge(Headers::getStandardHeaders(), Headers::getAuth($this->accessToken));

        return $this->client->send('GET', $path, [], $headers);
    }

    /**
     * Update merchant's profile.
     *
     * @param array $data
     *
     * @return \SumUp\HttpClients\Response
     *
     * @throws SumUpArgumentException
     * @throws \SumUp\Exceptions\SumUpConnectionException
     * @throws \SumUp\Exceptions\SumUpResponseException
     * @throws \SumUp\Exceptions\SumUpAuthenticationException
     * @throws \SumUp\Exceptions\SumUpSDKException
     */
    public function updateProfile(array $data)
    {
        if (empty($data)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('payload data'));
        }
        $path = '/v0.1/me/merchant-profile';
        $headers = array_merge(Headers::getStandardHeaders(), Headers::getAuth($this->accessToken));

        return $this->client->send('PUT', $path, $data, $headers);
    }

    /**
     * Get data for doing business as.
     *
     * @return \SumUp\HttpClients\Response
     *
     * @throws \SumUp\Exceptions\SumUpConnectionException
     * @throws \SumUp\Exceptions\SumUpResponseException
     * @throws \SumUp\Exceptions\SumUpAuthenticationException
     * @throws \SumUp\Exceptions\SumUpSDKException
     */
    public function getDoingBusinessAs()
    {
        $path = '/v0.1/me/merchant-profile/doing-business-as';
        $headers = array_merge(Headers::getStandardHeaders(), Headers::getAuth($this->accessToken));

        return $this->client->send('GET', $path, [], $headers);
    }

    /**
     * Update data for doing business as.
     *
     * @param array $data
     *
     * @return \SumUp\HttpClients\Response
     *
     * @throws SumUpArgumentException
     * @throws \SumUp\Exceptions\SumUpConnectionException
     * @throws \SumUp\Exceptions\SumUpResponseException
     * @throws \SumUp\Exceptions\SumUpAuthenticationException
     * @throws \SumUp\Exceptions\SumUpSDKException
     */
    public function updateDoingBusinessAs(array $data)
    {
        if (empty($data)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('payload data'));
        }
        $path = '/v0.1/me/merchant-profile/doing-business-as';
        $headers = array_merge(Headers::getStandardHeaders(), Headers::getAuth($this->accessToken));

        return $this->client->send('PUT', $path, $data, $headers);
    }
}
