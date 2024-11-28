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

namespace BluePayment\Configure;

if (!defined('_PS_VERSION_')) {
    exit;
}

use BluePayment\Adapter\ConfigurationAdapter;
use BluePayment\Config\Config;
use BluePayment\Statuses\CustomStatus;
use BluePayment\Until\Helper;
use Symfony\Component\Translation\TranslatorInterface;

class Configure
{
    protected $module;
    protected $configurationAdapter;
    protected $customStatus;
    protected $translator;
    protected $name;

    public const TRANSLATE_GROUP = 'Modules.Bluepayment';

    public function __construct(
        \BluePayment $module,
        ConfigurationAdapter $configurationAdapter,
        TranslatorInterface $translator
    ) {
        $this->module = $module;
        $this->translator = $translator;
        $this->configurationAdapter = $configurationAdapter;

        $this->name = \Tools::strtoupper($this->module->name);
    }

    public function install(): bool
    {
        return $this->installConfiguration(\Shop::isFeatureActive())
            && $this->addOrderStatuses(
                new CustomStatus()
            );
    }

    public function uninstall(): bool
    {
        $fields = [
            'normal' => Helper::getFields(),
            'lang' => Helper::getFieldsLang(),
        ];

        return $this->uninstallConfiguration($fields);
    }

