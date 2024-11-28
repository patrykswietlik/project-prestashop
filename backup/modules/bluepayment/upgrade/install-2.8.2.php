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

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

use BluePayment\Analyse\Amplitude;
use BluePayment\Config\Config;

function upgrade_module_2_8_2($module)
{
    $module->unregisterHook('header');
    $module->unregisterHook('displayHeader');
    $module->registerHook('actionFrontControllerSetMedia');

    $data = [
        'events' => [
            'event_type' => Config::PLUGIN_UPDATED,
            'user_properties' => [
                Config::PLUGIN_VERSION => $module->version,
            ],
        ],
    ];
    $amplitude = Amplitude::getInstance();
    $amplitude->sendEvent($data);

    $smarty = Context::getContext()->smarty;
    Tools::clearAllCache($smarty);
    Tools::clearCompile($smarty);

    return true;
}
