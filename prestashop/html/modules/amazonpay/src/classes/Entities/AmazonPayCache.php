<?php
/**
 * 2007-2023 patworx.de
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade AmazonPay to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    patworx multimedia GmbH <service@patworx.de>
 *  @copyright 2007-2023 patworx multimedia GmbH
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (! defined('_PS_VERSION_')) {
    exit();
}

class AmazonPayCache extends ObjectModel
{

    public $id;

    public $cache_key;

    public $cache_value;

    public $id_lang;
    
    public $date_add;

    public static $definition = array(
        'table' => 'amazonpay_cache',
        'primary' => 'id_amazonpay_cache',
        'fields' => array(
            'cache_key' => array(
                'type' => self::TYPE_STRING,
                'size' => 255
            ),
            'cache_value' => array(
                'type' => self::TYPE_STRING
            ),
            'id_lang' => array(
                'type' => self::TYPE_INT,
                'validate' => 'isInt'
            ),
            'date_add' => array(
                'type' => self::TYPE_DATE,
                'validate' => 'isDateFormat'
            )
        )
    );

    public static $cachedObjectsExist = [];

    /**
     * @param $cache_key_part
     * @param false $max_age
     */
    public static function fetchCaches($cache_key_part, $max_age = false, $id_lang = 1)
    {
        $sql = 'SELECT w.id_amazonpay_cache, w.cache_key, w.date_add FROM `' . _DB_PREFIX_ . 'amazonpay_cache` w
                 WHERE w.`cache_key` LIKE \'' . pSQL($cache_key_part) . '%\'
                   AND w.`id_lang` = ' . (int)$id_lang;
        if ($results = Db::getInstance()->ExecuteS($sql)) {
            foreach ($results as $result) {
                if ($max_age) {
                    if (date('YmdHis', strtotime($result['date_add'])) >= date("YmdHis", strtotime("-" . (int)$max_age . " seconds"))) {
                        if (!isset(self::$cachedObjectsExist[$max_age])) {
                            self::$cachedObjectsExist[$max_age] = [];
                        }
                        self::$cachedObjectsExist[$max_age][$result['cache_key']] = true;
                    }
                } else {
                    if (!isset(self::$cachedObjectsExist[-1])) {
                        self::$cachedObjectsExist[-1] = [];
                    }
                    self::$cachedObjectsExist[-1][$result['cache_key']] = true;
                }
            }
        }
    }

    /**
     * @param $cache_key
     * @param false $max_age
     * @param bool $create_new
     * @param int $id_lang
     * @return AmazonPayCache|false
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function getByKey($cache_key, $max_age = false, $create_new = true, $id_lang = 1)
    {
        $sql = 'SELECT w.id_amazonpay_cache, w.date_add FROM `' . _DB_PREFIX_ . 'amazonpay_cache` w
                 WHERE w.`cache_key` = \'' . pSQL($cache_key) . '\' AND w.`id_lang` = ' . (int)$id_lang;
        if (AmazonPayHelper::useCache()) {
            if ($result = Db::getInstance()->getRow($sql)) {
                if ($max_age) {
                    if (date('YmdHis', strtotime($result['date_add'])) >= date("YmdHis", strtotime("-" . (int)$max_age . " seconds"))) {
                        return new self($result['id_amazonpay_cache']);
                    } else {
                        $delObj = new self($result['id_amazonpay_cache']);
                        $delObj->delete();
                    }
                } else {
                    return new self($result['id_amazonpay_cache']);
                }
            }
        }
        if ($create_new) {
            $cache = new self();
            $cache->cache_key = $cache_key;
            $cache->date_add = date("Y-m-d H:i:s");
            $cache->id_lang = (int)$id_lang;
            return $cache;
        }
        return false;
    }

    /**
     * @param false $null_values
     * @param bool $auto_date
     * @return bool|void
     * @throws PrestaShopException
     */
    public function save($null_values = false, $auto_date = true)
    {
        if (AmazonPayHelper::useCache()) {
            return parent::save($null_values, $auto_date);
        }
        return;
    }
    
}
