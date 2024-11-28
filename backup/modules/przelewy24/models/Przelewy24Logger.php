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
 * Class Przelewy24Logger
 */
class Przelewy24Logger extends PrestaShopLoggerCore
{
    // Log message limit.
    const LOG_MESSAGE_LIMIT = 2 ** 14;

    /**
     * Add truncated log.
     *
     * @param string $message log message
     * @param string $truncMessage comment about shortening message
     *
     * @return bool
     */
    public static function addTruncatedLog($message, $truncMessage = 'Log message has been shortened.')
    {
        if (Tools::strlen($message) >= self::LOG_MESSAGE_LIMIT) {
            $message = mb_substr($message, 0, self::LOG_MESSAGE_LIMIT - 128, 'utf-8') . '…';
            self::addLog($truncMessage, 2);
        }

        return self::addLog($message);
    }
}
