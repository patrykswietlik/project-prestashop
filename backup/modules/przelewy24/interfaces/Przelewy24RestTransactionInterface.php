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
 * Interface Przelewy24RestTransactionInterface
 */
interface Przelewy24RestTransactionInterface
{
    /**
     * Register.
     *
     * @param Przelewy24PayloadForRestTransaction $payload
     *
     * @return string
     */
    public function register($payload);

    /**
     * Verify.
     *
     * @param Przelewy24PayloadForRestTransactionVerify $payload
     *
     * @return string
     */
    public function verify($payload);
}
