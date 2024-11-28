<?php
/**
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24RestAbstract
 */
class Przelewy24RestAbstract
{
    const URL_PRODUCTION = 'https://secure.przelewy24.pl/api/v1';
    const URL_TEST = 'https://sandbox.przelewy24.pl/api/v1';

    /**
     * Shop id.
     *
     * @var int|null
     */
    protected $shopId;

    /**
     * Api key.
     *
     * @var string|null
     */
    protected $apiKey;

    /**
     * Url.
     *
     * @var string|null
     */
    protected $url;

    /**
     * Salt.
     *
     * @var string|null
     */
    protected $salt;

    /**
     * Przelewy24RestAbstract constructor.
     *
     * @param int $shopId
     * @param string $apiKey
     * @param string $salt
     * @param bool $isTest
     */
    public function __construct($shopId, $apiKey, $salt, $isTest)
    {
        $this->shopId = (int) $shopId;
        $this->apiKey = (string) $apiKey;
        $this->salt = (string) $salt;
        if ($isTest) {
            $this->url = self::URL_TEST;
        } else {
            $this->url = self::URL_PRODUCTION;
        }
    }

    /**
     * Call rest command
     *
     * @param string $path
     * @param array|object|null $payload
     * @param string $method
     *
     * @return array
     */
    protected function call($path, $payload, $method)
    {
        $json_style = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        $options = [
            CURLOPT_USERPWD => $this->shopId . ':' . $this->apiKey,
            CURLOPT_URL => $this->url . $path,
            CURLOPT_RETURNTRANSFER => true,
        ];
        if ('PUT' === $method) {
            $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
        }
        if ('GET' !== $method) {
            $headers = [
                'Content-Type: application/json',
            ];
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_HTTPHEADER] = $headers;
            $options[CURLOPT_POSTFIELDS] = json_encode($payload, $json_style);
        }

        $h = curl_init();
        curl_setopt_array($h, $options);
        $ret = curl_exec($h);
        curl_close($h);

        $decoded = json_decode($ret, true);
        if (!is_array($decoded)) {
            $decoded = [];
        }

        return $decoded;
    }
}
