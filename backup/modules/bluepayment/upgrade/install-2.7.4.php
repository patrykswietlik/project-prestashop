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
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_7_4()
{
    define('TABLE', _DB_PREFIX_ . 'blue_transactions');
    $columnExists = Db::getInstance()->executeS('SELECT COUNT(*) as `count`
       FROM information_schema.columns WHERE table_schema = "' . _DB_NAME_ . '"
       AND table_name = "' . TABLE . '"
       AND column_name = "gtag_uid"');

    $sql = [];
    if (isset($columnExists[0]) && !(int) $columnExists[0]['count']) {
        $sql[] = 'ALTER TABLE `' . TABLE . '` ADD `gtag_uid` varchar(256) DEFAULT NULL AFTER `order_id`';
        $sql[] = 'ALTER TABLE `' . TABLE . '` ADD `gtag_state` int(1) DEFAULT NULL AFTER `order_id`';
    }

    if ($sql) {
        foreach ($sql as $query) {
            if (Db::getInstance()->execute($query)) {
                return Db::getInstance()->getMsgError();
            }
        }
    }

    return true;
}
