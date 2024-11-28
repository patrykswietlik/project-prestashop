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
use SumUp\Utils\Headers;

/**
 * Class Custom
 */
class Custom implements SumUpService
{
    /**
     * Allowed HTTP methods.
     */
    const HTTP_METHODS = ['GET', 'POST', 'PUT', 'DELETE'];

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
     * Custom constructor.
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
     * Make custom request.
     *
     * @param string $method
     * @param string $relativePath
     * @param array|null $payload
     *
     * @return mixed|\SumUp\HttpClients\Response
     *
     * @throws SumUpArgumentException
     * @throws \SumUp\Exceptions\SumUpConnectionException
     * @throws \SumUp\Exceptions\SumUpResponseException
     * @throws \SumUp\Exceptions\SumUpAuthenticationException
     * @throws \SumUp\Exceptions\SumUpSDKException
     */
    public function request($method, $relativePath, $payload = null)
    {
        if (!in_array($method, $this::HTTP_METHODS)) {
            $message = "Not allowed method provided: $method. Allowed values: " . implode(', ', $this::HTTP_METHODS) . '.';
            throw new SumUpArgumentException($message);
        }
        $headers = array_merge(Headers::getStandardHeaders(), Headers::getAuth($this->accessToken));

        return $this->client->send($method, $relativePath, $payload, $headers);
    }
}
