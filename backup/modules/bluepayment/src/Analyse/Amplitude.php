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

namespace BluePayment\Analyse;

if (!defined('_PS_VERSION_')) {
    exit;
}

use BluePayment\Config\Config;

class Amplitude
{
    private static $instance;

    /**
     * @throws \Exception
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * @throws \Exception
     * @codeCoverageIgnore
     */
    private function __clone()
    {
        throw new \InvalidArgumentException('Not supported clone');
    }

    /**
     * @throws \Exception
     * @codeCoverageIgnore
     */
    public function __wakeup()
    {
        throw new \InvalidArgumentException('Not supported wakeup');
    }

    public static function getInstance(): Amplitude
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public static function getUserId(): string
    {
        return md5(\Context::getContext()->shop->getBaseURL(true));
    }

    public function sendEvent(array $data = [])
    {
        $data['api_key'] = $this->getAmplitudeId();
        $data['events']['time'] = time();
        $data['events']['user_id'] = self::getUserId();
        $data['events']['os_name'] = 'Prestashop';
        $data['events']['os_version'] = _PS_VERSION_;

        $payload = json_encode($data);
        $post_url = 'https://api2.amplitude.com/2/httpapi';

        $ch = curl_init($post_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type:application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public function getAmplitudeId(): string
    {
        return getenv('AMPLITUDE_ID') ? getenv('AMPLITUDE_ID') : '';
    }

    /**
     * Get gateway id by transaction
     *
     * @param $paymentStatus
     * @param $orderId
     *
     * @return void
     */
    public function sendOrderAmplitudeEvent($paymentStatus, $orderId): void
    {
        $gatewayId = $this->getPaymentGatewayId($orderId);
        $data = [
            'events' => [
                'event_type' => Config::PLUGIN_PAY_COMPLETED,
                'user_id' => self::getUserId(),
                'event_properties' => [
                    'payment type group' => $this->getPaymentGroupNameById($gatewayId),
                    'payment type channel' => $this->getPaymentNameByGatewayId($gatewayId),
                    'successful' => $paymentStatus,
                ],
            ],
        ];

        $this->sendEvent($data);
    }

    /**
     * Get gateway id by transaction
     *
     * @param $orderId
     *
     * @return mixed
     */
    public function getPaymentGatewayId($orderId)
    {
        $query = new \DbQuery();
        $query->select('gateway_id')->from('blue_transactions')->where('order_id = ' . (int) $orderId);

        return \Db::getInstance()->getValue($query);
    }

    /**
     * Get group name by order
     *
     * @param $gatewayId
     *
     * @return string
     */
    public function getPaymentGroupNameById($gatewayId): string
    {
        $module = \Module::getInstanceByName('bluepayment');
        $module->debug($gatewayId);

        switch ($gatewayId) {
            case Config::GATEWAY_ID_BLIK:
                $name = 'Blik';
                break;
            case Config::GATEWAY_ID_BLIK_LATER:
                $name = 'Blik Płacę Później';
                break;
            case Config::GATEWAY_ID_CARD:
                $name = 'Karta kredytowa';
                break;
            case Config::GATEWAY_ID_ALIOR:
                $name = 'Alior Raty';
                break;
            case Config::GATEWAY_ID_PAYPO:
                $name = 'PayPo';
                break;
            case Config::GATEWAY_ID_SPINGO:
                $name = 'Spingo';
                break;
            case Config::GATEWAY_ID_VISA_MOBILE:
                $name = 'Visa Mobile';
                break;
            case Config::GATEWAY_ID_APPLE_PAY:
            case Config::GATEWAY_ID_GOOGLE_PAY:
                $name = 'Wirtualny portfel';
                break;
            default:
                $name = 'Przelew internetowy';
        }
        $module->debug($name);

        return $name;
    }

    /**
     * Get payment name by order
     *
     * @param $gatewayId
     *
     * @return mixed
     */
    public function getPaymentNameByGatewayId($gatewayId)
    {
        $query = new \DbQuery();
        $query->select('gateway_name')->from('blue_gateway_transfers')->where('gateway_id = ' . (int) $gatewayId);

        return \Db::getInstance()->getValue($query);
    }
}
