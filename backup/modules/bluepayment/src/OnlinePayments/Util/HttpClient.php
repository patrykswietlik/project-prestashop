<?php
/**
 * NOTICE OF LICENSE
 * This source file is subject to the GNU Lesser General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/lgpl-3.0.en.html
 *
 * @author     Autopay S.A.
 * @copyright  Since 2015 Autopay S.A.
 * @license    https://www.gnu.org/licenses/lgpl-3.0.en.html GNU Lesser General Public License
 */

namespace BlueMedia\OnlinePayments\Util;

if (!defined('_PS_VERSION_')) {
    exit;
}

use GuzzleHttp;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    /** @var GuzzleHttp\Client */
    private $guzzleClient;

    public function __construct()
    {
        $this->guzzleClient = new GuzzleHttp\Client(
            [
                GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => false,
                GuzzleHttp\RequestOptions::HTTP_ERRORS => false,
                GuzzleHttp\RequestOptions::VERIFY => true,
                'exceptions' => false,
            ]
        );
    }

    /**
     * Perform POST request.
     *
     * @param string $url
     * @param array $headers
     * @param null $data
     * @param array $options
     *
     * @return ResponseInterface
     */
    public function post($url, array $headers = [], $data = null, array $options = [])
    {
        $options = array_merge(
            $options,
            [
                GuzzleHttp\RequestOptions::HEADERS => $headers,
                GuzzleHttp\RequestOptions::BODY => $data,
            ]
        );

        return $this->guzzleClient->post($url, $options);
    }
}
