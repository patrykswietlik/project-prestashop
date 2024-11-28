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

use BlueMedia\OnlinePayments\Model\Gateway as GatewayModel;
use BluePayment\Config\Config;

class BlueGatewayChannels extends \ObjectModel implements GatewayInterface
{
    private $module;

    public const TABLE = 'blue_gateway_channels';
    public const PRIMARY = 'id_blue_gateway_channels';

    public $id_blue_gateway_channels;
    public $gateway_status;
    public $gateway_id;
    public $bank_name;
    public $gateway_name;
    public $gateway_description;
    public $position;
    public $gateway_currency;
    public $gateway_payments;
    public $gateway_type;
    public $gateway_logo_url;
    public $min_amount;
    public $max_amount;

    public static $definition = [
        'table' => self::TABLE,
        'primary' => self::PRIMARY,
        'fields' => [
            'id_blue_gateway_channels' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'gateway_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
            ],
            'gateway_status' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true,
            ],
            'bank_name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'required' => true,
                'size' => 100,
            ],
            'gateway_name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 100,
            ],
            'gateway_description' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
            ],
            'position' => [
                'type' => self::TYPE_INT,
                'validate' => 'isNullOrUnsignedId',
            ],
            'gateway_payments' => [
                'type' => self::TYPE_INT,
                'validate' => 'isNullOrUnsignedId',
            ],
            'gateway_currency' => [
                'type' => self::TYPE_STRING,
            ],
            'gateway_type' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 50,
                'required' => true,
            ],
            'gateway_logo_url' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isGenericName',
                'size' => 500,
            ],
            'min_amount' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
            ],
            'max_amount' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
            ],
        ],
    ];

    public function __construct($id_blue_gateway_channels = null, $id_lang = null, $id_shop = null)
    {
        parent::__construct($id_blue_gateway_channels, $id_lang, $id_shop);
        $this->module = new \BluePayment();

        if (\Shop::isFeatureActive()) {
            \Shop::addTableAssociation($this->table, ['type' => 'shop']);
        }
    }

    public function getOnlyGroups($group): array
    {
        $gatewayArray = [
            Config::GATEWAY_ID_BLIK,
            Config::GATEWAY_ID_BLIK_LATER,
            Config::GATEWAY_ID_ALIOR,
            Config::GATEWAY_ID_CARD,
            Config::GATEWAY_ID_PAYPO,
            Config::GATEWAY_ID_VISA_MOBILE,
            Config::GATEWAY_ID_SPINGO,
        ];

        return array_filter($group, function ($val) use ($gatewayArray) {
            return in_array($val->getGatewayId(), $gatewayArray);
        });
    }

    public function createTransferPaymentOption(): GatewayModel
    {
        $gateway = new GatewayModel();
        $gateway->setGatewayId((string) Config::GATEWAY_ID_TRANSFER)->setGatewayName('Przelew internetowy');
        $gateway->setGatewayType('1');
        $gateway->setBankName('Przelew internetowy');
        $gateway->setGatewayPayment('1');
        $gateway->setIconUrl($this->module->getAssetImages() . $this->getPaymentsIcon());

        return $gateway;
    }

    public function createWalletPaymentOption(): GatewayModel
    {
        $gateway = new GatewayModel();
        $gateway->setGatewayId((string) Config::GATEWAY_ID_WALLET)->setGatewayName('Wirtualny portfel');
        $gateway->setGatewayType('1');
        $gateway->setBankName('Wirtualny portfel');
        $gateway->setGatewayPayment('1');
        $gateway->setIconUrl($this->module->getAssetImages() . $this->getCardsIcon());

        return $gateway;
    }

    public function syncGateway($apiGateways, $currency, $position = 1): ?BlueGatewayChannels
    {
        $gatewayChannelsToRemove = $this->getGatewayChannels($currency['iso_code']);
        if (isset($gatewayChannelsToRemove) && !empty($gatewayChannelsToRemove)) {
            $gatewayChannelsToRemove = array_column($gatewayChannelsToRemove, 'gateway_id');
            $gatewayChannelsToRemove = array_flip($gatewayChannelsToRemove);
        }

        if ($apiGateways && $currency) {
            // Reset position by currency
            $paymentsGroup = $apiGateways->getGateways();
            $paymentOptionsArray = $this->getOnlyGroups($paymentsGroup);
            $paymentOptionsArray[Config::GATEWAY_ID_TRANSFER] = $this->createTransferPaymentOption();
            $paymentOptionsArray[Config::GATEWAY_ID_WALLET] = $this->createWalletPaymentOption();
            foreach ($paymentOptionsArray as $paymentGateway) {
                $payway = self::getByGatewayIdAndCurrency(
                    $paymentGateway->getGatewayId(),
                    $currency['iso_code']
                );

                if (isset($gatewayChannelsToRemove[$paymentGateway->getGatewayId()])) {
                    unset($gatewayChannelsToRemove[$paymentGateway->getGatewayId()]);
                }

                $payway->gateway_logo_url = $paymentGateway->getIconUrl();
                $payway->bank_name = $paymentGateway->getBankName();
                $payway->gateway_status = $payway->gateway_status !== null ? $payway->gateway_status : 1;
                $payway->gateway_name = $paymentGateway->getGatewayName();
                $payway->gateway_type = 1;
                $payway->gateway_currency = $currency['iso_code'];
                $payway->force_id = true;
                $payway->gateway_id = $paymentGateway->getGatewayId();

                if ($paymentGateway->getGatewayId() == '9999' || $paymentGateway->getGatewayId() == '999') {
                    $payway->gateway_payments = '1';
                }

                $payway->min_amount = $paymentGateway->getMinAmount();
                $payway->max_amount = $paymentGateway->getMaxAmount();

                if (!$payway->id) {
                    $maxPosition = $this->getMaxChannelsPositionsByCurrency($currency);
                    $payway->position = $maxPosition ? $maxPosition + 1 : 0;
                }

                $payway->save();
                ++$position;
            }

            foreach ($gatewayChannelsToRemove as $idGateway => $gatewayChannelToRemove) {
                $this->removeGatewayChannelByCurrency($idGateway, $currency);
            }
            $this->autoUpdatePosition($currency);

            return $payway;
        }

        \PrestaShopLogger::addLog('BM - Error sync gateway channels', 3);

        return null;
    }

    public function getPaymentsIcon(): string
    {
        return 'payments.png';
    }

    public function getCardsIcon(): string
    {
        return 'cards.png';
    }

    /**
     * @codeCoverageIgnore
     * Prestashop function overwrite change position gateway
     */
    public function getChannelsPositions($id, $shopId)
    {
        $q = new \DbQuery();
        $q->select('gc.id_blue_gateway_channels, gc.position');
        $q->from(self::TABLE, 'gc');
        $q->leftJoin('blue_gateway_channels_shop', 'gcs', 'gcs.id_blue_gateway_channels = gc.id_blue_gateway_channels');
        $q->where('gc.id_blue_gateway_channels = "' . (int) $id . '"');
        if (\Shop::isFeatureActive()) {
            $q->where('gcs.id_shop = ' . (int) $shopId);
        }
        $q->orderBy('gc.position ASC');

        return \Db::getInstance()->executeS($q);
    }

    /**
     * @codeCoverageIgnore
     * Prestashop function overwrite update position gateway
     */
    public function updatePosition($id, $way, $position): bool
    {
        $id_shop = \Context::getContext()->shop->id;
        $result = $this->getChannelsPositions($id, $id_shop);

        if ($result) {
            $movedBlock = false;

            foreach ($result as $block) {
                if ((int) $block['id_blue_gateway_channels'] == (int) $id) {
                    $movedBlock = $block;
                }
            }

            if ($movedBlock === false) {
                return false;
            }

            return \Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'blue_gateway_channels` SET
            `position`= `position` ' . ($way ? '- 1' : '+ 1')
                    . ' WHERE `position`' . ($way ? '> ' . (int) $movedBlock['position'] . ' AND `position` <= '
                        . (int) $position : '< ' . (int) $movedBlock['position'] . '
                        AND `position` >= ' . (int) $position))
                && \Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'blue_gateway_channels`
                SET `position` = ' . (int) $position . '
                WHERE `id_blue_gateway_channels`=' . (int) $movedBlock['id_blue_gateway_channels'])
            ;
        }

        return false;
    }

    public static function getChannelId($gatewayId, $currency)
    {
        $id_shop = \Context::getContext()->shop->id;

        $q = new \DbQuery();
        $q->select('gc.id_blue_gateway_channels');
        $q->from(self::TABLE, 'gc');
        $q->leftJoin('blue_gateway_channels_shop', 'gs', 'gs.id_blue_gateway_channels = gc.id_blue_gateway_channels');
        $q->where('gc.gateway_id = ' . (int) $gatewayId);
        $q->where('gc.gateway_currency = "' . pSql($currency) . '"');
        $q->where('gc.gateway_status = 1');

        if (\Shop::isFeatureActive()) {
            $q->where('gs.id_shop = ' . (int) $id_shop);
        }

        return \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($q);
    }

    /**
     * @param $gatewayId
     * @param $currency
     *
     * @return bool
     */
    public static function isChannelActive($gatewayId, $currency): bool
    {
        return (bool) self::getChannelId($gatewayId, $currency);
    }

    public static function getByGatewayIdAndCurrency($gatewayId, $currency): BlueGatewayChannels
    {
        return new BlueGatewayChannels(self::getChannelId($gatewayId, $currency));
    }

    public function removeGatewayCurrency($currency): bool
    {
        return \Db::getInstance()->delete(self::TABLE, 'gateway_currency = "' . $currency['iso_code'] . '"');
    }

    public function getGatewayChannels($isoCodeCurrency)
    {
        $idShop = \Context::getContext()->shop->id;

        $query = new \DbQuery();
        $query->select('gc.gateway_id');
        $query->from('blue_gateway_channels', 'gc');
        $query->leftJoin('blue_gateway_channels_shop', 'gcs', 'gcs.id_blue_gateway_channels = gc.id_blue_gateway_channels');
        $query->where('gc.gateway_currency = "' . pSql($isoCodeCurrency) . '"');
        $query->where('gc.gateway_status = 1');
        if (\Shop::isFeatureActive()) {
            $query->where('gcs.id_shop = ' . (int) $idShop);
        }

        return \Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
    }

    public function removeGatewayChannelByCurrency($idGatewayChannel, $currency): bool
    {
        return \Db::getInstance()->delete('blue_gateway_channels', 'gateway_id = ' . (int) $idGatewayChannel . ' AND gateway_currency = "' . pSQL($currency['iso_code']) . '"');
    }

    public function autoUpdatePosition($currency)
    {
        $channelsPosition = $this->getChannelsPositionsByCurrency($currency);
        $i = 0;
        foreach ($channelsPosition as $chanelPosition) {
            if ($chanelPosition['position'] != $i) {
                \Db::getInstance()->update(
                    self::TABLE,
                    ['position' => $i],
                    'id_blue_gateway_channels = ' . (int) $chanelPosition['id_blue_gateway_channels'] . ' AND gateway_currency = "' . pSQL($currency['iso_code']) . '"');
            }
            ++$i;
        }
    }

    public function getChannelsPositionsByCurrency($currency)
    {
        $q = new \DbQuery();
        $q->select('gc.id_blue_gateway_channels, gc.position');
        $q->from(self::TABLE, 'gc');
        $q->where('gc.gateway_currency = "' . pSQL($currency['iso_code']) . '"');
        $q->orderBy('gc.position ASC');

        return \Db::getInstance()->executeS($q);
    }

    public function getMaxChannelsPositionsByCurrency($currency)
    {
        $q = new \DbQuery();
        $q->select('MAX(gc.position)');
        $q->from(self::TABLE, 'gc');
        $q->where('gc.gateway_currency = "' . pSQL($currency['iso_code']) . '"');

        return \Db::getInstance()->getValue($q);
    }
}
