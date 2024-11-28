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

use BluePayment\Until\Helper;

if (!defined('_PS_VERSION_')) {
    exit;
}
class ConfigBanner
{
    public const BM_IFRAME_BANNER_PL = 'https://plugins-api.autopay.pl/plugins-baner-presta/';
    public const BM_IFRAME_BANNER_EN = 'https://plugins-api.autopay.pl/en/plugins-baner-presta/';
    public const BM_IFRAME_BANNER_IT = 'https://plugins-api.autopay.pl/it/plugins-baner-presta/';
    public const BM_IFRAME_BANNER_DE = 'https://plugins-api.autopay.pl/de/plugins-baner-presta/';
    public const BM_IFRAME_BANNER_SK = 'https://plugins-api.autopay.pl/sk/plugins-baner-presta/';
    public const BM_IFRAME_BANNER_CZ = 'https://plugins-api.autopay.pl/cz/plugins-baner-presta/';

    private $module;

    public function __construct()
    {
        $this->module = \Module::getInstanceByName('bluepayment');
    }

    public function getLinkIframeByIsoCode($isoCode, $currencyIsoCode)
    {
        switch (strtoupper($isoCode)) {
            case 'PL':
                $url = self::BM_IFRAME_BANNER_PL;
                break;
            case 'EN':
                $url = self::BM_IFRAME_BANNER_EN;
                break;
            case 'IT':
                $url = self::BM_IFRAME_BANNER_IT;
                break;
            case 'DE':
                $url = self::BM_IFRAME_BANNER_DE;
                break;
            case 'SK':
                $url = self::BM_IFRAME_BANNER_SK;
                break;
            case 'CZ':
                $url = self::BM_IFRAME_BANNER_CZ;
                break;
            default:
                $url = self::BM_IFRAME_BANNER_EN;
                break;
        }

        return $url . '?' . http_build_query($this->getParameters($currencyIsoCode), '', '&');
    }

    private function getParameters($currencyIsoCode)
    {
        return [
            'ecommerce' => 'prestashop',
            'ecommerce_version' => _PS_VERSION_,
            'programming_language_version' => PHP_VERSION,
            'plugin_name' => $this->module->name,
            'plugin_version' => $this->module->version,
            'service_id' => Helper::parseConfigByCurrency($this->module->name_upper . Config::SERVICE_PARTNER_ID, $currencyIsoCode),
        ];
    }
}
