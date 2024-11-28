<?php
/**
 * Class Przelewy24RestBlikEmpty
 *
 * @author    Przelewy24
 * @copyright Przelewy24
 * @license   https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24RestBlikEmpty
 *
 * This class is used if it is not possible to deliver working instance.
 * Added Logger to actually leave trace what is wrong.
 * Full rest integration will deprecate this and corresponding factory class.
 */
class Przelewy24RestBlikEmpty implements Przelewy24RestBlikInterface
{
    private $blikCode;
    private $token;

    /**
     * Execute payment by BLIK code.
     *
     * @param $token
     * @param $blikCode
     *
     * @return object|bool
     */
    public function executePaymentByBlikCode($token, $blikCode)
    {
        $this->blikCode = $blikCode;
        $this->token = $token;
        $this->logEmptySuffixErrorData();

        return false;
    }

    /**
     * Dump object state to log.
     */
    private function logEmptySuffixErrorData()
    {
        PrestaShopLogger::addLog(
            'No BlikRest class for suffix, probable misconfiguration details: ' . json_encode(get_object_vars($this))
        );
    }
}
