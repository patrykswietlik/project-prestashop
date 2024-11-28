<?php
/**
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
if (!defined('PRZELEWY24_LIB_ROOT')) {
    define('PRZELEWY24_LIB_ROOT', dirname(__FILE__));
}

/**
 * TODO: use autoload? It is possible in presta, magento, PHP5.2 ?
 */
require_once PRZELEWY24_LIB_ROOT . DIRECTORY_SEPARATOR . 'interfaces' . DIRECTORY_SEPARATOR . 'Przelewy24Interface.php';
require_once PRZELEWY24_LIB_ROOT . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'Przelewy24Product.php';
require_once PRZELEWY24_LIB_ROOT . DIRECTORY_SEPARATOR . 'installer' . DIRECTORY_SEPARATOR . 'Przelewy24Installer.php';
