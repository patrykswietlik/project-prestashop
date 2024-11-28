<?php
/**
 * Class Przelewy24ServiceInstall
 *
 * @author Przelewy24
 * @copyright Przelewy24
 * @license https://www.gnu.org/licenses/lgpl-3.0.en.html
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Przelewy24ServiceInstall
 */
class Przelewy24ServiceInstall extends Przelewy24Service
{
    /**
     * Execute.
     */
    public function execute()
    {
        // we check that the Multistore feature is enabled, and if so,
        // set the current context to all shops on this installation of PrestaShop.
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        Configuration::updateValue('P24_GRAPHICS_PAYMENT_METHOD_LIST', 1);
        Configuration::updateValue('P24_PAYMENT_METHOD_CHECKOUT_LIST', 1);
        Configuration::updateValue('P24_PAYMENT_METHOD_CONFIRM_LIST', 1);
        Configuration::updateValue('P24_INSTALMENT_ENABLED', 1);
        Configuration::updateValue('P24_INSTALMENT_PROMOTE', 1);
        Configuration::updateValue('P24_PAYMENTS_ORDER_LIST_FIRST', '25,31,112,20,65,');
        Configuration::updateValue(
            'P24_PAYMENTS_PROMOTE_LIST',
            303
        );

        Przelewy24Helper::addOrderState();

        $this->createDatabaseTables();
        Configuration::updateValue('P24_PLUGIN_VERSION', $this->getPrzelewy24()->version);
    }

    /**
     * Create database tables.
     *
     * Some people may use this function for upgrade existing version.
     */
    private function createDatabaseTables()
    {
        $tableName = addslashes(_DB_PREFIX_ . Przelewy24Recurring::TABLE);
        $sql = '

          CREATE TABLE IF NOT EXISTS `' . $tableName . '` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `website_id` INT UNSIGNED NOT NULL,
            `customer_id` INT UNSIGNED NOT NULL,
            `reference_id` VARCHAR(35) NOT NULL,
            `expires` VARCHAR(4) NOT NULL,
            `mask` VARCHAR (32) NOT NULL,
            `card_type` VARCHAR (20) NOT NULL,
            `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `UNIQUE_FIELDS` (`mask`,`card_type`,`expires`,`customer_id`,`website_id`));';
        Db::getInstance()->Execute($sql);

        $tableName = addslashes(_DB_PREFIX_ . Przelewy24CustomerSetting::TABLE);
        $sql = '
          CREATE TABLE IF NOT EXISTS`' . $tableName . '` (
			`customer_id` INT UNSIGNED NOT NULL PRIMARY KEY,
			`card_remember` TINYINT UNSIGNED DEFAULT 0
		  );';
        Db::getInstance()->Execute($sql);

        $tableName = addslashes(_DB_PREFIX_ . Przelewy24BlikAlias::TABLE);
        $sql = '
          CREATE TABLE IF NOT EXISTS`' . $tableName . '` (
			`customer_id` INT UNSIGNED NOT NULL PRIMARY KEY,
			`alias` VARCHAR(255) DEFAULT 0,
			`last_order_id` INT UNSIGNED NULL
		  );';
        Db::getInstance()->Execute($sql);

        $p24OrdersTable = addslashes(_DB_PREFIX_ . Przelewy24Order::TABLE);
        $sql = '
          CREATE TABLE IF NOT EXISTS `' . $p24OrdersTable . '` (
            `p24_order_id` BIGINT NOT NULL PRIMARY KEY,
			`pshop_order_id` INT UNSIGNED NOT NULL,
			`p24_session_id` VARCHAR(100) NOT NULL,
			`p24_full_order_id` VARCHAR(100),
			`amount` BIGINT,
			`received` DATETIME
		  );';
        Db::getInstance()->Execute($sql);

        /* The definition of table above has changed. If we upgrade from an older version, we have to add changes. */
        /* Some of the command below may fail. This is fine though. */
        /* We simply continue to the next SQL command. */
        try {
            $sql = 'ALTER TABLE `' . $p24OrdersTable . '` ADD COLUMN `p24_full_order_id` VARCHAR(100);';
            Db::getInstance()->Execute($sql);
        } catch (PrestaShopDatabaseException $ex) {
            /* It is fine. Do nothing. */
        }

        try {
            $sql = 'ALTER TABLE `' . $p24OrdersTable . '`MODIFY `p24_order_id` BIGINT NOT NULL;';
            Db::getInstance()->Execute($sql);
        } catch (PrestaShopDatabaseException $ex) {
            /* It is fine. Do nothing. */
        }

        try {
            $sql = 'ALTER TABLE `' . $p24OrdersTable . '` ADD COLUMN (`amount` BIGINT, `received` DATETIME);';
            Db::getInstance()->Execute($sql);
        } catch (PrestaShopDatabaseException $ex) {
            /* It is fine. Do nothing. */
        }

        /* End of commands that may fail. */

        $tableName = addslashes(_DB_PREFIX_ . Przelewy24Extracharge::TABLE);
        $sql = 'CREATE TABLE IF NOT EXISTS`' . $tableName . '` (
			`id_extracharge` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
			`id_order` INT UNSIGNED NOT NULL,
			`extra_charge_amount` INT NOT NULL,
			`total_paid` DECIMAL(20,6)
			);';

        Db::getInstance()->Execute($sql);
    }
}
