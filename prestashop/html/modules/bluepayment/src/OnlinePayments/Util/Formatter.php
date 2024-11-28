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
class Formatter
{
    /**
     * Format amount.
     *
     * @param float|number $amount
     *
     * @return string
     */
    public static function formatAmount($amount)
    {
        $amount = str_replace([',', ' '], '', $amount);
        $amount = number_format((float) $amount, 2, '.', '');

        return $amount;
    }

    /**
     * Format description.
     *
     * @param string $value
     *
     * @return string
     */
    public static function formatDescription($value)
    {
        $value = trim($value);

        if (EnvironmentRequirements::hasPhpExtension('iconv')) {
            $return = iconv('UTF-8', 'ASCII//TRANSLIT', $value);

            return $return;
        }

        if (EnvironmentRequirements::hasPhpExtension('mbstring')) {
            $tmp = ini_get('mbstring.substitute_character');
            @ini_set('mbstring.substitute_character', 'none');

            $return = mb_convert_encoding($value, 'ASCII', 'UTF-8');
            @ini_set('mbstring.substitute_character', $tmp);

            return $return;
        }

        return $value;
    }
}
