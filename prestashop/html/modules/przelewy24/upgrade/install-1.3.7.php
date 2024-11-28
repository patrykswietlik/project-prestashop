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
function upgrade_module_1_3_7()
{
    $p24OrdersTable = addslashes(_DB_PREFIX_ . Przelewy24Order::TABLE);
    $sql = '
          CREATE TABLE IF NOT EXISTS `' . $p24OrdersTable . '` (
            `p24_order_id` INT NOT NULL PRIMARY KEY,
			`pshop_order_id` INT NOT NULL,
			`p24_session_id` VARCHAR(100) NOT NULL
		  );';

    $success = Db::getInstance()->Execute($sql);

    if (!$success) {
        /* This is the best way to communicate problem with update. */
        throw new Exception('Cannot update module Przelewy24 to version 1.3.38.');
    }
}
