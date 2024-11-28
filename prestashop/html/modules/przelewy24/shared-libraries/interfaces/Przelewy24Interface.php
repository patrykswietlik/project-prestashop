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
 * Interface Przelewy24Interface
 */
interface Przelewy24Interface
{
    /**
     * Set translations.
     *
     * @param array
     */
    public function setTranslations(array $params = []);
}
