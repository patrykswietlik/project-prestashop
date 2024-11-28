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
 * Class Customers
 */
class Customers implements SumUpService
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
     * Create new customer.
     *
     * @param $customerId
     * @param array $customerDetails
     * @param array $customerAddress
     *
     * @return \SumUp\HttpClients\Response
     *
     * @throws SumUpArgumentException
     * @throws \SumUp\Exceptions\SumUpConnectionException
     * @throws \SumUp\Exceptions\SumUpResponseException
     * @throws \SumUp\Exceptions\SumUpAuthenticationException
     * @throws \SumUp\Exceptions\SumUpSDKException
     */
    public function create($customerId, array $customerDetails = [], array $customerAddress = [])
    {
        if (empty($customerId)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('customer id'));
        }

        $details = array_merge([
            'first_name' => null,
            'last_name' => null,
            'email' => null,
            'phone' => null,
        ], $customerDetails);
        $details = array_filter($details);

        $address = array_merge([
            'city' => null,
            'country' => null,
            'line1' => null,
            'line2' => null,
            'state' => null,
            'postalCode' => null,
        ], $customerAddress);
        $address = array_filter($address);

        if (sizeof($address) > 0) {
            $details['address'] = $address;
        }

        $payload = [
            'customer_id' => $customerId,
            'personal_details' => $details,
        ];
        $path = '/v0.1/customers';
        $headers = array_merge(Headers::getStandardHeaders(), Headers::getAuth($this->accessToken));

        return $this->client->send('POST', $path, $payload, $headers);
    }

    /**
     * Update existing customer.
     *
     * @param $customerId
     * @param array $customerDetails
     * @param array $customerAddress
     *
     * @return \SumUp\HttpClients\Response
     *
     * @throws SumUpArgumentException
     * @throws \SumUp\Exceptions\SumUpConnectionException
     * @throws \SumUp\Exceptions\SumUpResponseException
     * @throws \SumUp\Exceptions\SumUpAuthenticationException
     * @throws \SumUp\Exceptions\SumUpSDKException
     */
    public function update($customerId, array $customerDetails = [], array $customerAddress = [])
    {
        if (empty($customerId)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('customer id'));
        }

        $details = array_merge([
            'first_name' => null,
            'last_name' => null,
            'email' => null,
            'phone' => null,
        ], $customerDetails);
        $details = array_filter($details);

        $address = array_merge([
            'city' => null,
            'country' => null,
            'line1' => null,
            'line2' => null,
            'state' => null,
            'postalCode' => null,
        ], $customerAddress);
        $address = array_filter($address);

        if (sizeof($address) > 0) {
            $details['address'] = $address;
        }
        $payload = [
            'customer_id' => $customerId,
            'personal_details' => $details,
        ];
        $path = '/v0.1/customers/' . $customerId;
        $headers = array_merge(Headers::getStandardHeaders(), Headers::getAuth($this->accessToken));

        return $this->client->send('PUT', $path, $payload, $headers);
    }

    /**
     * Get customer by ID.
     *
     * @param $customerId
     *
     * @return \SumUp\HttpClients\Response
     *
     * @throws SumUpArgumentException
     * @throws \SumUp\Exceptions\SumUpConnectionException
     * @throws \SumUp\Exceptions\SumUpResponseException
     * @throws \SumUp\Exceptions\SumUpAuthenticationException
     * @throws \SumUp\Exceptions\SumUpSDKException
     */
    public function get($customerId)
    {
        if (empty($customerId)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('customer id'));
        }
        $path = '/v0.1/customers/' . $customerId;
        $headers = array_merge(Headers::getStandardHeaders(), Headers::getAuth($this->accessToken));

        return $this->client->send('GET', $path, [], $headers);
    }

    /**
     * Get payment instruments for a customer.
     *
     * @param $customerId
     *
     * @return \SumUp\HttpClients\Response
     *
     * @throws SumUpArgumentException
     * @throws \SumUp\Exceptions\SumUpConnectionException
     * @throws \SumUp\Exceptions\SumUpResponseException
     * @throws \SumUp\Exceptions\SumUpAuthenticationException
     * @throws \SumUp\Exceptions\SumUpSDKException
     */
    public function getPaymentInstruments($customerId)
    {
        if (empty($customerId)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('customer id'));
        }
        $path = '/v0.1/customers/' . $customerId . '/payment-instruments';
        $headers = array_merge(Headers::getStandardHeaders(), Headers::getAuth($this->accessToken));

        return $this->client->send('GET', $path, [], $headers);
    }

    /**
     * Deactivate payment instrument for a customer.
     *
     * @param $customerId
     * @param $cardToken
     *
     * @return \SumUp\HttpClients\Response
     *
     * @throws SumUpArgumentException
     * @throws \SumUp\Exceptions\SumUpConnectionException
     * @throws \SumUp\Exceptions\SumUpResponseException
     * @throws \SumUp\Exceptions\SumUpAuthenticationException
     * @throws \SumUp\Exceptions\SumUpSDKException
     */
    public function deletePaymentInstruments($customerId, $cardToken)
    {
        if (empty($customerId)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('customer id'));
        }
        if (empty($cardToken)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('card token'));
        }
        $path = '/v0.1/customers/' . $customerId . '/payment-instruments/' . $cardToken;
        $headers = array_merge(Headers::getStandardHeaders(), Headers::getAuth($this->accessToken));

        return $this->client->send('DELETE', $path, [], $headers);
    }
}
