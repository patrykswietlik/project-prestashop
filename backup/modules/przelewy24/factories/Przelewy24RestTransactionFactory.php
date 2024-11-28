<?php
/**
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/gpl.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24RestTransactionFactory
 *
 * One of factories for Przelewy24 plugin.
 *
 * The class is aware of the whole configuration.
 */
class Przelewy24RestTransactionFactory
{
    /**
     * Create instance of Przelewy24RestTransaction.
     *
     * @param string $suffix money suffix
     *
     * @return Przelewy24RestTransaction
     */
    public static function buildForSuffix($suffix)
    {
        $posId = (int) Configuration::get('P24_SHOP_ID' . $suffix);
        $apiKey = (string) Configuration::get('P24_API_KEY' . $suffix);
        $salt = Configuration::get('P24_SALT' . $suffix);
        $testMode = (bool) Configuration::get('P24_TEST_MODE' . $suffix);

        return new Przelewy24RestTransaction($posId, $apiKey, $salt, $testMode);
    }
}
