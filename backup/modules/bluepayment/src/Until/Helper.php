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

namespace BluePayment\Until;

if (!defined('_PS_VERSION_')) {
    exit;
}

use BlueMedia\OnlinePayments\Gateway;
use BluePayment\Config\Config;
use Configuration as Cfg;

class Helper
{
    public static function getFields(): array
    {
        return [
            'BLUEPAYMENT_STATUS_WAIT_PAY_ID',
            'BLUEPAYMENT_STATUS_ACCEPT_PAY_ID',
            'BLUEPAYMENT_STATUS_ERROR_PAY_ID',
            'BLUEPAYMENT_SHOW_PAYWAY',
            'BLUEPAYMENT_TEST_ENV',

            'BLUEPAYMENT_GA_TYPE',
            'BLUEPAYMENT_GA_TRACKER_ID',
            'BLUEPAYMENT_GA4_TRACKER_ID',
            'BLUEPAYMENT_GA4_SECRET',

            'BLUEPAYMENT_BLIK_REDIRECT',
            'BLUEPAYMENT_GPAY_REDIRECT',
            'BLUEPAYMENT_PROMO_PAY_LATER',
            'BLUEPAYMENT_PROMO_MATCHED_INSTALMENTS',
            'BLUEPAYMENT_PROMO_HEADER',
            'BLUEPAYMENT_PROMO_FOOTER',
            'BLUEPAYMENT_PROMO_LISTING',
            'BLUEPAYMENT_PROMO_PRODUCT',
            'BLUEPAYMENT_PROMO_CART',
            'BLUEPAYMENT_PROMO_CHECKOUT',
        ];
    }

    public static function getFieldsMultiple(): array
    {
        return [
            'BLUEPAYMENT_STATUS_CHANGE_PAY_ID[]',
        ];
    }

    public static function getFieldsLang(): array
    {
        return [
            'BLUEPAYMENT_PAYMENT_NAME',
            'BLUEPAYMENT_PAYMENT_GROUP_NAME',
        ];
    }

    public static function getFieldsService(): array
    {
        return [
            'BLUEPAYMENT_SERVICE_PARTNER_ID',
            'BLUEPAYMENT_SHARED_KEY',
        ];
    }

    public static function getImgPayments($type, $currency, $idShop)
    {
        if (!$currency || !$idShop) {
            return false;
        }

        $query = new \DbQuery();
        $query->from('blue_gateway_transfers', 'gt');
        $query->leftJoin('blue_gateway_transfers_shop', 'gts', 'gts.id = gt.id');

        if ($type === 'transfers') {
            $query->where('gt.gateway_id NOT IN (' . self::getGatewaysList() . ')');
        } elseif ($type === 'wallet') {
            $query->where('gt.gateway_id IN (' . self::getWalletsList() . ')');
        }

        $query->where('gt.gateway_status = 1');
        $query->where('gt.gateway_currency = "' . pSql($currency) . '"');

        if (\Shop::isFeatureActive()) {
            $query->where('gts.id_shop = ' . (int) $idShop);
        }

        $query->select('gateway_logo_url, gateway_name');

        return \Db::getInstance()->executeS($query);
    }

    public static function getGatewaysList(): string
    {
        $gatewayArray = [
            Config::GATEWAY_ID_BLIK,
            Config::GATEWAY_ID_BLIK_LATER,
            Config::GATEWAY_ID_ALIOR,
            Config::GATEWAY_ID_CARD,
            Config::GATEWAY_ID_GOOGLE_PAY,
            Config::GATEWAY_ID_APPLE_PAY,
            Config::GATEWAY_ID_PAYPO,
            Config::GATEWAY_ID_VISA_MOBILE,
            Config::GATEWAY_ID_SPINGO,
        ];

        return implode(',', $gatewayArray);
    }

    public static function getDeletedGatewaysList(): string
    {
        $gatewayArray = [
            Config::GATEWAY_ID_SMARTNEY,
        ];

        return implode(',', $gatewayArray);
    }

    public static function getWalletsList(): string
    {
        $walletArray = [
            Config::GATEWAY_ID_GOOGLE_PAY,
            Config::GATEWAY_ID_APPLE_PAY,
        ];

        return implode(',', $walletArray);
    }

    public static function parseConfigByCurrency($key, $currencyIsoCode)
    {
        $value = Cfg::get($key);
        if ($value == false) {
            return '';
        }
        $data = json_decode($value, true);

        return is_array($data) && array_key_exists($currencyIsoCode, $data) ? $data[$currencyIsoCode] : '';
    }

    /**
     * Get logo
     *
     * @return string
     */
    public static function getBrandLogo(): string
    {
        return \Context::getContext()->shop->getBaseURL(true) . 'modules/bluepayment/views/img/blue-media.svg';
    }

    /**
     * @param $id_order
     *
     * @return bool|array
     */
    public static function getLastOrderPaymentByOrderId($id_order)
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'blue_transactions
			WHERE order_id like "' . pSQL($id_order) . '-%"
			ORDER BY created_at DESC';

