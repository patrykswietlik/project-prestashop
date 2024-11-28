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

class AmazonPayAddressReference extends ObjectModel
{
    public $id_address;

    public $amazon_order_reference_id;

    public $amazon_hash;

    public static $definition = array(
        'table' => 'amazonpay_address_reference',
        'primary' => 'id',
        'fields' => array(
            'id_address' =>                 array('type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false),
            'amazon_order_reference_id' =>  array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 255),
            'amazon_hash' =>                array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => false, 'size' => 255),
        ),
    );

    /**
     * AmazonPayAddressReference constructor.
     * @param null $id
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    /**
     * @param bool $autodate
     * @param bool $null_values
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function add($autodate = true, $null_values = false)
    {
        if (!parent::add($autodate, $null_values)) {
            return false;
        }
        return true;
    }

    /**
     * @param bool $null_values
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function update($null_values = false)
    {
        return parent::update($null_values);
    }

    /**
     * @return bool
     * @throws PrestaShopException
     */
    public function delete()
    {
        return parent::delete();
    }
}
