<?php
/**
 * NOTICE OF LICENSE
 * This source file is subject to the GNU Lesser General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/lgpl-3.0.en.html
 *
 * @author     Autopay S.A.
 * @copyright  Since 2015 Autopay S.A.
 * @license    https://www.gnu.org/licenses/lgpl-3.0.en.html GNU Lesser General Public License
 */

namespace BlueMedia\OnlinePayments\Util;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class Logger
{
    public const EMERGENCY = LogLevel::EMERGENCY;
    public const ALERT = LogLevel::ALERT;
    public const CRITICAL = LogLevel::CRITICAL;
    public const ERROR = LogLevel::ERROR;
    public const WARNING = LogLevel::WARNING;
    public const NOTICE = LogLevel::NOTICE;
    public const INFO = LogLevel::INFO;
    public const DEBUG = LogLevel::DEBUG;

    /** @var LoggerInterface */
    protected static $logger;

    /**
     * Sets a logger.
     *
     * @param LoggerInterface $logger
     */
    public static function setLogger(LoggerInterface $logger)
    {
        self::$logger = $logger;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public static function log($level, $message, array $context = [])
    {
        if (self::$logger instanceof LoggerInterface) {
            self::$logger->log($level, $message, $context);
        }
    }
}
