<?php
/**
 * 2017-2022 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    MBE Worldwide
 * @copyright 2017-2024 MBE Worldwide
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of MBE Worldwide
 */

namespace PrestaShop\Module\Mbeshipping\Helper;

if (!defined('_PS_VERSION_')) {
    exit;
}

class LoggerHelper
{
    // Define and set the class constants
    const EMAIL = 0;        // Errors with sending/dealing with emails
    const DATABASE = 1;     // Errors with any sort of database work
    const SERVER = 2;       // 404 type server errors
    const INFO = 3;         // Basic information messages
    const SCRIPT = 4;       // Errors encountered by running scripts
    const DEBUG = 5;        // Debug messages
    const ALL = 6;          // All messages
    const ARGUMENTS = 'none';
    // Define the static variables that may be used
    private static $permissions = 0777;             // File writing permissions
    private static $timestamp = 'Y-m-d G:i:s';      // Format for the log timestamp
    private static $date = 'Y-m-d';                 // Format for the file name
    // Define the variables that may be used
    private $pathway = null;
    private $status = false;
    private $file = null;

    // Construct the class object when it is called initially
    public function __construct($_directory = '', $_severity = self::DEBUG)
    {
        // Create the full pathway to the log file to record to
        if ($_directory == '') {
            $_directory = _PS_MODULE_DIR_ . 'mbeshipping' . DIRECTORY_SEPARATOR . 'log/';
        }
        $this->pathway = $_directory . 'log_' . date(self::$date) . '.log';
        // Set the threshold for logging (match the severity level to record)
        $this->threshold = $_severity;
        // Check for and create the directory for the logs if not created already
        if (!file_exists($_directory)) {
            mkdir($_directory, self::$permissions, true);
        }

        $this->status = (\Configuration::get('mbe_debug') == 1);

        if($this->status) {

            // Check if the log file exists and can be written to
            if (file_exists($this->pathway) && !is_writable($this->pathway)) {
                // Set the flag so we don't attempt to write later
                $this->status = false;
            }
            // Open the log file for writting
            if (($this->file = fopen($this->pathway, 'a'))) {
                // Set the flag so we can write later
                $this->status = true;
            } else {
                // Set the flag so we don't attempt to write later
                $this->status = false;
            }
        }
    }

    // Log: Debugging
    public function logDebug($_line, $_args = self::ARGUMENTS)
    {
        $this->writeLog($_line, self::DEBUG, $_args);
    }

    // Write the constructed string into the log file
    public function writeLog($_line, $_severity, $_args = self::ARGUMENTS)
    {
        // Check if we can write to the log file first
        if (!$this->status) {
            // There is some reason we cannot write to the log file
            return false;
        }
        // Check to make sure the severity is not higher then the threshold set earlier
        if ($_severity > $this->threshold) {
            // A message above the threshold is trying to log so ignore it
            return false;
        }
        // Build the string for the log
        $line = $this->buildString($_severity, $_line);
        // Check for and add any additional arguments if passed
        if ($_args !== self::ARGUMENTS) {
            $line .= ' (' . var_export($_args, true) . ')';
        }
        // Add the proper 'new line' character to the end of the line
        $line .= "\r\n";
        // Write to the log file
        if (fwrite($this->file, $line) === false) {
            // Writting to the log failed for some reason
            return false;
        }
    }

    // Return the string with in its constructed format
    private function buildString($_level, $_line)
    {
        // Build the timestamp
        $time = date(self::$timestamp);
        if ($_line instanceof \stdClass) {
            $_line = print_r($_line, true);
        }
        if (is_array($_line)) {
            foreach ($_line as $k => $l) {
                if ($l instanceof \stdClass) {
                    $_line[$k] = print_r($l, true);
                }
            }
            $_line = implode("\n", $_line);
        }
        // Build the string based on the passed level
        switch ($_level) {
            case self::EMAIL:
                return "$time - EMAIL --> $_line";
            case self::DATABASE:
                return "$time - DATABASE --> $_line";
            case self::SERVER:
                return "$time - SERVER --> $_line";
            case self::INFO:
                return "$time - INFO --> $_line";
            case self::SCRIPT:
                return "$time - SCRIPT --> $_line";
            case self::DEBUG:
                return "$time - DEBUG --> $_line";
            default:
                return "$time - LOG --> $_line";
        }
    }
}
