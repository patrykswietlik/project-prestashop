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

class AmazonPaySimplePathHelper
{

    /**
     * @var Amazonpay
     */
    private $amazonPay;

    /**
     * @var Context
     */
    private $context;

    /**
     * AmazonPaySimplePathHelper constructor.
     * @param Amazonpay $amazonPay
     */
    public function __construct(Amazonpay $amazonPay)
    {
        $this->amazonPay = $amazonPay;
        $this->context = Context::getContext();
    }

    /**
     * @return string
     */
    protected function getSpId()
    {
        if (Configuration::get('AMAZONPAY_REGION') == 'UK') {
            return Amazonpay::$pfid_uk;
        } elseif (Configuration::get('AMAZONPAY_REGION') == 'US') {
            return Amazonpay::$pfid_us;
        }
        return Amazonpay::$pfid;
    }

    /**
     * @return array
     */
    public function getSimplePathVars()
    {
        $simple_path_data = array(
            'spId' => $this->getSpId(),
            'onboardingVersion' => '2',
            'publicKey' => $this->getPlainPubKey($this->getPublicKey()),
            'keyShareURL' =>  $this->getKeyShareURL(),
            'locale' =>  AmazonPayHelper::getCheckoutLanguage(),
            'merchantLoginDomains' => [$this->getDomainForWhitelist()],
            'spSoftwareVersion' => _PS_VERSION_,
            'spAmazonPluginVersion' => $this->amazonPay->version,
            'ld' => $this->getLd(),
            'merchantLoginRedirectURLs' => $this->getAllowedReturnUrls(),
            'merchantStoreDescription' => Configuration::get('PS_SHOP_NAME'),
            'merchantSandboxIPNURL' => 'https://' . $this->getIPNURL(),
            'merchantProductionIPNURL' => 'https://' . $this->getIPNURL(),
            'source' => 'SPPL'
        );
        return $simple_path_data;
    }

    /**
     * @param $pubkey
     * @return string
     */
    protected function getPlainPubKey($pubkey)
    {
        return
            trim(
                str_replace(
                    [
                        '-----BEGIN PUBLIC KEY-----',
                        '-----END PUBLIC KEY-----',
                        "\n"
                    ],
                    '',
                    $pubkey
                )
            );
    }

    /**
     * @return string
     */
    protected function getDomainForWhitelist()
    {
        $main_url = $this->getIPNURL();
        return 'https://' . Tools::substr($main_url, 0, Tools::strpos($main_url, '/'));
    }

    /**
     * @return string|string[]
     */
    protected function getIPNURL()
    {
        return str_replace(array('http://', 'https://'), '', $this->context->link->getModuleLink('amazonpay', 'ipn', array()));
    }

    /**
     * @return string|string[]
     */
    protected function getKeyShareURL()
    {
        return str_replace('http://', 'https://', $this->context->link->getModuleLink('amazonpay', 'keyshare', array()));
    }

    /**
     * @param int $type
     * @param bool $joined
     * @return array|string
     */
    protected function getAllowedReturnUrls($joined = false)
    {
        $urls = array();
        $language_ids = Language::getLanguages(true, false, true);
        foreach ($language_ids as $id_lang) {
            $url = str_replace('http://', 'https://', $this->context->link->getModuleLink('amazonpay', 'initcheckout', array(), null, (int)$id_lang));
            if (!in_array($url, $urls)) {
                $urls[] = $url;
            }
        }
        if ($joined) {
            return join($joined, $urls);
        } else {
            return $urls;
        }
    }

    /**
     * @return string
     */
    protected function getLd()
    {
        switch ($this->context->language->iso_code) {
            case 'de':
                return 'SPEXDEAPA-Prestashop-core_DE';
            case 'en':
                if (isset($this->context->language->local) && Tools::strtolower($this->context->language->local) == 'en-us') {
                    return 'SPEXUSAPA-Prestashop-core_US';
                } else {
                    return 'SPEXUKAPA-Prestashop-core_UK';
                }
                // no break
            case 'fr':
                return 'SPEXFRAPA-Prestashop-core_FR';
            case 'it':
                return 'SPEXITAPA-Prestashop-core_IT';
            case 'es':
                return 'SPEXESAPA-Prestashop-core_ES';
            default:
                return '';
        }
    }

    /**
     * @return false|string
     */
    protected function getPublicKey()
    {
        AmazonPayKeyShareHandler::createKeyPair();
        return Configuration::get('AMAZONPAY_KEYEXCHANGE_PUBLIC_KEY');
    }
}
