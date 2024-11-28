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

use BluePayment\Analyse\AnalyticsTracking;
use Configuration as Cfg;

class AnaliticsHelper
{
    /**
     * Send events Google Analitics
     *
     * @param $orderId
     *
     * @return void
     */
    public function sendOrderGaAnalitics($orderId): array
    {
        $gaTrackerId = Cfg::get('BLUEPAYMENT_GA_TRACKER_ID');
        $ga4TrackerId = Cfg::get('BLUEPAYMENT_GA4_TRACKER_ID');
        $ga4Secret = Cfg::get('BLUEPAYMENT_GA4_SECRET');
        $gaType = Cfg::get('BLUEPAYMENT_GA_TYPE');

        if ($gaTrackerId || ($ga4TrackerId && $ga4Secret)) {
            // Get ga user session
            $query = new \DbQuery();
            $query->from('blue_transactions')
                ->where('order_id like "' . (int) $orderId . '-%"')
                ->where('gtag_state IS NULL')
                ->select('gtag_uid');
            $gaUserId = \Db::getInstance()->getValue($query, false);

            if (!empty($gaUserId)) {
                $args = [];
                $items = [];
                $orderGa = new \OrderCore($orderId);

                if ($orderGa->getProducts()) {
                    $p_key = 0;
                    foreach ($orderGa->getProducts() as $p) {
                        $brand = null;
                        $category_name = null;

                        if ($p['id_manufacturer']) {
                            $brand = \Manufacturer::getNameById($p['id_manufacturer']);
                        }

                        $cat = new \Category($p['id_category_default'], \Context::getContext()->language->id);

                        if ($cat) {
                            $category_name = $cat->name;
                        }

                        ++$p_key;

                        if ($gaType === '1') {
                            $args['pr' . $p_key . 'id'] = $p['product_id'];
                            $args['pr' . $p_key . 'nm'] = \Product::getProductName($p['product_id']);
                            $args['pr' . $p_key . 'br'] = $brand;
                            $args['pr' . $p_key . 'ca'] = $category_name;
                            $args['pr' . $p_key . 'pr'] = $p['total_price_tax_incl'];
                            $args['pr' . $p_key . 'qt'] = $p['product_quantity'];
                        } elseif ($gaType === '2') {
                            $items[$p_key - 1] = [
                                'item_id' => $p['product_id'],
                                'item_name' => \Product::getProductName($p['product_id']),
                                'item_brand' => $brand,
                                'item_category' => $category_name,
                                'price' => $p['total_price_tax_incl'],
                                'quantity' => $p['product_quantity'],
                            ];
                        }
                    }
                }

                if ($gaType === '1') {
                    // GA Universal
                    $analitics = new AnalyticsTracking($gaTrackerId, $gaUserId);

                    $args['cu'] = \Context::getContext()->currency->iso_code;
                    $args['ti'] = $orderGa->id_cart . '-' . time();
                    $args['tr'] = $orderGa->total_paid_tax_incl;
                    $args['tt'] = $orderGa->total_paid - $orderGa->total_paid_tax_excl;
                    $args['ts'] = $orderGa->total_shipping_tax_incl;
                    $args['pa'] = 'purchase';
                    $analitics->gaSendEvent('ecommerce', 'purchase', 'accepted', $args);
                } elseif ($gaType === '2') {
                    // GA 4
                    $analitics = new AnalyticsTracking($ga4TrackerId, $gaUserId, $ga4Secret);

                    $args['events'][] = [
                        'name' => 'purchase',
                        'params' => [
                            'items' => $items,
                            'currency' => \Context::getContext()->currency->iso_code,
                            'transaction_id' => $orderGa->id_cart . '-' . time(),
                            'value' => $orderGa->total_paid_tax_incl,
                            'tax' => $orderGa->total_paid - $orderGa->total_paid_tax_excl,
                            'shipping' => $orderGa->total_shipping_tax_incl,
                        ],
                    ];
                    $args['user_id'] = $orderGa->id_customer;
                    $analitics->ga4SendEvent($args);
                }

                // Reset state
                $transactionData = [
                    'gtag_state' => 1,
                ];

                return [
                    'order_id' => $orderId,
                    'transaction_data' => $transactionData,
                ];
            }
        }

        return [];
    }
}
