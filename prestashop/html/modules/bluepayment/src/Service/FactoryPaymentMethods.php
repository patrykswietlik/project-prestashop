<?php
/**
 * NOTICE OF LICENSE
 * This source file is subject to the GNU Lesser General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/lgpl-3.0.en.html.
 *
 * @author     Autopay S.A.
 * @copyright  Since 2015 Autopay S.A.
 * @license    https://www.gnu.org/licenses/lgpl-3.0.en.html GNU Lesser General Public License
 */

declare(strict_types=1);

namespace BluePayment\Service;

if (!defined('_PS_VERSION_')) {
    exit;
}

use BluePayment\Config\Config;
use BluePayment\Service\PaymentMethods\MainGateway;

class FactoryPaymentMethods
{
    protected $module;
    protected $context;

    public function __construct(\BluePayment $module)
    {
        $this->module = $module;
        $this->context = $module->getContext();
    }

    public function getGroup()
    {
        if (!is_object(\Context::getContext()->currency)) {
            $currency = \Context::getContext()->currency['iso_code'];
        } else {
            $currency = \Context::getContext()->currency->iso_code;
        }
        $idShop = $this->context->shop->id;

        $q = new \DbQuery();
        $q->select('*');
        $q->from('blue_gateway_channels', 'gt');
        $q->leftJoin('blue_gateway_channels_shop', 'gts', 'gts.id_blue_gateway_channels = gt.id_blue_gateway_channels');
        $q->where('gt.gateway_status = 1');
        $q->where('gt.gateway_currency = "' . pSql($currency) . '"');
        if (\Shop::isFeatureActive()) {
            $q->where('gts.id_shop = ' . (int) $idShop);
        }
        $q->orderBy('gt.position');

        return \Db::getInstance()->executeS($q);
    }

    public function getPaymentMethodName($gatewayId): string
    {
        switch ($gatewayId) {
            case Config::GATEWAY_ID_TRANSFER:
                $gateway = 'InternetTransfer';
                break;
            case Config::GATEWAY_ID_WALLET:
                $gateway = 'VirtualWallet';
                break;
            case Config::GATEWAY_ID_CARD:
                $gateway = 'Card';
                break;
            case Config::GATEWAY_ID_ALIOR:
                $gateway = 'AliorInstallment';
                break;
            case Config::GATEWAY_ID_BLIK:
                $gateway = 'Blik';
                break;
            case Config::GATEWAY_ID_BLIK_LATER:
                $gateway = 'BlikLater';
                break;
            case Config::GATEWAY_ID_PAYPO:
                $gateway = 'PayPo';
                break;
            case Config::GATEWAY_ID_VISA_MOBILE:
                $gateway = 'VisaMobile';
                break;
            case Config::GATEWAY_ID_SPINGO:
                $gateway = 'Spingo';
                break;
            default:
                $gateway = '';
        }

        return $gateway;
    }

    /**
     * Create payments group.
     */
    public function create(): array
    {
        $availablePayments = [];
        foreach ($this->getGroup() as $item) {
            $className = $this->getPaymentMethodName($item['gateway_id']);
            $namespace = 'BluePayment\\Service\\PaymentMethods';
            $class = "$namespace\\$className";

            if (class_exists($class)) {
                $gateway = new Gateway(
                    $this->module,
                    new $class()
                );

                if ($gateway->isActive()) {
                    $availablePayments[] = $gateway->getPaymentOption($item);
                }
            }
        }

        return $availablePayments;
    }

    /**
     * Create single gateway.
     */
    public function single(): array
    {
        $availablePayments = [];
        $gateway = new Gateway(
            $this->module,
            new MainGateway()
        );
        if ($gateway->isActive()) {
            $availablePayments[] = $gateway->getPaymentOption();
        }

        return $availablePayments;
    }
}
