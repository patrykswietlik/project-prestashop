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
 * Interface Przelewy24StatusSupportInterface
 */
interface Przelewy24StatusSupportInterface
{
    /**
     * Get payload for log.
     *
     * @return string
     */
    public function getPayloadForLog();

    /**
     * Get session id.
     *
     * @return string
     */
    public function getSessionId();

    /**
     * Get P24 order id.
     *
     * @return string
     */
    public function getP24OrderId();

    /**
     * Get P24 order id.
     *
     * @return string
     */
    public function getP24Number();

    /**
     * Possible card to save.
     *
     * @return bool
     */
    public function possibleCardToSave();

    /**
     * Verify payload.
     *
     * @param string $totalAmount;
     * @param Currency $currency
     * @param string $suffix
     *
     * @return bool
     */
    public function verify($totalAmount, $currency, $suffix);
}