    /**
     * Create configuration fields
     *
     * @return bool
     */
    public function installConfiguration($isMultiStore = false): bool
    {
        $res = true;

        if ($isMultiStore) {
            foreach (\Shop::getContextListShopID() as $shop_id) {
                $group_id = \Shop::getGroupFromShop($shop_id, true);

                $res &= empty(
                    $this->configurationAdapter->get(
                        $this->name . '_TEST_ENV',
                        null,
                        $group_id,
                        $shop_id
                    )
                ) ? $this->configurationAdapter->updateValue(
                    $this->name . '_TEST_ENV',
                    0,
                    false,
                    $group_id,
                    $shop_id
                ) : true;

                $res &= empty(
                    $this->configurationAdapter->get(
                        $this->name . '_SHOW_PAYWAY',
                        null,
                        $group_id,
                        $shop_id
                    )
                ) ? $this->configurationAdapter->updateValue(
                    $this->name . '_SHOW_PAYWAY',
                    1,
                    false,
                    $group_id,
                    $shop_id
                ) : true;

                $res &= empty(
                    $this->configurationAdapter->get(
                        $this->name . '_GA_TYPE',
                        null,
                        $group_id,
                        $shop_id
                    )
                ) ? $this->configurationAdapter->updateValue(
                    $this->name . '_GA_TYPE',
                    2,
                    false,
                    $group_id,
                    $shop_id
                ) : true;

                $res &= empty(
                    $this->configurationAdapter->get(
                        $this->name . '_GA_TRACKER_ID',
                        null,
                        $group_id,
                        $shop_id
                    )
                ) ? $this->configurationAdapter->updateValue(
                    $this->name . '_GA_TRACKER_ID',
                    0,
                    false,
                    $group_id,
                    $shop_id
                ) : true;

                $res &= empty(
                    $this->configurationAdapter->get(
                        $this->name . '_GA4_TRACKER_ID',
                        null,
                        $group_id,
                        $shop_id
                    )
                ) ? $this->configurationAdapter->updateValue(
                    $this->name . '_GA4_TRACKER_ID',
                    0,
                    false,
                    $group_id,
                    $shop_id
                ) : true;

                $res &= empty(
                    $this->configurationAdapter->get(
                        $this->name . '_GA4_SECRET',
                        null,
                        $group_id,
                        $shop_id
                    )
                ) ? $this->configurationAdapter->updateValue(
                    $this->name . '_GA4_SECRET',
                    0,
                    false,
                    $group_id,
                    $shop_id
                ) : true;

                $res &= empty(
                    $this->configurationAdapter->get(
                        $this->name . '_BLIK_REDIRECT',
                        null,
                        $group_id,
                        $shop_id
                    )
                ) ? $this->configurationAdapter->updateValue(
                    $this->name . '_BLIK_REDIRECT',
                    0,
                    false,
                    $group_id,
                    $shop_id
                ) : true;

                $res &= empty(
                    $this->configurationAdapter->get(
                        $this->name . '_GPAY_REDIRECT',
                        null,
                        $group_id,
                        $shop_id
                    )
                ) ? $this->configurationAdapter->updateValue(
                    $this->name . '_GPAY_REDIRECT',
                    0,
                    false,
                    $group_id,
                    $shop_id
                ) : true;

                $res &= empty(
                    $this->configurationAdapter->get(
                        $this->name . '_PROMO_PAY_LATER',
                        null,
                        $group_id,
                        $shop_id
                    )
                ) ? $this->configurationAdapter->updateValue(
                    $this->name . '_PROMO_PAY_LATER',
                    1,
                    false,
                    $group_id,
                    $shop_id
                ) : true;

                $res &= empty(
                    $this->configurationAdapter->get(
                        $this->name . '_PROMO_MATCHED_INSTALMENTS',
                        null,
                        $group_id,
                        $shop_id
                    )
                ) ? $this->configurationAdapter->updateValue(
                    $this->name . '_PROMO_MATCHED_INSTALMENTS',
                    1,
                    false,
                    $group_id,
                    $shop_id
                ) : true;

                $res &= empty(
                    $this->configurationAdapter->get(
                        $this->name . '_PROMO_HEADER',
                        null,
                        $group_id,
                        $shop_id
                    )
                ) ? $this->configurationAdapter->updateValue(
                    $this->name . '_PROMO_HEADER',
                    0,
                    false,
                    $group_id,
                    $shop_id
                ) : true;

                $res &= empty(
                    $this->configurationAdapter->get(
                        $this->name . '_PROMO_FOOTER',
                        null,
                        $group_id,
                        $shop_id
                    )
                ) ? $this->configurationAdapter->updateValue(
                    $this->name . '_PROMO_FOOTER',
                    0,
                    false,
                    $group_id,
                    $shop_id
                ) : true;

                $res &= empty(
                    $this->configurationAdapter->get(
                        $this->name . '_PROMO_LISTING',
                        null,
                        $group_id,
                        $shop_id
                    )
                ) ? $this->configurationAdapter->updateValue(
                    $this->name . '_PROMO_LISTING',
                    0,
                    false,
                    $group_id,
                    $shop_id
                ) : true;

                $res &= empty(
                    $this->configurationAdapter->get(
                        $this->name . '_PROMO_PRODUCT',
                        null,
                        $group_id,
                        $shop_id
                    )
                ) ? $this->configurationAdapter->updateValue(
                    $this->name . '_PROMO_PRODUCT',
                    0,
                    false,
                    $group_id,
                    $shop_id
                ) : true;

                $res &= empty(
                    $this->configurationAdapter->get(
                        $this->name . '_PROMO_CART',
                        null,
                        $group_id,
                        $shop_id
                    )
                ) ? $this->configurationAdapter->updateValue(
                    $this->name . '_PROMO_CART',
                    0,
                    false,
                    $group_id,
                    $shop_id
                ) : true;

                $res &= empty(
                    $this->configurationAdapter->get(
                        $this->name . '_PROMO_CHECKOUT',
                        null,
                        $group_id,
                        $shop_id
                    )
                ) ? $this->configurationAdapter->updateValue(
                    $this->name . '_PROMO_CHECKOUT',
                    1,
                    false,
                    $group_id,
                    $shop_id
                ) : true;
            }
        } else {
            /* Sets up Global configuration */
            $res = empty($this->configurationAdapter->get($this->name . '_TEST_ENV')) ? $this->configurationAdapter->updateValue($this->name . '_TEST_ENV', 0) : true;
            $res &= empty($this->configurationAdapter->get($this->name . '_SHOW_PAYWAY')) ? $this->configurationAdapter->updateValue($this->name . '_SHOW_PAYWAY', 1) : true;
            $res &= empty($this->configurationAdapter->get($this->name . '_GA_TYPE')) ? $this->configurationAdapter->updateValue($this->name . '_GA_TYPE', 2) : true;
            $res &= empty($this->configurationAdapter->get($this->name . '_GA_TRACKER_ID')) ? $this->configurationAdapter->updateValue($this->name . '_GA_TRACKER_ID', 0) : true;
            $res &= empty($this->configurationAdapter->get($this->name . '_GA4_TRACKER_ID')) ? $this->configurationAdapter->updateValue($this->name . '_GA4_TRACKER_ID', 0) : true;
            $res &= empty($this->configurationAdapter->get($this->name . '_GA4_SECRET')) ? $this->configurationAdapter->updateValue($this->name . '_GA4_SECRET', 0) : true;
            $res &= empty($this->configurationAdapter->get($this->name . '_BLIK_REDIRECT')) ? $this->configurationAdapter->updateValue($this->name . '_BLIK_REDIRECT', 0) : true;
            $res &= empty($this->configurationAdapter->get($this->name . '_GPAY_REDIRECT')) ? $this->configurationAdapter->updateValue($this->name . '_GPAY_REDIRECT', 0) : true;

            $res &= empty($this->configurationAdapter->get($this->name . '_PROMO_PAY_LATER')) ? $this->configurationAdapter->updateValue($this->name . '_PROMO_PAY_LATER', 1) : true;
            $res &= empty($this->configurationAdapter->get($this->name . '_PROMO_MATCHED_INSTALMENTS')) ? $this->configurationAdapter->updateValue($this->name . '_PROMO_MATCHED_INSTALMENTS', 1) : true;

            $res &= empty($this->configurationAdapter->get($this->name . '_PROMO_HEADER')) ? $this->configurationAdapter->updateValue($this->name . '_PROMO_HEADER', 0) : true;
            $res &= empty($this->configurationAdapter->get($this->name . '_PROMO_FOOTER')) ? $this->configurationAdapter->updateValue($this->name . '_PROMO_FOOTER', 0) : true;
            $res &= empty($this->configurationAdapter->get($this->name . '_PROMO_LISTING')) ? $this->configurationAdapter->updateValue($this->name . '_PROMO_LISTING', 0) : true;
            $res &= empty($this->configurationAdapter->get($this->name . '_PROMO_PRODUCT')) ? $this->configurationAdapter->updateValue($this->name . '_PROMO_PRODUCT', 0) : true;
            $res &= empty($this->configurationAdapter->get($this->name . '_PROMO_CART')) ? $this->configurationAdapter->updateValue($this->name . '_PROMO_CART', 0) : true;
            $res &= empty($this->configurationAdapter->get($this->name . '_PROMO_CHECKOUT')) ? $this->configurationAdapter->updateValue($this->name . '_PROMO_CHECKOUT', 1) : true;
        }

        $res &= $this->installConfigurationTranslations(
            $isMultiStore
        );

        $smarty = \Context::getContext()->smarty;
        \Tools::clearAllCache($smarty);
        \Tools::clearCompile($smarty);

        return (bool) $res;
    }

