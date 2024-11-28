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
class Przelewy24RestBig extends Przelewy24RestAbstract
{
    const NON_PLN_PAYMENT_IDS = [
        145,
        171,
        172,
        173,
        203,
        204,
        205,
        206,
        207,
        209,
        210,
        211,
        212,
        213,
        214,
        215,
        216,
        217,
        219,
    ];

    const ANY_CURRENCY_PAYMENT_IDS = [
        66,
        92,
        124,
        140,
        145,
        152,
        218,
        229,
        241,
        242,
        252,
        253,
        265,
    ];

    const BLIK_PAYMENT_IDS = [
        181,
    ];

    const PAY_PO_ID = 227;

    /**
     * Cached payment list.
     *
     * @var Przelewy24CachedPaymentList|null
     */
    private $cachedPaymentList;

    /**
     * Set payment cache.
     *
     * @param Przelewy24CachedPaymentList|null $cachedPaymentList
     */
    public function setPaymentCache($cachedPaymentList)
    {
        $this->cachedPaymentList = $cachedPaymentList;
    }

    /**
     * Returns non pln payment channels.
     *
     * @param array $paymentMethodList
     *
     * @return array
     */
    public static function getChannelsNonPln($paymentMethodList)
    {
        $channelsNonPln = array_merge(self::ANY_CURRENCY_PAYMENT_IDS, self::NON_PLN_PAYMENT_IDS);

        foreach (array_keys($paymentMethodList) as $key) {
            if (!in_array($key, $channelsNonPln)) {
                unset($paymentMethodList[$key]);
            }
        }

        return $paymentMethodList;
    }

    /**
     * Filters out non pln payment channels.
     *
     * @param array $paymentMethodList
     *
     * @return array
     */
    public function filterOutNonAllowedPaymentMethodsInPln($paymentMethodList)
    {
        $channelsNonPln = self::NON_PLN_PAYMENT_IDS;

        foreach (array_keys($paymentMethodList) as $key) {
            if (in_array($key, $channelsNonPln)) {
                unset($paymentMethodList[$key]);
            }
        }

        return $paymentMethodList;
    }

    /**
     * Filter out BLIK if config.
     *
     * @param array $paymentMethodList
     * @param string $suffix
     *
     * @return array
     */
    public static function filterOutBlikIfConfig($paymentMethodList, $suffix)
    {
        $includeBlik = Configuration::get('P24_BLIK_SHOW_TO_CUSTOMER' . $suffix);
        if ($includeBlik) {
            return $paymentMethodList;
        }
        $filterOut = array_flip(self::BLIK_PAYMENT_IDS);

        return array_diff_key($paymentMethodList, $filterOut);
    }

    /**
     * Tests api access.
     *
     * @return bool
     */
    public function apiTestAccess()
    {
        $path = '/testAccess';
        $data = $this->call($path, null, 'GET');

        return isset($data['error']) && empty($data['error']);
    }

    /**
     * Returns available payment methods.
     *
     * @return array
     */
    public function availablePaymentMethods()
    {
        $result = [];
        $toProcess = null;
        $language = Context::getContext()->language->iso_code;

        if (isset($this->cachedPaymentList)) {
            $toProcess = $this->cachedPaymentList->getList($language);
        } else {
            $res = $this->paymentMethods($language);
            if (isset($res['data']) && $res['data']) {
                $toProcess = $res['data'];
            }
        }

        if ($toProcess) {
            $is218MethodSet = false;
            foreach ($toProcess as $item) {
                if ($item['status']) {
                    $result[$item['id']] = $item['name'];
                    if (218 === (int) $item['id']) {
                        $is218MethodSet = true;
                    }
                }
            }
            if ($is218MethodSet) {
                unset($result[142], $result[145]);
            }
        }

        return $result;
    }

    /**
     * Retrieves payment methods for a given language.
     *
     * @param string $lang The language for which to retrieve payment methods
     *
     * @return array An array containing the payment methods
     */
    public function paymentMethods($lang)
    {
        $ret = $this->paymentMethodsInternal($lang);
        $new_data = [];
        /* Loop to add optional filters. */
        foreach ($ret['data'] as $row) {
            $new_data[] = $row;
        }
        $ret['data'] = $new_data;

        return $ret;
    }

    /**
     * Retrieves payment methods for a given language (internal).
     *
     * @param string $lang The language for which to retrieve payment methods
     *
     * @return array An array containing the payment methods
     */
    private function paymentMethodsInternal($lang)
    {
        if (!in_array($lang, ['pl', 'en'])) {
            /* Force a supported language. */
            $lang = 'en';
        }
        $path = '/payment/methods/' . $lang;
        $ret = $this->call($path, null, 'GET');

        return $ret;
    }

    /**
     * Returns available payment methods for consumer.
     *
     * @param string $currency
     *
     * @return array
     */
    public function availablePaymentMethodsForConsumer($currency)
    {
        $filters = $this->getAdditionalFiltersForConsumer($currency);
        $set = $this->availablePaymentMethods();
        foreach ($filters as $filter) {
            $set = $filter($set);
        }

        return $set;
    }

