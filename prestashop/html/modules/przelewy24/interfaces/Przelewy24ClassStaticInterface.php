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
 * Interface Przelewy24ClassStaticInterface
 */
interface Przelewy24ClassStaticInterface
{
    /**
     * Returns host URL For Environmen
     *
     * @param bool $isTestMode
     *
     * @return string
     */
    public static function getHostForEnvironment($isTestMode = false);
}
