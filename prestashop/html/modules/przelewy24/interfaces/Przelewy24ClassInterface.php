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
 * Interface Przelewy24ClassInterface
 */
interface Przelewy24ClassInterface
{
    /**
     * Returns host URL.
     *
     * @return string
     */
    public function getHost();

    /**
     * Returns URL for direct request (trnDirect).
     *
     * @return string
     */
    public function trnDirectUrl();

    /**
     * Adds value do post request.
     *
     * @param string $name argument name
     * @param int|string|bool $value argument value
     */
    public function addValue($name, $value);
}
