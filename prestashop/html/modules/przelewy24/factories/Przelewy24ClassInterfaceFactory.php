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
 * Class Przelewy24ClassInterfaceFactory
 *
 * One of factories for Przelewy24 plugin.
 */
class Przelewy24ClassInterfaceFactory
{
    /**
     * Create instance of Przelewy24ClassInterface based on suffix.
     *
     * @param string $suffix money suffix
     *
     * @return Przelewy24ClassInterface
     *
     * @throws Exception
     */
    public static function getForSuffix($suffix)
    {
        return Przelewy24ClassFactory::buildForSuffix($suffix);
    }
}
