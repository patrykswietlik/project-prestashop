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
 * Class Przelewy24RestCardInterfaceFactory
 *
 * One of factories for Przelewy24 plugin.
 */
class Przelewy24RestCardInterfaceFactory
{
    /**
     * Create instance of Przelewy24RestCardInterfaceFactory.
     *
     * @param string $suffix money suffix
     *
     * @return Przelewy24RestCardInterface
     */
    public static function buildForSuffix($suffix)
    {
        return Przelewy24RestCardFactory::buildForSuffix($suffix);
    }
}
