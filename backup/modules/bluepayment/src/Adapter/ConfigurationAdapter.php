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

namespace BluePayment\Adapter;

if (!defined('_PS_VERSION_')) {
    exit;
}

use Configuration as Cfg;

class ConfigurationAdapter
{
    /**
     * @var \Shop
     */
    private $shopId;

    public function __construct($shopId)
    {
        $this->shopId = $shopId;
    }

    public function get($key, $idLang = null, $idShopGroup = null, $idShop = null, $default = false)
    {
        if ($idShop === null) {
            $idShop = $this->shopId;
        }

        return Cfg::get($key, $idLang, $idShopGroup, $idShop, $default);
    }

    public function updateValue($key, $values, $html = false, $idShopGroup = null, $idShop = null)
    {
        if ($idShop === null) {
            $idShop = $this->shopId;
        }

        return Cfg::updateValue($key, $values, $html, $idShopGroup, $idShop);
    }

    public function deleteByName($key)
    {
        return Cfg::deleteByName($key);
    }
}
