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

namespace BluePayment\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}
class ConfigServices
{
    public const BM_IFRAME_SERVICES_PL = 'https://plugins-api.autopay.pl/presta-vas/';
    public const BM_IFRAME_SERVICES_EN = 'https://plugins-api.autopay.pl/en/presta-vas/';
    public const BM_IFRAME_SERVICES_IT = 'https://plugins-api.autopay.pl/it/presta-vas/';
    public const BM_IFRAME_SERVICES_DE = 'https://plugins-api.autopay.pl/de/presta-vas/';
    public const BM_IFRAME_SERVICES_SK = 'https://plugins-api.autopay.pl/sk/presta-vas/';
    public const BM_IFRAME_SERVICES_CZ = 'https://plugins-api.autopay.pl/cz/presta-vas/';

    private $module;

    public function __construct()
    {
        $this->module = \Module::getInstanceByName('bluepayment');
    }

    public function getLinkIframeByIsoCode($isoCode)
    {
        switch (strtoupper($isoCode)) {
            case 'PL':
                $url = self::BM_IFRAME_SERVICES_PL;
                break;
            case 'IT':
                $url = self::BM_IFRAME_SERVICES_IT;
                break;
            case 'DE':
                $url = self::BM_IFRAME_SERVICES_DE;
                break;
            case 'SK':
                $url = self::BM_IFRAME_SERVICES_SK;
                break;
            case 'CZ':
                $url = self::BM_IFRAME_SERVICES_CZ;
                break;
            case 'EN':
            default:
                $url = self::BM_IFRAME_SERVICES_EN;
                break;
        }

        return $url . '?' . http_build_query($this->getParameters(), '', '&');
    }

    private function getParameters()
    {
        return [
            'ecommerce' => 'prestashop',
            'ecommerce_version' => _PS_VERSION_,
            'programming_language_version' => PHP_VERSION,
            'plugin_name' => $this->module->name,
            'plugin_version' => $this->module->version,
        ];
    }
}
