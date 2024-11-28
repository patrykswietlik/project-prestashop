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

namespace BlueMedia\OnlinePayments\Action\PaywayList;

if (!defined('_PS_VERSION_')) {
    exit;
}

use BlueMedia\OnlinePayments\Gateway;
use BlueMedia\OnlinePayments\Model\Gateway as GatewayModel;
use BlueMedia\OnlinePayments\Model\PaywayList;

class Transformer
{
    /**
     * Transforms model into an array.
     *
     * @param PaywayList $model
     *
     * @return array
     */
    public static function modelToArray(PaywayList $model)
    {
        $result = [];
        $result['serviceID'] = $model->getServiceId();
        $result['messageID'] = $model->getMessageId();
        $result['gateway'] = [];

        if (is_array($model->getGateways())) {
            foreach ($model->getGateways() as $key => $gateway) {
                if ($gateway instanceof GatewayModel) {
                    $result['gateway'][] = [
                        'gatewayID' => $gateway->getGatewayId(),
                        'gatewayName' => $gateway->getGatewayName(),
                        'gatewayType' => $gateway->getGatewayType(),
                        'bankName' => $gateway->getBankName(),
                        'gatewayPayments' => $gateway->getGatewayPayment(),
                        'iconURL' => $gateway->getIconUrl(),
                        'statusDate' => ($gateway->getStatusDate() instanceof \DateTime)
                            ? $gateway->getStatusDate()->format(Gateway::DATETIME_FORMAT_LONGER) : '',
                    ];
                }
            }
        }

        $result['hash'] = $model->getHash();

        return $result;
    }

    /**
     * Transforms XML to model.
     *
     * @param \SimpleXMLElement $xml
     *
     * @return PaywayList
     */
    public static function toModel(\SimpleXMLElement $xml)
    {
        $model = new PaywayList();

        if ($xml->serviceID) {
            $model->setServiceId((string) $xml->serviceID);
        }

        if ($xml->messageID) {
            $model->setMessageId((string) $xml->messageID);
        }

        if (isset($xml->gateway)) {
            if (is_array($xml->gateway) || isset($xml->gateway[0])) {
                foreach ($xml->gateway as $key => $gateway) {
                    $gatewayModel = new GatewayModel();
                    $gatewayModel->setGatewayId((string) $gateway->gatewayID)
                        ->setGatewayName((string) $gateway->gatewayName);

                    if (isset($gateway->gatewayType)) {
                        $gatewayModel->setGatewayType((string) $gateway->gatewayType);
                    }

                    if (isset($gateway->bankName)) {
                        $gatewayModel->setBankName((string) $gateway->bankName);
                    }

                    if (isset($gateway->gatewayPayment)) {
                        $gatewayModel->setGatewayPayment((string) $gateway->gatewayPayment);
                    }

                    if (isset($gateway->iconURL)) {
                        $gatewayModel->setIconUrl((string) $gateway->iconURL);
                    }

                    if (isset($gateway->statusDate)) {
                        $gatewayModel->setStatusDate(
                            \DateTime::createFromFormat(
                                Gateway::DATETIME_FORMAT_LONGER,
                                (string) $gateway->statusDate,
                                new \DateTimeZone(Gateway::DATETIME_TIMEZONE)
                            )
                        );
                    }

                    $model->addGateway($gatewayModel);
                    unset($gatewayModel, $gateway);
                }
            }
        }

        if ($xml->hash) {
            $model->setHash((string) $xml->hash);
        }

        return $model;
    }

    /**
     * Transforms JSON to model.
     *
     * @param \stdClass $xml
     *
     * @return PaywayList
     */
    public static function JSONtoModel(\stdClass $json): PaywayList
    {
        $model = new PaywayList();

        if ($json->serviceID) {
            $model->setServiceId((string) $json->serviceID);
        }

        if ($json->messageID) {
            $model->setMessageId((string) $json->messageID);
        }

        if (isset($json->gatewayList)) {
            if (is_array($json->gatewayList) || isset($json->gatewayList[0])) {
                foreach ($json->gatewayList as $key => $gateway) {
                    $gatewayModel = new GatewayModel();
                    $gatewayModel->setGatewayId((string) $gateway->gatewayID)
                        ->setGatewayName((string) $gateway->gatewayName);

                    if (isset($gateway->gatewayType)) {
                        $gatewayModel->setGatewayType((string) $gateway->gatewayType);
                    }

                    if (isset($gateway->bankName)) {
                        $gatewayModel->setBankName((string) $gateway->bankName);
                    }

                    if (isset($gateway->gatewayPayment)) {
                        $gatewayModel->setGatewayPayment((string) $gateway->gatewayPayment);
                    }

                    if (isset($gateway->iconURL)) {
                        $gatewayModel->setIconUrl((string) $gateway->iconURL);
                    }

                    if (isset($gateway->statusDate)) {
                        $gatewayModel->setStatusDate(
                            \DateTime::createFromFormat(
                                Gateway::DATETIME_FORMAT_LONGER,
                                (string) $gateway->statusDate,
                                new \DateTimeZone(Gateway::DATETIME_TIMEZONE)
                            )
                        );
                    }

                    if (isset($gateway->currencyList)
                        && is_array($gateway->currencyList)
                        && isset($gateway->currencyList[0]->minAmount, $gateway->currencyList[0]->maxAmount)
                    ) {
                        $gatewayModel->setMinAmount($gateway->currencyList[0]->minAmount);
                        $gatewayModel->setMaxAmount($gateway->currencyList[0]->maxAmount);
                    }

                    $model->addGateway($gatewayModel);
                    unset($gatewayModel, $gateway);
                }
            }
        }

        if ($json->hash) {
            $model->setHash((string) $json->hash);
        }

        return $model;
    }
}
