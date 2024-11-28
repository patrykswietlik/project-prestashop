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
 * Upgrades module.
 *
 * @return bool
 */
function upgrade_module_1_3_76()
{
    $p24OrdersTable = addslashes(_DB_PREFIX_ . Przelewy24Order::TABLE);
    $sql = '
		  ALTER TABLE `' . $p24OrdersTable . '` ADD (
		    `amount` BIGINT,
		    `received` DATETIME
		  )';

    $success = Db::getInstance()->Execute($sql);

    if (!$success) {
        /* This is the best way to communicate problem with update. */
        throw new Exception('Cannot update module Przelewy24 to version 1.3.76.');
    }

    return true;
}
