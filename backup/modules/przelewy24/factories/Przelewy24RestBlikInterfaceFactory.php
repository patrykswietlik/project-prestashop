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
 * Class Przelewy24RestBlikInterfaceFactory
 *
 * One of factories for Przelewy24 plugin.
 */
class Przelewy24RestBlikInterfaceFactory
{
    /**
     * Create instance of Przelewy24RestBlikInterface for suffix.
     *
     * @param string $suffix money suffix
     *
     * @return Przelewy24RestBlikInterface
     *
     * @throws Exception
     */
    public static function getForSuffix($suffix)
    {
        return Przelewy24RestBlikFactory::buildForSuffix($suffix);
    }

    /**
     * Create default instance of Przelewy24RestBlikInterface.
     *
     * @return Przelewy24RestBlikInterface
     *
     * @throws Exception
     */
    public static function getDefault()
    {
        $default = Przelewy24RestBlikFactory::buildDefault();
        if (!$default) {
            /* There is no default */
            $default = new Przelewy24RestBlikEmpty();
        }

        return $default;
    }
}
