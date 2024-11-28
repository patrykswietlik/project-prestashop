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

namespace BlueMedia\OnlinePayments\Util;

if (!defined('_PS_VERSION_')) {
    exit;
}
class Sorter
{
    /**
     * @param array $params
     *
     * @return array
     */
    public static function sortTransactionParams(array $params)
    {
        $transactionParamsInOrder = [
            'ServiceID',
            'OrderID',
            'Amount',
            'Description',
            'GatewayID',
            'Currency',
            'CustomerEmail',
            'Language',
            'CustomerNRB',
            'TaxCountry',
            'CustomerIP',
            'Title',
            'ReceiverName',
            'BlikUIDKey',
            'BlikUIDLabel',
            'BlikAMKey',
            'ValidityTime',
            'LinkValidityTime',
            'receiverNRB',
            'receiverName',
            'receiverAddress',
            'remoteID',
            'bankHref',
            'AuthorizationCode',
            'ScreenType',
            'DefaultRegulationAcceptanceState',
            'DefaultRegulationAcceptanceID',
            'DefaultRegulationAcceptanceTime',
            'PlatformName',
            'PlatformVersion',
            'PlatformPluginVersion',
            'Hash',
        ];

        $result = [];
        $lowercaseKeysParams = array_change_key_case($params, CASE_LOWER);

        foreach ($transactionParamsInOrder as $paramName) {
            $lowercaseParamName = \strtolower($paramName);

            if (\array_key_exists($lowercaseParamName, $lowercaseKeysParams)) {
                $result[$paramName] = $lowercaseKeysParams[$lowercaseParamName];
            }
        }

        return $result;
    }
}
