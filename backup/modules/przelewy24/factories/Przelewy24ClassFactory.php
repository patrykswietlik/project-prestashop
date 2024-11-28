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
 * Class Przelewy24ClassFactory
 *
 * One of factories for Przelewy24 plugin.
 *
 * The class is aware of the whole configuration.
 */
class Przelewy24ClassFactory
{
    /**
     * Create instance of Przelewy24Class.
     *
     * @param string $suffix money suffix
     *
     * @return Przelewy24Class
     *
     * @throws Exception
     */
    public static function buildForSuffix($suffix)
    {
        $merchantId = (int) Configuration::get('P24_MERCHANT_ID' . $suffix);
        $posId = (int) Configuration::get('P24_SHOP_ID' . $suffix);
        $salt = Configuration::get('P24_SALT' . $suffix);
        $testMode = (bool) Configuration::get('P24_TEST_MODE' . $suffix);

        return self::buildFromParams($merchantId, $posId, $salt, $testMode);
    }

    /**
     * Create instance of Przelewy24Class.
     *
     * @return Przelewy24Class
     *
     * @throws Exception
     */
    public static function buildDefault()
    {
        return self::buildForSuffix('');
    }

    /**
     * Create instance of Przelewy24Class.
     *
     * @param int $merchantId
     * @param int $posId
     * @param string $salt
     * @param bool $testMode
     *
     * @return Przelewy24Class
     *
     * @throws Exception
     */
    public static function buildFromParams($merchantId, $posId, $salt, $testMode)
    {
        $merchantId = (int) $merchantId;
        $posId = (int) $posId;
        $salt = (string) $salt;
        $testMode = (bool) $testMode;

        return new Przelewy24Class($merchantId, $posId, $salt, $testMode);
    }
}
