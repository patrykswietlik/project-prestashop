<?php
/**
 * Class Przelewy24OneClickHelper
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24OneClickHelper
 */
class Przelewy24OneClickHelper
{
    /**
     * Get card payment ids.
     *
     * @return array
     */
    public static function getCardPaymentIds()
    {
        return [140, 142, 145, 218, 241, 242];
    }

    /**
     * Escape string.
     *
     * @param string $string
     *
     * @return string
     */
    public static function escape($string)
    {
        $string = trim($string);

        return htmlspecialchars($string);
    }

    /**
     * Is one click enabled.
     *
     * @param string $suffix
     *
     * @return bool
     *
     * @throws Exception
     */
    public static function isOneClickEnable($suffix = '')
    {
        return 1 === (int) Configuration::get('P24_ONECLICK_ENABLE' . $suffix);
    }
}
