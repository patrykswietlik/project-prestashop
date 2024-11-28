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
 * Class Przelewy24ReastCard
 */
class Przelewy24CachedPaymentList
{
    /**
     * Shop id.
     *
     * @var int|null
     */
    private $shopId;

    /**
     * Api key.
     *
     * @var string|null
     */
    private $apiKey;

    /**
     * Url.
     *
     * @var string|null
     */
    private $url;

    /**
     * Salt.
     *
     * @var string|null
     */
    private $salt;

    /**
     * Salt.
     *
     * @var bool|null
     */
    private $isTest;

    /**
     * List.
     *
     * @var array|null
     */
    private $list;

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
        $this->isTest = (bool) $isTest;
        $this->list = [];
    }

    private function constructList($rawData)
    {
        $list = [];
        foreach ($rawData as $row) {
            $id = $row['id'];
            $list[$id] = $row;
        }

        return $list;
    }

    /**
     * Get payment list using cache.
     *
     * @param $lang
     *
     * @return array
     */
    public function getList($lang)
    {
        if (!isset($this->list[$lang])) {
            $restApi = new Przelewy24RestBig($this->shopId, $this->apiKey, $this->salt, $this->isTest);
            $res = $restApi->paymentMethods($lang);
            if (isset($res['data'])) {
                $list = $this->constructList($res['data']);
            } else {
                $list = [];
            }
            $this->list[$lang] = $list;
        }

        return $this->list[$lang];
    }
}
