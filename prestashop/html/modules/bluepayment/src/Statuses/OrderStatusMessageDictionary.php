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

namespace BluePayment\Statuses;

if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class OrderStatusMessageDictionary
{
    public const PENDING = 1;

    public const ORDER_STATUS_MESSAGE = [
        self::PENDING => 'Payment in progress',
    ];

    public static function getMessage($order_status_id)
    {
        return self::hasKey($order_status_id) ? self::ORDER_STATUS_MESSAGE[$order_status_id] : null;
    }

    private static function hasKey($order_status_id): bool
    {
        return array_key_exists($order_status_id, self::ORDER_STATUS_MESSAGE);
    }
}
