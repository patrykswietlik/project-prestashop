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
 * Class Przelewy24RestBlikFactory
 *
 * One of factories for Przelewy24 plugin.
 *
 * The class is aware of the whole configuration.
 */
class Przelewy24RestBlikFactory
{
    /**
     * Create instance of Przelewy24RestBlik
     *
     * @return Przelewy24RestBlik|null
     *
     * @throws Exception
     */
    public static function buildDefault()
    {
        try {
            return self::buildForSuffix('');
        } catch (UnexpectedValueException $e) {
            /* If we have this error, there is no default. */
            return null;
        }
    }

    /**
     * Create instance of Przelewy24RestCardFactory.
     *
     * @param string $suffix money suffix
     *
     * @return Przelewy24RestBlik
     */
    public static function buildForSuffix($suffix)
    {
        $posId = (int) Configuration::get('P24_SHOP_ID' . $suffix);
        $apiKey = (string) Configuration::get('P24_API_KEY' . $suffix);
        $salt = Configuration::get('P24_SALT' . $suffix);
        $testMode = (bool) Configuration::get('P24_TEST_MODE' . $suffix);

        return new Przelewy24RestBlik($posId, $apiKey, $salt, $testMode);
    }
}
