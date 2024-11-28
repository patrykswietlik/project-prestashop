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

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Psr7\Request;
use SumUp\Exceptions\SumUpConnectionException;
use SumUp\Exceptions\SumUpSDKException;
use SumUp\Exceptions\SumUpServerException;

/**
 * Class SumUpGuzzleHttpClient
 */
class SumUpGuzzleHttpClient implements SumUpHttpClientInterface
{
    /**
     * The Guzzle Client instance.
     *
     * @var Client
     */
    private $guzzleClient;

    /**
     * Custom headers for every request.
     *
     * @var
     */
    private $customHeaders;

    /**
     * SumUpGuzzleHttpClient constructor.
     *
     * @param string $baseUrl
     * @param array $customHeaders
     */
    public function __construct($baseUrl, $customHeaders)
    {
        $this->guzzleClient = new Client(['base_uri' => $baseUrl]);
        $this->customHeaders = $customHeaders;
    }

    /**
     * @param string $method the request method
     * @param string $url the endpoint to send the request to
     * @param array $body the body of the request
     * @param array $headers the headers of the request
     *
     * @return Response
     *
     * @throws SumUpConnectionException
     * @throws SumUpServerException
     * @throws \SumUp\Exceptions\SumUpResponseException
     * @throws \SumUp\Exceptions\SumUpAuthenticationException
     * @throws \SumUp\Exceptions\SumUpValidationException
     * @throws SumUpSDKException
     */
    public function send($method, $url, $body, $headers = [])
    {
        $options = [
            'headers' => array_merge($headers, $this->customHeaders),
            'json' => $body,
        ];

        $request = new Request($method, $url);

        try {
            $response = $this->guzzleClient->send($request, $options);
        } catch (ConnectException $e) {
            throw new SumUpConnectionException($e->getMessage(), $e->getCode(), $e->getPrevious());
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $body = $this->parseBody($response);

            return new Response($response->getStatusCode(), $body);
        } catch (ServerException $e) {
            $response = $e->getResponse();
            $body = $this->parseBody($response);
            if (isset($body) && isset($body->message)) {
                $message = $body->message;
            } else {
                $message = $body;
            }
            throw new SumUpServerException($message, $e->getCode(), $e->getPrevious());
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            throw new SumUpSDKException($e->getMessage(), $e->getCode(), $e->getPrevious());
        } catch (\Exception $e) {
            throw new SumUpSDKException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
        $body = $this->parseBody($response);

        return new Response($response->getStatusCode(), $body);
    }

    /**
     * Returns JSON encoded the response's body if it is of JSON type.
     *
     * @param $response
     *
     * @return mixed
     */
    private function parseBody($response)
    {
        $jsonBody = json_decode($response->getBody());
        if (isset($jsonBody)) {
            return $jsonBody;
        }

        return $response->getBody();
    }
}