    /**
     * Get payment list.
     *
     * @param string $apiKey
     * @param string $currency
     * @param string $firstConfName
     * @param bool $secondConfName
     * @param array $additionalFilters
     *
     * @return array
     */
    private function getPaymentList($currency, $firstConfName, $secondConfName, $additionalFilters)
    {
        $suffix = Przelewy24Helper::getSuffix($currency);

        $paymethodListFirst = [];
        $paymethodListSecond = [];

        $paymethodList = $this->availablePaymentMethods();

        if ('' === $suffix) {
            $paymethodList = $this->filterOutNonAllowedPaymentMethodsInPln($paymethodList);
        }

        if ($suffix) {
            $paymethodList = $this->getChannelsNonPln($paymethodList);
        }

        foreach ($additionalFilters as $filter) {
            $paymethodList = $filter($paymethodList);
        }
        $paymethodList = $this->replacePaymentDescriptionsListToOwn($paymethodList, $suffix);

        $firstList = Configuration::get($firstConfName . $suffix);
        $firstList = explode(',', $firstList);
        $secondList = [];

        if ($secondConfName) {
            $secondList = Configuration::get($secondConfName . $suffix);
            $secondList = explode(',', $secondList);
        }

        if (count($firstList)) {
            foreach ($firstList as $item) {
                if ((int) $item > 0 && isset($paymethodList[(int) $item])) {
                    $paymethodListFirst[(int) $item] = $paymethodList[(int) $item];
                    unset($paymethodList[(int) $item]);
                }
            }
        }

        if (count($secondList)) {
            foreach ($secondList as $item) {
                if ((int) $item > 0 && isset($paymethodList[(int) $item])) {
                    $paymethodListSecond[(int) $item] = $paymethodList[(int) $item];
                    unset($paymethodList[(int) $item]);
                }
            }
        }

        $paymethodListSecond += $paymethodList;

        return [$paymethodListFirst, $paymethodListSecond];
    }

    /**
     * Replace payment names in array with user defined.
     *
     * @param array $paymethodList
     * @param $suffix
     *
     * @return array
     */
    public function replacePaymentDescriptionsListToOwn(array $paymethodList, $suffix)
    {
        foreach ($paymethodList as $bankId => $bankName) {
            if (($value = $this->replacePaymentDescriptionToOwn($bankId, $bankName, $suffix)) !== false) {
                $paymethodList[$bankId] = $value;
            }
        }

        return $paymethodList;
    }

    /**
     * Replace payment method name to user defined.
     *
     * @param $bankId
     * @param $bankName
     * @param $suffix
     *
     * @return bool
     */
    private function replacePaymentDescriptionToOwn($bankId, $bankName, $suffix)
    {
        if (($value = Configuration::get("P24_PAYMENT_DESCRIPTION_{$bankId}{$suffix}")) && ($value !== $bankName)) {
            return $value;
        }

        return false;
    }

    /**
     * Get first and second payment list.
     *
     * @param string $currency
     *
     * @return array
     */
    public function getFirstAndSecondPaymentList($currency)
    {
        return $this->getFirstAndSecondPaymentListInternal($currency, []);
    }

    /**
     * Get first and second payment list for consumer.
     *
     * @param string $currency
     *
     * @return array
     */
    public function getFirstAndSecondPaymentListForConsumer($currency)
    {
        $additionalFilters = $this->getAdditionalFiltersForConsumer($currency);

        return $this->getFirstAndSecondPaymentListInternal($currency, $additionalFilters);
    }

    /**
     * Get first and second payment list internal.
     *
     * @param string $currency
     *
     * @return array
     */
    public function getFirstAndSecondPaymentListInternal($currency, $additionalFilters)
    {
        list($paymethodListFirst, $paymethodListSecond) = $this->getPaymentList(
            $currency,
            'P24_PAYMENTS_ORDER_LIST_FIRST',
            'P24_PAYMENTS_ORDER_LIST_SECOND',
            $additionalFilters
        );

        return [
            'p24_paymethod_list_first' => $paymethodListFirst,
            'p24_paymethod_list_second' => $paymethodListSecond,
        ];
    }

    /**
     * Get promoted payment list.
     *
     * @param string $currency
     *
     * @return array
     */
    public function getPromotedPaymentList($currency)
    {
        return $this->getPromotedPaymentListInternal($currency, []);
    }

    /**
     * Get promoted payment list for consumer.
     *
     * @param string $currency
     *
     * @return array
     */
    public function getPromotedPaymentListForConsumer($currency)
    {
        $additionalFilters = $this->getAdditionalFiltersForConsumer($currency);

        return $this->getPromotedPaymentListInternal($currency, $additionalFilters);
    }

    /**
     * Get promoted payment list.
     *
     * @param string $currency
     * @param array $additionalFilters
     *
     * @return array
     */
    public function getPromotedPaymentListInternal($currency, $additionalFilters)
    {
        list($paymethodListFirst, $paymethodListSecond) = $this->getPaymentList(
            $currency,
            'P24_PAYMENTS_PROMOTE_LIST',
            false,
            $additionalFilters
        );

        return [
            'p24_paymethod_list_promote' => $paymethodListFirst,
            'p24_paymethod_list_promote_2' => $paymethodListSecond,
        ];
    }

    /**
     * Get additional filters for consumer.
     *
     * @param $currency string
     */
    private function getAdditionalFiltersForConsumer($currency)
    {
        $suffix = Przelewy24Helper::getSuffix($currency);

        return [
            function ($list) use ($suffix) {
                return self::filterOutBlikIfConfig($list, $suffix);
            },
        ];
    }
}
