<?php
/**
 * Class Przelewy24Helper
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24Helper
 */
class Przelewy24Helper
{
    /**
     * Available status colors.
     *
     * @var array
     */
    private static $statusColor = [
        0 => 'Lightblue',
        1 => 'Limegreen',
    ];

    /**
     * Available statuses template.
     *
     * @var array
     */
    private static $statusTemplate = [
        0 => '',
        1 => 'payment',
    ];

    /**
     * Statuses translations.
     *
     * @var array
     */
    private static $lang = [
        'pl' => [
            0 => 'Oczekiwanie na płatność Przelewy24',
            1 => 'Płatność Przelewy24 przyjęta',
        ],
        'en' => [
            0 => 'Waiting for payment Przelewy24',
            1 => 'The payment Przelewy24 has been accepted',
        ],
    ];

    /**
     * These statuses will be inserted into database.
     *
     * @var array
     */
    private static $status = [
        0 => ['statusKey' => 0, 'number' => 1, 'colorKey' => 0, 'paid' => 0, 'invoice' => 0, 'templateKey' => 0],
        1 => ['statusKey' => 1, 'number' => 2, 'colorKey' => 1, 'paid' => 1, 'invoice' => 1, 'templateKey' => 1],
    ];

    /**
     *  Add new order state to DB.
     */
    public static function addOrderState()
    {
        foreach (self::$status as $statusKey) {
            $title = self::getArrayLangByStatusKey((int) $statusKey['statusKey']);
            $template = self::getArrayTemplateByStatusKey((int) $statusKey['statusKey']);
            $number = (int) $statusKey['number'];
            $color = self::$statusColor[(int) $statusKey['colorKey']];
            $paid = (int) $statusKey['paid'];
            $invoice = (int) $statusKey['invoice'];

            $sql = new DbQuery();
            $sql->select('id_order_state');
            $sql->from('order_state');
            $sql->where('paid = ' . $paid . ' AND invoice = ' . $invoice . ' AND module_name = \'przelewy24\'');

            $orderStateExists = Db::getInstance()->getRow($sql->build());
            if ($orderStateExists) {
                $stateId = (int) $orderStateExists['id_order_state'];
            } else {
                $orderState = new OrderState();
                $orderState->name = $title;
                $orderState->template = $template;
                $orderState->unremovable = 1;
                $orderState->color = $color;
                $orderState->paid = $paid;
                $orderState->invoice = $invoice;
                $orderState->module_name = 'przelewy24';
                $orderState->add();
                $stateId = (int) $orderState->id;
            }

            Configuration::updateValue('P24_ORDER_STATE_' . $number, $stateId);
        }
    }

    /**
     * Get array lang by status key.
     *
     * @param int $statusKey
     *
     * @return array
     */
    private static function getArrayLangByStatusKey($statusKey)
    {
        $return = [];
        $langTable = Language::getLanguages(false, false);
        $arrayLang = self::$lang;
        foreach ($langTable as $langRow) {
            if (array_key_exists($langRow['iso_code'], $arrayLang)) {
                $return[$langRow['id_lang']] = $arrayLang[$langRow['iso_code']][$statusKey];
            } else {
                $return[$langRow['id_lang']] = $arrayLang['en'][$statusKey];
            }
        }

        return $return;
    }

    /**
     * Get array template by status key.
     *
     * @param int $statusKey
     *
     * @return array
     */
    private static function getArrayTemplateByStatusKey($statusKey)
    {
        $return = [];
        $langTable = Language::getLanguages(false, false);
        $arrayStatusTemplate = self::$statusTemplate;
        foreach ($langTable as $langRow) {
            $return[$langRow['id_lang']] = $arrayStatusTemplate[$statusKey];
        }

        return $return;
    }

    /**
     * Format amount.
     *
     * @param float $amount
     *
     * @return string
     */
    public static function p24AmountFormat($amount)
    {
        return number_format($amount * 100, 0, '', '');
    }

    /**
     * Render json.
     *
     * @param array $data
     */
    public static function renderJson($data)
    {
        header('Content-Type: application/json;charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Get suffix.
     *
     * @param string $currency
     *
     * @return string
     */
    public static function getSuffix($currency)
    {
        if ('PLN' === $currency) {
            return '';
        } else {
            return '_' . $currency;
        }
    }

    /**
     * Gets content of requested url.
     *
     * @param string $url
     *
     * @return string
     */
    public static function requestGet($url)
    {
        $isCurl = function_exists('curl_init')
            && function_exists('curl_setopt')
            && function_exists('curl_exec')
            && function_exists('curl_close');

        if ($isCurl) {
            $userAgent = 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)';
            $curlConnection = curl_init();
            curl_setopt($curlConnection, CURLOPT_URL, $url);
            curl_setopt($curlConnection, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($curlConnection, CURLOPT_USERAGENT, $userAgent);
            curl_setopt($curlConnection, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlConnection, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($curlConnection);
            curl_close($curlConnection);

            return $result;
        }

        return '';
    }
}
