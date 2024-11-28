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

declare(strict_types=1);

namespace BluePayment\Analyse;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AnalyticsTracking
{
    private $trackedId;
    private $sessionGa;
    private $secretApi;

    public function __construct($trackedId, $sessionGa, $secretApi = null)
    {
        $this->trackedId = $trackedId;
        $this->sessionGa = $sessionGa;
        $this->secretApi = $secretApi;
    }

    /**
     * Handle cid _ga cookie
     *
     * @return false|mixed
     */
    public function gaParseCookie()
    {
        $ver = false;
        $domain = false;
        $cid1 = false;
        $cid2 = false;

        [$ver, $domain, $cid1, $cid2] = explode('.', $this->sessionGa, 4);
        $contents = ['version' => $ver, 'domainDepth' => $domain, 'cid' => $cid1 . '.' . $cid2];

        return $contents['cid'];
    }

    /**
     * Data send with curl
     *
     * @param array $data
     *
     * @return array
     */
    public function gaSendData(array $data): array
    {
        $postUrl = 'https://www.google-analytics.com/collect?';
        $postUrl .= http_build_query($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'statusCode' => $statusCode,
            'resp' => $result,
        ];
    }

    /**
     * Data send with curl
     *
     * @param array $data
     *
     * @return array
     */
    public function ga4SendData(array $data): array
    {
        $postUrl = 'https://www.google-analytics.com/mp/collect?measurement_id='
            . $this->trackedId . '&api_secret=' . $this->secretApi;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        $result = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'statusCode' => $statusCode,
            'resp' => $result,
        ];
    }

    /**
     * Tracking Universal Ga
     *
     * @param $category
     * @param $action
     * @param $label
     * @param array $products
     *
     * @return array
     */
    public function gaSendEvent($category = null, $action = null, $label = null, array $products = []): array
    {
        $data = [
            'v' => 1,
            'tid' => $this->trackedId,
            'cid' => $this->gaParseCookie(),
            't' => 'event',
            'ec' => $category, // (Required)
            'ea' => $action, // (Required)
            'el' => $label,
        ];

        $dataMerge = array_merge($data, $products);

        return $this->gaSendData($dataMerge);
    }

    /**
     * Tracking GA 4
     *
     * @param array $products
     *
     * @return array
     */
    public function ga4SendEvent(array $products = []): array
    {
        $data = [
            'client_id' => $this->gaParseCookie(),
        ];

        $dataMerge = array_merge($data, $products);

        return $this->ga4SendData((array) json_encode($dataMerge, JSON_PRETTY_PRINT));
    }
}
