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
 * Class Checkouts
 */
class Checkouts implements SumUpService
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
     * Create new checkout.
     *
     * @param float $amount
     * @param string $currency
     * @param string $checkoutRef
     * @param string $payToEmail
     * @param string $description
     * @param null $payFromEmail
     * @param null $returnURL
     *
     * @return \SumUp\HttpClients\Response
     *
     * @throws SumUpArgumentException
     * @throws \SumUp\Exceptions\SumUpConnectionException
     * @throws \SumUp\Exceptions\SumUpResponseException
     * @throws \SumUp\Exceptions\SumUpAuthenticationException
     * @throws \SumUp\Exceptions\SumUpSDKException
     */
    public function create($amount, $currency, $checkoutRef, $payToEmail, $description = '', $payFromEmail = null, $returnURL = null)
    {
        if (empty($amount) || !is_numeric($amount)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('amount'));
        }
        if (empty($currency)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('currency'));
        }
        if (empty($checkoutRef)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('checkout reference id'));
        }
        if (empty($payToEmail)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('pay to email'));
        }
        $payload = [
            'amount' => $amount,
            'currency' => $currency,
            'checkout_reference' => $checkoutRef,
            'pay_to_email' => $payToEmail,
            'description' => $description,
        ];
        if (isset($payFromEmail)) {
            $payload['pay_from_email'] = $payFromEmail;
        }
        if (isset($returnURL)) {
            $payload['return_url'] = $returnURL;
        }
        $path = '/v0.1/checkouts';
        $headers = array_merge(Headers::getStandardHeaders(), Headers::getAuth($this->accessToken));

        return $this->client->send('POST', $path, $payload, $headers);
    }

    /**
     * Get single checkout by provided checkout ID.
     *
     * @param string $checkoutId
     *
     * @return \SumUp\HttpClients\Response
     *
     * @throws SumUpArgumentException
     * @throws \SumUp\Exceptions\SumUpConnectionException
     * @throws \SumUp\Exceptions\SumUpResponseException
     * @throws \SumUp\Exceptions\SumUpAuthenticationException
     * @throws \SumUp\Exceptions\SumUpSDKException
     */
    public function findById($checkoutId)
    {
        if (empty($checkoutId)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('checkout id'));
        }
        $path = '/v0.1/checkouts/' . $checkoutId;
        $headers = array_merge(Headers::getStandardHeaders(), Headers::getAuth($this->accessToken));

        return $this->client->send('GET', $path, [], $headers);
    }

    /**
     * Get single checkout by provided checkout reference ID.
     *
     * @param string $referenceId
     *
     * @return \SumUp\HttpClients\Response
     *
     * @throws SumUpArgumentException
     * @throws \SumUp\Exceptions\SumUpConnectionException
     * @throws \SumUp\Exceptions\SumUpResponseException
     * @throws \SumUp\Exceptions\SumUpAuthenticationException
     * @throws \SumUp\Exceptions\SumUpSDKException
     */
    public function findByReferenceId($referenceId)
    {
        if (empty($referenceId)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('reference id'));
        }
        $path = '/v0.1/checkouts?checkout_reference=' . $referenceId;
        $headers = array_merge(Headers::getStandardHeaders(), Headers::getAuth($this->accessToken));

        return $this->client->send('GET', $path, [], $headers);
    }

    /**
     * Delete a checkout.
     *
     * @param string $checkoutId
     *
     * @return \SumUp\HttpClients\Response
     *
     * @throws SumUpArgumentException
     * @throws \SumUp\Exceptions\SumUpConnectionException
     * @throws \SumUp\Exceptions\SumUpResponseException
     * @throws \SumUp\Exceptions\SumUpAuthenticationException
     * @throws \SumUp\Exceptions\SumUpSDKException
     */
    public function delete($checkoutId)
    {
        if (empty($checkoutId)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('checkout id'));
        }
        $path = '/v0.1/checkouts/' . $checkoutId;
        $headers = array_merge(Headers::getStandardHeaders(), Headers::getAuth($this->accessToken));

        return $this->client->send('DELETE', $path, [], $headers);
    }

    /**
     * Pay a checkout with tokenized card.
     *
     * @param string $checkoutId
     * @param string $customerId
     * @param string $cardToken
     * @param int $installments
     *
     * @return \SumUp\HttpClients\Response
     *
     * @throws SumUpArgumentException
     * @throws \SumUp\Exceptions\SumUpConnectionException
     * @throws \SumUp\Exceptions\SumUpResponseException
     * @throws \SumUp\Exceptions\SumUpAuthenticationException
     * @throws \SumUp\Exceptions\SumUpSDKException
     */
    public function pay($checkoutId, $customerId, $cardToken, $installments = 1)
    {
        if (empty($checkoutId)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('checkout id'));
        }
        if (empty($customerId)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('customer id'));
        }
        if (empty($cardToken)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('card token'));
        }
        if (empty($installments) || !is_int($installments)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('installments'));
        }
        $payload = [
            'payment_type' => 'card',
            'customer_id' => $customerId,
            'token' => $cardToken,
            'installments' => $installments,
        ];
        $path = '/v0.1/checkouts/' . $checkoutId;
        $headers = array_merge(Headers::getStandardHeaders(), Headers::getAuth($this->accessToken));

        return $this->client->send('PUT', $path, $payload, $headers);
    }

    public function payWithCard($checkoutId, $card)
    {
        if (empty($checkoutId)) {
            throw new SumUpArgumentException(ExceptionMessages::getMissingParamMsg('checkout id'));
        }

        $payload = [
            'payment_type' => 'card',
            'card' => $card,
        ];
        $path = '/v0.1/checkouts/' . $checkoutId;
        $headers = array_merge(Headers::getStandardHeaders(), Headers::getAuth($this->accessToken));

        return $this->client->send('PUT', $path, $payload, $headers);
    }
}
