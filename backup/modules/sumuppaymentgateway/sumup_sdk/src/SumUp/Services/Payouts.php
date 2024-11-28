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
 * Class Payouts
 */
class Payouts implements SumUpService
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
     * Customers constructor.
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
     * Get a list of payouts.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int $limit
     * @param bool $descendingOrder
     * @param string $format
     *
     * @return \SumUp\HttpClients\Response
     *
     * @throws SumUpArgumentException
     * @throws \SumUp\Exceptions\SumUpConnectionException
     * @throws \SumUp\Exceptions\SumUpResponseException
     * @throws \SumUp\Exceptions\SumUpAuthenticationException
     * @throws \SumUp\Exceptions\SumUpSDKException
     */
    public function getPayouts($startDate, $endDate, $limit = 10, $descendingOrder = true, $format = 'json')
    {
        if (empty($startDate)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('start date'));
        }
        if (empty($endDate)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('end date'));
        }
        if (empty($limit) || !is_int($limit)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('limit'));
        }
        if (empty($descendingOrder)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('order'));
        }
        if (empty($format)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('format'));
        }
        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'limit' => $limit,
            'order' => $descendingOrder ? 'desc' : 'asc',
            'format' => $format,
        ];
        $queryParams = http_build_query($filters);
        $path = '/v0.1/me/financials/payouts?' . $queryParams;
        $headers = array_merge(Headers::getStandardHeaders(), Headers::getAuth($this->accessToken));

        return $this->client->send('GET', $path, null, $headers);
    }

    /**
     * Get a list of payed out transactions.
     *
     * @param string $startDate
     * @param string $endDate
     * @param int $limit
     * @param bool $descendingOrder
     * @param string $format
     *
     * @return \SumUp\HttpClients\Response
     *
     * @throws SumUpArgumentException
     * @throws \SumUp\Exceptions\SumUpConnectionException
     * @throws \SumUp\Exceptions\SumUpResponseException
     * @throws \SumUp\Exceptions\SumUpAuthenticationException
     * @throws \SumUp\Exceptions\SumUpSDKException
     */
    public function getTransactions($startDate, $endDate, $limit = 10, $descendingOrder = true, $format = 'json')
    {
        if (empty($startDate)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('start date'));
        }
        if (empty($endDate)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('end date'));
        }
        if (empty($limit) || !is_int($limit)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('limit'));
        }
        if (empty($descendingOrder)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('order'));
        }
        if (empty($format)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('format'));
        }
        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'limit' => $limit,
            'order' => $descendingOrder ? 'desc' : 'asc',
            'format' => $format,
        ];
        $queryParams = http_build_query($filters);
        $path = '/v0.1/me/financials/transactions?' . $queryParams;
        $headers = array_merge(Headers::getStandardHeaders(), Headers::getAuth($this->accessToken));

        return $this->client->send('GET', $path, null, $headers);
    }
}
