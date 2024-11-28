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

namespace BluePayment\Api;

if (!defined('_PS_VERSION_')) {
    exit;
}

use BluePayment\Until\AdminHelper;

class BlueGateway
{
    private $module; // do usuniecia prawdopodobnie
    private $api;

    public function __construct(\BluePayment $module, $api)
    {
        $this->api = $api;
        $this->module = $module;
    }

    public function getMode()
    {
        return $this->api->getApiMode();
    }

    public function getTransfers()
    {
        return $this->api->getGatewaysFromAPI(
            new BlueGatewayTransfers(),
            $this->getMode(),
            AdminHelper::getSortCurrencies()
        );
    }

    public function getChannels()
    {
        return $this->api->getGatewaysFromAPI(
            new BlueGatewayChannels(),
            $this->getMode(),
            AdminHelper::getSortCurrencies()
        );
    }
}