    public function uninstallConfiguration($fields): bool
    {
        $res = true;

        if (isset($fields['normal']) && !empty($fields['normal'])) {
            foreach ($fields['normal'] as $field) {
                $res &= $this->configurationAdapter->deleteByName($field);
            }
        } else {
            \PrestaShopLogger::addLog('BM - Cant uninstall statuses ', 4);
            $res = false;
        }

        if (isset($fields['lang']) && !empty($fields['lang'])) {
            foreach ($fields['lang'] as $field) {
                $res &= $this->configurationAdapter->deleteByName($field);
            }
        } else {
            \PrestaShopLogger::addLog('BM - Cant uninstall statuses ', 4);
            $res = false;
        }

        $res &= $this->configurationAdapter->deleteByName($this->name . Config::SHARED_KEY);
        $res &= $this->configurationAdapter->deleteByName($this->name . Config::SERVICE_PARTNER_ID);

        return (bool) $res;
    }

    public function addOrderStatuses($customStatus): bool
    {
        $res = false;

        try {
            $res = $customStatus->addOrderStates(
                \Context::getContext()->language->id,
                $this->module->name_upper
            );
        } catch (\Exception $exception) {
            \PrestaShopLogger::addLog('BM - Add statuses - error', 4);
        }

        return $res;
    }

    public function removeOrderStatuses($customStatus): bool
    {
        $res = false;

        try {
            $customStatus->removeOrderStates();
            $res = true;
        } catch (\Exception $exception) {
            \PrestaShopLogger::addLog('BM - Remove statuses - error', 4);
        }

        return $res;
    }

    /**
     * Install default text translations for fields in the main configuration
     *
     * @return bool
     */
    public function installConfigurationTranslations($isMultiStore = false): bool
    {
        $res = true;

        $name_lang = [];
        $name_group_lang = [];

        foreach (\Language::getLanguages() as $lang) {
            if ($lang['locale'] === 'pl-PL') {
                $name_lang[$lang['id_lang']] = $this->translator->trans(
                    'Szybka płatność',
                    [],
                    self::TRANSLATE_GROUP,
                    $lang['locale']
                );
                $name_group_lang[$lang['id_lang']] = $this->translator->trans(
                    'Przelew internetowy',
                    [],
                    self::TRANSLATE_GROUP,
                    $lang['locale']
                );
            } else {
                $name_lang[$lang['id_lang']] = $this->translator->trans(
                    'Fast payment',
                    [],
                    self::TRANSLATE_GROUP,
                    $lang['locale']
                );
                $name_group_lang[$lang['id_lang']] = $this->translator->trans(
                    'Internet transfer',
                    [],
                    self::TRANSLATE_GROUP,
                    $lang['locale']
                );
            }
        }

        if ($isMultiStore) {
            foreach (\Shop::getContextListShopID() as $shop_id) {
                $group_id = \Shop::getGroupFromShop($shop_id, true);

                $res &= !empty(
                    $this->configurationAdapter->get(
                        $this->name . '_PAYMENT_NAME',
                        null,
                        $group_id,
                        $shop_id
                    ))
                    || $this->configurationAdapter->updateValue(
                        $this->name . '_PAYMENT_NAME',
                        $name_lang,
                        $group_id,
                        $shop_id
                    );

                $res &= !empty(
                    $this->configurationAdapter->get(
                        $this->name . '_PAYMENT_GROUP_NAME',
                        null,
                        $group_id,
                        $shop_id
                    ))
                    || $this->configurationAdapter->updateValue(
                        $this->name . '_PAYMENT_GROUP_NAME',
                        $name_group_lang,
                        $group_id,
                        $shop_id
                    );
            }
        } else {
            $res &= empty($this->configurationAdapter->get($this->name . '_PAYMENT_NAME', \Context::getContext()->language->id)) ? $this->configurationAdapter->updateValue($this->name . '_PAYMENT_NAME', $name_lang) : true;
            $res &= empty($this->configurationAdapter->get($this->name . '_PAYMENT_GROUP_NAME', \Context::getContext()->language->id)) ? $this->configurationAdapter->updateValue($this->name . '_PAYMENT_GROUP_NAME', $name_group_lang) : true;
        }

        return (bool) $res;
    }
}
