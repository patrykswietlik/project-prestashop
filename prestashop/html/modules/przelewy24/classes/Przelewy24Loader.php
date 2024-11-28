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
 * Autoloader
 *
 * @param string $class
 */
function autoloader($class)
{
    $directories = ['classes', 'factories', 'interfaces', 'models', 'controllers/front'];
    foreach ($directories as $directory) {
        $fileName = _PS_MODULE_DIR_ . 'przelewy24/' . $directory . '/' . $class . '.php';
        if ('index' === $class || !file_exists($fileName)) {
            continue;
        }
        include_once $fileName;
    }

    include_once _PS_MODULE_DIR_ . 'przelewy24/controllers/front/Przelewy24Controller.php';
}

spl_autoload_register('autoloader');