        return \Db::getInstance()->getRow($sql, false);
    }

    /**
     * @param $id_order
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     */
    public static function getOrdersByOrderId($id_order): array
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'blue_transactions
			WHERE order_id like "' . pSQL($id_order) . '-%"
			ORDER BY created_at DESC';

        return \Db::getInstance()->executeS($sql, true, false);
    }

    /**
     * Generates and returns a hash key based on field values from an array
     *
     * @param array $data
     *
     * @return string
     */
    public static function generateAndReturnHash($data): string
    {
        $values_array = array_values($data);
        $values_array_filter = array_filter($values_array);

        $comma_separated = implode(',', $values_array_filter);
        $replaced = str_replace(',', Config::HASH_SEPARATOR, $comma_separated);

        return hash(Gateway::HASH_SHA256, $replaced);
    }

    public static function getIsoFromContext($context): string
    {
        if (!is_object($context->currency)) {
            $iso_code = $context->currency['iso_code'];
        } else {
            $iso_code = $context->currency->iso_code;
        }
        if (empty($iso_code)) {
            $iso_code = 'PLN';
        }

        return $iso_code;
    }

    // Prestashop < 1.7.5 fix states

    public static function sendEmail($order, $template_vars = false, $id = 0)
    {
        $result = \Db::getInstance()->getRow('
            SELECT osl.`template`, c.`lastname`, c.`firstname`, osl.`name` AS osname, c.`email`, os.`module_name`,
                   os.`id_order_state`, os.`pdf_invoice`, os.`pdf_delivery`
            FROM `' . _DB_PREFIX_ . 'order_history` oh
                LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON oh.`id_order` = o.`id_order`
                LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON o.`id_customer` = c.`id_customer`
                LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON oh.`id_order_state` = os.`id_order_state`
                LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state`
                AND osl.`id_lang` = o.`id_lang`)
            WHERE oh.`id_order_history` = ' . (int) $id . ' AND os.`send_email` = 1');
        if (isset($result['template']) && \Validate::isEmail($result['email'])) {
            \ShopUrl::cacheMainDomainForShop($order->id_shop);

            $topic = $result['osname'];
            $carrierUrl = '';
            if (\Validate::isLoadedObject($carrier = new \Carrier((int) $order->id_carrier, $order->id_lang))) {
                $carrierUrl = $carrier->url;
            }
            $data = [
                '{lastname}' => $result['lastname'],
                '{firstname}' => $result['firstname'],
                '{id_order}' => (int) $order->id,
                '{order_name}' => $order->getUniqReference(),
                '{followup}' => str_replace('@', $order->getWsShippingNumber(), $carrierUrl),
                '{shipping_number}' => $order->getWsShippingNumber(),
            ];

            if ($result['module_name']) {
                $module = \Module::getInstanceByName('bluepayment');
                if (
                    \Validate::isLoadedObject($module)
                    && isset($module->extra_mail_vars)
                    && is_array($module->extra_mail_vars)
                ) {
                    $data = array_merge($data, $module->extra_mail_vars);
                }
            }

            if (is_array($template_vars)) {
                $data = array_merge($data, $template_vars);
            }

            $context = \Context::getContext();
            $data['{total_paid}'] = \Tools::getContextLocale($context)->formatPrice(
                (float) $order->total_paid,
                \Currency::getIsoCodeById((int) $order->id_currency)
            );

            if (\Validate::isLoadedObject($order)) {
                // Attach invoice and / or delivery-slip if they exists and status is set to attach them
                if ($result['pdf_invoice'] || $result['pdf_delivery']) {
                    $invoice = $order->getInvoicesCollection();
                    $file_attachement = [];

                    if ($result['pdf_invoice'] && (int) \Configuration::get('PS_INVOICE') && $order->invoice_number) {
                        Hook::exec('actionPDFInvoiceRender', ['order_invoice_list' => $invoice]);
                        $pdf = new \PDF($invoice, \PDF::TEMPLATE_INVOICE, $context->smarty);
                        $file_attachement['invoice']['content'] = $pdf->render(false);
                        $file_attachement['invoice']['name'] = \Configuration::get(
                            'PS_INVOICE_PREFIX',
                            (int) $order->id_lang,
                            null,
                            $order->id_shop
                        ) . sprintf('%06d', $order->invoice_number) . '.pdf';
                        $file_attachement['invoice']['mime'] = 'application/pdf';
                    }
                    if ($result['pdf_delivery'] && $order->delivery_number) {
                        $pdf = new \PDF($invoice, PDF::TEMPLATE_DELIVERY_SLIP, $context->smarty);
                        $file_attachement['delivery']['content'] = $pdf->render(false);
                        $file_attachement['delivery']['name'] = \Configuration::get(
                            'PS_DELIVERY_PREFIX',
                            \Context::getContext()->language->id,
                            null,
                            $order->id_shop
                        ) . sprintf('%06d', $order->delivery_number) . '.pdf';
                        $file_attachement['delivery']['mime'] = 'application/pdf';
                    }
                } else {
                    $file_attachement = null;
                }

                if (
                    !\Mail::Send(
                        (int) $order->id_lang,
                        $result['template'],
                        $topic,
                        $data,
                        $result['email'],
                        $result['firstname'] . ' ' . $result['lastname'],
                        null,
                        null,
                        $file_attachement,
                        null,
                        _PS_MAIL_DIR_,
                        false,
                        (int) $order->id_shop
                    )
                ) {
                    return false;
                }
            }

            \ShopUrl::resetMainDomainCache();
        }

        return true;
    }

    public static function checkConfigurationServices(){

        $data = [];

        foreach (AdminHelper::getSortCurrencies() as $currency) {
            $data[$currency['iso_code']] =
                Helper::parseConfigByCurrency('BLUEPAYMENT_SERVICE_PARTNER_ID', $currency['iso_code']) == '' ||
                Helper::parseConfigByCurrency('BLUEPAYMENT_SHARED_KEY', $currency['iso_code']) == '' ? false : true;
        }

        return $data;
    }
}
